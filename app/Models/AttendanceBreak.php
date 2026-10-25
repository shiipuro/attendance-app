<?php

namespace App\Models;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// データベースのテーブルを操作する基本クラス
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    // Factory機能を使用できるようにする
    use HasFactory;

    // =========================================
    // ① 使用するテーブルを指定する
    // =========================================

    // このModelが操作するテーブルはbreaks
    // Laravelの自動推測とは名前が異なるため明示的に指定する
    protected $table = 'breaks';

    // =========================================
    // ② データの登録・更新を許可するカラム
    // =========================================

    // create()やupdate()で一括代入できるカラム
    protected $fillable = [
        'attendance_id', // どの勤怠に紐づく休憩なのか
        'break_in',      // 休憩開始時刻
        'break_out',     // 休憩終了時刻
    ];

    // =========================================
    // ③ Attendanceモデルとのリレーション
    // =========================================

    // 1件の休憩は、1件の勤怠データに所属する
    // breaks.attendance_id と attendances.id を関連付ける
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
