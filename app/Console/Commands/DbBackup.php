<?php namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
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

        try {
            if ( ! File::exists($this->dir)) {
                File::makeDirectory($this->dir, 0775, true);
            }
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
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
            env('MYSQL_BIN')."/mysqldump",
            "-u".$db_creds['username'],
            "-p'".$db_creds['password']."'",
            $db_creds['database'],
            "--ignore-table=".$db_creds['database'].".cache",
			" > ",
            $this->dir."/".$db_creds['database'].".".Carbon::now()->format("H").".sql",
        ];

        $process = new Process(implode(" ", $cmd_parts));
        $process->run();

        if ( ! $process->isSuccessful()) {
            \Bugsnag::notifyException($process->getErrorOutput());
        }
	}

}
