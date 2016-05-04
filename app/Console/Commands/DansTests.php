<?php

namespace App\Console\Commands;

use App\User;
use App\Vehicle;
use Illuminate\Console\Command;
use GeometryLibrary\PolyUtil;

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

        $vehicle = Vehicle::find(15);

        $this->info($vehicle->full_name());
        dd("done");

        $response =  PolyUtil::containsLocation(
//            ['lat' => 34.098575, 'lng' => -118.322472], // point array [lat, lng]
            ['lat' => 34.098646, 'lng' => -118.320884], // point array [lat, lng]
//            ['lat' => 23.886, 'lng' => -65.269], // point array [lat, lng]
            [ // poligon arrays of [lat, lng]
                ['lat' => 34.098939, 'lng' => -118.322376],
                ['lat' => 34.098912, 'lng' => -118.320562],
                ['lat' => 34.098015, 'lng' => -118.320562],
                ['lat' => 34.098024, 'lng' => -118.322333],
            ]);

        dd($response); // false

    }
}
