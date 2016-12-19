<?php namespace App\Console\Commands;

use App\Credit;
use App\Order;
use App\Squeegy\Facades\CampaignMonitor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PayrollGenerate extends Command {

    protected $mailer;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'payroll:generate';

    protected $washer_names = [
        1847 => 'Ricardo Alanso',
        2149 => 'Daniel Garcia',
        2900 => 'Victor Rodriguez',
        3198 => 'David Montano',
        6861 => 'Angel Rodriguez',
        10620 => 'Antonio Uribe',
        11353 => 'Javier Macias',
        15638 => 'Jorge Villalobos',
        15785 => 'Victor Valdez',
        16217 => 'Luis Lopez',
        16111 => 'Scott Parkhurst',
    ];

    protected $week_of=null;

    protected $hold_email_from_washer = [
        7527,
        10018,
        10691,
    ];

    protected $ids_to_process=[];

    protected $training = [
//        7896=>192, //Guillermo Lizardi
    ];

    protected $ignore_midweek_special = [
        7188, //santos
        7269, //salvador
        7527, //gonzalo
    ];

    protected $referral_code = [];

    protected $ignore_ids =[
        6119, //Ops
        1, //dan
        2, //andrew
        2882, //lopez
    ];

    protected $payroll_washers = [
        2882, //juan lopez
    ];

    protected $service_price = [
        1=>2500,
        2=>3500,
        3=>1500,
    ];

    protected $commission_pct = [
        'squeegy' => 0.30,
        'txn' => 0.025,
    ];

    protected $no_kit_rental = [
        6349, //Melvyn
        2882, //Juan lopez
        7146, //Leo
        5482, //juan lara
        7506, //Rafael
        7269, //salvador
        16217, //Luis
    ];

    protected $default_kit_fee = 25;

    protected $washer_kit_fee = [
//        11353 => 5,
//        16217 => 5,
    ];

    protected $kit_fee = [
//        7269 => 50, // salvador
//        7527 => 50, //gonzalo
    ];

    protected $min_weekly_worker_id = [
        //2149 => 500,
        //2900 => 500,
        //3198 => 600,
    ];


    protected $bonus = [
//        10018 => 250, //daniel medina
//        3198 => 65.95, //david mon
//        1847 =>188.08 //ricardo - back pay for mins
    ];

    protected $daily_bonus_worker_id = [
        3198 => [ //david
//            2=>15,
        ],
        15785 => [ //victor valdez
//            3=>8,
        ],
        16217 => [ //luis
//            6=>25,
        ]
    ];

    protected $referral_program = [
//        2149 => 250, // David G
//        10267 => 250, // Sheldon
    ];

	protected $min_day_worker_id = [
        1847 => [ //ricardo
//            0 => 120,
//            1 => 120,
//            2 => 120,
//            3 => 120,
//            4 => 120,
//            5 => 120,
//            6 => 120,
        ],
        2149 => [ //daniel garcia
//            0 => 120,
//            1 => 120,
//            2 => 120,
//            3 => 120,
//            4 => 120,
//            5 => 120,
//            6 => 150,
        ],
        3198 => [ //david
//            1=>120,
//            2=>120,
//            3=>120,
//            4=>120,
//            5=>120,
//            6=>120,
		],
        10267 => [ //sheldon springs
//            1=>120,
//            2=>120,
//            4=>120,
//            5=>120,
//            6=>120,
        ],
        10620 => [ //Antonio
//            0=>120,
//            1=>120,
//            2=>120,
//            3=>120,
//            4=>120,
//            5=>120,
//            6=>120,
        ],
        15638 => [ //Jorge
//            1 => 87,
//            2 => 99,
//            3 => 120,
//            4 => 120,
//            5 => 120,
        ],
        15785 => [ //Victor valdez
//            1 => 87,
//            2 => 99,
//            3 => 120,
//            4 => 120,
//            5 => 120,
        ],
        16217 => [ //Luis Lopez
//            1 => 87,
//            2 => 90,
//            3 => 60,
//            2 => 99,
//            5 => 30,
        ],
	];

    protected $onsite =[
        3198 => [ //david
//            1 => 56,
//            2 => 133,
//            3 => 100.75,
//            4 => 52,
//            5 => 100,
        ],
        2149 => [ //daniel
//            1 => 120,
//            2 => 60,
//            4 => 119,
//            5 => 105,
        ],
        1847 => [ //ricardo
//            2 => 146.25,
            3 => 105,
            4 => 105,
            5 => 75,
        ],
        10267 => [ //sheldon
//            2 => 63.25,
//            3 => 88,
//            4 => 55,
//            4 => 74.25,
        ],
        10350 => [ //michael
//            1 => 40,
//            4 => 48,
        ],
        10620 => [ //Antonio
//            1 => 87,
//            3 => 71.5,
//            4 => 96,
//            5 => 75,
        ],
        15638 => [ //Jorge
//            1 => 87,
//            3 => 60,
//            2 => 99,
//            5 => 30,
        ],
        15785 => [ //Victor valdez
//            1 => 22,
//            2 => 107.25,
//            3 => 71.5,
//            4 => 68.75,
//            5 => 38.5,
        ],
        16217 => [ //Luis Lopez
//            1 => 87,
            2 => 123.5,
//            3 => 110.5,
//            4 => 78,
//            5 => 117,
        ],
        16111 => [ //Scott Parkhurst
//            2 => 66,
//            3 => 38.5,
//            4 => 99,
        ]
    ];

    protected $deductions = [
        1847 => [ //ricardo
//            0 => 120,
//            1 => 120,
//            2 => 120,
//            3 => 120,
//            4 => 100,
//            5 => 120,
//            6 => 120,
        ],
        2149 => [ //daniel garcia
//            0 => 19.08,
//            1 => 150,
//            2 => 45.98,
//            4 => 150,
//            5 => 150,
//            6 => 150,
        ],
        3198 => [ //david
//            1=>12.99,
//            2=>25,
//            3=>120,
//            4=>120,
//            5=>120,
//            6=>12.50,
        ],
        10267 => [ //sheldon springs
//            1=>15,
//            2=>120,
//            4=>119.36,
//            6=>120,
        ],
        10350 => [ //michael wallace
//            0=>120,
//            1=>35,
//            2=>5,
//            3=>120,
//            4=>120,
//            5=>120,
//            6=>120,
        ],
        10620 => [ //Antonio
//            1=>120,
//            2=>30,
//            3=>20, //equipment
//            4=>120,
//            5=>120,
//            6=>120,
        ],
        15638 => [ //Jorge
//            1 => 87,
//            3 => 60,
//            2 => 20.67,
//            4 => 10,
//            5 => 30,
        ],
        15785 => [ //Victor valdez
//            1 => 87,
//            2 => 99,
//            3 => 120,
//            4 => 120,
//            5 => 120,
        ],
        16217 => [ //Luis Lopez
//            1 => 87,
//            3 => 60,
//            2 => 99,
//            5 => 30,
        ],
    ];

    protected $onsite_tips = [
        1847 => [ //ricardo
//            0 => 120,
//            1 => 120,
//            2 => 27.5,
            3 => 5.72,
            4 => 17.06,
            5 => 1.64,
//            6 => 20,
        ],
        2149 => [ //daniel garcia
//            0 => 19.08,
//            1 => 150,
//            2 => 45.98,
//            4 => 24.35,
//            5 => 13.8,
//            6 => 150,
        ],
        3198 => [ //david
//            1=>1.17,
//            2=>23.21,
//            3=>13.08,
//            4=>4.52,
//            5=>11,
//            6=>12.50,
        ],
        10267 => [ //sheldon springs
//            1=>15,
//            2=>19.52,
//            3=>6.79,
//            4=>11.87,
//            5=>120,
//            6=>120,
        ],
        10350 => [ //michael wallace
//            0=>120,
//            1=>120,
//            2=>120,
//            3=>120,
//            4=>6.22,
//            5=>120,
//            6=>120,
        ],
        10620 => [ //Antonio
//            1=>3.34,
//            2=>30,
//            3=>29.2,
//            4 => 19,
//            5=>18.87,
//            6=>120,
        ],
        15638 => [ //Jorge
//            1 => 87,
//            3 => 60,
//            2 => 20.67,
//            5 => 30,
        ],
        15785 => [ //Victor valdez
//            1 => 87,
//            2 => 4.7,
//            3 => 3.35,
//            4 => 9.66,
//            5 => 13.67,
        ],
        16217 => [ //Luis Lopez
//            1 => 87,
            2 => 11.38,
//            3 => 13.71,
//            4 => 9.8,
//            5 => 7.9,
        ],
        16111 => [ //Scott Parkhurst
//            2 => 0,
//            3 => 2.25,
//            4 => 12.09,
        ]
    ];

    protected $washer_training = [
        3198 => [ //david
//            2 => 220, // michael
//            3 => 220, // michael
        ],
//        10350 => [ //michael
//            2 => 50,
//            3 => 50,
//        ]
    ];

    protected $washer_tips =[];

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate payroll for past week';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

        $this->get_tips();

        $this->mailer = CampaignMonitor::classicSend(config('campaignmonitor.client_id'));
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        //get referral codes used

        $this->get_refarral_code_amounts();

        if($this->option('worker_ids')) {
            $this->ids_to_process = explode(",", $this->option('worker_ids'));
        }

        Carbon::setWeekStartsAt(Carbon::SUNDAY);
		Carbon::setWeekEndsAt(Carbon::SATURDAY);

		$now_date = Carbon::now()->format('m-d-Y');

		$orders = Order::select('id', 'vehicle_id', 'worker_id', 'service_id', 'etc', 'discount_id', 'start_at', 'price', 'charged', 'done_at', 'rating', DB::raw('TIMESTAMPDIFF(MINUTE,orders.start_at,orders.done_at) as wash_time'))
			->with('worker')
			->with('service')
			->with('vehicle')
            ->with('schedule')
			->where('status', 'done')
			->whereRaw('WEEK(DATE_FORMAT(done_at, "%Y-%m-%d"), 2) = (WEEK(NOW(), 2) - 1)')
			->whereRaw('DATE_FORMAT(done_at, "%Y") = DATE_FORMAT(now(), "%Y")')
            ->whereNull('partner_id')
			->orderBy('done_at')
            ->get();

        $start_day = Carbon::parse('2 sundays ago');
        $this->week_of = $start_day->format("M jS");

        $init_days = [];
        for($day_count=0; $day_count<7; $day_count++) {
            $day_of_week = clone $start_day->addDay(($day_count > 1 ? 1 : $day_count ));
            $job_date = $day_of_week->format('m/d/Y (l)');
            $init_days[$job_date] = ["date"=>$day_of_week, "orders"=>[],"pay"=>0];
        }

        $orders_by_worker = [];

        foreach ($orders as $order)
        {
            if (in_array($order->worker->id, $this->ignore_ids)) continue;

            $default_kit_fee = (isset($this->washer_kit_fee[$order->worker_id]) ? $this->washer_kit_fee[$order->worker_id] : $this->default_kit_fee );

            @$orders_by_worker[$order->worker->id]['job_count']++;
            @$orders_by_worker[$order->worker->id]['washer'] = ['name' => $this->washer_names[$order->worker_id], 'email' => $order->worker->email];
            @$orders_by_worker[$order->worker->id]['rental'] = (in_array($order->worker->id, $this->no_kit_rental) ? 0 : (isset($this->kit_fee[$order->worker->id]) ? $this->kit_fee[$order->worker->id] : $default_kit_fee ) );
            @$orders_by_worker[$order->worker->id]['total_pay'] = 0;
            @$orders_by_worker[$order->worker->id]['total_onsite_tip'] = 0;
//            @$orders_by_worker[$order->worker->id]['total_daily_tip'] = 0;
//            @$orders_by_worker[$order->worker->id]['extra_tip'] = 0;
            @$orders_by_worker[$order->worker->id]['minimum'] = 0;
            @$orders_by_worker[$order->worker->id]['referral_program'] = 0;
            @$orders_by_worker[$order->worker->id]['referrals'] = 0;
            @$orders_by_worker[$order->worker->id]['daily_min_pay'] = 0;
            @$orders_by_worker[$order->worker->id]['total_washer_training'] = 0;
            @$orders_by_worker[$order->worker->id]['total_bonus'] = 0;
            @$orders_by_worker[$order->worker->id]['total_deduction'] = 0;
            @$orders_by_worker[$order->worker->id]['comp_type'] = 'flat';
            @$orders_by_worker[$order->worker->id]['full_price_rev'] += $order->price / 100;

            @$orders_by_worker[$order->worker->id]['training'] = 0;
            if (isset($this->training[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['training'] = (int)@$this->training[$order->worker->id];
            }

            @$orders_by_worker[$order->worker->id]['bonus'] = 0;
            if (isset($this->bonus[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['bonus'] = (float)@$this->bonus[$order->worker->id];
            }

            @$orders_by_worker[$order->worker->id]['referral_code'] = 0;
            if (isset($this->referral_code[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['referral_code'] = (int)@$this->referral_code[$order->worker->id];
            }


            if (in_array($order->worker->id, $this->payroll_washers)) continue;

            //did order have surcharge
            $surcharge_row = $order->order_details()->where('name', 'like', '%surcharge')->first();
            $surcharge_amt = ($surcharge_row ? $surcharge_row->amount : 0 );

            $job = [];
            $job['id'] = $order->id;
            $job['time'] = $order->start_at->format('H:i') . " - " . $order->done_at->format('H:i');
            $job['vehicle'] = $order->vehicle->toArray();
            $job['wash_type'] = $order->service->name;

            if ($surcharge_amt) {
                $job['wash_type'] .= " + ".$order->vehicle->type." surcharge";
            }

            $job['wash_time'] = $order->wash_time;
            $job['etc'] = $order->etc;
            $job['rating'] = $order->rating;

            if( ! in_array($order->worker_id, $this->ignore_midweek_special)) {
                if ($order->price != $this->service_price[$order->service_id]) {
                    $order->price = $this->service_price[$order->service_id] + $surcharge_amt;
                }
            }
//            if($order->id==15302) {
//                dd($order);
//            }
//$order->price / 100
            
            $squeegy_comm = (in_array($order->worker_id, [15638,15785]) ? 0.45 : $this->commission_pct['squeegy'] ); //Jorge & Victor & Luis @ 55% comm
            
            $job['price'] = (round($order->price * (1 - 0.029)) - 30)/100;
            $job['squeegy'] = ($job['price'] * $squeegy_comm);
            $job['txn'] = ($order->price * $this->commission_pct['txn']) / 100;
            $job['addons'] = $this->get_addon_payout($order);
            $job['rev'] = $order->charged;

            if (in_array($order->discount_id, array_keys($promo_costs = Config::get('squeegy.groupon_gilt_promotions')))) { //groupon & gilt
                $job['rev'] = $promo_costs[$order->discount_id];
            }

            if ($order->schedule && $order->schedule->type == 'subscription') {
                $job['rev'] = $order->price;
            }
//dd($this->service_price[$order->service_id]);
//            if ($order->price < $this->service_price[$order->service_id]) {
////                dd($order->order_details);
//                $job['rev'] = $this->service_price[$order->service_id];
//
//            }
//            dd($job);
            $job['rev'] = $job['rev'] / 100;

            @$orders_by_worker[$order->worker->id]['comp_type'] = 'comm';

            $job_pay = (float)number_format(($job['price'] - $job['squeegy'] - $job['txn'] + $job['addons']), 2);

            if( $order->rating >= 3 || $order->rating === null ) {
                $job['pay'] = $job_pay;
            } else {
                $job['pay'] = 0;

                if(isset($this->min_day_worker_id[$order->worker->id]) && isset($this->min_day_worker_id[$order->worker->id][$order->done_at->dayOfWeek])) {
                    $this->min_day_worker_id[$order->worker->id][$order->done_at->dayOfWeek] -= $job_pay;
                }

            }

//            if (in_array($order->worker_id, $this->commission_userids)) {
//                @$orders_by_worker[$order->worker->id]['comp_type'] = 'comm';
//                $job['pay'] = (float)number_format(($order->price / 100 - $job['squeegy'] - $job['txn']), 2);
//            } else {
//                $job['pay'] = ($order->rating === null || $order->rating >= 4 ? $this->service_price[$order->service->id] : 0);
//            }

            $job['promotional_cost'] = ($job['pay'] > $job['rev'] ? $job['pay'] - $job['rev'] : 0);
            $job['cog'] = ($job['rev'] > $job['pay'] ? $job['pay'] : $job['rev']);

            @$orders_by_worker[$order->worker->id]['jobs']['total_promotional'] += $job['promotional_cost'];
            @$orders_by_worker[$order->worker->id]['jobs']['total_cog'] += $job['cog'];

            @$orders_by_worker[$order->worker->id]['jobs']['total'] += $job['pay'];

            $job_date = $order->done_at->format('m/d/Y (l)');


            if( ! isset($orders_by_worker[$order->worker->id]['jobs']['days'])) { // && isset($this->min_day_worker_id[$order->worker->id])
                $orders_by_worker[$order->worker->id]['jobs']['days'] = $init_days;
            }

            if( ! isset($orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['tip']) ) {
                $orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['tip'] = (float)@array_sum($this->washer_tips[$order->worker->id][$order->done_at->format('m/d/Y')]);
                @$orders_by_worker[$order->worker->id]['total_daily_tip'] += $orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['tip'];
            }

            $orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['orders'][] = $job;
            @$orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['pay'] += $job['pay'];



            if (in_array($order->worker->id, array_keys($this->min_weekly_worker_id)) &&
                $orders_by_worker[$order->worker->id]['jobs']['total'] < $this->min_weekly_worker_id[$order->worker_id]) {
                @$orders_by_worker[$order->worker->id]['minimum'] = max(0, $this->min_weekly_worker_id[$order->worker_id] - $orders_by_worker[$order->worker->id]['jobs']['total']);
            }

            @$orders_by_worker[$order->worker->id]['total_pay'] = ($orders_by_worker[$order->worker->id]['jobs']['total'] +
                $orders_by_worker[$order->worker->id]['minimum'] +
                $orders_by_worker[$order->worker->id]['training'] +
                $orders_by_worker[$order->worker->id]['total_daily_tip'] +
                $orders_by_worker[$order->worker->id]['extra_tip'] +
                $orders_by_worker[$order->worker->id]['bonus'] -
                $orders_by_worker[$order->worker->id]['rental']);

        }


        foreach( $orders_by_worker as $worker_id => &$washer_d ) {
            //allocate referral code
            if( $washer_d['referral_code'] > 0 ) {
                $washer_d['total_pay'] += $washer_d['referral_code'];

                //record referral payout
                $this->save_referral_payout($worker_id, $washer_d['referral_code']);
            }

            ///allocate extra tips
            @$washer_d['extra_tip'] = $this->washer_tips[$worker_id]['total'] - $washer_d['total_daily_tip'];
            $orders_by_worker[$worker_id]['total_pay'] += $washer_d['extra_tip'];
        }

        //referral program
        foreach($this->referral_program as $worker_id => $ref_pay) {
            $orders_by_worker[$worker_id]['referral_program'] += $ref_pay;
            $orders_by_worker[$worker_id]['total_pay'] += $ref_pay;
        }

        foreach($this->onsite as $worker_id => $onsite_details) {
            if (!isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$day_details) {

                if(isset($onsite_details[$day_details['date']->dayOfWeek])) {

//                    if(isset($this->min_day_worker_id[$worker_id]) && isset($this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek])) {
//                        $this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek] = 0;//no min if washer was on-site
//                    }

                    if( ! isset($day_details['onsite'])) {
                        $day_details['onsite']=0;
                    }
                    $day_details['onsite'] += $onsite_details[$day_details['date']->dayOfWeek];
                    $orders_by_worker[$worker_id]['jobs']['total_cog'] += $day_details['onsite'];
                    $orders_by_worker[$worker_id]['jobs']['total'] += $day_details['onsite'];
                    $orders_by_worker[$worker_id]['total_pay'] += $day_details['onsite'];
                }
            }
        }

        foreach($this->onsite_tips as $worker_id => $onsite_tip_details) {
            if (!isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$day_details) {

                if(isset($onsite_tip_details[$day_details['date']->dayOfWeek])) {

//                    if(isset($this->min_day_worker_id[$worker_id]) && isset($this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek])) {
//                        $this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek] = 0;//no min if washer was on-site
//                    }

                    if( ! isset($day_details['onsite_tip'])) {
                        $day_details['onsite_tip']=0;
                    }
                    $day_details['onsite_tip'] += $onsite_tip_details[$day_details['date']->dayOfWeek];
                    $orders_by_worker[$worker_id]['total_onsite_tip'] += $day_details['onsite_tip'];
//                    $orders_by_worker[$worker_id]['jobs']['total'] += $day_details['onsite_tip'];
                    $orders_by_worker[$worker_id]['total_pay'] += $day_details['onsite_tip'];
                }
            }
        }

        foreach($this->min_day_worker_id as $worker_id=>$worker_min_details) {
            if(!isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

                if(isset($worker_min_details[$details['date']->dayOfWeek]) && ($details['pay'] + @(int)$details['onsite']) < $worker_min_details[$details['date']->dayOfWeek]) {

                    @$details['min'] = max(0, $worker_min_details[$details['date']->dayOfWeek] - $details['pay'] - $details['onsite'] - $details['tip'] - $details['onsite_tip']);
//                    if($details['pay'] === 0) $details['pay'] = $details['min'];
                    $orders_by_worker[$worker_id]['total_pay'] += $details['min'];
                    @$orders_by_worker[$worker_id]['daily_min_pay'] += $details['min'];
                }

            }
        }


        foreach($this->washer_training as $worker_id=>$worker_training_details) {
            if(!isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

                if(isset($worker_training_details[$details['date']->dayOfWeek])) {
                    $details['washer_training'] = $worker_training_details[$details['date']->dayOfWeek];

                    $orders_by_worker[$worker_id]['total_pay'] += $details['washer_training'];
                    @$orders_by_worker[$worker_id]['total_washer_training'] += $details['washer_training'];
                }

            }
        }
        
        foreach($this->daily_bonus_worker_id as $worker_id=>$bonus_details) {
            if(!isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

                if(isset($bonus_details[$details['date']->dayOfWeek])) {
                    $details['bonus'] = $bonus_details[$details['date']->dayOfWeek];

                    $orders_by_worker[$worker_id]['total_pay'] += $details['bonus'];
                    @$orders_by_worker[$worker_id]['total_bonus'] += $details['bonus'];
                }

            }
        }

        foreach($this->deductions as $worker_id=>$deduction_details) {
            if( ! isset($orders_by_worker[$worker_id])) continue;
            if(empty($orders_by_worker[$worker_id]['jobs'])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

                if(isset($deduction_details[$details['date']->dayOfWeek])) {
                    $details['deduction'] = $deduction_details[$details['date']->dayOfWeek];

                    $orders_by_worker[$worker_id]['total_pay'] -= $details['deduction'];
                    @$orders_by_worker[$worker_id]['total_deduction'] -= $details['deduction'];
                }

            }
        }

//        foreach($orders_by_worker as $worker_id=>&$worker_info) {
//            if(isset($this->washer_tips[$worker_id])) {
//                $worker_info['tip'] = $this->washer_tips[$worker_id];
//                $orders_by_worker[$worker_id]['total_pay'] += array_sum($worker_info['tip']);
//            }
//        }

//        dd($this->washer_tips[3198]);
//        dd("adsf");
//        dd($orders_by_worker);

//        dd($orders_by_worker[1847]);

		$disk = Storage::disk('local');
		$dir_path = ['payroll', date('Y'), $orders->first()->done_at->startOfWeek()->format("m-d")];
        $cogs_by_washer=[];

		foreach($orders_by_worker as $worker_id => &$worker) {

//            if($worker['total_pay'] <= 0) continue;
            
			$worker['promotional'] = (float)@$cogs_by_washer[$worker_id] + (float)@$worker['minimum'];

			$data=[];
			$data['week_of'] = $this->week_of;
			$data['washer_info'] = $worker;
			$data['weekly_min'] = ( ! empty($min_weekly_worker_id[$worker_id]) ? $min_weekly_worker_id[$worker_id] : 0 );
            $data['colspan'] = ($worker['comp_type']=="flat" ? 7 : 10 );

			$view = view('payroll.time_sheet', $data);

			$file_name = preg_replace('/[\s]+/', '',$worker['washer']['name'])."-".$now_date;
			$dir_path['file'] = $file_name.".html";

			$disk->put(implode("/", $dir_path), $view->render());

			$time_sheet = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

			$email_data = [
				'time_sheet' => $time_sheet,
				'washer'=>$worker['washer'],
				'week_of'=>$this->week_of,
			];

            //only send to IDs that are in the array if there is an array
            if(count($this->ids_to_process) && !in_array($worker_id, $this->ids_to_process)) continue;

            try {

                $message=[
                    'From' => 'Squeegy Payments '.'<payments@squeegyapp.com>',
                    'ReplyTo' => 'support@squeegyapp.com',
                ];

                if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review") {
                    $message['To'] = ["Dan Schultz "."<dan@squeegyapp.com>"];
                    $message['CC'] = ["Javier Macias "."<javier@squeegyapp.com>"];
                } else {
                    if( ! in_array($worker_id, $this->hold_email_from_washer)) {
                        $message['To'] = [$email_data['washer']['name']." <".$email_data['washer']['email'].">"];
                    }
                    $message['BCC'] = [
                        "Dan Schultz "."<dan@squeegyapp.com>",
                        "Andrew Davis "."<andrew@squeegyapp.com>",
                        "Javier Macias "."<javier@squeegyapp.com>"
                    ];
                }

                $message['Subject'] = "Squeegy Pay - Week of ".$email_data['week_of'];
                $message['Html'] = view('payroll.email', ['washer'=>$worker['washer']['name'], 'week_of'=>$this->week_of])->render();
                $message['Attachments'] = [
                    ['Name' => $file_name,
                    'Type' => 'text/html',
                    'Content' => base64_encode(File::get($email_data['time_sheet']))
                    ],
                ];

                $resp = $this->mailer->send($message, 'Payroll');

                if( ! $resp->was_successful()) {
                    $this->error($resp->http_status_code." - ".$resp->response->Message);
                } else {
                    if( ! in_array($worker_id, $this->hold_email_from_washer)) {
                        $this->info("Email sent: ".$email_data['washer']['email']);
                    } else {
                        $this->info("Email HOLD: ".$email_data['washer']['email']);
                    }
                }

            } catch(\Exception $e) {
                $this->error($e->getMessage());
            }
		}


        try {

            ///generate COGs file and email
            $email_data['orders_by_worker'] = $orders_by_worker;

            $view = view('payroll.cogs', $email_data);
            $file_name = "COGs-$now_date";
            $dir_path['file'] = "$file_name.html";
            $disk->put(implode("/", $dir_path), $view->render());

            $email_data['time_sheet'] = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

            $message=[
                'From' => 'Squeegy Payments '.'<payments@squeegyapp.com>',
                'ReplyTo' => 'support@squeegyapp.com',
            ];

            if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review" || $this->argument('send_cog')=='false') {
                $message['To'] = ["Dan Schultz "."<dan@squeegyapp.com>"];
                $message['CC'] = ["Javier Macias "."<javier@squeegyapp.com>"];
            } else {
                $message['To'] = ["Terri Perkins "."<Terri@lrmcocpas.com>"];
                $message['CC'] = ["Anna Asuncion "."<Anna@lrmcocpas.com>"];
                $message['BCC'] = [
                    "Dan Schultz "."<dan@squeegyapp.com>",
                    "Andrew Davis "."<andrew@squeegyapp.com>",
                    "Javier Macias "."<javier@squeegyapp.com>"
                ];
            }

            $message['Subject'] = "Squeegy Pay - COGs Week of ".$email_data['week_of'];
            $message['Html'] = File::get($email_data['time_sheet']);
            $message['Attachments'] = [
                [
                    'Name' => $file_name,
                    'Type' => 'text/html',
                    'Content' => base64_encode(File::get($email_data['time_sheet']))
                ],
            ];

            $resp = $this->mailer->send($message, 'Payroll COGs');

            if( ! $resp->was_successful()) {
                $this->error($resp->http_status_code." - ".$resp->response->Message);
            } else {
                $this->info("COG Email sent");
            }

        } catch(\Exception $e) {
            $this->error($e->getMessage());
        }
	}

    protected function get_tips()
    {
        $tip_orders = Order::select('id', 'worker_id', 'done_at','tip','tip_at','rating')
            ->whereRaw('WEEK(DATE_FORMAT(tip_at, "%Y-%m-%d"), 2) = (WEEK(NOW(), 2) - 1)')
            ->where('status', 'done')
            ->whereNull('partner_id')
            ->where('tip', '>', 0)
            ->get();

        foreach($tip_orders as $tip_order) {
            if(empty($this->washer_tips[$tip_order->worker_id])) {
                $this->washer_tips[$tip_order->worker_id]=[];
            }
            if(empty($this->washer_tips[$tip_order->worker_id]['total'])) {
                $this->washer_tips[$tip_order->worker_id]['total']=0;
            }

            $this->washer_tips[$tip_order->worker_id][$tip_order->tip_at->format('m/d/Y')][] = (round($tip_order->tip * (1 - 0.029)) - 30)/100;
            $this->washer_tips[$tip_order->worker_id]['total'] += (round($tip_order->tip * (1 - 0.029)) - 30)/100;
        }

        return;
    }

    protected function save_referral_payout($worker_id, $amount) {
        if(env('APP_ENV') != 'production' || $this->argument('send_email') == 'review') return;

        Credit::create([
            'user_id'=>$worker_id,
            'amount'=>-$amount*100,
            'status'=>'capture',
            'description'=>'Payroll payout - Week of '.$this->week_of,
        ]);
        return;
    }

    protected function get_refarral_code_amounts()
    {
        $referral_codes = DB::select('SELECT users.id, users.name, sum(amount) as ref_amount FROM credits, users WHERE credits.user_id = users.id AND user_id IN (SELECT user_id FROM users, role_user WHERE users.id = role_user.user_id AND role_user.role_id = 2) GROUP BY users.id HAVING sum(amount) > 0');

        if(count($referral_codes)) {
            foreach($referral_codes as $referral_worker) {
                $this->referral_code[$referral_worker->id] = (int)$referral_worker->ref_amount/100;
            }
        }
    }

    protected function get_addon_payout(Order $order)
    {
        if($addons = $order->order_details()->whereNotNull('addon_id')->get()) {
            return (($addons->sum('amount') * 0.25) / 100);
        }
        return 0;
    }

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
            ['send_email', InputArgument::OPTIONAL, 'Send email to washers or to internal review.', 'review'],
            ['send_cog', InputArgument::OPTIONAL, 'Send COGs email to accounting.', 'false'],
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
            ['worker_ids', null, InputOption::VALUE_OPTIONAL, 'Only send emails to the following worker IDs -- 1111,2222,3333'],
		];
	}

}
