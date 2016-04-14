<?php namespace App\Console\Commands;

use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

    protected $training = [
//        6633=>192,
    ];

    protected $bonus = [
//        6633=>0, //example
    ];

    protected $ignore_ids =[
        6119, //ben
        1, //dan
    ];

	protected $service_price = [
        1=>20,
        2=>30,
        3=>10
    ];

    protected $commission_pct = [
        'squeegy' => 0.25,
        'txn' => 0.025,
    ];

	protected $commission_userids = [
        6349, //Melvyn
        6633, //Rob
        2882, //Juan
        3198, //david
    ];

    protected $no_kit_rental = [
        6349, //Melvyn
        2882, //Juan
    ];

    protected $min_weekly_worker_id = [
        //2149 => 500,
        //2900 => 500,
//        3198 => 600,
    ];

	protected $min_day_worker_id = [
		3198 => [
			'min' => 100,
			'days' => [1,2,3,4,5,6],
		],
		6633 => [
			'min' => 120,
			'days' => [3],
		]
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
			->orderBy('done_at')->get();

        $start_day = Carbon::parse('2 sundays ago');
        $init_days = [];
        for($day_count=0; $day_count<7; $day_count++) {
            $day_of_week = clone $start_day->addDay(($day_count > 1 ? 1 : $day_count ));
            $job_date = $day_of_week->format('m/d/Y (l)');
            $init_days[$job_date] = ["date"=>$day_of_week, "orders"=>[],"pay"=>0];
        }

		$orders_by_worker = [];

		$week_of = $orders->first()->done_at->startOfWeek()->format("M jS");

        foreach ($orders as $order)
        {
            if (in_array($order->worker->id, $this->ignore_ids)) continue;

            @$orders_by_worker[$order->worker->id]['job_count']++;
            @$orders_by_worker[$order->worker->id]['washer'] = ['name' => $order->worker->name, 'email' => $order->worker->email];
            @$orders_by_worker[$order->worker->id]['rental'] = (in_array($order->worker->id, $this->no_kit_rental) ? 0 : 25);
            @$orders_by_worker[$order->worker->id]['total_pay'] = 0;
            @$orders_by_worker[$order->worker->id]['minimum'] = 0;
            @$orders_by_worker[$order->worker->id]['daily_min_pay'] = 0;
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

            $job = [];
            $job['id'] = $order->id;
            $job['time'] = $order->start_at->format('H:i') . " - " . $order->done_at->format('H:i');
            $job['vehicle'] = $order->vehicle->toArray();
            $job['wash_type'] = $order->service->name;

            if ($order->vehicleSurCharge()) {
                $job['wash_type'] .= " + $" . number_format($order->vehicleSurCharge() / 100) . "(" . $order->vehicle->type . ")";
            }

            $job['wash_time'] = $order->wash_time;
            $job['etc'] = $order->etc;
            $job['rating'] = $order->rating;

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

            $job['rev'] = $job['rev'] / 100;

            if (in_array($order->worker_id, $this->commission_userids)) {
                @$orders_by_worker[$order->worker->id]['comp_type'] = 'comm';
                $job['pay'] = (float)number_format(($order->price / 100 - $job['squeegy'] - $job['txn']), 2);
            } else {
                $job['pay'] = ($order->rating === null || $order->rating >= 4 ? $this->service_price[$order->service->id] : 0);
            }

            $job['promotional_cost'] = ($job['pay'] > $job['rev'] ? $job['pay'] - $job['rev'] : 0);
            $job['cog'] = ($job['rev'] > $job['pay'] ? $job['pay'] : $job['rev']);

            @$orders_by_worker[$order->worker->id]['jobs']['total_promotional'] += $job['promotional_cost'];
            @$orders_by_worker[$order->worker->id]['jobs']['total_cog'] += $job['cog'];

            @$orders_by_worker[$order->worker->id]['jobs']['total'] += $job['pay'];

            $job_date = $order->done_at->format('m/d/Y (l)');

            if( ! isset($orders_by_worker[$order->worker->id]['jobs']['days']) && isset($this->min_day_worker_id[$order->worker->id])) {
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
                $orders_by_worker[$order->worker->id]['bonus'] -
                $orders_by_worker[$order->worker->id]['rental']);

        }

        foreach($this->min_day_worker_id as $worker_id=>$worker_min_details) {

            foreach($orders_by_worker[$worker_id]['jobs']['days'] as $day_display => &$details) {
                if(in_array($details['date']->dayOfWeek, $worker_min_details['days']) && $details['pay'] < $worker_min_details['min']) {
                    $details['min'] = $worker_min_details['min'] - $details['pay'];

                    $orders_by_worker[$order->worker->id]['total_pay'] += $details['min'];
                    @$orders_by_worker[$order->worker->id]['daily_min_pay'] += $details['min'];
                }
            }
        }

//        dd($orders_by_worker[3198]);

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

			$file_name = $worker['washer']['name']."-".$now_date;
			$dir_path['file'] = $file_name.".html";

			$disk->put(implode("/", $dir_path), $view->render());

			$time_sheet = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

			$email_data = [
				'time_sheet' => $time_sheet,
				'washer'=>$worker['washer'],
				'week_of'=>$week_of,
			];

			Mail::send('payroll.email', ['washer'=>$worker['washer']['name'], 'week_of'=>$week_of], function($message) use ($email_data)
			{
				$message->from('payments@squeegyapp.com', 'Squeegy Payments');
                $message->replyTo('tech@squeegyapp.com', 'Squeegy');

				if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review") {
					$message->to('dan@squeegyapp.com', 'Dan Schultz');
					$message->cc('ben@squeegyapp.com', 'Ben Grodsky');
				} else {
					$message->to($email_data['washer']['email'], $email_data['washer']['name']);
					$message->bcc('ben@squeegyapp.com', 'Ben Grodsky');
					$message->bcc('andrew@squeegyapp.com', 'Andrew Davis');
					$message->bcc('dan@squeegyapp.com', 'Dan Schultz');
				}

				$message->subject("Squeegy Pay - Week of ".$email_data['week_of']);
				$message->attach($email_data['time_sheet']);
			});

		}

        ///generate COGs file and email
        $email_data['orders_by_worker'] = $orders_by_worker;

		$view = view('payroll.cogs', $email_data);
		$dir_path['file'] = "COGs-$now_date.html";
		$disk->put(implode("/", $dir_path), $view->render());

		$email_data['time_sheet'] = $disk->getDriver()->getAdapter()->getPathPrefix().implode("/", $dir_path);

		Mail::raw('COGs Attached', function($message) use ($email_data)
		{
			if(env('APP_ENV') != 'production' || $this->argument('send_email') == "review") {
				$message->to('dan@squeegyapp.com', 'Dan Schultz');
				$message->cc('ben@squeegyapp.com', 'Ben Grodsky');
			} else {
				$message->bcc('Terri@lrmcocpas.com', 'Terri Perkins');
				$message->bcc('Anna@lrmcocpas.com', 'Anna Asuncion');
				$message->bcc('ben@squeegyapp.com', 'Ben Grodsky');
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
		];
	}

}
