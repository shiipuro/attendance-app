<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // 会員登録時の入力内容をチェックする
Validator::make($input, [
    'name' => ['required'],
    'email' => ['required', 'email'],
    'password' => ['required', 'min:8', 'confirmed'],
    'password_confirmation' => ['required'],
], [
    // 名前の入力チェック
    'name.required' => 'お名前を入力してください',

    // メールアドレスの入力チェック
    'email.required' => 'メールアドレスを入力してください',
    'email.email' => 'メールアドレスはメール形式で入力してください',

    // パスワードの入力チェック
    'password.required' => 'パスワードを入力してください',
    'password.min' => 'パスワードは8文字以上で入力してください',
    'password.confirmed' => 'パスワードと一致しません',

    // 確認用パスワードの入力チェック
    'password_confirmation.required' => '確認用パスワードを入力してください',
])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
