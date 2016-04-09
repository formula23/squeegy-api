<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/8/16
 * Time: 15:00
 */

namespace app\Observers;


use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EtaLogObserver
{

    protected $mixPanel;

    /**
     * EtaLogObserver constructor.
     * @param LaravelMixpanel $mixpanel
     */
    public function __construct(LaravelMixpanel $mixpanel)
    {
        $this->mixPanel = $mixpanel;
    }

    public function saved(Model $eta_log)
    {
        $data = [
            'Last Known City' => $eta_log->city,
            'Last Known Zip' => $eta_log->postal_code,
        ];

        Log::info($data);

        $this->mixPanel->people->set($eta_log->user_id, $data);
    }

}