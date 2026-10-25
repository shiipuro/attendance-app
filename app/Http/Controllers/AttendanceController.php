<?php

namespace App\Http\Controllers;

// フォームから送信されたデータを受け取る
use Illuminate\Http\Request;

// 現在ログインしているユーザーの情報を取得する
use Illuminate\Support\Facades\Auth;

// 現在の日付や時刻を取得する
use Carbon\Carbon;

// 勤怠データ（attendancesテーブル）を操作するModel
use App\Models\Attendance;

// 休憩データ（breaksテーブル）を操作するModel
use App\Models\AttendanceBreak;

class AttendanceController extends Controller
{
    // =========================================
    // 勤怠打刻画面を表示する処理
    // =========================================
    public function index()
    {
        // 現在ログインしているユーザーを取得する
        $user = Auth::user();

        // ログインユーザーの「今日の勤怠データ」を取得する
        // first()は条件に一致した最初の1件を取得する
        // データが存在しなければnullになる
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        // 今日の勤怠データがなければ「勤務外」
        if (!$attendance) {
            $user->attendance_status = '勤務外';

        // 退勤時刻が登録されていれば「退勤済」
        } elseif ($attendance->clock_out) {
            $user->attendance_status = '退勤済';

        // 今日の勤怠に終了時刻が未登録の休憩があれば「休憩中」
        // whereNull()でbreak_outがnullのデータを探す
        // exists()で該当するデータが存在するか確認する
        } elseif (
            AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_out')
                ->exists()
        ) {
            $user->attendance_status = '休憩中';

        // 上記に該当しなければ「出勤中」
        } else {
            $user->attendance_status = '出勤中';
        }

        // 現在の日付を画面表示用の形式に変換する
        $formattedDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');

        // 現在の時刻を「時:分」の形式に変換する
        $formattedTime = Carbon::now()->format('H:i');

        // ユーザー情報・日付・時刻をBladeに渡して画面を表示する
        return view('user.attendance-register', compact(
            'user',
            'formattedDate',
            'formattedTime'
        ));
    }

    // =========================================
    // 出勤・休憩開始・休憩終了・退勤を保存する処理
    // =========================================
    public function store(Request $request)
    {
        // 現在ログインしているユーザーを取得する
        $user = Auth::user();

        // フォームから送られたactionの値を取得する
        // actionによって実行する打刻処理を切り替える
        $action = $request->input('action');

        // =====================================
        // 出勤処理
        // =====================================
        if ($action === 'clock_in') {

            // 今日の日付を取得する
            $date = Carbon::today()->toDateString();

            // attendancesテーブルに新しい勤怠データを登録する
            // user_id：ログインユーザーのID
            // date：今日の日付
            // clock_in：現在の時刻
            Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in' => Carbon::now()->format('H:i:s'),
            ]);

            // 保存後、勤怠打刻画面に戻る
            return redirect('/attendance');
        }

        // =====================================
        // 休憩開始処理
        // =====================================
        if ($action === 'break_in') {

            // ログインユーザーの今日の勤怠データを取得する
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->first();

            // breaksテーブルに新しい休憩データを登録する
            // attendance_id：今日の勤怠ID
            // break_in：休憩を開始した時刻
            // break_outはまだ登録しない
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_in' => Carbon::now()->format('H:i:s'),
            ]);

            // 保存後、勤怠打刻画面に戻る
            return redirect('/attendance');
        }

        // =====================================
        // 休憩終了処理
        // =====================================
        if ($action === 'break_out') {

            // 休憩終了時刻が未登録の休憩データを探す
            // whereHas()で関連する勤怠データの条件も指定する
            // ログインユーザーの今日の休憩だけを対象にする
            $break = AttendanceBreak::whereNull('break_out')
                ->whereHas('attendance', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('date', Carbon::today()->toDateString());
                })
                ->first();

            // 見つかった休憩データの終了時刻を更新する
            $break->update([
                'break_out' => Carbon::now()->format('H:i:s'),
            ]);

            // 更新後、勤怠打刻画面に戻る
            return redirect('/attendance');
        }

        // =====================================
        // 退勤処理
        // =====================================
        if ($action === 'clock_out') {

            // ログインユーザーの今日の勤怠データを取得する
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', Carbon::today()->toDateString())
                ->first();

            // attendancesテーブルの退勤時刻を更新する
            $attendance->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
            ]);

            // 更新後、勤怠打刻画面に戻る
            return redirect('/attendance');
        }
    }
}
