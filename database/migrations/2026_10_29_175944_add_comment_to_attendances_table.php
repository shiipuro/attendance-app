<?php

// マイグレーションを実行するための基本クラス
use Illuminate\Database\Migrations\Migration;

// テーブルのカラムを定義するためのクラス
use Illuminate\Database\Schema\Blueprint;

// テーブルを変更するための機能
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // =========================================
    // ① 備考カラムを追加する処理
    // =========================================
    public function up(): void
    {
        // 既存のattendancesテーブルを変更する
        Schema::table('attendances', function (Blueprint $table) {

            // 備考を保存するカラムを追加する
            // nullable()で未入力も許可する
            $table->text('comment')->nullable();
        });
    }

    // =========================================
    // ② 追加したカラムを削除する処理
    // =========================================
    public function down(): void
    {
        // マイグレーションを戻す場合は備考カラムを削除する
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};