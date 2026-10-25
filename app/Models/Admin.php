<?php

namespace App\Models;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// ログイン認証に対応したユーザークラス
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    // Factory機能を使用できるようにする
    use HasFactory;

    // =========================================
    // ① 管理者のログイン認証
    // =========================================

    // Authenticatableを継承することで、
    // Laravelのログイン認証機能を利用できる
    //
    // 一般ユーザーとは別のadminsテーブルを使用する
    // ※管理者用の認証ガードはconfig/auth.phpで設定する

    // =========================================
    // ② 一括登録・更新を許可するカラム
    // =========================================

    // create()やupdate()で一括代入できるカラムを指定する
    protected $fillable = [
        'email',    // 管理者のメールアドレス
        'password', // 管理者のパスワード
    ];
}
