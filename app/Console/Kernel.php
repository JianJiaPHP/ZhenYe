<?php

namespace App\Console;

use App\Console\Commands\CancelTheOrder;
use App\Console\Commands\FilEncapsulation;
use App\Console\Commands\FilGrant;
use App\Console\Commands\GiveRelease;
use App\Console\Commands\HycDayRelease;
use App\Console\Commands\UsdtRecharge;
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
        //fil封装定时处理
        FilEncapsulation::class,
        //fil矿机 产币发放
        FilGrant::class,
        //fil线性发放::class
        FilEncapsulation::class,
        //HYC矿机 产币发放
        HycDayRelease::class,
        //USDT充值
        UsdtRecharge::class,
        //赠送积分线性365天释放
        GiveRelease::class,
        //交易市场挂卖撤回
        CancelTheOrder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        //1分钟 fil封装定时处理
        $schedule->command('command:FilEncapsulation')->everyMinute();
        //02:00 fil矿机 产币发放
        $schedule->command('command:FilGrant')->dailyAt('02:00');
        //02:15 fil线性发放::class
        $schedule->command('command:FilLinearRelease')->dailyAt('02:15');
        //1:30 HYC矿机 产币发放
        $schedule->command('command:HycDayRelease')->dailyAt('01:30');
        //分钟 USDT充值扫描
        $schedule->command('command:UsdtRecharge')->everyMinute();
        //赠送积分365天线性释放
        $schedule->command('command:GiveRelease')->dailyAt('00:05');
        //交易市场挂卖撤回
        $schedule->command('command:CancelTheOrder')->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
