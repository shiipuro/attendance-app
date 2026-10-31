<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionFormRequest extends FormRequest
{
    // ログイン済みユーザーの入力チェックを許可する
    public function authorize(): bool
    {
        return true;
    }

    // 出勤・退勤時刻、備考、休憩時間の入力チェック
    public function rules(): array
    {
        return [
            'new_clock_in' => ['required', 'date_format:H:i'],
            'new_clock_out' => [
                'required',
                'date_format:H:i',
                'after:new_clock_in',
            ],
            'comment' => ['required', 'string', 'max:1000'],
            'new_break_in' => ['nullable', 'array'],
            'new_break_out' => ['nullable', 'array'],
            'new_break_in.*' => ['nullable', 'date_format:H:i'],
            'new_break_out.*' => ['nullable', 'date_format:H:i'],
        ];
    }

    // 仕様書FN029で指定されたエラーメッセージ
    public function messages(): array
    {
        return [
            'new_clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'new_clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'new_clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'new_clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'new_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'comment.required' => '備考を記入してください',
            // 休憩開始時刻の入力形式が不正な場合
            'new_break_in.*.date_format' => '休憩時間が不適切な値です',

            // 休憩終了時刻の入力形式が不正な場合
            'new_break_out.*.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }
}