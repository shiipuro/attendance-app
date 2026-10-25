<?php

namespace App\Models;

// メール認証機能を使用する場合に必要なインターフェース
// 現在は使用していないためコメントアウトされている
// use Illuminate\Contracts\Auth\MustVerifyEmail;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// ログイン認証に対応したユーザークラス
use Illuminate\Foundation\Auth\User as Authenticatable;

// ユーザーへの通知機能
use Illuminate\Notifications\Notifiable;

// APIトークンを扱うための機能
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // APIトークン・Factory・通知機能を使用できるようにする
    use HasApiTokens, HasFactory, Notifiable;

    // =========================================
    // ① 一括登録・更新を許可するカラム
    // =========================================

    // create()などで一括代入できる項目を指定する
    protected $fillable = [
        'name',     // ユーザー名
        'email',    // メールアドレス
        'password', // パスワード
    ];

    // =========================================
    // ② 外部に表示しない項目
    // =========================================

    // 配列やJSONなどに変換するときに隠す項目
    // パスワードなどの情報が不用意に出力されないようにする
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =========================================
    // ③ データの型を変換する設定
    // =========================================

    protected $casts = [

        // メール認証日時を日付・時刻として扱う
        'email_verified_at' => 'datetime',

        // パスワードを保存する際にハッシュ化する
        'password' => 'hashed',
    ];

    // =========================================
    // ④ Attendanceモデルとのリレーション
    // =========================================

    // 1人のユーザーは複数日の勤怠データを持つ
    // users.id と attendances.user_id を関連付ける
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
