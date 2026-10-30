<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // 勤怠一覧APIは認証不要なので、リクエストを許可する
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    // APIに送られた検索条件をチェックする
    public function rules(): array
    {
        return [
            // 日付で絞り込む
            'date' => ['nullable', 'date_format:Y-m-d'],

            // 1ページの表示件数
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            // ユーザーIDで絞り込む
            'user_id' => ['nullable', 'integer', 'min:1'],

            // 年月で絞り込む
            'month' => ['nullable', 'date_format:Y-m'],

            // ページ番号は省略可能。指定する場合は1以上の整数
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
