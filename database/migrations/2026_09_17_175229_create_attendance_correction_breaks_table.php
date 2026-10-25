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
    // ① テーブルを作成する処理
    // =========================================

    // php artisan migrate 実行時に呼び出される
    public function up(): void
    {
        // 勤怠修正申請の休憩データを保存するテーブルを作成
        Schema::create('attendance_correction_breaks', function (Blueprint $table) {

            // 主キーとなるidカラムを作成
            $table->id();

            // どの勤怠修正申請に紐づく休憩なのかを示す外部キー
            // attendance_correction_requestsテーブルのidを参照する
            $table->foreignId('request_id')
                ->constrained('attendance_correction_requests');

            // 修正希望の休憩開始時刻
            $table->time('new_break_in');

            // 修正希望の休憩終了時刻
            $table->time('new_break_out');

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
        // attendance_correction_breaksテーブルが存在すれば削除する
        Schema::dropIfExists('attendance_correction_breaks');
    }
};
