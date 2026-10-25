<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceCorrectionRequest;

class AdminAttendanceController extends Controller
{
    // 管理者の日別勤怠一覧
    public function index(Request $request)
    {
        // 表示する日付を決める
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $previousDay = $date->copy()->subDay()->format('Y-m-d');
        $nextDay = $date->copy()->addDay()->format('Y-m-d');

        // 全スタッフを取得
        $users = User::orderBy('id')->get();

        // 指定日の勤怠を休憩データと一緒に取得
        $attendances = Attendance::with('breaks')
            ->whereDate('date', $date->format('Y-m-d'))
            ->get()
            ->keyBy('user_id');

        // 画面表示用のデータを作成
        $attendanceRecords = $users->map(function ($user) use ($attendances) {
            $attendance = $attendances->get($user->id);

            $totalBreakMinutes = 0;
            $totalWorkMinutes = null;

            if ($attendance) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $totalBreakMinutes += Carbon::parse($break->break_in)
                            ->diffInMinutes(Carbon::parse($break->break_out));
                    }
                }

                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalWorkMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out))
                        - $totalBreakMinutes;
                }
            }

            return [
                'id' => $attendance?->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'clock_in' => $attendance?->clock_in
                    ? Carbon::parse($attendance->clock_in)->format('H:i')
                    : '',
                'clock_out' => $attendance?->clock_out
                    ? Carbon::parse($attendance->clock_out)->format('H:i')
                    : '',
                'total_break_time' => $totalBreakMinutes > 0
                    ? sprintf(
                        '%02d:%02d',
                        intdiv($totalBreakMinutes, 60),
                        $totalBreakMinutes % 60
                    )
                    : '',
                'total_time' => $totalWorkMinutes !== null
                    ? sprintf(
                        '%02d:%02d',
                        intdiv(max(0, $totalWorkMinutes), 60),
                        max(0, $totalWorkMinutes) % 60
                    )
                    : '',
            ];
        });

        return view('admin.admin-attendance-list', compact(
            'date',
            'previousDay',
            'nextDay',
            'users',
            'attendanceRecords'
        ));
    }

    // 管理者の勤怠詳細
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        $user = $attendance->user;
        $attendanceDate = Carbon::parse($attendance->date);

        $attendanceRecord = [
            'id' => $attendance->id,
            'year' => $attendanceDate->format('Y年'),
            'date' => $attendanceDate->format('n月j日'),
            'clock_in' => $attendance->clock_in
                ? Carbon::parse($attendance->clock_in)->format('H:i')
                : '',
            'clock_out' => $attendance->clock_out
                ? Carbon::parse($attendance->clock_out)->format('H:i')
                : '',
            'breaks' => $attendance->breaks->map(function ($break) {
                return [
                    'break_in' => $break->break_in
                        ? Carbon::parse($break->break_in)->format('H:i')
                        : '',
                    'break_out' => $break->break_out
                        ? Carbon::parse($break->break_out)->format('H:i')
                        : '',
                ];
            })->toArray(),
            'comment' => '',
        ];

        return view('admin.admin-detail', compact(
            'user',
            'attendanceRecord'
        ));
    }

    // 管理者による勤怠の直接修正
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 入力内容をチェック
        $validated = $request->validate([
            'new_clock_in' => ['required', 'date_format:H:i'],
            'new_clock_out' => [
                'required',
                'date_format:H:i',
                'after:new_clock_in',
            ],
            'comment' => ['required', 'string', 'max:1000'],
            'new_break_in' => ['nullable', 'array'],
            'new_break_out' => ['nullable', 'array'],
            'new_break_in.*' => ['nullable', 'date_format:H:i'],
            'new_break_out.*' => ['nullable', 'date_format:H:i'],
        ]);

        $breakIns = $request->input('new_break_in', []);
        $breakOuts = $request->input('new_break_out', []);
        $breaks = [];

        foreach (array_unique(array_merge(
            array_keys($breakIns),
            array_keys($breakOuts)
        )) as $index) {
            $breakIn = $breakIns[$index] ?? null;
            $breakOut = $breakOuts[$index] ?? null;

            // 両方空欄なら保存しない
            if (blank($breakIn) && blank($breakOut)) {
                continue;
            }

            // 片方だけ入力された場合
            if (blank($breakIn) || blank($breakOut)) {
                return back()->withErrors([
                    "new_break_in.$index" =>
                        '休憩開始と終了の両方を入力してください。',
                ])->withInput();
            }

            // 休憩の時刻を確認
            if ($breakIn >= $breakOut) {
                return back()->withErrors([
                    "new_break_out.$index" =>
                        '休憩終了は開始より後にしてください。',
                ])->withInput();
            }

            if (
                $breakIn < $validated['new_clock_in'] ||
                $breakOut > $validated['new_clock_out']
            ) {
                return back()->withErrors([
                    "new_break_out.$index" =>
                        '休憩時間は出勤から退勤までの間にしてください。',
                ])->withInput();
            }

            $breaks[] = [
                'break_in' => $breakIn,
                'break_out' => $breakOut,
            ];
        }

        // 勤怠と休憩をまとめて更新
        DB::transaction(function () use ($attendance, $validated, $breaks) {
            $attendance->update([
                'clock_in' => $validated['new_clock_in'],
                'clock_out' => $validated['new_clock_out'],
            ]);

            // 既存の休憩を削除し、修正後の休憩を保存
            $attendance->breaks()->delete();

            foreach ($breaks as $break) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_in' => $break['break_in'],
                    'break_out' => $break['break_out'],
                ]);
            }
        });

        return redirect('/admin/attendance/' . $id)
            ->with('message', '勤怠を修正しました');
    }

    
    // 管理者の修正申請一覧
    public function applicationIndex()
    {
        $applications = AttendanceCorrectionRequest::with([
            'attendance.user'
        ])
            ->orderByDesc('created_at')
            ->get();

        $formattedApplications = $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'approval_status' => $application->approval_status == 0
                    ? '承認待ち'
                    : '承認済み',
                'name' => $application->attendance->user->name,
                'date' => Carbon::parse(
                    $application->attendance->date
                )->format('Y/m/d'),
                'comment' => $application->comment,
                'application_date' => Carbon::parse(
                    $application->created_at
                )->format('Y/m/d H:i'),
            ];
        });

        return view('admin.admin-application-list', [
            'applications' => $formattedApplications,
        ]);
    }
    
    // 管理者の修正申請詳細
    public function applicationShow($id)
    {
        // 申請・勤怠・スタッフ・休憩修正をまとめて取得
        $application = AttendanceCorrectionRequest::with([
            'attendance.user',
            'correctionBreaks',
        ])->findOrFail($id);

        $user = $application->attendance->user;

        return view('admin.admin-application-detail', compact(
            'application',
            'user'
        ));
    }
    
    // 管理者による修正申請の承認
    public function applicationApprove($id)
    {
        DB::transaction(function () use ($id) {

            $application = AttendanceCorrectionRequest::with(
                'correctionBreaks'
            )->lockForUpdate()->findOrFail($id);

            // 承認済みなら再処理しない
            if ($application->approval_status != 0) {
                return;
            }

            // 元の勤怠データを修正
            $attendance = Attendance::findOrFail(
                $application->attendance_id
            );

            $attendance->update([
                'clock_in' => $application->new_clock_in,
                'clock_out' => $application->new_clock_out,
            ]);

            // 元の休憩データを削除
            $attendance->breaks()->delete();

            // 申請された休憩時間を保存
            foreach ($application->correctionBreaks as $break) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_in' => $break->new_break_in,
                    'break_out' => $break->new_break_out,
                ]);
            }

            // 申請を承認済みに変更
            $application->update([
                'approval_status' => 1,
            ]);
        });

        return redirect('/stamp_correction_request/list/admin');
    }
    // 管理者のスタッフ一覧
public function staffIndex()
{
    // 登録されているスタッフを取得
    $users = User::orderBy('id')->get();

    // スタッフ一覧画面にデータを渡す
    return view('admin.staff-list', [
        'users' => $users,
    ]);
}
    
    // 管理者：スタッフ別の月間勤怠一覧
    public function staffAttendanceIndex(Request $request, $id)
    {
        // スタッフIDから対象のユーザーを取得
        $user = User::findOrFail($id);

        // 表示する月を取得（指定がなければ今月）
        $date = $request->filled('date')
            ? Carbon::parse($request->query('date'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        // 前月・翌月のリンク用データ
        $previousMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        // 対象スタッフの指定月の勤怠を取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m-d');
            });

        $formattedAttendanceRecords = collect();

        // 月初から月末まで、1日ずつ勤怠データを作成
        for ($day = $date->copy(); $day->month === $date->month; $day->addDay()) {
            $attendance = $attendances->get($day->format('Y-m-d'));

            $clockIn = $attendance && $attendance->clock_in
                ? Carbon::parse($attendance->clock_in)
                : null;

            $clockOut = $attendance && $attendance->clock_out
                ? Carbon::parse($attendance->clock_out)
                : null;

            // 休憩時間を分単位で合計
            $breakMinutes = 0;

            if ($attendance) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakMinutes += Carbon::parse($break->break_in)
                            ->diffInMinutes(Carbon::parse($break->break_out));
                    }
                }
            }

            // 勤務時間から休憩時間を引く
            $workMinutes = null;

            if ($clockIn && $clockOut) {
                $workMinutes = max(
                    0,
                    $clockIn->diffInMinutes($clockOut) - $breakMinutes
                );
            }

            // Bladeに渡す1日分のデータ
            $formattedAttendanceRecords->push([
                'id' => $attendance?->id,
                'date' => $day->format('m/d') . ' (' .
                    ['日', '月', '火', '水', '木', '金', '土'][$day->dayOfWeek] . ')',
                'clock_in' => $clockIn?->format('H:i') ?? '',
                'clock_out' => $clockOut?->format('H:i') ?? '',
                'total_break_time' => $attendance && $breakMinutes > 0
                    ? sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                    : '',
                'total_time' => $workMinutes !== null
                    ? sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
                    : '',
            ]);
        }

        // スタッフ別の月間勤怠一覧を表示
        return view('admin.staff-attendance-list', compact(
            'user',
            'date',
            'previousMonth',
            'nextMonth',
            'formattedAttendanceRecords'
        ));
    }

    // 管理者：スタッフの月間勤怠をCSV出力
    public function exportCsv(Request $request)
    {
        // 対象スタッフと年月をチェック
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'year_month' => ['required', 'date_format:Y-m'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $date = Carbon::createFromFormat(
            '!Y-m',
            $validated['year_month']
        );

        // 対象月の勤怠データを取得
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        // CSVファイル名
        $filename = 'attendance_' . $user->id . '_'
            . $date->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($attendances) {
            $file = fopen('php://output', 'w');

            // Excelで日本語を表示するためのBOM
            fwrite($file, "\xEF\xBB\xBF");

            // CSVの見出し
            fputcsv($file, [
                '日付', '出勤', '退勤', '休憩', '合計',
            ]);

            foreach ($attendances as $attendance) {
                $breakMinutes = 0;

                // 休憩時間を合計
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakMinutes += Carbon::parse($break->break_in)
                            ->diffInMinutes(Carbon::parse($break->break_out));
                    }
                }

                $clockIn = $attendance->clock_in
                    ? Carbon::parse($attendance->clock_in)
                    : null;

                $clockOut = $attendance->clock_out
                    ? Carbon::parse($attendance->clock_out)
                    : null;

                $workMinutes = ($clockIn && $clockOut)
                    ? max(0, $clockIn->diffInMinutes($clockOut) - $breakMinutes)
                    : null;

                // 1日分の勤怠をCSVに書き込む
                fputcsv($file, [
                    Carbon::parse($attendance->date)->format('Y/m/d'),
                    $clockIn?->format('H:i') ?? '',
                    $clockOut?->format('H:i') ?? '',
                    sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60),
                    $workMinutes !== null
                        ? sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60)
                        : '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

}
