<?php

// このクラスが所属する名前空間
namespace App\Http\Requests;

// LaravelのFormRequestクラスを読み込む
// FormRequestは入力値の検証（バリデーション）を行うための仕組み
use Illuminate\Foundation\Http\FormRequest;

// =========================================
// ① 会員登録用のFormRequestクラス
// =========================================

// FormRequestを継承して、会員登録専用の入力チェックを定義する
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    // =========================================
    // ② リクエストを許可するか判断する
    // =========================================

    // authorize（オーソライズ）
    // このリクエストを実行する権限があるか確認する
    public function authorize(): bool
    {
        // true：リクエストを許可する
        // 会員登録はログイン前のユーザーも行うため、ここでは許可する
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    // =========================================
    // ③ 会員登録時のバリデーションルール
    // =========================================

    // rules（ルールズ）
    // 入力された各項目が条件を満たしているか確認する
    public function rules(): array
    {
        return [

            // =========================================
            // ④ 名前の入力チェック
            // =========================================

            // required：必須入力
            // 名前が未入力の場合はエラーにする
            'name' => ['required'],

            // =========================================
            // ⑤ メールアドレスの入力チェック
            // =========================================

            // required：必須入力
            // email：メールアドレスの形式であること
            'email' => ['required', 'email'],

            // =========================================
            // ⑥ パスワードの入力チェック
            // =========================================

            // required：必須入力
            // min:8：8文字以上
            // confirmed：確認用パスワードと一致すること
            'password' => ['required', 'min:8', 'confirmed'],

            // =========================================
            // ⑦ 確認用パスワードの入力チェック
            // =========================================

            // required：確認用パスワードも必須入力
            // passwordのconfirmedルールで一致を確認する
            'password_confirmation' => ['required'],
        ];
    }
}
