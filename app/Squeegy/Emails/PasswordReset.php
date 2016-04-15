<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/14/16
 * Time: 20:55
 */

namespace app\Squeegy\Emails;


class PasswordReset extends Email
{
    public function getEmailId()
    {
        return config("campaignmonitor.template_ids.pw_reset");
    }

    public function variables($user, $token)
    {
        return [
            'RESET_PW_URL'=>config('squeegy.website_url').'/password/reset/'.$token
        ];

    }
}