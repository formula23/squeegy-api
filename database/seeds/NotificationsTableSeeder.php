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

        
        
        Notification::create([
            'name'=>'Notify Customer - Schedule',
            'key'=>'messages.order.push_notice.schedule',
        ]);

        Notification::create([
            'name'=>'Notify Customer - Subscription Schedule',
            'key'=>'messages.order.push_notice_subscription.schedule',
        ]);

        Notification::create([
            'name'=>'Notify Customer - Corp Schedule',
            'key'=>'messages.order.push_notice_corp.schedule',
        ]);
        
        
        
        Notification::create([
            'name'=>'Notify Customer - Assign',
            'key'=>'messages.order.push_notice.assign',
        ]);

        Notification::create([
            'name'=>'Notify Customer - Schedule Assign',
            'key'=>'messages.order.push_notice.schedule_assign',
        ]);
        
        Notification::create([
            'name'=>'Notify Corp Customer - Assign',
            'key'=>'messages.order.push_notice_corp.assign',
        ]);
        
        
        
        Notification::create([
            'name'=>'Notify Customer - Enroute',
            'key'=>'messages.order.push_notice.enroute',
        ]);

        Notification::create([
            'name'=>'Notify Customer - Enroute Manual',
            'key'=>'messages.order.push_notice.enroute_manual',
        ]);

        Notification::create([
            'name'=>'Notify Corp Customer - Enroute',
            'key'=>'messages.order.push_notice_corp.enroute',
        ]);

        
        
        Notification::create([
            'name'=>'Notify Customer - Start',
            'key'=>'messages.order.push_notice.start',
        ]);

        
        
        Notification::create([
            'name'=>'Notify Customer - Done',
            'key'=>'messages.order.push_notice.done',
        ]);

        
        
        Notification::create([
            'name'=>'Notify Corp Customer - Cancel',
            'key'=>'messages.order.push_notice.cancel',
        ]);

        
        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints
    }
}
