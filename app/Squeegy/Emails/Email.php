<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/13/16
 * Time: 18:22
 */

namespace App\Squeegy\Emails;

use CampaignMonitor;

abstract class Email
{
    protected $data = [];
    protected $bcc=[];
    /**
     * @param array $data
     * @return $this
     */
    public function withData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function withBCC($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @param $user
     * @return mixed
     */
    public function sendTo($user)
    {
        $mailer = $this->newTransaction();

        $data = call_user_func_array(
            [$this, 'variables'],
            array_merge(compact('user'), $this->data)
        );

        $payload = [
            'To' => $user->email,
            'Data'=> $data,
        ];

        if($this->bcc) {
            $payload['Bcc'] = $this->bcc;
        }

        try {
            return $mailer->send($payload);
        } catch(\Exception $e) {
            \Log::info($e);
            \Bugsnag::notifyException($e);
        }

    }

    /**
     * @return mixed
     */
    protected function newTransaction()
    {
        return CampaignMonitor::smartSend($this->getEmailId());
    }

    /**
     * @return mixed
     */
    protected abstract function getEmailId();
}