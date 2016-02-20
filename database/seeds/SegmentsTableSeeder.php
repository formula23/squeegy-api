<?php

use App\Segment;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class SegmentsTableSeeder extends Seeder
{
    public function run()
    {

        Segment::create([
            'name'=>'Subscriber',
            'description'=>'Email signup, no app.',
        ]);

        Segment::create([
            'name'=>'User',
            'description'=>'Downloaded app, free/no wash.',
        ]);

        Segment::create([
            'name'=>'Customer',
            'description'=>'Paid for at least one wash.',
        ]);

        Segment::create([
            'name'=>'Repeat Customer',
            'description'=>'Paid for 2+ washes.',
        ]);

        Segment::create([
            'name'=>'Advocate',
            'description'=>'Referred code and friend used.',
        ]);
    }
}
