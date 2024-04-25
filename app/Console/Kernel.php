<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 'App\Console\Commands\OrderAssignment',
        // 'App\Console\Commands\AddNewSlot',
        // 'App\Console\Commands\DatabaseBackUp',
        // 'App\Console\Commands\OrderPaymentStatus'
        // 'App\Console\Commands\ActivateLaterOrders',
        'App\Console\Commands\LoadPayThemProducts',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        
        // ----------------------------
        // Notifications
        // ----------------------------
        $schedule->command('Send:SheduledPushNotifications')->everyMinute();
        $schedule->command('Send:PushNotificationForUsersCart')->everyThirtyMinutes();
        $schedule->command('Send:PushNotificationForNewUsers')->everyTenMinutes();

        // ----------------------------
        // PayThem products updates
        // ----------------------------
        $schedule->command('command:LoadPayThemProducts')->daily();
        
        // ----------------------------
        // Home_static json data updates
        // ----------------------------
        $schedule->command('command:HomeStaticJsonUpdate home_static_1 en')->cron('*/20 * * * *');
        $schedule->command('command:HomeStaticJsonUpdate home_static_1 ar')->cron('*/20 * * * *');
        $schedule->command('command:HomeStaticJsonUpdate home_static_instant en')->cron('*/20 * * * *');
        $schedule->command('command:HomeStaticJsonUpdate home_static_instant ar')->cron('*/20 * * * *');
        $schedule->command('command:HomeStaticStoreJsonUpdate')->cron('*/20 * * * *');

        // ----------------------------
        // Store shedules (activation and deactivation)
        // ----------------------------
        $schedule->command('command:StoreActivate')->cron('*/10 * * * *');

        // ----------------------------
        // Assign storekeeper for orders in next 30 minutes to deliver
        // ----------------------------
        // $schedule->command('command:activatelaterorders')->everyTenMinutes();
        
        // ----------------------------
        // Old cron jobs not in use
        // ----------------------------
        // $schedule->command('RetailMart:UpdateStock')->everyTenMinutes();
        // $schedule->command('command:orderassignment')->everyMinute();
        // $schedule->command('command:addnewslot')->dailyAt('00:01');
        // $schedule->command('command:databasebackup')->dailyAt('01:00');
        // $schedule->command('command:orderpaymentstatus')->everyFiveMinutes();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
