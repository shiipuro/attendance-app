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

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'テストユーザー',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );


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
    }
}