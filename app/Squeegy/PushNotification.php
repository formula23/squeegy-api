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
     * @param Order $order
     * @param string $message
     */
    public static function send(Order $order, $message='') {

        if( ! $order->customer->push_token) return;

        self::$sns_client = \App::make('SnsClient');

        self::$sns_client->publish([
            'TargetArn' => $order->customer->push_token,
            'MessageStructure' => 'json',
            'Message' => json_encode([
                'default' => $message,
                env('APNS') => json_encode([
                    'aps' => [
                        'alert' => $message,
                        'sound' => 'default',
                        'badge' => 1
                    ],
                    'order_id' => (string)$order->id,
                ])
            ]),
        ]);

        return;
    }

}