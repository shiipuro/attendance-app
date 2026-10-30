<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendanceRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * 勤怠データの所有者だけ更新を許可する
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // ログインユーザーのIDと勤怠のユーザーIDを比較する
        return $user->id === $attendance->user_id;
    }

    /**
 * 勤怠データの所有者だけ削除を許可する
 */
public function delete(User $user, Attendance $attendance): bool
{
    // ログインユーザーと勤怠の所有者が同じか確認する
    return $user->id === $attendance->user_id;
}

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        //
    }
}
