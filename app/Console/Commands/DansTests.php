<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class DansTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squeegy:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Random tests... Playground...';

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
    public function handle()
    {
        

        $users = User::take(30)->get();

        foreach($users as $user) {
            $this->info($user->name);
            $this->info($user->first_name());
            $this->info($user->last_name());
            $this->info("****************");
        }

    }
}
