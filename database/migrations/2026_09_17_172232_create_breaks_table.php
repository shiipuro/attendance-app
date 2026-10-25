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
    // ① 休憩テーブルを作成する処理
    // =========================================

    // php artisan migrate 実行時に呼び出される
    public function up(): void
    {
        // 休憩開始・終了時刻を保存するbreaksテーブルを作成
        Schema::create('breaks', function (Blueprint $table) {

            // 休憩データを識別するための主キー
            $table->id();

            // どの勤怠データに紐づく休憩なのかを示す外部キー
            // attendancesテーブルのidを参照する
            $table->foreignId('attendance_id')->constrained();

            // 休憩開始時刻を保存する
            $table->time('break_in');

            // 休憩終了時刻を保存する
            // nullable()により休憩中はNULLを許可する
            $table->time('break_out')->nullable();

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
        // breaksテーブルが存在すれば削除する
        Schema::dropIfExists('breaks');
    }
};
