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

class MixPanelUserObserver
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
        if($user->is('worker')) return;

        if($user->is_anon()) {
            $data = [
                '$email' => $user->email,
                'Is Anonymous' => true,
                '$created' => ($user->created_at
                    ? $user->created_at->toAtomString()
                    : null),
                "Segment ID" =>$user->segment?$user->segment->segment_id:0,
            ];
        } else {

            $data = [
                '$email' => $user->email,
                '$first_name' => $user->first_name(),
                '$last_name' => $user->last_name(),
                '$name' => $user->name,
                '$phone' => substr($user->phone, 2),
                'Gender' => $user->gender,
                'Age Range' => $user->age_range,
                '$created' => ($user->created_at
                    ? $user->created_at->toAtomString()
                    : null),
                "Available Credits"=>$user->availableCredit()/100,
                "Referral Code" =>$user->referral_code,
                "Segment ID" =>$user->segment?$user->segment->segment_id:0,
                'Is Anonymous' => false,
                "Last Wash At" => ( ! empty($user->segment->last_wash_at)?$user->segment->last_wash_at->toAtomString():""),
                "Last Wash Type" => ( ! empty($user->lastWash()) ? $user->lastWash()->service->name : "" ),
                "Partner ID" => ( ! $user->partners->isEmpty() ? $user->partners->first()->id : ""),
                "Partner Name" => ( ! $user->partners->isEmpty() ? $user->partners->first()->name : ""),
            ];

            if ($partner = $user->partners->first()) {
                $data['Partner ID'] = $partner->id;
                $data['Partner Name'] = $partner->name;
            }
            
        }

        if($this->request->input('push_token')) {
            $key_prop = ( $user->device() == 'iOS' ? '$ios_devices' : '$android_devices' );
            $data[$key_prop] = [$this->request->input('push_token')];
        }

        array_filter($data);

        Log::info('User Observer - Saved...');
        Log::info($data);

        if (count($data)) {
//            Log::info('User key:'.$user->getKey());
            $this->mixPanel->people->set($user->getKey(), $data, $this->request->ip());
//            Log::info('Result:');
//            Log::info($result);
        }
    }

    public function deleting(Model $user)
    {
        $this->mixPanel->people->deleteUser($user->getKey());
    }

}