<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Dọn dẹp token xác thực email hết hạn hàng ngày
        $schedule->call(function () {
            DB::table('email_verification_tokens')
                ->where('created_at', '<', now()->subMinutes(60)) // hết hạn sau 60 phút thì xóa
                ->delete();
        })->daily();
        
        // Dọn dẹp token reset mật khẩu hết hạn hàng ngày
        $schedule->call(function () {
            DB::table('password_reset_tokens')
                ->where('created_at', '<', now()->subMinutes(60))
                ->delete();
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

