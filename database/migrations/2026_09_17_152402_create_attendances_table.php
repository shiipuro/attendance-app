<?php

// マイグレーションを実行するための基本クラス
use Illuminate\Database\Migrations\Migration;

// テーブルのカラムや外部キーを定義するためのクラス
use Illuminate\Database\Schema\Blueprint;

// テーブルの作成・削除などを行う機能
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // =========================================
    // ① 勤怠テーブルを作成する処理
    // =========================================

    // php artisan migrate 実行時に呼び出される
    public function up(): void
    {
        // 出勤・退勤情報を保存するattendancesテーブルを作成
        Schema::create('attendances', function (Blueprint $table) {

            // 勤怠データを識別するための主キー
            $table->id();

            // どのユーザーの勤怠なのかを示す外部キー
            // usersテーブルのidを参照する
            $table->foreignId('user_id')->constrained();

            // 勤怠の日付を保存する
            $table->date('date');

            // 出勤時刻を保存する
            $table->time('clock_in');

            // 退勤時刻を保存する
            // nullable()により未退勤の場合はNULLを許可する
            $table->time('clock_out')->nullable();

            // 同じユーザーが同じ日付で
            // 複数の勤怠データを登録できないようにする
            $table->unique(['user_id', 'date']);

            // created_at（作成日時）とupdated_at（更新日時）を作成
            $table->timestamps();
        });
    }

    // =========================================
    // ② テーブル作成を取り消す処理
    // =========================================

    // マイグレーションをロールバックしたときに呼び出される
    public function down(): void
    {
        // attendancesテーブルが存在すれば削除する
        Schema::dropIfExists('attendances');
    }
};
