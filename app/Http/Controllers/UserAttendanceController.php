<?php

namespace App\Http\Controllers;

// フォームやURLから送られたデータを受け取る
use Illuminate\Http\Request;

// 勤怠修正申請の入力チェックを行うFormRequest
use App\Http\Requests\AttendanceCorrectionFormRequest;

// ログイン中のユーザー情報を取得する
use Illuminate\Support\Facades\Auth;

// 複数のデータベース処理をまとめて実行する
use Illuminate\Support\Facades\DB;

// 勤怠データを操作するModel
use App\Models\Attendance;

// 勤怠修正申請を操作するModel
use App\Models\AttendanceCorrectionRequest;

// 修正申請に含まれる休憩時間を操作するModel
use App\Models\AttendanceCorrectionBreak;

// 日付や時刻の取得・計算・表示形式の変更に使用する
use Carbon\Carbon;

class UserAttendanceController extends Controller
{
    // =========================================
    // ① 一般ユーザーの月間勤怠一覧
    // =========================================
    public function index(Request $request)
    {
        // URLのdateパラメータから表示対象の月を取得する
        // dateが指定されていなければ今月を表示する
        // startOfMonth()で対象月の1日に合わせる
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->startOfMonth()
            : Carbon::today()->startOfMonth();

        // 前月・翌月に移動するリンク用の日付を作成する
        // copy()を使い、元の$dateを変更しないようにする
        $previousMonth = $date->copy()->subMonth()->format('Y-m-d');
        $nextMonth = $date->copy()->addMonth()->format('Y-m-d');

        // ログインユーザーの対象月の勤怠データを取得する
        // with('breaks')で関連する休憩データも一緒に取得する
        // whereYearとwhereMonthで年・月を絞り込む
        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get()

            // 日付をキーにして、各日の勤怠を探しやすくする
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->format('Y-m-d');
            });

        // Bladeに渡す1か月分の表示データを入れる配列
        $formattedAttendanceRecords = [];

        // 対象月の1日から月末まで1日ずつ処理する
        // 勤怠データがない日も一覧に表示するためのループ
        for (
            $day = $date->copy();
            $day->month === $date->month;
            $day->addDay()
        ) {
            // 処理中の日付に対応する勤怠データを取得する
            $attendance = $attendances->get($day->format('Y-m-d'));

            // 休憩合計時間を0分で初期化する
            $totalBreakMinutes = 0;

            // 勤務時間は退勤が完了するまで未確定とする
            $totalWorkMinutes = null;

            // その日の勤怠データがある場合だけ計算する
            if ($attendance) {

                // その日のすべての休憩を順番に確認する
                foreach ($attendance->breaks as $break) {

                    // 休憩開始と終了が両方登録されている場合
                    if ($break->break_in && $break->break_out) {

                        // 休憩開始から終了までの分数を合計する
                        $totalBreakMinutes += Carbon::parse($break->break_in)
                            ->diffInMinutes(Carbon::parse($break->break_out));
                    }
                }

                // 出勤・退勤時刻が両方ある場合だけ勤務時間を計算する
                if ($attendance->clock_in && $attendance->clock_out) {

                    // 退勤－出勤－休憩合計＝実際の勤務時間
                    $totalWorkMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out))
                        - $totalBreakMinutes;
                }
            }

            // 1日分の勤怠データを画面表示用に整える
            $formattedAttendanceRecords[] = [

                // 勤怠ID。データがない日はnull
                'id' => $attendance?->id,

                // 日付を「10/08 (木)」などの形式にする
                'date' => $day->format('m/d') . ' (' . $day->isoFormat('ddd') . ')',

                // 出勤時刻を「09:00」形式にする
                // 登録されていなければ空欄にする
                'clock_in' => $attendance?->clock_in
                    ? Carbon::parse($attendance->clock_in)->format('H:i')
                    : '',

                // 退勤時刻を「18:00」形式にする
                'clock_out' => $attendance?->clock_out
                    ? Carbon::parse($attendance->clock_out)->format('H:i')
                    : '',

                // 休憩合計を「01:00」形式に変換する
                // intdiv()は整数の割り算で時間部分を求める
                // % 60で残りの分を求める
                'total_break_time' => $attendance && $totalBreakMinutes > 0
                    ? sprintf(
                        '%02d:%02d',
                        intdiv($totalBreakMinutes, 60),
                        $totalBreakMinutes % 60
                    )
                    : '',

                // 勤務時間を「08:00」形式に変換する
                // max(0, ...)でマイナス表示を防ぐ
                'total_time' => $totalWorkMinutes !== null
                    ? sprintf(
                        '%02d:%02d',
                        intdiv(max(0, $totalWorkMinutes), 60),
                        max(0, $totalWorkMinutes) % 60
                    )
                    : '',
            ];
        }

        // 対象月・前月・翌月・勤怠一覧をBladeに渡す
        return view('user.user-attendance-list', compact(
            'date',
            'previousMonth',
            'nextMonth',
            'formattedAttendanceRecords'
        ));
    }


    // =========================================
    // ② 一般ユーザーの勤怠詳細
    // =========================================
    public function show($id)
    {
        // ログイン中のユーザーを取得する
        $user = Auth::user();

        // 指定されたIDの勤怠を休憩データと一緒に取得する
        // 自分の勤怠だけを対象にする
        // 見つからなければ404エラーになる
        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // 勤怠の日付をCarbonで扱える形式にする
        $attendanceDate = Carbon::parse($attendance->date);

        // この勤怠に承認待ちの修正申請があるか確認する
        // approval_status = 0 は承認待ち
        // 修正後の休憩データも一緒に取得する
        $pendingApplication = AttendanceCorrectionRequest::with('correctionBreaks')
            ->where('attendance_id', $attendance->id)
            ->where('approval_status', 0)
            ->first();

        // 通常は元の勤怠データを表示する
        $clockIn = $attendance->clock_in;
        $clockOut = $attendance->clock_out;

        // 元の休憩データを画面表示用の配列に変換する
        // map()は各データを順番に加工するメソッド
        $breaks = $attendance->breaks->map(function ($break) {
            return [
                'break_in' => $break->break_in
                    ? Carbon::parse($break->break_in)->format('H:i')
                    : '',
                'break_out' => $break->break_out
                    ? Carbon::parse($break->break_out)->format('H:i')
                    : '',
            ];
        })->toArray();

        // 申請理由は初期状態では空欄
        $comment = '';

        // 承認待ちの申請がある場合は、申請内容を優先表示する
        if ($pendingApplication) {

            // 修正申請で入力された出勤・退勤時刻に切り替える
            $clockIn = $pendingApplication->new_clock_in;
            $clockOut = $pendingApplication->new_clock_out;

            // 申請された休憩時間に切り替える
            $breaks = $pendingApplication->correctionBreaks
                ->map(function ($break) {
                    return [
                        'break_in' => Carbon::parse($break->new_break_in)->format('H:i'),
                        'break_out' => Carbon::parse($break->new_break_out)->format('H:i'),
                    ];
                })->toArray();

            // 修正申請の理由を表示する
            $comment = $pendingApplication->comment;
        }

        // Bladeに渡す勤怠詳細データをまとめる
        $data = [
            'id' => $attendance->id,
            'year' => $attendanceDate->format('Y年'),
            'date' => $attendanceDate->format('n月j日'),

            // 出勤時刻を時:分の形式に整える
            'clock_in' => $clockIn
                ? Carbon::parse($clockIn)->format('H:i')
                : '',

            // 退勤時刻を時:分の形式に整える
            'clock_out' => $clockOut
                ? Carbon::parse($clockOut)->format('H:i')
                : '',

            // 休憩時間・申請理由・承認待ち申請を渡す
            'breaks' => $breaks,
            'comment' => $comment,
            'application' => $pendingApplication,
        ];

        // 勤怠詳細画面を表示する
        return view('user.user-detail', compact('user', 'data'));
    }


    // =========================================
    // ③ 一般ユーザーの勤怠修正申請を保存する
    // =========================================
    public function store(AttendanceCorrectionFormRequest $request, $id)
    {
        // 指定された勤怠がログインユーザー本人のものか確認する
        $attendance = Attendance::where('user_id', Auth::id())
            ->findOrFail($id);

        // 同じ勤怠に承認待ちの申請が存在するか確認する
        // exists()は該当データの有無をtrue/falseで返す
        $hasPendingRequest = AttendanceCorrectionRequest::where(
            'attendance_id',
            $attendance->id
        )
            ->where('approval_status', 0)
            ->exists();

        // 承認待ちがある場合は新しい申請を受け付けない
        if ($hasPendingRequest) {
        // 仕様書PG05の勤怠詳細画面へ戻す
            return redirect('/attendance/detail/' . $id)
        ->with('message', '承認待ちのため修正できません');
}

        // フォームから送られた入力内容をチェックする
        // required：必須入力
        // date_format:H:i：時:分の形式
        // after：出勤より退勤が後であること
        // nullable：未入力でもよい
        // array：配列形式
        // *：配列内の各項目をチェックする
        // FormRequestでチェック済みの入力データを取得する
        $validated = $request->validated();

        // 入力された休憩データをまとめる配列
        $breaks = [];

        // 休憩開始・終了時刻をそれぞれ配列で取得する
        // 入力がない場合は空の配列にする
        $breakIns = $request->input('new_break_in', []);
        $breakOuts = $request->input('new_break_out', []);

        // 休憩開始時刻を1件ずつ確認する
        foreach ($breakIns as $index => $breakIn) {

            // 同じ位置にある休憩終了時刻を取得する
            // ?? null は値がなければnullにする
            $breakOut = $breakOuts[$index] ?? null;

            // 開始と終了が両方空欄なら保存対象にしない
            // continueで次の休憩データへ進む
            if (blank($breakIn) && blank($breakOut)) {
                continue;
            }

            // 片方だけ入力されていたらエラーにする
            // withErrors()でエラーを画面へ渡す
            // withInput()で入力済みの内容を保持する
            if (blank($breakIn) || blank($breakOut)) {
                return back()->withErrors([
                    "new_break_in.$index" =>
                        '休憩開始と終了の両方を入力してください。',
                ])->withInput();
            }

            // 休憩終了が開始より後になっているか確認する
            if ($breakIn >= $breakOut) {
                return back()->withErrors([
                    "new_break_out.$index" =>
                        '休憩時間もしくは退勤時間が不適切な値です',
                ])->withInput();
            }

            // 休憩開始が出勤前、または退勤後の場合
            if (
                $breakIn < $validated['new_clock_in'] ||
                $breakIn > $validated['new_clock_out']
            ) {
                return back()->withErrors([
                    "new_break_in.$index" =>
                        '休憩時間が不適切な値です',
                ])->withInput();
            }

            // 休憩終了が退勤後の場合
            if ($breakOut > $validated['new_clock_out']) {
                return back()->withErrors([
                    "new_break_out.$index" =>
                        '休憩時間もしくは退勤時間が不適切な値です',
                ])->withInput();
            }

            // チェックを通過した休憩データを配列に追加する
            $breaks[] = [
                'new_break_in' => $breakIn,
                'new_break_out' => $breakOut,
            ];
        }

        // 修正申請と休憩修正をまとめて保存する
        // transaction()は途中でエラーが出た場合に
        // 一連のデータベース変更を取り消すための仕組み
        // use()で外側の変数を処理の中へ渡す
        DB::transaction(function () use (
            $attendance,
            $validated,
            $breaks
        ) {

            // attendance_correction_requestsテーブルに申請を作成
            // 元のattendancesテーブルはここでは変更しない
            // approval_status = 0 で承認待ちとして保存する
            $correctionRequest = AttendanceCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'new_clock_in' => $validated['new_clock_in'],
                'new_clock_out' => $validated['new_clock_out'],
                'comment' => $validated['comment'],
                'approval_status' => 0,
            ]);

            // 申請された休憩時間を1件ずつ保存する
            foreach ($breaks as $break) {

                // attendance_correction_breaksテーブルに保存
                // request_idで修正申請と休憩修正を紐づける
                AttendanceCorrectionBreak::create([
                    'request_id' => $correctionRequest->id,
                    'new_break_in' => $break['new_break_in'],
                    'new_break_out' => $break['new_break_out'],
                ]);
            }
        });

        // 修正申請の保存後は、仕様書PG05の勤怠詳細画面へ戻る
        return redirect('/attendance/detail/' . $id)
            ->with('message', '修正申請を送信しました');
    }


    // =========================================
    // ④ 一般ユーザーの修正申請一覧
    // =========================================
    public function applicationIndex()
    {
        // ログイン中のユーザーを取得する
        $user = Auth::user();

        // 自分の勤怠に紐づく修正申請だけを取得する
        // with('attendance')で元の勤怠データも取得する
        // whereHas()で関連する勤怠のuser_idを確認する
        // orderByDesc()で申請日時の新しい順に並べる
        $applications = AttendanceCorrectionRequest::with('attendance')
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get();

        // 取得した申請をBlade表示用の形式に変換する
        $formattedApplications = $applications->map(function ($application) {
            return [
                // 修正申請そのもののID
                'id' => $application->id,

                // 承認状態の数値を日本語に変換する
                // 0：承認待ち、1：承認済み
                'approval_status' => $application->approval_status == 0
                    ? '承認待ち'
                    : '承認済み',

                // 修正対象の勤怠日付
                'date' => Carbon::parse(
                    $application->attendance->date
                )->format('Y/m/d'),

                // 申請理由
                'comment' => $application->comment,

                // 申請を作成した日時
                'application_date' => Carbon::parse(
                    $application->created_at
                )->format('Y/m/d H:i'),
            ];
        });

        // ユーザー情報と修正申請一覧をBladeへ渡す
        return view('user.user-application-list', compact(
            'user',
            'formattedApplications'
        ));
    }


    // =========================================
    // ⑤ 修正申請一覧から勤怠詳細へ移動
    // =========================================
    public function applicationShow($id)
    {
        // 指定された修正申請を取得する
        // 自分の勤怠に紐づく申請だけを対象にする
        // 他人の申請や存在しない申請は404エラーになる
        $application = AttendanceCorrectionRequest::whereHas(
            'attendance',
            function ($query) {
                $query->where('user_id', Auth::id());
            }
        )->findOrFail($id);

        // 申請に紐づく元の勤怠IDを使い、
        // 仕様書PG05の勤怠詳細画面へ移動する
        return redirect('/attendance/detail/' . $application->attendance_id);
    }
}
