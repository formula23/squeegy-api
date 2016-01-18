<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/26/15
 * Time: 02:11
 */

namespace App\Squeegy;

use Aws\Sns\SnsClient;
use App\Order;
use Illuminate\Http\Request;

/**
 * Class PushNotification
 * @package App\Squeegy
 */
class PushNotification {

    /**
     * @var
     */
    static $sns_client;

    /**
     * @param $push_token
     * @param string $message
     * @param int $badge
     * @param int $order_id
     */
    public static function send($push_token, $message, $badge = 1, $order_id = 0) {

        if( ! $push_token) return;

        try {

            $target = (preg_match('/gcm/i', $push_token) ? "gcm" : "apns" );

            if($target=="apns") {
                $platform = env('APNS');
                $payload = [
                    'aps' => [
                        'alert' => $message,
                        'sound' => 'default',
                        'badge' => $badge
                    ],
                ];
            } else {
                $platform = env('GCM');
                $payload = [
                    'data' => [
                        'message' => $message,
                        'url' => "squeegy://"
                    ],
                ];
            }

            if($order_id) $payload['order_id'] = (string)$order_id;

            self::$sns_client = \App::make('Aws\Sns\SnsClient');

            self::$sns_client->publish([
                'TargetArn' => $push_token,
                'MessageStructure' => 'json',
                'Message' => json_encode([
                    'default' => $message,
                    $platform => json_encode($payload)
                ]),
            ]);
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }

        return;
    }

}