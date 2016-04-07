<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/7/16
 * Time: 10:35
 */

namespace app\Squeegy;


class CampaignMonitor extends \Casinelli\CampaignMonitor\CampaignMonitor
{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    public function smartSend($smartemail_id, $clientId = null)
    {
        return new \CS_REST_Transactional_SmartEmail($smartemail_id, $this->getAuthTokens(), $clientId);
    }

}