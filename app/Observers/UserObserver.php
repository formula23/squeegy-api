<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/6/16
 * Time: 17:40
 */

namespace App\Observers;


use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserObserver
{

    protected $request;
    protected $mixPanel;

    /**
     * UserObserver constructor.
     * @param Request $request
     * @param LaravelMixpanel $mixpanel
     */
    public function __construct(Request $request, LaravelMixpanel $mixpanel)
    {
        $this->request = $request;
        $this->mixPanel = $mixpanel;
    }

    public function saving(Model $user)
    {
        Log::info("********** saving **********");
    }

    /**
     * @param Model $user
     */
    public function saved(Model $user)
    {
        $firstName = $user->first_name;
        $lastName = $user->last_name;

        if ($user->name) {
            $nameParts = explode(' ', $user->name);
            array_filter($nameParts);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
        }

        if($user->is_anon()) {
            $data = [
                '$email' => $user->email,
                'Is Anonymous' => true,
            ];
        } else {
            $data = [
                '$email' => $user->email,
                '$first_name' => $firstName,
                '$last_name' => $lastName,
                '$name' => $user->name,

                '$phone' => substr($user->phone, 2),
                '$created' => ($user->created_at
                    ? $user->created_at->format('Y-m-d\Th:i:s')
                    : null),
                "Available Credits"=>$user->availableCredit()/100,
                "Referral Code" =>$user->referral_code,
                "Segment ID" =>$user->segment?$user->segment->segment_id:0,
                'Is Anonymous' => false,
            ];
        }

        if($this->request->input('push_token')) {
            $data['$ios_devices'] = [$this->request->input('push_token')];
        }

        array_filter($data);

        Log::info('User Observer - Saved...');
        Log::info($data);

        if (count($data)) {
            Log::info('User key:'.$user->getKey());
            $result = $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
            Log::info($result);
        }
    }

    public function deleted($u)
    {
        Log::info("*******************************************************************************");
        Log::info('Deleted');
    }

    public function deleting(Model $user)
    {
        Log::info("*******************************************************************************");
        Log::info('Deleting');
//        Log::info($user);

        $this->mixPanel->people->deleteUser($user->getKey());
    }

}