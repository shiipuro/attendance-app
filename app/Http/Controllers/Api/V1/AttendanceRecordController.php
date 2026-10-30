<?php

namespace App\Http\Controllers\Api\V1;

// APIで既存の勤怠データを取得・操作するためのモデル
use App\Models\Attendance;

// Controllerの基本機能
use App\Http\Controllers\Controller;

// APIから送られてきたリクエストを受け取る
use Illuminate\Http\Request;

// APIで公開する勤怠データの項目を整理する
use App\Http\Resources\AttendanceRecordResource;

// 勤怠一覧APIの検索条件をチェックするFormRequest
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;

use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;

use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;


class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // =========================================
// ① 勤怠一覧を取得するAPI（AP01）
// =========================================
    public function index(IndexAttendanceRecordRequest $request)
    {
    // 勤怠データを取得するための準備
    $query = Attendance::query();

    // ユーザーIDが指定されている場合は絞り込む
    $userId = $request->validated('user_id');

    if ($userId) {
        $query->where('user_id', $userId);
    }

    // 日付が指定されている場合は絞り込む
    $date = $request->validated('date');

    if ($date) {
        $query->whereDate('date', $date);
    }

    // バリデーション済みの年月を取得する
    $month = $request->validated('month');

    // 年月が指定されている場合、その月の勤怠に絞り込む
    if ($month) {
    $query->where('date', 'like', $month . '%');
    }

    // ユーザーと休憩データをまとめて取得する
    // N+1問題を防ぐためにEager Loadingを使用する
    $query->with(['user', 'breaks']);

    // 表示件数を取得する（指定がなければ20件）
    $perPage = $request->validated('per_page') ?? 20;

    // 日付の新しい順に並べて、ページごとに取得する
    $attendances = $query->orderByDesc('date')->paginate($perPage);

    // Resourceを使って、公開する項目だけをJSONで返す
    return AttendanceRecordResource::collection($attendances);
}

    /**
     * Store a newly created resource in storage.
     */
    // =========================================
    // ③ 勤怠を新規登録するAPI（AP03）
    // =========================================
    public function store(StoreAttendanceRecordRequest $request)
    {
    // バリデーション済みの入力データを取得する
    $data = $request->validated();

    // ログイン中のユーザーIDを設定する
    // 外部から送られたuser_idは使用しない
    $data['user_id'] = $request->user()->id;

    // 勤怠データをデータベースに登録する
    $attendance = Attendance::create($data);

    // ユーザー情報と休憩情報を読み込む
    $attendance->load(['user', 'breaks']);

    // 登録した勤怠をJSONで返す（HTTP 201）
    return (new AttendanceRecordResource($attendance))
        ->response()
        ->setStatusCode(201);
}

    /**
     * Display the specified resource.
     */
    //========================================
    // ② 勤怠詳細を取得するAPI（AP02）
    // =========================================
    public function show(string $id)
    {
    // 指定されたIDの勤怠を取得する
    // ユーザー情報と休憩情報もまとめて取得する
    $attendance = Attendance::with(['user', 'breaks'])
        ->findOrFail($id);

    // Resourceを使って勤怠詳細をJSONで返す
    return new AttendanceRecordResource($attendance);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRecordRequest $request, string $id)
{
    // 更新対象の勤怠データをIDで取得する
    // 存在しないIDの場合は404エラーを返す
    $attendance = Attendance::findOrFail($id);

    // ログインユーザーがこの勤怠を更新できるかPolicyで確認する
    $this->authorize('update', $attendance);

    // バリデーション済みの入力データで勤怠を更新する
    $attendance->update($request->validated());

    // 更新後のユーザー情報と休憩データを読み込む
    $attendance->load(['user', 'breaks']);

    // 更新した勤怠データをJSON形式で返す
    return new AttendanceRecordResource($attendance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        // 削除対象の勤怠データを取得する
        $attendance = Attendance::findOrFail($id);

        // 本人の勤怠かどうかをPolicyで確認する
        $this->authorize('delete', $attendance);

        // 勤怠データを削除する
        $attendance->delete();

        // 削除成功を204 No Contentで返す
        return response()->noContent();
}
    }

