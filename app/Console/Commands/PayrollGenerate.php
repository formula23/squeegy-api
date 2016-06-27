<?php namespace App\Console\Commands;

use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PayrollGenerate extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'payroll:generate';

    protected $washer_names = [
        1847 => 'Ricardo Alanso',
        2149 => 'Daniel Garcia',
        2882 => 'Juan Lopez',
        2900 => 'Victor Rodriguez',
        3198 => 'David Montano',
        5482 => 'Juan Lara',
        6349 => 'Melvyn Miller',
        6861 => 'Angel Rodriguez',
        7146 => 'Leonel Yanez',
        7188 => 'Santos Aguilar',
        7269 => 'Salvador Lacarcel',
        7279 => 'Cleto Hernandez',
        7506 => 'Rafael Sanchez',
        7527 => 'Gonzalo Hidalgo',
        7896 => 'Guillermo Lizardi',
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

    protected $bonus = [
//        3198 => 80, //david
//        2882 => 10,

    ];

    protected $referral_code = [
//        5482 => 10,
        2882 => 10,
    ];

    protected $ignore_ids =[
        6119, //Ops
        1, //dan
    ];

    protected $service_price = [
        1=>2500,
        2=>3500,
        3=>1500,
    ];

    protected $commission_pct = [
        'squeegy' => 0.25,
        'txn' => 0.025,
    ];

//	protected $commission_userids = [
//        6349, //Melvyn
//        6633, //Rob
//        2882, //Juan
//        3198, //David
//        6861, //Angel
//        1847, //Ricardo
//        2149, //Daniel
//        2900, //Victor
//    ];

    protected $no_kit_rental = [
        6349, //Melvyn
        2882, //Juan lopez
        7146, //Leo
        5482, //juan lara
        7506, //Rafael
    ];

    protected $default_kit_fee = 25;

    protected $kit_fee = [
//        7269 => 50, // salvador
//        7527 => 50, //gonzalo
    ];

    protected $min_weekly_worker_id = [
        //2149 => 500,
        //2900 => 500,
        //3198 => 600,
    ];

    protected $daily_bonus_worker_id = [
//        3198 => [ //david
//            2=>50,
//        ],
//        2882 => [ //juan lopez
//            0=>50,
//        ],
//        2149 => [ //daniel garcia
//            5=>30,
//        ],
    ];

	protected $min_day_worker_id = [
		3198 => [ //david
            1=>100,
//            2=>100,
//            3=>100,
//            4=>100,
//            5=>100,
            6=>100,
		],
        1847 => [ //ricardo
            0 => 100,
            2 => 100,
            3 => 100,
            4 => 100,
            5 => 100,
        ],
        7146 => [ //leonel
//            1 => 100,
//            2 => 100,
//            3 => 100,
//            4 => 100,
//            5 => 100,
        ],
        2882 => [ // juan lopez
//            0 => 120,
        ],
        5482 => [ // juan lara
            1 => 120,
            2 => 120,
            3 => 120,
            4 => 120,
//            6 => 120,
        ],
        7527 => [ // Gonzalo hidalgo
            1 => 120,
            3 => 120,
            4 => 120,
            5 => 120,
            6 => 120,
        ],
        2149 => [ //daniel
            0 => 150,
            1 => 150,
            2 => 150,
            4 => 150,
            5 => 150,
        ],
//        6349 => [ //Melvyn
//            0 => 100,
//            1 => 120,
//        ],
	];

    protected $washer_training = [
//        3198 => [ //david
//            1 => 100, // gonzalo
//            2 => 100, // gonzalo
//            3 => 100, // edgardo
//        ],
//        2882 => [ //juan lopez
//            2 => 100, //guillermo
//            3 => 100, //guillermo
//        ]
    ];

    protected $onsite =[
        3198 => [ //david
//            3 => 75, //snack nation half day
//            4 => 130,
//            5 => 130,
        ],
        7146 => [ //leo
//            4 => 120,
        ],
        2882 => [ //juan lopez
            2 => 100,
            4 => 100,
        ],
        2149 => [ //daniel
//            4 => 130,
//            5 => 60,
        ],
        7527 => [ // Gonzalo hidalgo
            4 => 100,
        ],
    ];

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
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{

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
            ->whereNull('partner_id')
//            ->where('worker_id', 6861)
			->orderBy('done_at')->get();

        $start_day = Carbon::parse('2 sundays ago');
        $init_days = [];
        for($day_count=0; $day_count<7; $day_count++) {
            $day_of_week = clone $start_day->addDay(($day_count > 1 ? 1 : $day_count ));
            $job_date = $day_of_week->format('m/d/Y (l)');
            $init_days[$job_date] = ["date"=>$day_of_week, "orders"=>[],"pay"=>0];
        }
//dd($init_days);
		$orders_by_worker = [];

		$week_of = $orders->first()->done_at->startOfWeek()->format("M jS");

        foreach ($orders as $order)
        {
            if (in_array($order->worker->id, $this->ignore_ids)) continue;

            @$orders_by_worker[$order->worker->id]['job_count']++;
            @$orders_by_worker[$order->worker->id]['washer'] = ['name' => $this->washer_names[$order->worker_id], 'email' => $order->worker->email];
            @$orders_by_worker[$order->worker->id]['rental'] = (in_array($order->worker->id, $this->no_kit_rental) ? 0 : (isset($this->kit_fee[$order->worker->id]) ? $this->kit_fee[$order->worker->id] : $this->default_kit_fee ) );
            @$orders_by_worker[$order->worker->id]['total_pay'] = 0;
            @$orders_by_worker[$order->worker->id]['minimum'] = 0;
            @$orders_by_worker[$order->worker->id]['referral_program'] = 0;
            @$orders_by_worker[$order->worker->id]['referrals'] = 0;
            @$orders_by_worker[$order->worker->id]['daily_min_pay'] = 0;
            @$orders_by_worker[$order->worker->id]['total_washer_training'] = 0;
            @$orders_by_worker[$order->worker->id]['total_bonus'] = 0;
            @$orders_by_worker[$order->worker->id]['comp_type'] = 'flat';
            @$orders_by_worker[$order->worker->id]['full_price_rev'] += $order->price / 100;

            @$orders_by_worker[$order->worker->id]['training'] = 0;
            if (isset($this->training[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['training'] = (int)@$this->training[$order->worker->id];
            }

            @$orders_by_worker[$order->worker->id]['bonus'] = 0;
            if (isset($this->bonus[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['bonus'] = (int)@$this->bonus[$order->worker->id];
            }

            @$orders_by_worker[$order->worker->id]['referral_code'] = 0;
            if (isset($this->referral_code[$order->worker->id])) {
                @$orders_by_worker[$order->worker->id]['referral_code'] = (int)@$this->referral_code[$order->worker->id];
            }

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
                if ($order->price < $this->service_price[$order->service_id]) {
                    $order->price = $this->service_price[$order->service_id] + $surcharge_amt;
                }
            }

            $job['price'] = $order->price / 100;
            $job['squeegy'] = ($order->price * $this->commission_pct['squeegy']) / 100;
            $job['txn'] = ($order->price * $this->commission_pct['txn']) / 100;

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

            $job_pay = (float)number_format(($order->price / 100 - $job['squeegy'] - $job['txn']), 2);

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

            $orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['orders'][] = $job;
            @$orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['pay'] += $job['pay'];

            if (in_array($order->worker->id, array_keys($this->min_weekly_worker_id)) &&
                $orders_by_worker[$order->worker->id]['jobs']['total'] < $this->min_weekly_worker_id[$order->worker_id]) {
                @$orders_by_worker[$order->worker->id]['minimum'] = max(0, $this->min_weekly_worker_id[$order->worker_id] - $orders_by_worker[$order->worker->id]['jobs']['total']);
            }

            @$orders_by_worker[$order->worker->id]['total_pay'] = ($orders_by_worker[$order->worker->id]['jobs']['total'] +
                $orders_by_worker[$order->worker->id]['minimum'] +
                $orders_by_worker[$order->worker->id]['training'] +
                $orders_by_worker[$order->worker->id]['referral_code'] +
                $orders_by_worker[$order->worker->id]['bonus'] -
                $orders_by_worker[$order->worker->id]['rental']);

        }


        foreach($this->onsite as $worker_id=>$onsite_details) {
            if (!isset($orders_by_worker[$worker_id])) continue;

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$day_details) {

                if(isset($onsite_details[$day_details['date']->dayOfWeek])) {

                    if(isset($this->min_day_worker_id[$worker_id]) && isset($this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek])) {
                        $this->min_day_worker_id[$worker_id][$day_details['date']->dayOfWeek] = 0;//no min if washer was on-site
                    }

                    @$day_details['onsite'] += $onsite_details[$day_details['date']->dayOfWeek];
                    $orders_by_worker[$worker_id]['jobs']['total_cog'] += $day_details['onsite'];
                    $orders_by_worker[$worker_id]['jobs']['total'] += $day_details['onsite'];
                    $orders_by_worker[$worker_id]['total_pay'] += $day_details['onsite'];
                }
            }
        }

        foreach($this->min_day_worker_id as $worker_id=>$worker_min_details) {
            if(!isset($orders_by_worker[$worker_id])) continue;
            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

//                if(in_array($details['date']->dayOfWeek, $worker_min_details['days']) && $details['pay'] < $worker_min_details['min']) {
                if(isset($worker_min_details[$details['date']->dayOfWeek]) && ($details['pay'] + @(int)$details['onsite']) < $worker_min_details[$details['date']->dayOfWeek]) {
                    @$details['min'] = $worker_min_details[$details['date']->dayOfWeek] - $details['pay'] - $details['onsite'];
//                    if($details['pay'] === 0) $details['pay'] = $details['min'];
                    $orders_by_worker[$worker_id]['total_pay'] += $details['min'];
                    @$orders_by_worker[$worker_id]['daily_min_pay'] += $details['min'];
                }

            }
        }


        foreach($this->washer_training as $worker_id=>$worker_training_details) {
            if(!isset($orders_by_worker[$worker_id])) continue;
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
            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {

                if(isset($bonus_details[$details['date']->dayOfWeek])) {
                    $details['bonus'] = $bonus_details[$details['date']->dayOfWeek];

                    $orders_by_worker[$worker_id]['total_pay'] += $details['bonus'];
                    @$orders_by_worker[$worker_id]['total_bonus'] += $details['bonus'];
                }

            }
        }

//        dd("adsf");
//        dd($orders_by_worker);
//        dd($orders_by_worker[5482]);

		$disk = Storage::disk('local');
		$dir_path = ['payroll', date('Y'), $orders->first()->done_at->startOfWeek()->format("m-d")];
        $cogs_by_washer=[];

		foreach($orders_by_worker as $worker_id => &$worker) {

			$worker['promotional'] = (float)@$cogs_by_washer[$worker_id] + (float)@$worker['minimum'];

			$data=[];
			$data['week_of'] = $week_of;
			$data['washer_info'] = $worker;
			$data['weekly_min'] = ( ! empty($min_weekly_worker_id[$worker_id]) ? $min_weekly_worker_id[$worker_id] : 0 );
            $data['colspan'] = ($worker['comp_type']=="flat" ? 8 : 11 );

			$view = view('payroll.time_sheet', $data);

			$file_name = preg_replace('/[\s]+/', '',$worker['washer']['name'])."-".$now_date;
			$dir_path['file'] = $file_name.".html";

			$disk->put(implode("/", $dir_path), $view->render());

			$time_sheet = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

			$email_data = [
				'time_sheet' => $time_sheet,
				'washer'=>$worker['washer'],
				'week_of'=>$week_of,
			];

            //only send to IDs that are in the array if there is an array
            if(count($this->ids_to_process) && !in_array($worker_id, $this->ids_to_process)) continue;

			Mail::send('payroll.email', ['washer'=>$worker['washer']['name'], 'week_of'=>$week_of], function($message) use ($email_data)
			{
                $message->getHeaders()->addTextHeader('X-CMail-GroupName', 'Payroll');
                
				$message->from('payments@squeegyapp.com', 'Squeegy Payments');
                $message->replyTo('support@squeegyapp.com', 'Squeegy');

				if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review") {
					$message->to('dan@squeegyapp.com', 'Dan Schultz');
				} else {
					$message->to($email_data['washer']['email'], $email_data['washer']['name']);
					$message->bcc('andrew@squeegyapp.com', 'Andrew Davis');
					$message->bcc('dan@squeegyapp.com', 'Dan Schultz');
				}

				$message->subject("Squeegy Pay - Week of ".$email_data['week_of']);
				$message->attach($email_data['time_sheet']);
			});
            $this->info("Email sent: ".$email_data['washer']['email']);
//            sleep(2);
		}

        ///generate COGs file and email
        $email_data['orders_by_worker'] = $orders_by_worker;

		$view = view('payroll.cogs', $email_data);
		$dir_path['file'] = "COGs-$now_date.html";
		$disk->put(implode("/", $dir_path), $view->render());

		$email_data['time_sheet'] = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

		Mail::raw('COGs Attached', function($message) use ($email_data)
		{
            $message->getHeaders()->addTextHeader('X-CMail-GroupName', 'Payroll COGs');
            
			if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review") {
				$message->to('dan@squeegyapp.com', 'Dan Schultz');
			} else {
				$message->to('Terri@lrmcocpas.com', 'Terri Perkins');
				$message->cc('Anna@lrmcocpas.com', 'Anna Asuncion');
				$message->bcc('andrew@squeegyapp.com', 'Andrew Davis');
				$message->bcc('dan@squeegyapp.com', 'Dan Schultz');
			}

			$message->subject("Squeegy Pay - COGs Week of ".$email_data['week_of']);
			$message->attach($email_data['time_sheet']);
		});

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
//			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
            ['worker_ids', null, InputOption::VALUE_OPTIONAL, 'Only send emails to the following worker IDs -- 1111,2222,3333'],
		];
	}

}
