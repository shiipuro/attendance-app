<?php

namespace App\Models;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// データベースのテーブルを操作する基本クラス
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    // Factory機能を使用できるようにする
    use HasFactory;

    // =========================================
    // ① 一括登録・更新を許可するカラム
    // =========================================

    // create()やupdate()で一括代入できるカラムを指定する
    protected $fillable = [
        'attendance_id',   // 修正対象となる勤怠データのID
        'new_clock_in',    // 申請された新しい出勤時刻
        'new_clock_out',   // 申請された新しい退勤時刻
        'comment',         // 修正を申請する理由
        'approval_status', // 承認状態（0：承認待ち、1：承認済み）
    ];

    // =========================================
    // ② Attendanceモデルとのリレーション
    // =========================================

    // 1件の修正申請は、1件の勤怠データに所属する
    // attendance_correction_requests.attendance_id
    // と attendances.id を関連付ける
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // =========================================
    // ③ AttendanceCorrectionBreakモデルとのリレーション
    // =========================================

    // 1件の修正申請には、複数の休憩修正データを登録できる
    // attendance_correction_breaks.request_id
    // と attendance_correction_requests.id を関連付ける
    //
    // 第2引数の 'request_id' は子テーブル側の外部キー
    public function correctionBreaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class, 'request_id');
    }
}
