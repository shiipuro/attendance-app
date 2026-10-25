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
    // ① 管理者テーブルを作成する処理
    // =========================================

    // php artisan migrate 実行時に呼び出される
    public function up(): void
    {
        // 管理者のログイン情報を保存するadminsテーブルを作成
        Schema::create('admins', function (Blueprint $table) {

            // 管理者を識別するための主キー
            $table->id();

            // 管理者のメールアドレスを保存する
            // unique()により同じメールアドレスの重複登録を防ぐ
            $table->string('email')->unique();

            // 管理者のパスワードを保存する
            // 登録時にはハッシュ化したパスワードを保存する
            $table->string('password');

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
        // adminsテーブルが存在すれば削除する
        Schema::dropIfExists('admins');
    }
};
