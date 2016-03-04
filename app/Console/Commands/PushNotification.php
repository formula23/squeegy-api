<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PushNotification extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'push:notification';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send Push Notification';

    protected $sns_client = null;
    protected $message = "";
    protected $user=null;

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
	public function fire()
	{
        $this->sns_client = \App::make('Aws\Sns\SnsClient');

        $type = $this->argument('type');
        $topic_name = $this->option('topic_name');
        $this->message = $this->option('message');

        if(!$this->message) {
            $this->error('Message is required!');
            return;
        }

        if($type == "topic" && !$topic_name) {
            $this->error('Topic name is required to create a topic!');
            return;
        }

        $default_users = \DB::table('users')
                ->select(['id','push_token', 'target_arn_gcm'])
            ->where('email', 'dan@formula23.com')
            ->orWhere('email', 'sinisterindustries@yahoo.com')
            ->orWhere('email', 'chas2@f23.com')
            ->get();

//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')
//            ->whereNotIn('id', function($q) {
//                $q->select('user_id')
//                    ->from('orders')
//                    ->where('status', 'done')
//                    ->where('confirm_at', '>', '2015-11-26')
//                    ->orWhere(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2015-12-03');
//            })->get();

        $zip_codes = explode(",", $this->option('zip_codes'));

        $users_qry = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(['users.id', 'push_token', 'target_arn_gcm'])
//            ->where('app_version', '1.4')
//            ->where('push_token', '!=', '')
//            ->where('status','done')
            ->where(function($q) use ($zip_codes) {
                if( ! empty($zip_codes)) {
                    foreach($zip_codes as $zip_code) {
                        $q->orWhere('location', 'like', '%'.$zip_code.'%');
                    }
                }
            })
            ->where('location', 'not like', '%31050 Venice Blvd%')
            ->whereNotIn('user_id', function($q) {
                $q->select('user_id')
                    ->from('orders')
                    ->whereIn('status', ['enroute', 'start', 'done'])
                    ->where('confirm_at', '>', \DB::raw('DATE_SUB(NOW(), INTERVAL 1 WEEK)'));
            })
            ->groupBy('user_id');

//        $users = \DB::table('users')->select(['id','push_token'])->where('app_version', '1.4')->where('push_token', '!=', '')->get();

        //daily anonymous users push
//        $users_qry = \DB::table('users')->select(['id','push_token'])->where('push_token', '!=', '')
//            ->where('email', 'like', '%squeegyapp-tmp.com%')
//            ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '=', '2016-02-10')
////            ->where(\DB::raw('DATE_FORMAT(created_at, \'%Y-%m-%d\')'), '<=', '2016-01-26')
//            ->orderBy('id');


        ///limits
//        if($this->option('take')) {
//            if($this->option('skip')) $users_qry->skip($this->option('skip'));
//            $users_qry->take($this->option('take'));
//        }

        $users = $users_qry->get();

//        $users = \DB::select('SELECT users.id, users.push_token, users.`target_arn_gcm`
//          FROM orders, users
//          WHERE ((push_token IS NOT NULL AND push_token != \'\') OR target_arn_gcm IS NOT NULL)
//          AND orders.user_id = users.id
//          AND (charged > 0 OR discount_id IN (27,28,55,56,57,58))
//          AND status IN (\'assign\',\'enroute\',\'start\',\'done\')
//          AND orders.user_id NOT IN (SELECT user_id FROM orders WHERE status IN (\'enroute\', \'start\', \'done\')
//          AND confirm_at > DATE_SUB(NOW(), INTERVAL 1 WEEK))
//          GROUP BY user_id
//          LIMIT 200');


        //users & non-paid cust
//        $users = \DB::select('SELECT users.id, users.`push_token`, users.`target_arn_gcm`
//          FROM users
//          WHERE ((push_token IS NOT NULL AND `push_token` != \'\') OR `target_arn_gcm` IS NOT NULL)
//          AND users.id NOT IN (
//            SELECT users.id
//            FROM orders, users
//            WHERE orders.user_id = users.id
//            AND (charged > 0 OR discount_id IN (27,28,55,56,57,58))
//            AND `status` IN (\'assign\',\'enroute\',\'start\',\'done\') GROUP BY user_id)
//            LIMIT 2000
//            OFFSET 2000');


//        $users = \DB::select('SELECT id, push_token, `target_arn_gcm`
//                FROM users
//                WHERE users.id NOT IN (
//                    SELECT users.id
//                    FROM orders, users
//                    WHERE orders.user_id = users.id
//                    AND `status` IN (\'assign\',\'enroute\',\'start\',\'done\')
//                    AND confirm_at > DATE_SUB(NOW(), INTERVAL 1 WEEK)
//                    GROUP BY user_id
//                )
//            ');

        $users = \DB::select('SELECT id, push_token, `target_arn_gcm`
                FROM users WHERE users.id NOT IN (8,14,15,17,19,20,1976,24,25,28,29,31,32,33,35,34,37,38,39,40,41,43,44,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,63,64,65,2791,71,72,73,74,75,77,78,79,80,81,82,83,86,88,91,92,93,108,109,110,111,114,115,116,121,143,154,156,161,128,226,235,258,144,337,342,293,344,347,398,260,399,429,406,195,394,454,455,448,471,288,452,87,369,524,521,512,427,436,389,170,531,343,533,365,390,547,199,594,527,559,569,600,523,604,605,377,551,647,648,595,650,634,591,649,696,479,287,534,704,705,658,168,709,355,746,694,748,562,751,717,755,463,207,669,776,801,805,810,691,840,767,765,851,775,816,857,532,1432,940,880,860,945,929,986,795,837,781,974,824,993,997,2806,1009,2424,563,1022,1025,1016,1027,1028,1029,930,1033,1032,1035,1054,890,1065,883,1057,1067,916,1000,1073,1080,1082,1039,963,372,1125,4117,1132,1153,643,1162,1119,1164,644,1167,841,1158,1173,1174,3550,1159,1178,1127,1260,3070,1202,1177,1268,1171,1118,1215,1279,1218,1293,1307,1163,1301,1083,1315,1278,1319,437,1289,1379,3851,1373,971,1282,2876,1429,914,1423,1306,1297,1414,1031,1058,3144,752,1489,1415,1500,1570,1166,1376,1576,1579,1563,1549,1559,1613,1518,1593,1545,480,1622,1239,1627,1628,1547,1664,1662,1679,1554,1234,1695,1560,1729,1732,1665,1746,1691,1750,1715,456,740,1615,1389,1772,1467,1114,1818,950,1830,1323,1605,1816,1711,1802,2662,1402,1060,1856,1800,1860,1493,1731,1892,1893,1284,1590,1692,1366,1869,1424,1311,1933,1752,1958,1507,1927,1954,1957,1956,1506,1962,1965,1637,1924,1909,764,1911,3433,237,1617,1740,1488,1990,1109,1993,1997,638,1888,2062,2066,2057,2069,2022,2078,2049,2079,2096,1049,2111,2061,2110,1400,2081,690,996,1718,2038,2101,2145,2127,1987,2121,1369,1556,2175,2178,2179,2181,2045,2184,2188,1966,2032,2193,2218,2219,2092,2076,2225,2226,1999,1259,1466,2260,2835,2148,2273,2039,1803,1110,2331,2332,1864,1544,2195,2114,2303,2364,2304,2376,1931,2356,2210,1192,2295,2397,2395,209,1710,2419,1836,2308,2432,2155,1331,2429,1896,1840,2269,2438,2461,1912,2464,2318,2337,2526,2530,2473,2533,2377,2535,2537,2539,2019,2455,2484,2555,2384,2558,2587,2592,2433,2593,2595,2596,2598,1942,2606,2551,2621,2626,2623,2631,2627,2647,2630,2186,2654,2655,2391,2676,2664,2678,1881,2534,2671,1951,2682,2491,2720,2658,2695,2727,1903,2497,2712,2542,2751,2065,2757,2742,2672,2650,1618,89,2731,2783,2786,2787,2772,2190,2291,2686,2782,2475,2261,431,2818,214,653,1955,819,4689,2262,2836,142,2841,1137,2060,2884,2887,2635,2897,881,2794,2896,2899,2777,2840,2826,2923,2925,2609,2927,2939,2903,1638,515,2816,2768,2944,2578,2952,2883,936,2962,2965,2963,2970,2478,2972,2978,2979,2977,2976,2997,3001,3000,1524,2931,2615,2996,3051,2745,2208,1952,3034,3056,2886,2693,3022,3058,2987,1204,3064,3067,3069,3061,2941,3041,3033,3073,1190,462,1063,2960,2511,3006,3095,1916,1309,3113,3066,3131,3139,3140,3142,3127,3158,2509,1321,3191,3189,3102,3196,3188,3098,3212,3204,1225,3207,3016,3250,804,3092,3226,3108,3182,1222,2602,3328,3330,3175,3335,3149,3023,3367,3394,3257,3376,3393,3277,3392,3426,3431,3448,2603,3454,3014,3514,3491,3223,3494,2585,2821,1765,3104,3542,1895,2476,3484,3378,3573,3300,2849,2421,3615,3523,3660,3109,1317,3604,3554,3710,3712,3733,2255,3770,3055,2713,1773,3371,3772,3882,3923,3876,3931,3370,3934,3922,3332,3952,3940,3879,3886,3988,3843,4055,2600,4065,4071,3939,3829,4092,4086,4021,4093,4119,4106,4113,3612,4031,4044,4155,4159,3862,4131,4168,4004,4121,4215,4139,4222,4227,4189,4235,4213,4154,4236,4263,4240,1106,4280,3928,4281,4255,1334,4125,4292,4308,4309,4209,4193,5290,4232,4321,4322,4248,3631,4336,4370,3814,4258,4360,4247,4355,4372,4380,4132,4120,4234,2014,4141,4231,4423,4435,4126,4036,4442,4417,4375,4045,3855,4428,4493,4278,4266,3745,4220,4474,4554,4494,4267,4473,4562,4557,4563,3171,4568,2403,4566,4407,4376,4626,4635,3750,4291,4642,4644,4645,4646,4651,4085,4578,4479,4586,4670,4673,4009,4672,4613,4713,4715,4717,4354,4353,4729,4734,4315,2730,2250,4576,4536,4329,4138,4311,4874,4775,4819,4420,4785,4786,4918,4924,4911,3273,4938,4391,2409,4944,3290,4949,2919,2950,4383,4994,4980,4512,2833,4999,3869,5003,3760,4109,4630,4966,4855,5036,5030,4668,1433,3266,5029,4431,1452,4929,4485,4830,4412,5057,4730,5062,4731,5079,4808,4854,4146,5087,5028,4492,4960,423,5073,4660,4310,5126,5037,2793,5127,4136,5077,5083,5095,5160,5156,4869,4894,5099,3415,4265,4825,4895,5186,3117,5133,5192,4930,3816,4047,4982,5198,3293,5089,4379,5238,5258,4863,4181,4484,5266,5279,5285,4105,587,5299,5300,5250,5306,5291,5109,5309,4771,4298,5328,5118,5372,5381,5380,5403,5283,5423,4782,2808,5359,2942,5389,4403,5355,5449,5450,2297,5462,5483,5485,5310,3020,5495,5425,1412,5502,5504,5356,3641,4406,3606,5515,5524,5526,5159,4526,5344,4514,2636,3919,5590,5624,1044,5454,5658,3476) AND email NOT LIKE \'old%\'
            ');

        $send_list = array_merge($users, $default_users);

        $this->info("user count: ".count($send_list));
        $this->info("publish message: ".$this->message);

        $this->sns_client = \App::make('Aws\Sns\SnsClient');

        if($type == "topic") {

//                $this->info('Topic created: '.$topic_name);
//
//                if($this->argument('env') == "test") {
//
//                } else {
//
//                    $resp = $this->sns_client->CreateTopic(['Name' => $topic_name]);
//
//                    $topic_arn = $resp->get('TopicArn');
//                    $this->info("TopicArn: ".$topic_arn);
//
//                    foreach($send_list as $user) {
//                        if (empty($user->push_token) && empty($user->target_arn_gcm)) continue;
//
//                        $this->sns_client->Subscribe([
//                            'TopicArn' => $topic_arn,
//                            'Protocol' => 'application',
//                            'Endpoint' => $this->push_token($user),
//                        ]);
//                        $this->_output($user);
//                    }
//
//                    $this->info('Publish to TopicArn');
//                    $this->publish($topic_arn);
//
//                }

        } else {
            foreach ($send_list as $this->user) {
                $this->publish();
            }
        }

        $this->info("Done!");

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['env', InputArgument::OPTIONAL, 'Run as test or live', 'test'],
			['type', InputArgument::OPTIONAL, 'Create a topic or send direct message', 'direct'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['message', null, InputOption::VALUE_REQUIRED, 'The message to send.', null],
			['topic_name', null, InputOption::VALUE_OPTIONAL, 'Topic name.', null],
			['zip_codes', null, InputOption::VALUE_OPTIONAL, 'Zip codes', null],
            ['skip', null, InputOption::VALUE_OPTIONAL, 'Skip', null],
            ['take', null, InputOption::VALUE_OPTIONAL, 'Take', null],
		];
	}

    /**
     * @param $id
     * @param $push_token
     * @internal param $user
     */
    protected function _output($id, $push_token)
    {
        $this->info('user id: ' . $id." -- ".$push_token);
    }

    private function publish()
    {
        foreach(['push_token', 'target_arn_gcm'] as $endpoint_field) {

            if(empty($this->user->{$endpoint_field})) continue;

            $endpoint_arn = $this->user->{$endpoint_field};

            $target = (preg_match('/gcm/i', $endpoint_arn) ? "gcm" : "apns" );

            try {

                if($this->argument('env') == "live") {

                    if($target=="apns") {
                        $platform = env('APNS');
                        $payload = [
                            'aps' => [
                                'alert' => $this->message,
                                'sound' => 'default',
                                'badge' => 0
                            ],
                        ];

                    } else {
                        $platform = env('GCM');
                        $payload = [
                            'data' => [
                                'title' => 'Squeegy',
                                'message' => $this->message,
                                'url' => "squeegy://"
                            ],
                        ];
                    }

                    $publish_payload = [
                        'TargetArn' => $endpoint_arn,
                        'MessageStructure' => 'json',
                        'Message' => json_encode([
                            'default' => $this->message,
                            $platform => json_encode($payload)
                        ]),
                    ];

                    $this->sns_client->publish($publish_payload);
                }

                $this->_output($this->user->id, $endpoint_arn);

            } catch(\Exception $e) {
                $this->error($e->getMessage().'- '.$this->user->id.": ".$endpoint_arn);
            }
        }

        return;
    }

}
