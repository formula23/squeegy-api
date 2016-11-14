<?php

namespace App\Console\Commands;

use App\Order;
use App\Partner;
use App\Squeegy\Orders;
use App\Squeegy\Transformers\AddonTransformer;
use App\User;
use App\Vehicle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use GeometryLibrary\PolyUtil;

use Services_Twilio as TwilioRestClient;
use Services_Twilio_Twiml as TwilioTwiml;

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
    public function __construct(TwilioRestClient $twilio)
    {
        parent::__construct();

        $this->twilio = $twilio;

//        dd($this->twilio->account->incoming_phone_numbers);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $addon = new AddonTransformer();
        dd($addon);

        $order = Order::find(12714);

        dd($order->order_details);



        $arr = [
            "key_box" => [
                "label"=>"KeyBox#",
                "value"=>"255",
            ],
            "parking_lot" => [
                "label"=>"Parkign Lot",
                "value"=>"A",
            ]
        ];

        dd(json_encode($arr));

//        foreach($this->twilio->account->incoming_phone_numbers->getIterator(0, 50, array("FriendlyName"=>'AirTng')) as $number)
//        {
//            $this->info($number->phone_number);
//            $this->info($number->friendly_name);
////            dd($number);
//        }
        dd('done');
        
//        dd(Orders::get_location("33.9752461","-118.4198021"));
        $dist = Orders::get_distance("33.975,-118.42", "33.975,-118.42");
$this->info($dist);
        \Log::info("Distance to next job: ".$dist);
        dd("end");
        $start_date = Carbon::create(2016,5,11,9,0,0);

        $current_day = Carbon::create(2016,5,18,15,23,0);


        $diff_wks = $start_date->diffInWeeks($current_day);
//dd($diff_wks);
        print $diff_wks;
        dd( $start_date->addWeeks( $diff_wks + ($diff_wks % 2 ? 2 : 1 ) ) );



        $user = User::find(7566);
//        $user = User::find(525);

//        dd($user->partners->isEmpty());

        dd( ! $user->partners->isEmpty() ? $user->partners->first()->id : "" );

        dd("Done");

        $days = array(4,5);
//        $day_iterator=date('w');
        $day_iterator=5;
        do {
            $position = array_search($day_iterator, $days);
            if($position !== false)
            {
                $first_part = array_splice($days, $position);
                $days = array_merge($first_part, $days);
                break;
            }
            $day_iterator++;
            
        } while($day_iterator <= 6);

        dd($days);

        $days = array(3,4,5,6);

        for($current_day=date('N');$current_day++;$current_day<8)
        {
            $this->info('cur day:'.$current_day);
            $position = array_search($current_day, $days);

            $this->info("pos: ".$position);
            if($position===false) continue;

            $first_part=[];
            if($position !== false) {
                $this->info('--pos:'.$position);
                $first_part = array_splice($days, $position);
                print_r($first_part);
//                $this->info($first_part);
            }
            $days = array_merge($first_part, $days);
            break;
        }
//        $current_day = 4;

        dd($days);




        dd("done");


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
