<?php namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdatePassword extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:update_password';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update a users password';

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
		$user = User::where('email', $this->argument('user_email'))->first();
		$user->password = $this->argument('new_password');
		$user->save();
		$this->info("Password updated for ".$this->argument('user_email'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['user_email', InputArgument::REQUIRED, 'User Email'],
			['new_password', InputArgument::REQUIRED, 'New password to set'],
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
