<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Attendance;
use App\Policies\AttendanceRecordPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    // 勤怠データの認可にはAttendanceRecordPolicyを使用する
    Attendance::class => AttendanceRecordPolicy::class,
];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
