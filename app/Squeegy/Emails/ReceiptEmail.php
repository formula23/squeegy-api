<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/13/16
 * Time: 17:22
 */

namespace app\Squeegy\Emails;

use CampaignMonitor;

class ReceiptEmail
{

    public function __construct()
    {


    }

    public function sendTo($user)
    {

    }

    protected function newTransaction()
    {
        return CampaignMonitor::smartSend($this->getEmailId());


    }

    public function getEmailId()
    {
        return config("campaignmonitor.template_ids.receipt");
    }

    public function variables()
    {
        return [
            
        ];
    }

}