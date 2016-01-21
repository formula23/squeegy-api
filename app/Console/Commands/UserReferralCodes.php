<?php namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UserReferralCodes extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:populate-referral-codes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$users = User::whereNull('referral_code')->get();

		foreach($users as $user) {
			$user->referral_code = User::generateReferralCode();
			$user->save();
			$this->info("#$user->id - Updated");
		}
		return;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
//			['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
//			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
