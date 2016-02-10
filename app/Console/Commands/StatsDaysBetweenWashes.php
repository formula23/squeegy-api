<?php namespace App\Console\Commands;

use App\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class StatsDaysBetweenWashes extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'stats:days_between_washes';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get the average number of days between each customers wash.';

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
		$orders = Order::where('status', 'done')
			->whereIn('user_id', function($q) {
				$q->select('user_id')
					->from('orders')
					->where('status', 'done')
					->where('created_at', '>', '2015-08-14')
					->groupBy('user_id')
					->having(DB::raw('count(*)'), '>', 1)
					->orderBy('user_id');
			})
			->orderBy('user_id')->orderBy('done_at')->get();

		$time_between_washes=[];
		$all_days_between=[];
		foreach($orders as $idx=>$order) {

			if(!isset($orders[$idx+1])) continue;

			$next_order = $orders[$idx+1];

			if($next_order->done_at->isSameDay($order->done_at)) continue;

			if($next_order->user_id != $order->user_id) continue;

			$time_diff_key = $order->done_at." -> ".$next_order->done_at;

			if( ! isset($time_between_washes[$order->user_id])) {
				$time_between_washes[$order->user_id]=[];
			}

			$days_between = $order->done_at->diffInDays($next_order->done_at);
			$all_days_between[]=$days_between;
			$time_between_washes[$order->user_id][$time_diff_key] = $days_between;

		}

		$avg_day_between = array_sum($all_days_between) / count($all_days_between);

		if($this->getOutput()->getVerbosity() > 1) {
			print_r($time_between_washes);
		}

		$this->info("Avg. days between washes: ".$avg_day_between);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['stat', InputArgument::OPTIONAL, 'i.e. days_between_washes, avg_order_value', 'days_between_washes'],
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
