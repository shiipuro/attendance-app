<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;


class AttendanceRecordApiTest extends TestCase
{
    // テスト時のデータベースを管理する
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    // 認証なしでも勤怠一覧APIを取得できることを確認する
    public function test_認証なしで勤怠一覧を取得できる(): void
    {
    // 勤怠一覧APIにGETリクエストを送る
    $response = $this->getJson('/api/v1/attendance-records');

    // 正常に取得でき、dataがJSONに含まれることを確認する
    $response->assertOk()
            ->assertJsonStructure(['data']);
    }
    // 認証していないユーザーは勤怠を登録できないことを確認する
    public function test_認証なしでは勤怠を登録できない(): void
    {
    // 認証せずに勤怠登録APIへPOSTリクエストを送る
    $response = $this->postJson('/api/v1/attendance-records', [
        'date' => '2026-10-30',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 認証エラー（401）が返ることを確認する
    $response->assertUnauthorized();
    }

     // 認証済みユーザーが勤怠を登録できることを確認する
    public function test_認証済みユーザーは勤怠を登録できる(): void
    {
        // テスト用ユーザーを作成する
        $user = User::factory()->create();

        // 作成したユーザーとして認証する
        $this->actingAs($user, 'sanctum');

        // 勤怠登録APIにPOSTリクエストを送る
        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026-10-30',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API自動テスト',
        ]);

        // 登録成功（201）を確認する
        $response->assertCreated();

        // データベースに保存されたことを確認する
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-10-30',
            'comment' => 'API自動テスト',
        ]);
    }
        // 他人の勤怠は更新できないことを確認する
public function test_他人の勤怠は更新できない(): void
{
    // 勤怠の持ち主を作成する
    $owner = User::factory()->create();

    // 別のユーザーを作成する
    $otherUser = User::factory()->create();

    // 持ち主の勤怠を作成する
    $attendance = Attendance::create([
        'user_id' => $owner->id,
        'date' => '2026-10-30',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 別のユーザーとして認証する
    $this->actingAs($otherUser, 'sanctum');

    // 他人の勤怠を更新しようとする
    $response = $this->patchJson(
        "/api/v1/attendance-records/{$attendance->id}",
        ['comment' => '他人による更新']
    );

    // 権限エラー（403）を確認する
    $response->assertForbidden();

    // データが変更されていないことを確認する
    $this->assertDatabaseMissing('attendances', [
        'id' => $attendance->id,
        'comment' => '他人による更新',
    ]);
}
    // 他人の勤怠は削除できないことを確認する
public function test_他人の勤怠は削除できない(): void
{
    // 勤怠の持ち主を作成する
    $owner = User::factory()->create();

    // 別のユーザーを作成する
    $otherUser = User::factory()->create();

    // 持ち主の勤怠を作成する
    $attendance = Attendance::create([
        'user_id' => $owner->id,
        'date' => '2026-10-30',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 別のユーザーとして認証する
    $this->actingAs($otherUser, 'sanctum');

    // 他人の勤怠を削除しようとする
    $response = $this->deleteJson(
        "/api/v1/attendance-records/{$attendance->id}"
    );

    // 権限エラー（403）を確認する
    $response->assertForbidden();

    // 勤怠データが削除されていないことを確認する
    $this->assertDatabaseHas('attendances', [
        'id' => $attendance->id,
        'user_id' => $owner->id,
    ]);
    }
    // 存在しない勤怠を取得すると404になることを確認する
    public function test_存在しない勤怠は取得できない(): void
    {
    // 存在しないIDを指定して詳細APIにアクセスする
    $response = $this->getJson('/api/v1/attendance-records/999999');

    // 404 Not Foundが返ることを確認する
    $response->assertNotFound();
    }
    // 退勤時刻が出勤時刻より早い場合は登録できないことを確認する
public function test_不正な勤怠時刻は登録できない(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // 作成したユーザーとして認証する
    $this->actingAs($user, 'sanctum');

    // 退勤時刻が出勤時刻より早いデータを送信する
    $response = $this->postJson('/api/v1/attendance-records', [
        'date' => '2026-10-30',
        'clock_in' => '09:00:00',
        'clock_out' => '08:00:00',
    ]);

    // 入力エラー（422）が返ることを確認する
    $response->assertUnprocessable();

    // 退勤時刻にバリデーションエラーがあることを確認する
    $response->assertJsonValidationErrors(['clock_out']);
}
    // 同じ日付の勤怠は重複登録できないことを確認する
public function test_同じ日付の勤怠は重複登録できない(): void
{
    // テスト用ユーザーを作成する
    $user = User::factory()->create();

    // すでに登録されている勤怠を作成する
    Attendance::create([
        'user_id' => $user->id,
        'date' => '2026-10-30',
        'clock_in' => '09:00:00',
        'clock_out' => '18:00:00',
    ]);

    // 作成したユーザーとして認証する
    $this->actingAs($user, 'sanctum');

    // 同じ日付の勤怠をもう一度登録しようとする
    $response = $this->postJson('/api/v1/attendance-records', [
        'date' => '2026-10-30',
        'clock_in' => '10:00:00',
        'clock_out' => '19:00:00',
    ]);

    // 入力エラー（422）を確認する
    $response->assertUnprocessable();

    // dateにバリデーションエラーがあることを確認する
    $response->assertJsonValidationErrors(['date']);

    // 勤怠が1件のままであることを確認する
    $this->assertDatabaseCount('attendances', 1);
    }
}
