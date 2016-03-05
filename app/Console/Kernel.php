<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\CreateUser',
        'App\Console\Commands\PushNotification',
        'App\Console\Commands\DbBackup',
        'App\Console\Commands\UserReferralCodes',
        'App\Console\Commands\StatsDaysBetweenWashes',
        'App\Console\Commands\UpdatePassword',
        'App\Console\Commands\UserSegmentation',
        'App\Console\Commands\PayrollGenerate',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
        $schedule->command('db:backup')->cron('* */4 * * *');
	}

}
