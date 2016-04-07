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
                "Segment ID" =>$user->segment->segment_id,
                'Is Anonymous' => false,
                '$ios_devices' => ["90d2346ba8db9466f074159a13515735e277fd77eca24eb6e3036353203dcd53"],
            ];
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

    /**
     * @param Model $user
     */
//    public function updated(Model $user)
//    {
//        $this->mixPanel->identify($user->getKey());
//        $firstName = $user->first_name;
//        $lastName = $user->last_name;
//
//        if ($user->name) {
//            $nameParts = explode(' ', $user->name);
//            array_filter($nameParts);
//            $lastName = array_pop($nameParts);
//            $firstName = implode(' ', $nameParts);
//        }
//
//        $data = [
//            '$first_name' => $firstName,
//            '$last_name' => $lastName,
//            '$name' => $user->name,
//            '$email' => $user->email,
//            '$created' => ($user->created_at
//                ? $user->created_at->format('Y-m-d\Th:i:s')
//                : null),
//        ];
//        array_filter($data);
//
//        if (count($data) && $user->getKey()) {
//            $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
//        }
//    }
//
}