<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 勤怠APIのControllerを読み込む
use App\Http\Controllers\Api\V1\AttendanceRecordController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =========================================
// API v1：勤怠一覧取得（AP01）
// =========================================

// 認証不要で勤怠一覧を取得する
Route::get('/v1/attendance-records', [AttendanceRecordController::class, 'index'])
    ->name('api.v1.attendance-records.index');

// =========================================
// AP02：指定したIDの勤怠詳細を取得するAPI
// =========================================
Route::get('/v1/attendance-records/{id}', [AttendanceRecordController::class, 'show'])
    ->name('api.v1.attendance-records.show');

// =========================================
// AP03：勤怠を新規登録するAPI
// Sanctum認証済みのユーザーだけが利用できる
// =========================================
Route::post('/v1/attendance-records', [AttendanceRecordController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.attendance-records.store');

// 勤怠更新API（認証が必要）
Route::patch('/v1/attendance-records/{id}', [AttendanceRecordController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.v1.attendance-records.update');

// PUTによる勤怠更新API
Route::put('/v1/attendance-records/{id}', [AttendanceRecordController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.v1.attendance-records.put');

// 認証済みユーザーによる勤怠削除API
Route::delete('/v1/attendance-records/{id}', [AttendanceRecordController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.v1.attendance-records.destroy');
