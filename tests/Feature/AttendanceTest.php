<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
  // テストごとにデータベースの状態を整える
    use RefreshDatabase;

    // ログインしていないユーザーは勤怠画面を開けない
    public function test_guest_cannot_access_attendance_page(): void
    {
        // 未ログインの状態で勤怠画面にアクセス
        $response = $this->get('/attendance');

        // ログイン画面へリダイレクトされることを確認
        $response->assertRedirect('/login');
    }
    // ログインしていない人は管理者の勤怠一覧画面を開けない
public function test_guest_cannot_access_admin_attendance_page(): void
{
    // 未ログインの状態で管理者の勤怠一覧画面にアクセス
    $response = $this->get('/admin/attendance/list');

    // 管理者ログイン画面へリダイレクトされることを確認
    $response->assertRedirect('/admin/login');
}
// ログイン済みユーザーは勤怠画面を開ける
public function test_authenticated_user_can_access_attendance_page(): void
{
    // Factoryを使ってテスト用ユーザーを作成
    $user = User::factory()->create();

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 勤怠画面にアクセスする
    $response = $this->get('/attendance');

    // 画面が正常に表示されることを確認する
    $response->assertStatus(200);
}
// テストで使用するデータベース名を確認する
public function test_database_connection_is_testing(): void
{
    // 実際に接続しているデータベース名を取得する
    $databaseName = \Illuminate\Support\Facades\DB::selectOne(
        'SELECT DATABASE() AS name'
    )->name;

    // testingデータベースに接続していることを確認する
    $this->assertSame('testing', $databaseName);
}
// ログイン済みユーザーが出勤打刻できることを確認する
public function test_authenticated_user_can_clock_in(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 出勤打刻のリクエストを送信する
    $response = $this->post('/attendance', [
        'action' => 'clock_in',
    ]);

    // 打刻画面へ戻ることを確認する
    $response->assertRedirect('/attendance');

    // attendancesテーブルに出勤記録が保存されたことを確認する
    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'date' => now()->toDateString(),
    ]);
}
// 出勤中のユーザーが休憩を開始できることを確認する
public function test_authenticated_user_can_start_break(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 先に出勤打刻を行う
    $this->post('/attendance', [
        'action' => 'clock_in',
    ]);

    // 休憩開始のリクエストを送信する
    $response = $this->post('/attendance', [
        'action' => 'break_in',
    ]);

    // 打刻画面へ戻ることを確認する
    $response->assertRedirect('/attendance');

    // 出勤記録を取得する
    $attendance = \App\Models\Attendance::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->firstOrFail();

    // breaksテーブルに休憩開始記録が保存されたことを確認する
    $this->assertDatabaseHas('breaks', [
        'attendance_id' => $attendance->id,
    ]);
}
// 休憩中のユーザーが休憩を終了できることを確認する
public function test_authenticated_user_can_end_break(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // ログインする
    $this->actingAs($user);

    // 出勤する
    $this->post('/attendance', [
        'action' => 'clock_in',
    ]);

    // 休憩を開始する
    $this->post('/attendance', [
        'action' => 'break_in',
    ]);

    // 休憩を終了する
    $response = $this->post('/attendance', [
        'action' => 'break_out',
    ]);

    // 勤怠画面に戻ることを確認する
    $response->assertRedirect('/attendance');

    // 今日の勤怠データを取得する
    $attendance = \App\Models\Attendance::where('user_id', $user->id)
        ->where('date', now()->toDateString())
        ->firstOrFail();

    // 休憩終了時刻が保存されていることを確認する
    $this->assertDatabaseHas('breaks', [
        'attendance_id' => $attendance->id,
        'break_out' => now()->format('H:i:s'),
    ]);
}
// ログイン済みユーザーが退勤打刻できることを確認する
public function test_authenticated_user_can_clock_out(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // ログインする
    $this->actingAs($user);

    // 先に出勤する
    $this->post('/attendance', [
        'action' => 'clock_in',
    ]);

    // 退勤打刻をする
    $response = $this->post('/attendance', [
        'action' => 'clock_out',
    ]);

    // 勤怠画面に戻ることを確認する
    $response->assertRedirect('/attendance');

    // 退勤時刻が保存されたことを確認する
    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_out' => now()->format('H:i:s'),
    ]);
}
// ログイン済みユーザーが勤怠一覧を表示できることを確認する
public function test_authenticated_user_can_access_attendance_list(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 勤怠一覧画面にアクセスする
    $response = $this->get('/attendance/list');

    // 画面が正常に表示されることを確認する
    $response->assertStatus(200);
}
// 勤怠一覧で勤務時間が正しく計算されることを確認する
public function test_attendance_list_calculates_working_hours(): void
{
    // テスト用ユーザーを作成してログインする
    $user = User::factory()->create();
    $this->actingAs($user);

    // 出勤09:00・退勤18:00の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 休憩12:00〜13:00を登録する
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_in' => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    // 勤怠一覧を取得する
    $response = $this->get('/attendance/list');

    // 画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // 勤務時間08:00が表示用データに含まれることを確認する
    $records = $response->viewData('formattedAttendanceRecords');

    $todayRecord = collect($records)->firstWhere(
        'id',
        $attendance->id
    );

    $this->assertNotNull($todayRecord);
    $this->assertSame('08:00', $todayRecord['total_time']);
}
// 他のユーザーの勤怠データが一覧に含まれないことを確認する
public function test_attendance_list_does_not_include_other_users_records(): void
{
    // 2人のテスト用ユーザーを作成する
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // 他のユーザーの勤怠データを作成する
    $otherAttendance = \App\Models\Attendance::create([
        'user_id' => $otherUser->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 1人目のユーザーとしてログインする
    $this->actingAs($user);

    // 勤怠一覧にアクセスする
    $response = $this->get('/attendance/list');

    // 画面が正常に表示されることを確認する
    $response->assertStatus(200);

    // Bladeに渡された勤怠一覧データを取得する
    $records = $response->viewData('formattedAttendanceRecords');

    // 他のユーザーの勤怠IDが含まれていないことを確認する
    $this->assertFalse(
        collect($records)->contains('id', $otherAttendance->id)
    );
}
// ログイン済みユーザーが自分の勤怠詳細を表示できることを確認する
public function test_authenticated_user_can_access_own_attendance_detail(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // ユーザーの勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 勤怠詳細画面にアクセスする
    $response = $this->get('/attendance/' . $attendance->id);

    // 正常に画面が表示されることを確認する
    $response->assertStatus(200);
}
// 他のユーザーの勤怠詳細にアクセスできないことを確認する
public function test_user_cannot_access_other_users_attendance_detail(): void
{
    // 2人のテスト用ユーザーを作成する
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // 他のユーザーの勤怠データを作成する
    $otherAttendance = \App\Models\Attendance::create([
        'user_id' => $otherUser->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 1人目のユーザーとしてログインする
    $this->actingAs($user);

    // 他のユーザーの勤怠詳細にアクセスする
    $response = $this->get('/attendance/' . $otherAttendance->id);

    // 404エラーになることを確認する
    $response->assertStatus(404);
}
// 一般ユーザーが勤怠修正申請を送信できることを確認する
public function test_user_can_submit_attendance_correction_request(): void
{
    // テスト用ユーザーを作成してログインする
    $user = User::factory()->create();
    $this->actingAs($user);

    // 修正対象の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 勤怠修正申請を送信する
    $response = $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '打刻時間を間違えたため',
    ]);

    // 勤怠詳細画面に戻ることを確認する
    $response->assertRedirect('/attendance/' . $attendance->id);

    // 修正申請が承認待ちで保存されたことを確認する
    $this->assertDatabaseHas('attendance_correction_requests', [
        'attendance_id' => $attendance->id,
        'comment' => '打刻時間を間違えたため',
        'approval_status' => 0,
    ]);
}
// 承認待ちの修正申請がある場合、重複申請できないことを確認する
public function test_user_cannot_submit_duplicate_pending_correction_request(): void
{
    // テスト用ユーザーを作成してログインする
    $user = User::factory()->create();
    $this->actingAs($user);

    // 勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 1回目の修正申請を送信する
    $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '1回目の修正申請',
    ]);

    // 2回目の修正申請を送信する
    $response = $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '10:00',
        'new_clock_out' => '19:00',
        'comment' => '2回目の修正申請',
    ]);

    // 勤怠詳細画面に戻ることを確認する
    $response->assertRedirect('/attendance/' . $attendance->id);

    // 修正申請が1件だけ保存されていることを確認する
    $this->assertDatabaseCount('attendance_correction_requests', 1);
}
// ログインユーザーが自分の勤怠修正申請一覧を表示できることを確認する
public function test_authenticated_user_can_access_correction_request_list(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // 作成したユーザーとしてログインする
    $this->actingAs($user);

    // 修正申請一覧ページにアクセスする
    $response = $this->get('/stamp_correction_request/list');

    // 正常に表示されることを確認する
    $response->assertStatus(200);
}
// 修正申請一覧に他のユーザーの申請が含まれないことを確認する
public function test_correction_request_list_does_not_include_other_users_requests(): void
{
    // 2人のユーザーを作成する
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // 1人目のユーザーでログインする
    $this->actingAs($user);

    // 自分の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 他のユーザーの勤怠データを作成する
    $otherAttendance = \App\Models\Attendance::create([
        'user_id' => $otherUser->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 自分の修正申請を作成する
    $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '自分の修正申請',
    ]);

    // 他のユーザーでログインして修正申請を作成する
    $this->actingAs($otherUser);

    $this->post('/attendance/' . $otherAttendance->id, [
        'new_clock_in' => '10:00',
        'new_clock_out' => '19:00',
        'comment' => '他人の修正申請',
    ]);

    // 1人目のユーザーで再ログインする
    $this->actingAs($user);

    // 修正申請一覧にアクセスする
    $response = $this->get('/stamp_correction_request/list');

    // 一覧に自分の申請だけが含まれることを確認する
    $response->assertViewHas('formattedApplications', function ($applications) {
        return $applications->count() === 1
            && $applications->first()['comment'] === '自分の修正申請';
    });
}
// 管理者が勤怠一覧画面を表示できることを確認する
public function test_authenticated_admin_can_access_attendance_list(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 管理者用の勤怠一覧画面にアクセスする
    $response = $this->get('/admin/attendance/list');

    // 正常に表示されることを確認する
    $response->assertStatus(200);
}
// 管理者が一般ユーザーの勤怠詳細を表示できることを確認する
public function test_authenticated_admin_can_access_attendance_detail(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 一般ユーザーの勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 一般ユーザーの勤怠詳細にアクセスする
    $response = $this->get('/admin/attendance/' . $attendance->id);

    // 正常に表示されることを確認する
    $response->assertStatus(200);
}
// 管理者が一般ユーザーの勤怠を直接修正できることを確認する
public function test_admin_can_update_attendance(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 修正対象の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 勤怠修正を送信する
    $response = $this->post('/admin/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '打刻時間の修正',
    ]);

    // 勤怠詳細画面に戻ることを確認する
    $response->assertRedirect('/admin/attendance/' . $attendance->id);

    // 勤怠データが更新されたことを確認する
    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'clock_in' => '09:30:00',
        'clock_out' => '18:30:00',
    ]);
}
// 管理者が勤怠修正申請一覧を表示できることを確認する
public function test_admin_can_access_correction_request_list(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 管理者用の修正申請一覧にアクセスする
    $response = $this->get('/stamp_correction_request/list/admin');

    // 正常に表示されることを確認する
    $response->assertStatus(200);
}
// 管理者が勤怠修正申請の詳細を表示できることを確認する
public function test_admin_can_access_correction_request_detail(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 一般ユーザーの勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 一般ユーザーとして修正申請を送信する
    $this->actingAs($user);

    $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '打刻時間を修正したいため',
    ]);

    // 保存された修正申請を取得する
    $application = \App\Models\AttendanceCorrectionRequest::where(
        'attendance_id',
        $attendance->id
    )->firstOrFail();

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 修正申請の詳細画面にアクセスする
    $response = $this->get(
        '/stamp_correction_request/approve/' . $application->id
    );

    // 正常に表示されることを確認する
    $response->assertStatus(200);
}
// 管理者が修正申請を承認できることを確認する
public function test_admin_can_approve_correction_request(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 元の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 一般ユーザーとして修正申請を送信する
    $this->actingAs($user);

    $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '打刻時間を修正したいため',
    ]);

    // 保存された修正申請を取得する
    $application = \App\Models\AttendanceCorrectionRequest::where(
        'attendance_id',
        $attendance->id
    )->firstOrFail();

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 修正申請を承認する
    $response = $this->post(
        '/stamp_correction_request/approve/' . $application->id
    );

    // 承認後、管理者の申請一覧へ戻ることを確認する
    $response->assertRedirect('/stamp_correction_request/list/admin');

    // 勤怠データが修正されていることを確認する
    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'clock_in' => '09:30:00',
        'clock_out' => '18:30:00',
    ]);

    // 修正申請が承認済みになっていることを確認する
    $this->assertDatabaseHas('attendance_correction_requests', [
        'id' => $application->id,
        'approval_status' => 1,
    ]);
}
// 承認済みの修正申請を再承認しても再処理されないことを確認する
public function test_admin_cannot_approve_already_approved_request_again(): void
{
    // 管理者と一般ユーザーを作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $user = User::factory()->create();

    // 元の勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 一般ユーザーとして修正申請する
    $this->actingAs($user);

    $this->post('/attendance/' . $attendance->id, [
        'new_clock_in' => '09:30',
        'new_clock_out' => '18:30',
        'comment' => '打刻時間の修正',
    ]);

    // 修正申請を取得する
    $application = \App\Models\AttendanceCorrectionRequest::where(
        'attendance_id',
        $attendance->id
    )->firstOrFail();

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // 1回目の承認
    $this->post('/stamp_correction_request/approve/' . $application->id);

    // 承認後の勤怠データを取得する
    $attendance->refresh();
    $updatedAt = $attendance->updated_at;

    // 2回目の承認
    $this->post('/stamp_correction_request/approve/' . $application->id);

    // 勤怠データが変わっていないことを確認する
    $attendance->refresh();

    $this->assertEquals($updatedAt, $attendance->updated_at);

    // 承認済みのままであることを確認する
    $this->assertDatabaseHas('attendance_correction_requests', [
        'id' => $application->id,
        'approval_status' => 1,
    ]);
}
// 管理者がスタッフ一覧画面を表示できることを確認する
public function test_admin_can_access_staff_list(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // スタッフ一覧画面にアクセスする
    $response = $this->get('/admin/staff/list');

    // 正常に表示されることを確認する
    $response->assertStatus(200);

    // 作成したユーザーが一覧データに含まれることを確認する
    $response->assertViewHas('users', function ($users) use ($user) {
        return $users->contains('id', $user->id);
    });
}
// 管理者がスタッフ別の月間勤怠一覧を表示できることを確認する
public function test_admin_can_access_staff_attendance_list(): void
{
    // テスト用の管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // テスト用の一般ユーザーを作成する
    $user = User::factory()->create();

    // 一般ユーザーの勤怠データを作成する
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // スタッフ別の月間勤怠一覧にアクセスする
    $response = $this->get(
        '/admin/attendance/staff/' . $user->id
    );

    // 正常に表示されることを確認する
    $response->assertStatus(200);

    // 対象スタッフの情報が渡されていることを確認する
    $response->assertViewHas('user', function ($viewUser) use ($user) {
        return $viewUser->id === $user->id;
    });

    // 作成した勤怠が一覧データに含まれることを確認する
    $response->assertViewHas('formattedAttendanceRecords', function ($records) {
        return $records->contains(function ($record) {
            return $record['clock_in'] === '09:00'
                && $record['clock_out'] === '18:00';
        });
    });
}
// スタッフ別勤怠一覧に他のスタッフの勤怠が含まれないことを確認する
public function test_admin_staff_attendance_list_excludes_other_users_records(): void
{
    // 管理者を作成する
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // 2人のスタッフを作成する
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // スタッフAの勤怠を作成する
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // スタッフBの勤怠を作成する
    \App\Models\Attendance::create([
        'user_id' => $otherUser->id,
        'date' => now()->toDateString(),
        'clock_in' => '10:00:00',
        'clock_out' => '19:00:00',
    ]);

    // 管理者としてログインする
    $this->actingAs($admin, 'admin');

    // スタッフAの月間勤怠一覧にアクセスする
    $response = $this->get(
        '/admin/attendance/staff/' . $user->id
    );

    // スタッフAの勤怠だけが取得されていることを確認する
    $response->assertViewHas('formattedAttendanceRecords', function ($records) {
        $today = now()->format('m/d');

        $todayRecord = $records->first(function ($record) use ($today) {
            return str_starts_with($record['date'], $today);
        });

        return $todayRecord !== null
            && $todayRecord['clock_in'] === '09:00'
            && $todayRecord['clock_out'] === '18:00';
    });
}
// 管理者がスタッフの勤怠CSVを出力できることを確認する
public function test_admin_can_export_attendance_csv(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // CSV出力をリクエスト
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026-10',
    ]);

    // 正常にダウンロードできることを確認
    $response->assertStatus(200);

    // CSV形式であることを確認
    $response->assertHeader(
        'Content-Type',
        'text/csv; charset=UTF-8'
    );

    // ファイル名を確認
    $response->assertDownload(
        'attendance_' . $user->id . '_2026-10.csv'
    );
}
// CSVに正しい勤怠データが出力されることを確認する
public function test_admin_export_csv_contains_attendance_data(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // CSV出力をリクエスト
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026-10',
    ]);

    // CSVの出力内容を取得
    ob_start();
    $response->baseResponse->sendContent();
    $csv = ob_get_clean();

    // 日付と出退勤時刻が含まれているか確認
    $this->assertStringContainsString(
        '2026/10/15,09:00,18:00',
        $csv
    );

    // 休憩なしの場合、勤務時間が9時間になることを確認
    $this->assertStringContainsString(
        '00:00,09:00',
        $csv
    );
}
// CSV出力時に休憩時間が正しく計算されることを確認する
public function test_admin_export_csv_calculates_break_time(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 休憩データを作成
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_in' => '12:00:00',
        'break_out' => '13:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // CSV出力をリクエスト
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026-10',
    ]);

    // CSVの内容を取得
    ob_start();
    $response->baseResponse->sendContent();
    $csv = ob_get_clean();

    // 休憩1時間、勤務8時間になっていることを確認
    $this->assertStringContainsString(
        '2026/10/15,09:00,18:00,01:00,08:00',
        $csv
    );
}
// CSVに他のスタッフの勤怠が含まれないことを確認する
public function test_admin_export_csv_excludes_other_users(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを2人作成
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // スタッフAの勤怠
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // スタッフBの勤怠
    \App\Models\Attendance::create([
        'user_id' => $otherUser->id,
        'date' => '2026-10-16',
        'clock_in' => '10:00:00',
        'clock_out' => '19:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // スタッフAのCSVを出力
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026-10',
    ]);

    // CSVの内容を取得
    ob_start();
    $response->baseResponse->sendContent();
    $csv = ob_get_clean();

    // スタッフAの勤怠が含まれる
    $this->assertStringContainsString(
        '2026/10/15,09:00,18:00',
        $csv
    );

    // スタッフBの勤怠は含まれない
    $this->assertStringNotContainsString(
        '2026/10/16,10:00,19:00',
        $csv
    );
}
// CSVに指定月以外の勤怠が含まれないことを確認する
public function test_admin_export_csv_excludes_other_months(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 10月の勤怠
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 9月の勤怠
    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-09-15',
        'clock_in' => '10:00:00',
        'clock_out' => '19:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 10月のCSVを出力
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026-10',
    ]);

    // CSVの内容を取得
    ob_start();
    $response->baseResponse->sendContent();
    $csv = ob_get_clean();

    // 10月の勤怠は含まれる
    $this->assertStringContainsString(
        '2026/10/15,09:00,18:00',
        $csv
    );

    // 9月の勤怠は含まれない
    $this->assertStringNotContainsString(
        '2026/09/15,10:00,19:00',
        $csv
    );
}
// CSV出力時にスタッフIDが必須であることを確認する
public function test_admin_export_csv_requires_user_id(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // スタッフIDを指定せずにCSV出力
    $response = $this->post('/export', [
        'year_month' => '2026-10',
    ]);

    // user_idにバリデーションエラーが出ることを確認
    $response->assertSessionHasErrors('user_id');
}
// 存在しないスタッフIDではCSV出力できないことを確認する
public function test_admin_export_csv_rejects_invalid_user_id(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 存在しないスタッフIDを指定してCSV出力
    $response = $this->post('/export', [
        'user_id' => 99999,
        'year_month' => '2026-10',
    ]);

    // user_idにバリデーションエラーが出ることを確認
    $response->assertSessionHasErrors('user_id');
}
// CSV出力時に年月が必須であることを確認する
public function test_admin_export_csv_requires_year_month(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 年月を指定せずにCSV出力
    $response = $this->post('/export', [
        'user_id' => $user->id,
    ]);

    // year_monthにバリデーションエラーが出ることを確認
    $response->assertSessionHasErrors('year_month');
}
// CSV出力時に年月の形式が正しいことを確認する
public function test_admin_export_csv_rejects_invalid_year_month(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 間違った年月形式でCSV出力
    $response = $this->post('/export', [
        'user_id' => $user->id,
        'year_month' => '2026/10',
    ]);

    // year_monthにバリデーションエラーが出ることを確認
    $response->assertSessionHasErrors('year_month');
}
// 退勤時刻が出勤時刻より前の場合、管理者が修正できないことを確認する
public function test_admin_cannot_update_attendance_with_invalid_clock_out(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠を作成
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 退勤時刻を出勤時刻より前にして修正
    $response = $this->post(
        '/admin/attendance/' . $attendance->id,
        [
            'new_clock_in' => '09:00',
            'new_clock_out' => '08:00',
            'comment' => '勤怠修正',
        ]
    );

    // 退勤時刻に入力エラーが出ることを確認
    $response->assertSessionHasErrors('new_clock_out');

    // 元の勤怠が変更されていないことを確認
    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);
}
// 休憩終了が開始より前の場合、管理者が修正できないことを確認する
public function test_admin_cannot_update_attendance_with_invalid_break_time(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 休憩終了が開始より前になるように修正
    $response = $this->post(
        '/admin/attendance/' . $attendance->id,
        [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_break_in' => ['13:00'],
            'new_break_out' => ['12:00'],
            'comment' => '休憩時間の修正',
        ]
    );

    // 休憩終了時刻にエラーが出ることを確認
    $response->assertSessionHasErrors('new_break_out.0');

    // 不正な休憩データが保存されていないことを確認
    $this->assertDatabaseMissing('breaks', [
        'attendance_id' => $attendance->id,
        'break_in' => '13:00:00',
        'break_out' => '12:00:00',
    ]);
}
// 出勤前の休憩時間では勤怠を修正できないことを確認する
public function test_admin_cannot_update_attendance_with_break_before_clock_in(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 出勤前の休憩時間を指定して修正
    $response = $this->post(
        '/admin/attendance/' . $attendance->id,
        [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_break_in' => ['08:00'],
            'new_break_out' => ['08:30'],
            'comment' => '休憩時間の修正',
        ]
    );

    // 休憩時間に入力エラーが出ることを確認
    $response->assertSessionHasErrors('new_break_out.0');

    // 不正な休憩データが保存されないことを確認
    $this->assertDatabaseMissing('breaks', [
        'attendance_id' => $attendance->id,
        'break_in' => '08:00:00',
        'break_out' => '08:30:00',
    ]);
}
// 退勤後の休憩時間では勤怠を修正できないことを確認する
public function test_admin_cannot_update_attendance_with_break_after_clock_out(): void
{
    // 管理者を作成
    $admin = \App\Models\Admin::create([
        'email' => 'admin-test@example.com',
        'password' => bcrypt('password123'),
    ]);

    // スタッフを作成
    $user = User::factory()->create();

    // 勤怠データを作成
    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-15',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 管理者としてログイン
    $this->actingAs($admin, 'admin');

    // 退勤後の休憩時間を指定して修正
    $response = $this->post(
        '/admin/attendance/' . $attendance->id,
        [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_break_in' => ['18:30'],
            'new_break_out' => ['19:00'],
            'comment' => '休憩時間の修正',
        ]
    );

    // 休憩時間に入力エラーが出ることを確認
    $response->assertSessionHasErrors('new_break_out.0');

    // 不正な休憩データが保存されないことを確認
    $this->assertDatabaseMissing('breaks', [
        'attendance_id' => $attendance->id,
        'break_in' => '18:30:00',
        'break_out' => '19:00:00',
    ]);
}
}
