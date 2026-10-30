<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // APIの認証はルート側のSanctumで行うため、
    // FormRequestではリクエストの処理を許可する
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // 勤怠登録時の入力チェック
public function rules(): array
{
    return [
        // 勤務日は必須・年月日形式
        'date' => [
    'required',
    'date_format:Y-m-d',
    Rule::unique('attendances', 'date')
        ->where('user_id', $this->user()->id),
],

        // 出勤時刻は必須・時分秒形式
        'clock_in' => ['required', 'date_format:H:i:s'],

        // 退勤時刻は任意・時分秒形式
        'clock_out' => ['nullable', 'date_format:H:i:s', 'after:clock_in'],

        // 備考は任意・1000文字以内
        'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
    // 勤怠登録時のバリデーションエラーを日本語で表示する
public function messages(): array
{
    return [
        'date.required' => '勤務日を入力してください。',
        'date.date_format' => '勤務日はYYYY-MM-DD形式で入力してください。',
        'date.unique' => 'この日付の勤怠はすでに登録されています。',

        'clock_in.required' => '出勤時刻を入力してください。',
        'clock_in.date_format' => '出勤時刻はHH:MM:SS形式で入力してください。',

        'clock_out.date_format' => '退勤時刻はHH:MM:SS形式で入力してください。',
        'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を入力してください。',

        'comment.string' => '備考は文字列で入力してください。',
        'comment.max' => '備考は1000文字以内で入力してください。',
        ];
    }
}
