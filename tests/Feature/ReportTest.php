<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReportTest extends TestCase
{
// 未ログインの場合、レポート画面にアクセスできないことを確認する
public function test_guest_cannot_access_reports(): void
{
// ログインせずにレポート画面へアクセスする
$response = $this->get('/reports');

// ログイン画面へリダイレクトされることを確認する
$response->assertRedirect('/login');
}

// ログイン・メール認証済みユーザーがレポート画面を表示できることを確認する
public function test_verified_user_can_access_reports(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 作成したユーザーでログインする
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // レポートのタイトルが表示されることを確認する
    $response->assertSee('マイ勤怠レポート');
}
    // 勤怠データがない場合、総労働時間が0と表示されることを確認する
public function test_reports_show_zero_when_no_attendance_exists(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 総労働時間の項目に0h 0mが表示されることを確認する
    $response->assertSee('0h 0m');
}
    // 出勤・退勤・休憩時間から実労働時間を正しく計算できることを確認する
public function test_reports_calculate_work_hours_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 今月の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 12時から13時までの休憩を登録する
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_in' => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 総労働時間が8時間と表示されることを確認する
    $response->assertSee('8h 0m');
}
    // 1日8時間を超えた分が残業時間として集計されることを確認する
public function test_reports_calculate_overtime_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 実労働時間が9時間になる勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '19:00:00',
    ]);

    // 12時から13時までの休憩を登録する
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_in' => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 総残業時間が1時間と表示されることを確認する
    $response->assertSee('1h 0m');
}
    // 今月の勤怠時間が月次推移に正しく反映されることを確認する
public function test_reports_show_monthly_work_hours_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 今月の勤怠データを登録する（9時出勤・17時退勤）
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '17:00:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 今月の月次推移に8時間と表示されることを確認する
    $response->assertSeeInOrder([
        now()->format('Y-m'),
        '8h 0m',
    ]);
}
    // 9時を過ぎた出勤が遅刻として集計されることを確認する
public function test_reports_count_late_arrivals_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 9時30分に出勤した勤怠データを作成する
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:30:00',
        'clock_out' => '18:00:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 遅刻件数が1件として集計されていることを確認する
    $response->assertViewHas('anomalies', function ($anomalies) {
        return $anomalies['late_count'] === 1;
    });
}
    // 18時より前の退勤が早退として集計されることを確認する
public function test_reports_count_early_leaves_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 17時30分に退勤した勤怠データを作成する
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '17:30:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 早退件数が1件として集計されていることを確認する
    $response->assertViewHas('anomalies', function ($anomalies) {
        return $anomalies['early_leave_count'] === 1;
    });
}
    // 実労働時間が10時間を超えた場合に長時間労働として集計されることを確認する
public function test_reports_count_long_work_hours_correctly(): void
{
    // メール認証済みの一般ユーザーを作成する
    $user = \App\Models\User::factory()->create();

    // 9時出勤・21時退勤の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '21:00:00',
    ]);

    // 12時から13時までの休憩を登録する
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_in' => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    // 作成したユーザーでログインし、レポート画面を開く
    $response = $this->actingAs($user)->get('/reports');

    // レポート画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 長時間労働が1件として集計されていることを確認する
    $response->assertViewHas('anomalies', function ($anomalies) {
        return $anomalies['long_work_count'] === 1;
    });
}
}
