<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class FixAdvocates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:fix-advocates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User fix advocates';

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
        $i=1;
        User::customers()->chunk(1000, function($users) use (&$i) {

            foreach($users as $user) {

                if($user->is_advocate()) {

                    $this->info($i."-".$user->name."-".$user->email."--".$user->id. "-".$user->completedPaidOrders()->get()->last()->done_at->format('Y/m/d'));
                    $i++;
                }
            }


        });
    }
}
