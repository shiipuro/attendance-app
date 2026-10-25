<?php

namespace App\Models;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// データベースのテーブルを操作する基本クラス
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionBreak extends Model
{
    // Factory機能を使用できるようにする
    use HasFactory;

    // =========================================
    // ① 一括登録・更新を許可するカラム
    // =========================================

    // create()などで一括代入できるカラムを指定する
    protected $fillable = [
        'request_id',    // 紐づく勤怠修正申請のID
        'new_break_in',  // 修正希望の休憩開始時刻
        'new_break_out', // 修正希望の休憩終了時刻
    ];

    // =========================================
    // ② 勤怠修正申請モデルとのリレーション
    // =========================================

    // 1件の休憩修正データは、1件の勤怠修正申請に所属する
    // attendance_correction_breaks.request_id
    // と attendance_correction_requests.id を関連付ける
    public function correctionRequest()
    {
        // 第2引数で外部キー request_id を明示的に指定する
        return $this->belongsTo(
            AttendanceCorrectionRequest::class,
            'request_id'
        );
    }
}
