<?php

use App\Notification;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class NotificationsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Notification::truncate();

        Notification::create([
            'name'=>'Wash Review',
            'key'=>'messages.order.push_notice.review_wash',
        ]);

        Notification::create([
            'name'=>'Arriving Soon',
            'key'=>'messages.order.push_notice.arriving_soon',
        ]);
        
        Notification::create([
            'name'=>'Change Washer - Original Washer',
            'key'=>'messages.order.push_notice.change_washer.original_washer',
        ]);

        Notification::create([
            'name'=>'Change Washer - New Washer',
            'key'=>'messages.order.push_notice.change_washer.new_washer',
        ]);
        
        Notification::create([
            'name'=>'Change Washer - Customer',
            'key'=>'messages.order.push_notice.change_washer.customer',
        ]);
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints
    }
}
