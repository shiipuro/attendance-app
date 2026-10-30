<?php

namespace Database\Seeders;

// Seederの基本機能
use Illuminate\Database\Seeder;

// 勤怠データを登録するためのモデル
use App\Models\Attendance;

// 休憩データを登録するためのモデル
use App\Models\AttendanceBreak;

// 一般ユーザーを取得するためのモデル
use App\Models\User;

// 日付を操作するための機能
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // =========================================
    // ① 一般ユーザー3人の勤怠データを作成
    // =========================================

    // 登録済みの一般ユーザーを取得する
    $users = User::whereIn('email', [
        'user1@example.com',
        'user2@example.com',
        'user3@example.com',
    ])->get();

    // 各ユーザーについて勤怠データを作成する
    foreach ($users as $user) {

        // 今月を含む過去5か月分
        for ($month = 4; $month >= 0; $month--) {

            $start = Carbon::now()->startOfMonth()->subMonths($month);
            $end = $start->copy()->endOfMonth();

            // 月初から月末まで1日ずつ処理する
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                // 土日は登録しない
                if ($date->isWeekend()) {
                    continue;
                }

                // 同じユーザー・日付のデータがあれば更新する
                $attendance = Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                    ]
                );

                // 休憩データを登録する
                AttendanceBreak::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'break_in' => '12:00:00',
                    ],
                    [
                        'break_out' => '13:00:00',
                    ]
                );
            }
        }
    }
}
}
