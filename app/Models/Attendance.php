<?php

namespace App\Models;

// テスト用データなどを作成するFactory機能
use Illuminate\Database\Eloquent\Factories\HasFactory;

// データベースのテーブルを操作するための基本クラス
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Factory機能を使用できるようにする
    use HasFactory;

    // =========================================
    // ① データの登録・更新を許可するカラム
    // =========================================

    // create()やupdate()で一括代入できるカラムを指定する
    protected $fillable = [
        'user_id',   // 勤怠を登録したユーザーのID
        'date',      // 勤怠の日付
        'clock_in',  // 出勤時刻
        'clock_out', // 退勤時刻
    ];

    // =========================================
    // ② Userモデルとのリレーション
    // =========================================

    // 1件の勤怠は、1人のユーザーに所属する
    // attendances.user_id と users.id を関連付ける
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =========================================
    // ③ AttendanceBreakモデルとのリレーション
    // =========================================

    // 1件の勤怠には、複数の休憩データが紐づく
    // breaks.attendance_id と attendances.id を関連付ける
    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
}
