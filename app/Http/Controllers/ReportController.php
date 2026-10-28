<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // ログインユーザーの勤怠レポートを表示する
    public function index()
    {
        // 今月を含む過去6か月の開始日
        $startDate = Carbon::now()
            ->startOfMonth()
            ->subMonths(5);

        // ログインユーザーの対象期間の勤怠を取得する
        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', Carbon::today()->toDateString())
            ->orderBy('date')
            ->get();

        // 1日ごとの実労働時間を計算する
        $dailyWorkMinutes = [];

        foreach ($attendances as $attendance) {

        // 出勤・退勤がそろっていない日は集計しない
        if (!$attendance->clock_in || !$attendance->clock_out) {
        continue;
    }

        // その日の休憩時間を合計する
        $breakMinutes = $attendance->breaks->sum(function ($break) {
        if (!$break->break_in || !$break->break_out) {
            return 0;
        }

        return Carbon::parse($break->break_in)
            ->diffInMinutes(Carbon::parse($break->break_out));
    });

        // 出勤から退勤までの時間から休憩を引く
        $workMinutes = Carbon::parse($attendance->clock_in)
        ->diffInMinutes(Carbon::parse($attendance->clock_out));

        $dailyWorkMinutes[$attendance->id] = max(0, $workMinutes - $breakMinutes);
}
        // 過去6か月の総労働時間を計算する
        $totalWorkMinutes = array_sum($dailyWorkMinutes);

        // 1日8時間を超えた分を残業時間として合計する
        $totalOvertimeMinutes = 0;

        foreach ($dailyWorkMinutes as $workMinutes) {
        $totalOvertimeMinutes += max(0, $workMinutes - 480);
}

        // 実労働時間が1分以上ある日だけを数える
        $workDays = count(array_filter($dailyWorkMinutes, function ($minutes) {
            return $minutes > 0;
}));

        // 1日あたりの平均労働時間を計算する
        $avgWorkMinutes = $workDays > 0
        ? intdiv($totalWorkMinutes, $workDays)
        : 0;

        // 基本サマリーとして集計結果をまとめる
        $summary = [
        'total_work_minutes' => $totalWorkMinutes,
        'total_overtime_minutes' => $totalOvertimeMinutes,
        'avg_work_minutes' => $avgWorkMinutes,
];
        // 過去6か月の月次推移を保存する配列
        $monthlyTrend = [];

        // 5か月前から今月まで、6か月分を順番に用意する
        for ($i = 5; $i >= 0; $i--) {
        $month = Carbon::now()->startOfMonth()->subMonths($i);

        $monthlyTrend[] = [
        'month' => $month->format('Y-m'),
        'work_minutes' => 0,
        'overtime_minutes' => 0,
    ];
}
        // 月ごとに労働時間と残業時間を集計する
        foreach ($attendances as $attendance) {

        // 出勤・退勤がそろっていない日は集計しない
        if (!isset($dailyWorkMinutes[$attendance->id])) {
        continue;
    }

        // 勤怠の日付から年月を取得する
        $attendanceMonth = Carbon::parse($attendance->date)->format('Y-m');

        // その日の労働時間と残業時間
        $workMinutes = $dailyWorkMinutes[$attendance->id];
        $overtimeMinutes = max(0, $workMinutes - 480);

        // 該当する月に時間を加算する
        foreach ($monthlyTrend as &$row) {
        if ($row['month'] === $attendanceMonth) {
            $row['work_minutes'] += $workMinutes;
            $row['overtime_minutes'] += $overtimeMinutes;
            break;
        }
    }
        unset($row);
}

        // 今月の異常検知の集計結果を保存する
        $anomalies = [
        'late_count' => 0,
        'early_leave_count' => 0,
        'long_work_count' => 0,
];
                // 今月の遅刻回数を集計する
        foreach ($attendances as $attendance) {
            // 今月以外の勤怠は対象外
            if (Carbon::parse($attendance->date)->format('Y-m') !== Carbon::now()->format('Y-m')) {
                continue;
            }

            // 9時より後に出勤した場合は遅刻として数える
            if ($attendance->clock_in && Carbon::parse($attendance->clock_in)->format('H:i:s') > '09:00:00') {
                $anomalies['late_count']++;
            }
        }
                    // 今月の早退回数を集計する
        foreach ($attendances as $attendance) {
            // 今月以外の勤怠は対象外
            if (Carbon::parse($attendance->date)->format('Y-m') !== Carbon::now()->format('Y-m')) {
                continue;
            }

            // 18時より前に退勤した場合は早退として数える
            if ($attendance->clock_out && Carbon::parse($attendance->clock_out)->format('H:i:s') < '18:00:00') {
                $anomalies['early_leave_count']++;
            }
        }
                    // 今月の長時間労働の日数を集計する
        foreach ($attendances as $attendance) {
            // 今月以外の勤怠は対象外
            if (Carbon::parse($attendance->date)->format('Y-m') !== Carbon::now()->format('Y-m')) {
                continue;
            }

            // 実労働時間が計算されていない日は対象外
            if (!isset($dailyWorkMinutes[$attendance->id])) {
                continue;
            }

            // 実労働時間が10時間を超えた日を数える
            if ($dailyWorkMinutes[$attendance->id] > 600) {
                $anomalies['long_work_count']++;
            }
        }
            // 集計結果をレポート画面に渡す
            return view('reports.index', compact(
                'summary',
                'monthlyTrend',
                'anomalies'
        ));
    }
}