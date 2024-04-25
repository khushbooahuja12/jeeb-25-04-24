<?php

namespace App\Http\Traits;

require __DIR__ . '/../../../vendor/autoload.php';

trait PushNotification
{

    public function send_inapp_notification_to_user($user_id, $title_en, $title_ar, $text_en, $text_ar, $n_type, $data)
    {
        $notification_arr = [
            'fk_user_id' => $user_id,
            'notification_type' => $n_type,
            'notification_title_en' => $title_en,
            'notification_title_ar' => $title_ar,
            'notification_text_en' => $text_en,
            'notification_text_ar' => $text_ar,
            'data' => json_encode($data)
        ];
        $ret = \App\Model\UserNotification::create($notification_arr);
        if ($ret) {
            return $ret->id;
        } else {
            return false;
        }
    }

    public function send_push_notification_to_user($device_token, $type, $title, $body, $data = [])
    {
        $server_key = 'AAAAcXL5StM:APA91bFOeAJRdP_C8QyqEouSfJ78t0Agsem9Me4JUjn4dvjlRn90jxywV7VpwlwH-vUknEpyFYHlN15QD7LDSKT5E0gLtKXGmj1U3Q5CyatowpzcnxexJXByuDMuM24cGTfZc2I13yOV';

        // if ($device_type == 1) {
        //     $msg = [
        //         'type' => $type,
        //         'data' => $data,
        //     ];

        //     $postfields = json_encode(
        //         [
        //             "to" => $device_token,
        //             "notification" => [
        //                 "title" => $title,
        //                 "body" => $body,
        //                 "sound" => "default"
        //             ],
        //             'data' => $msg
        //         ]
        //     );
        // } elseif ($device_type == 2) {
        //     $msg = [
        //         'type' => $type,
        //         'data' => $data,
        //         'title' => $title,
        //         'body' => $body
        //     ];

        //     $postfields = json_encode(
        //         [
        //             "to" => $device_token,
        //             'data' => $msg
        //         ]
        //     );
        //     // pp($postfields);
        // }

        $msg = [
            'type' => $type,
            'data' => (object) $data,
            'title' => $title,
            'body' => $body
        ];

        $postfields = json_encode(
            [
                "to" => $device_token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "sound" => "default"
                ],
                'data' => $msg
            ]
        );

        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';

        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );

        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);

        // echo "<pre>";print_r($result);die;

        if ($result === FALSE) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        if ($result === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    public function send_push_notification_to_multiple_user($deviceTokens, $type, $title, $body, $data = [])
    {
        $server_key = 'AAAAcXL5StM:APA91bFOeAJRdP_C8QyqEouSfJ78t0Agsem9Me4JUjn4dvjlRn90jxywV7VpwlwH-vUknEpyFYHlN15QD7LDSKT5E0gLtKXGmj1U3Q5CyatowpzcnxexJXByuDMuM24cGTfZc2I13yOV';

        $msg = [
            'type' => $type,
            'data' => (object) $data,
            'title' => $title,
            'body' => $body
        ];

        $postfields = json_encode(
            [
                "registration_ids" => $deviceTokens,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                    "sound" => "default"
                ],
                'data' => $msg
            ]
        );

        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';

        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );

        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $result = curl_exec($ch);

        if ($result === FALSE) {
            return false;
        } else {
            return true;
        }
    }
}
