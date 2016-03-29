<?php namespace App\Console\Commands;

use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
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

	protected $service_price = [1=>20, 2=>30, 3=>10];

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

		//COGs query
		$cogs = DB::select('SELECT worker.id, worker.name,
				sum(IF(services.id = \'1\', \'2000\', IF(services.id=3,\'1000\',\'3000\')) - IF(orders.discount_id=55, 900, IF(orders.discount_id=56,1200,IF(orders.discount_id=57,756,IF(orders.discount_id=58,900,IF(orders.discount_id=27,750,IF(orders.discount_id=28,950,charged)))))))/100 AS PromotionalCost
			FROM orders, users, services, users AS worker
			WHERE orders.user_id = users.id
			AND orders.`worker_id` = worker.id
			AND orders.service_id = services.id
			AND `status` IN (\'done\')
			AND WEEK(DATE_FORMAT(done_at, "%Y-%m-%d"), 2) = (WEEK(NOW(), 2) - 1)
			AND IF(services.id = \'1\', \'2000\', IF(services.id=3,\'1000\',\'3000\')) - IF(orders.discount_id=55, 900, IF(orders.discount_id=56,1200,IF(orders.discount_id=57,756,IF(orders.discount_id=58,900,IF(orders.discount_id=27,750,IF(orders.discount_id=28,950,charged)))))) - charged > 0
			GROUP BY worker.id
			ORDER BY orders.created_at');

		$cogs_by_washer=[];
		foreach($cogs as $cog) {
			$cogs_by_washer[$cog->id] = $cog->PromotionalCost;
		}

		$no_kit_rental = [2882, 6349];
		$min_worker_id = [
//			2149 => 500,
//			2900 => 500,
			3198 => 600,
		];

		$orders = Order::select('id', 'vehicle_id', 'worker_id', 'service_id', 'etc', 'start_at', 'done_at', 'rating', DB::raw('TIMESTAMPDIFF(MINUTE,orders.start_at,orders.done_at) as wash_time'))
			->with('worker')
			->with('service')
			->with('vehicle')
			->where('status', 'done')
			->whereRaw('WEEK(DATE_FORMAT(done_at, "%Y-%m-%d"), 2) = (WEEK(NOW(), 2) - 1)')
			->orderBy('done_at')
			->get();

		$orders_by_worker = [];

		$week_of = $orders->first()->done_at->startOfWeek()->format("M jS");

		foreach($orders as $order) {

			@$orders_by_worker[$order->worker->id]['job_count']++;
			@$orders_by_worker[$order->worker->id]['washer'] = ['name'=>$order->worker->name, 'email'=>$order->worker->email];
			@$orders_by_worker[$order->worker->id]['rental'] = (in_array($order->worker->id, $no_kit_rental) ? 0 : 25 );
			@$orders_by_worker[$order->worker->id]['minimum'] = 0;

			@$orders_by_worker[$order->worker->id]['jobs']['total'] += $this->service_price[$order->service->id];

			$job = [];
			$job['id'] = $order->id;
			$job['time'] = $order->start_at->format('H:i:s');
			$job['vehicle'] = $order->vehicle->toArray();
			$job['wash_type'] = $order->service->name;
			$job['wash_time'] = $order->wash_time;
			$job['etc'] = $order->etc;
			$job['rating'] = $order->rating;
			$job['pay'] = ($order->rating === null || $order->rating >= 4 ? $this->service_price[$order->service->id] : 0 );

			$job_date = $order->done_at->format('m/d/Y (l)');

			$orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['orders'][] = $job;
			@$orders_by_worker[$order->worker->id]['jobs']['days'][$job_date]['pay'] += $job['pay'];

			if(in_array($order->worker->id, array_keys($min_worker_id)) && $orders_by_worker[$order->worker->id]['jobs']['total'] < $min_worker_id[$order->worker_id]) {
				@$orders_by_worker[$order->worker->id]['minimum'] = max(0, $min_worker_id[$order->worker_id] - $orders_by_worker[$order->worker->id]['jobs']['total']);
			}

		}

		$disk = Storage::disk('local');
		$dir_path = ['payroll', date('Y'), $orders->first()->done_at->startOfWeek()->format("m-d")];

		foreach($orders_by_worker as $worker_id => &$worker) {

			$worker['promotional'] = (float)@$cogs_by_washer[$worker_id] + (float)@$worker['minimum'];

			$data=[];
			$data['week_of'] = $week_of;
			$data['washer_info'] = $worker;
			$data['weekly_min'] = ( ! empty($min_worker_id[$worker_id]) ? $min_worker_id[$worker_id] : 0 );

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

		$data=[];
		$data['week_of'] = $week_of;
		foreach($orders_by_worker as $worker_id => $w) {

			$total = ($w['jobs']['total'] + $w['minimum']) - $w['rental'];
			$cog = ($w['jobs']['total'] + $w['minimum']) - $w['promotional'];

			$data['cogs'][] = [$w['washer']['name'], $cog, $w['promotional'], $w['rental'], $total];
		}

		$view = view('payroll.cogs', $data);
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
