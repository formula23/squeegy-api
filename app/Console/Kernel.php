<?php namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use File;

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
        'App\Console\Commands\DansTests',
        'App\Console\Commands\AddMissingUserToCM',
        'App\Console\Commands\AssignScheduleWashes',
        'App\Console\Commands\AdvancePartnerDate',
        'App\Console\Commands\EmailCustomerReceipt',
        'App\Console\Commands\WasherTipNotify',
        'App\Console\Commands\UpdatePartnerDays',
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
//		$schedule->command('order:review_wash_notice')->cron('* 8-20 * * *');
		$schedule->command('order:assign-scheduled')->cron('* 9-19 * * *')->appendOutputTo($this->dir('assign-scheduled'));
		$schedule->command('squeegy:advance_partner_dates')->cron('0 10-19 * * *');
	}

    protected function dir($process_name)
    {
        $base_dir = storage_path('logs')."/$process_name".$this->dir_structure();

        if( ! File::exists($base_dir)) {
            File::makeDirectory($base_dir, 0775, true);
        }

        $dir = $base_dir."/".Carbon::now()->format('d').".log";
        
        return $dir;
    }

    protected function dir_structure()
    {
        return Carbon::now()->format('/Y/m');
    }

}
