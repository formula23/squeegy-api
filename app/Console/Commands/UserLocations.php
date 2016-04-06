<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $locations = DB::select('SELECT orders.location FROM user_segments, orders WHERE user_segments.user_id = orders.user_id AND `segment_id` = 4 AND orders.status = \'done\'');

        $locations = DB::select('SELECT orders.location FROM orders WHERE user_id IN (SELECT users.id FROM orders
                JOIN users ON orders.user_id = users.id
                LEFT JOIN user_segments ON users.id = `user_segments`.user_id
                WHERE `status` = \'done\'
                GROUP BY orders.user_id HAVING sum(charged)/100 > 300
                ORDER BY sum(charged) DESC)
                AND orders.status = \'done\'');

        foreach($locations as $location) {
            $loc = json_decode($location->location);
            $this->info("T,".$loc->lat.",".$loc->lon);
        }



    }
}
