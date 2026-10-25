<?php

namespace App\Http\Requests;

// フォームから送信されたデータの入力チェックを行うための機能
use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    // =========================================
    // ① このリクエストを実行できるか確認する
    // =========================================
    public function authorize(): bool
    {
        // true：このリクエストを許可する
        // false：このリクエストを拒否する（403エラー）
        // ログイン前のユーザーも送信できるようにtrueにする
        return true;
    }

    // =========================================
    // ② 管理者ログイン時の入力ルール
    // =========================================
    public function rules(): array
    {
        return [
            // メールアドレスは必須で、メール形式であること
            'email' => ['required', 'email'],

            // パスワードは必須入力
            'password' => ['required'],
        ];
    }
}
