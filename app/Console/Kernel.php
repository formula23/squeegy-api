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
        'App\Console\Commands\FixAdvocates',
        'App\Console\Commands\UserLocations',
        'App\Console\Commands\UpdateCM',
        'App\Console\Commands\CMTest',
        'App\Console\Commands\ImportMixpanel',
        'App\Console\Commands\FixLastWash',
        'App\Console\Commands\SanitizeDb',
        'App\Console\Commands\ChargeOrder',
        'App\Console\Commands\ReviewWashNotification',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
        $schedule->command('db:backup')->cron('* */6 * * *');
		$schedule->command('order:review_wash_notice')->everyMinute();
	}

}
