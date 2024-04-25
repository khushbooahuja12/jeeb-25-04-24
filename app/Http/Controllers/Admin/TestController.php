<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{

    use \App\Http\Traits\PushNotification;

    protected function notify()
    {
        // $data = [
        //     'status' => 1,
        //     'order_id' => '1',
        //     'user_id' => 4,
        //     'orderId' => '1000'
        // ];
        $data = [
            'ticket_id' => 9,
            'order_id' => '1072',
            'message_type' => 'received',
            'message' => 'Hi, Below mentioned products are unfortunately out of stock, please choose appropriate action',
            'created_at' => '',
            'status' => 1
        ];

        // $title = "Still didn't eat the suhoor ? 😒";
        // $body = 'No worries! Get Frodz frozen meals at Jeeb in 30 min. Pasta or chicken...';

        $title = "Out of Stock";
        $body = 'Some of the items of your order $order->orderId are out of stock';

        $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => 4])->first();
        if ($user_device) {
            $type = 3;
            $title = $title;
            $body = $body;

            $this->send_push_notification_to_user($user_device->device_token, $type, $title, $body, $data);
        }
    }
}
