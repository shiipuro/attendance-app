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
        // 勤怠修正申請を保存するテーブルを作成
        Schema::create('attendance_correction_requests', function (Blueprint $table) {

            // 主キーとなるidカラムを作成
            $table->id();

            // どの勤怠データに対する修正申請なのかを示す外部キー
            // attendancesテーブルのidを参照する
            $table->foreignId('attendance_id')->constrained();

            // ユーザーが修正を希望する出勤時刻
            $table->time('new_clock_in');

            // ユーザーが修正を希望する退勤時刻
            $table->time('new_clock_out');

            // 勤怠修正を申請する理由
            $table->text('comment');

            // 申請の承認状態を保存する
            // 0：承認待ち、1：承認済み
            // 新規登録時は自動的に0（承認待ち）になる
            $table->tinyInteger('approval_status')->default(0);

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
        // テーブルが存在する場合に削除する
        Schema::dropIfExists('attendance_correction_requests');
    }
};
