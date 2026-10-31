<?php

// LaravelでURLと処理を結びつけるための機能
use Illuminate\Support\Facades\Route;

// 一般ユーザーの打刻処理を担当するController
use App\Http\Controllers\AttendanceController;

// 管理者のログイン・ログアウトを担当するController
use App\Http\Controllers\AdminAuthController;

// 管理者の勤怠管理・承認・スタッフ管理を担当するController
use App\Http\Controllers\AdminAttendanceController;

// 一般ユーザーの勤怠一覧・詳細・修正申請を担当するController
use App\Http\Controllers\UserAttendanceController;

// 勤怠レポート画面を表示するController
use App\Http\Controllers\ReportController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| アプリケーションのURLとControllerの処理を結びつけるファイル。
| GETは画面表示やデータ取得、POSTはデータの送信・保存に使用する。
|
*/


// ================================
// トップページ
// ================================

// トップページにアクセスするとwelcome画面を表示する
Route::get('/', function () {
    return view('welcome');
});


// ================================
// 一般ユーザー：打刻機能
// ================================

// 勤怠打刻画面を表示する
// ログインしている一般ユーザーのみアクセス可能
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

// 出勤・休憩開始・休憩終了・退勤の打刻データを保存する
Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware(['auth', 'verified']);


// ================================
// 一般ユーザー：勤怠一覧・詳細
// ================================

// ログインユーザーの月間勤怠一覧を表示する
Route::get('/attendance/list', [UserAttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

// ログイン・メール認証済みの一般ユーザーに勤怠レポートを表示する
// 仕様書PG14に合わせて /attendance/report を使用する
Route::get('/attendance/report', [ReportController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('reports.index');

// 仕様書PG05に合わせて、指定された勤怠IDの詳細画面を表示する
// {id}には対象の勤怠データのIDが入る
Route::get('/attendance/detail/{id}', [UserAttendanceController::class, 'show'])
    ->middleware(['auth', 'verified']);

// 勤怠詳細画面から送信された修正申請を保存する
Route::post('/attendance/detail/{id}', [UserAttendanceController::class, 'store'])
    ->middleware(['auth', 'verified']);


// ================================
// 一般ユーザー：修正申請
// ================================

// 自分が提出した修正申請の一覧を表示する
// 承認待ち・承認済みの申請を確認する
Route::get('/stamp_correction_request/list', [UserAttendanceController::class, 'applicationIndex'])
    ->middleware(['auth', 'verified']);

// 指定された修正申請の詳細画面を表示する
Route::get('/application/{id}', [UserAttendanceController::class, 'applicationShow'])
    ->middleware(['auth', 'verified']);


// ================================
// 管理者：ログイン・ログアウト
// ================================

// 管理者ログイン画面を表示する
Route::get('/admin/login', [AdminAuthController::class, 'create']);

// 管理者のメールアドレスとパスワードを確認してログインする
Route::post('/admin/login', [AdminAuthController::class, 'store']);

// 管理者のログアウト処理を行う
Route::post('/admin/logout', [AdminAuthController::class, 'destroy']);


// ================================
// 管理者：勤怠管理
// ================================

// 指定した日付のスタッフ全員の勤怠一覧を表示する
Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
    ->middleware('auth:admin');

// 指定された勤怠IDの詳細画面を表示する
Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
    ->middleware('auth:admin');

// 管理者が勤怠時間や休憩時間を直接修正して保存する
Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
    ->middleware('auth:admin');


// ================================
// 管理者：修正申請の確認・承認
// ================================

// 一般ユーザーから提出された修正申請一覧を表示する
Route::get('/stamp_correction_request/list/admin', [AdminAttendanceController::class, 'applicationIndex'])
    ->middleware('auth:admin');

// 指定された修正申請の詳細画面を表示する
Route::get('/stamp_correction_request/approve/{id}', [AdminAttendanceController::class, 'applicationShow'])
    ->middleware('auth:admin');

// 修正申請を承認し、勤怠・休憩データに反映する
Route::post('/stamp_correction_request/approve/{id}', [AdminAttendanceController::class, 'applicationApprove'])
    ->middleware('auth:admin');


// ================================
// 管理者：スタッフ管理
// ================================

// 登録されているスタッフの一覧を表示する
Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staffIndex'])
    ->middleware('auth:admin');

// 指定されたスタッフの月間勤怠一覧を表示する
Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendanceIndex'])
    ->middleware('auth:admin');

// 指定されたスタッフの月間勤怠データをCSV形式で出力する
Route::post('/export', [AdminAttendanceController::class, 'exportCsv'])
    ->middleware('auth:admin');
