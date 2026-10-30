<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================
        // ① 修正申請を作成するユーザーを取得
        // =========================================

        $user = User::where('email', 'user1@example.com')->first();

        // ユーザーが存在しない場合は処理を終了する
        if (!$user) {
            return;
        }

        // =========================================
        // ② 修正対象の勤怠データを取得
        // =========================================

        // ユーザー1の過去の勤怠を1件取得する
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', '<', now()->toDateString())
            ->orderBy('date')
            ->first();

        // 勤怠が存在しない場合は処理を終了する
        if (!$attendance) {
            return;
        }

        // =========================================
        // ③ 承認待ちの修正申請を登録
        // =========================================

        AttendanceCorrectionRequest::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'approval_status' => 0,
            ],
            [
                'new_clock_in' => '09:30:00',
                'new_clock_out' => '18:30:00',
                'comment' => '出勤・退勤時刻の修正をお願いします。',
            ]
        );

                // =========================================
        // ④ 承認済みの修正申請を登録
        // =========================================

        // 承認待ちとは別の日の勤怠を取得する
        $approvedAttendance = Attendance::where('user_id', $user->id)
            ->where('date', '<', now()->toDateString())
            ->where('id', '!=', $attendance->id)
            ->orderBy('date')
            ->first();

        // 別の日の勤怠が存在する場合だけ登録する
        if ($approvedAttendance) {
            AttendanceCorrectionRequest::updateOrCreate(
                [
                    'attendance_id' => $approvedAttendance->id,
                    'approval_status' => 1,
                ],
                [
                    'new_clock_in' => '08:45:00',
                    'new_clock_out' => '17:45:00',
                    'comment' => '勤務時刻の修正が承認されました。',
                ]
            );
        }
    }
}