<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// 一般ユーザーと管理者の初期データを登録するためのモデル
use App\Models\User;
use App\Models\Admin;

// パスワードを安全にハッシュ化するための機能
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

                // =========================================
    // ① 一般ユーザーのテスト用アカウント
    // =========================================

        // =========================================
// ① 一般ユーザー3人のテスト用アカウント
// =========================================

$users = [
    ['name' => 'ユーザー1', 'email' => 'user1@example.com'],
    ['name' => 'ユーザー2', 'email' => 'user2@example.com'],
    ['name' => 'ユーザー3', 'email' => 'user3@example.com'],
];

// 3人分のアカウントを順番に登録する
foreach ($users as $user) {
    User::updateOrCreate(
        ['email' => $user['email']],
        [
            'name' => $user['name'],
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]
    );
}


    // =========================================
    // ② 管理者のテスト用アカウント
    // =========================================

    // 管理者がいれば更新、いなければ作成する
                Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'password' => Hash::make('password123'),
            ]
        );

        // =========================================
        // ③ 勤怠・休憩のダミーデータを登録
        // =========================================

        // 一般ユーザーの登録後に勤怠データを作成する
        $this->call(AttendanceSeeder::class);

        // =========================================
        // ④ 修正申請のダミーデータを登録
        // =========================================

        // 勤怠データの登録後に修正申請を作成する
        $this->call(AttendanceCorrectionRequestSeeder::class);
    }
}