<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\OrderSchedule;
use App\Squeegy\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ScheduleController extends Controller {


	/**
	 * Display a listing of available time slots
	 *
	 * @return Response
	 */
	public function available()
	{
		$schedule = new Schedule();
		$available = $schedule->availability();

		$data = ["data"=>$available];

		return $this->response->withArray($data);

	}

}
