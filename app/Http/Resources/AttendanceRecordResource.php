<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;
// 出勤・退勤・休憩の時刻を計算するために使用する
use Carbon\Carbon;
// 分数を「HH:MM」形式に変換するために使用する
use Carbon\CarbonInterval;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // APIで公開する勤怠データの項目を指定する
    public function toArray(Request $request): array
    {
    // 休憩時間の合計（分）を入れる変数
    $totalBreakMinutes = 0;

    // この勤怠に紐づく休憩を1件ずつ確認する
    foreach ($this->breaks as $break) {

        // 休憩終了時刻が未登録の場合は計算しない
        if (!$break->break_out) {
        continue;
    }

        // 休憩開始・終了時刻をCarbonで扱える形にする
        $breakStart = Carbon::parse($break->break_in);
        $breakEnd = Carbon::parse($break->break_out);

        // 休憩時間を分単位で合計する
        $totalBreakMinutes += $breakStart->diffInMinutes($breakEnd);
    }
        // 合計休憩時間を「時間:分」の形式に変換する
        $breakHours = intdiv((int) $totalBreakMinutes, 60);
        $breakMinutes = (int) $totalBreakMinutes % 60;

        $totalBreakTime = sprintf('%02d:%02d', $breakHours, $breakMinutes);

    // 実働時間の初期値（退勤していない場合はnull）
    $totalTime = null;

    // 出勤時刻と退勤時刻が両方ある場合だけ計算する
    if ($this->clock_in && $this->clock_out) {

    $clockIn = Carbon::parse($this->clock_in);
    $clockOut = Carbon::parse($this->clock_out);

    $workMinutes = $clockIn->diffInMinutes($clockOut);

    $actualWorkMinutes = max(0, $workMinutes - $totalBreakMinutes);

    $workHours = intdiv((int) $actualWorkMinutes, 60);
    $workRemainingMinutes = (int) $actualWorkMinutes % 60;

    $totalTime = sprintf('%02d:%02d', $workHours, $workRemainingMinutes);
}

    return [
        // 勤怠データの基本情報
        'id' => $this->id,
        'date' => $this->date,
        'clock_in' => $this->clock_in,
        'clock_out' => $this->clock_out,

        // 勤怠を登録したユーザーのID
        'user_id' => $this->user_id,

        // 関連するユーザーの名前
        'user_name' => $this->user?->name,

        // 休憩時間の合計（例：01:00）
        'total_break_time' => $totalBreakTime,

        // 実働時間（例：08:00）
        'total_time' => $totalTime,

        // 勤怠の備考
        'comment' => $this->comment,
    ];
}
}
