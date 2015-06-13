<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SendController extends Controller {

	public function index(Twilio $twilio)
    {
        $twilio->message("+18185177309", 'Hi baby... Love you!! :-* -- It\'s me Dan');
        $twilio->message("+13106004938", 'SENT');
    }

}
