<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 更新APIへの入力を受け付ける（本人確認はPolicyで行う）
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 更新対象の勤怠データを取得する
        $attendance = \App\Models\Attendance::findOrFail($this->route('id'));

        // 出勤時刻が送信されていなければ、保存済みの時刻を使用する
        $clockIn = $this->input('clock_in', $attendance->clock_in);

        // 退勤時刻が送信されていなければ、保存済みの時刻を使用する
        $clockOut = $this->input('clock_out', $attendance->clock_out);

        return [
            // 更新する勤務日（指定された場合だけチェック）
            'date' => ['sometimes', 'required', 'date_format:Y-m-d'],

            // 更新する出勤時刻
            'clock_in' => [
            'sometimes',
            'required',
            'date_format:H:i:s',
            'before:' . ($clockOut ?? '23:59:59'),
            ],

            // 退勤時刻
            'clock_out' => [
            'sometimes',
            'nullable',
            'date_format:H:i:s',
            'after:' . $clockIn,
            ],

            // 備考（最大1000文字）
            'comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ];
        }
        // 更新時のバリデーションエラーを日本語で返す
        public function messages(): array
        {
            return [
                'date.required' => '勤務日を入力してください。',
                'date.date_format' => '勤務日はYYYY-MM-DD形式で入力してください。',
                'clock_in.required' => '出勤時刻を入力してください。',
                'clock_in.date_format' => '出勤時刻はHH:MM:SS形式で入力してください。',
                'clock_in.before' => '出勤時刻は退勤時刻より前の時刻を入力してください。',
                'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を入力してください。',
                'clock_out.date_format' => '退勤時刻はHH:MM:SS形式で入力してください。',
                'comment.string' => '備考は文字列で入力してください。',
                'comment.max' => '備考は1000文字以内で入力してください。',
                ];
        }
}
