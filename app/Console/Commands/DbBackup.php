<?php namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DbBackup extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'db:backup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Dump the database for backup';

    protected $dir = '';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

        $this->dir = storage_path().'/database/auto_backup/'.Carbon::now()->format("D");

        if ( ! file_exists($this->dir)) {
            mkdir($this->dir, 0777, true);
        }

	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $db_creds = \Config::get("database.connections.".\Config::get('database.default'));

        $cmd_parts = [
            "mysqldump",
            "-u".$db_creds['username'],
            "-p'".$db_creds['password']."'",
            $db_creds['database']." > ",
            $this->dir."/".$db_creds['database'].".".Carbon::now()->format("H").".sql",
        ];

        $process = new Process(implode(" ", $cmd_parts));
        $process->run();

        if ( ! $process->isSuccessful()) {
            \Bugsnag::notifyException($process->getErrorOutput());
        }
	}

}
