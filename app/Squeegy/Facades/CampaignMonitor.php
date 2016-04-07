<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/7/16
 * Time: 12:01
 */

namespace App\Squeegy\Facades;


use Illuminate\Support\Facades\Facade;

class CampaignMonitor extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'campaignmonitor';
    }
}