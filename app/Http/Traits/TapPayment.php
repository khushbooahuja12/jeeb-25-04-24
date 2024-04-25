<?php

namespace App\Http\Traits;

require __DIR__ . '/../../../vendor/autoload.php';

trait TapPayment
{

    public function create_customer($user)
    {

        if (!$user || !$user->mobile) {
            return false;
        }
        
        $curl = curl_init();

        $first_name = $user->name!="" ? $user->name : "User_".$user->id;
        $postfields = json_encode(
            [
                "first_name" => $first_name,
                "middle_name" => "",
                "last_name" => "",
                "email" => $user->email,
                "phone" => [
                    "country_code" => $user->country_code,
                    "number" => $user->mobile
                ],
                "description" => "",
                "metadata" => [
                    "created_at" => $user->created_at
                ],
                "currency" => "QAR"
            ]
        );

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/customers",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postfields,
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $customer_id = false;
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap create_customer cURL Error #:' . $err);
        } else {
            \Log::info('Tap create_customer response:' . $response);
            $response_json = json_decode($response);
            $customer_id = $response_json && isset($response_json->id) ? $response_json->id : false;
        }

        return $customer_id;

    }

    public function get_charge($charge_id, $order_id=0, $cus_ref="")
    {

        if (!$charge_id) {
            return false;
        }
        
        $curl = curl_init();

        $postfields = json_encode(
            []
        );

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/charges/".$charge_id,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => $postfields,
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $charge_status = false;
        $charge_text = "Payment is not successfully made, please try again..";
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap get_charge cURL Error #:' . $err);
        } else {
            \Log::info('Tap get_charge response:' . $response);
            $response_json = json_decode($response);
            if ($response_json && isset($response_json->status)) {
                if ($response_json->status=="CAPTURED") {
                    $charge_status = true;
                    $charge_text = "Success";
                } else {
                    if (isset($response_json->response) && isset($response_json->response->message)) {
                        $charge_status = false;
                        if ($response_json->response->message=="Declined, Tap") {
                            $charge_text = "Your card is blocked temporarily, please use another card..";
                        } else {
                            $charge_text = $response_json->response->message;
                        }
                    }
                }
            }
        }

        return ["status"=>$charge_status,"text"=>$charge_text,"response"=>$response];

    }

    public function create_charge_with_token($token_id, $order_id, $cus_ref, $amount,$pay_type=false)
    {

        if (!$token_id || !$order_id || !$cus_ref || !$amount) {
            return false;
        }
        
        $curl = curl_init();
        
        $redirect_url = env("APP_URL").'order_payment';
        if ($pay_type=="forgot_something_order") {
            $redirect_url = env("APP_URL").'forgot_something_order_payment';
        }
        elseif ($pay_type=="add_wallet") {
            $redirect_url = env("APP_URL").'add_wallet_payment';
        }

        $postfields = json_encode(
            [
                "amount"=>$amount,
                "currency"=>"QAR",
                "reference"=>[
                    "order"=>$order_id
                ],
                "customer"=>[
                    "id"=>$cus_ref
                ],
                "source"=>[
                    "id"=>$token_id
                ],
                "redirect"=>[
                    "url"=>$redirect_url
                ],
                "metadata"=>[
                    "order_id"=>$order_id
                ]
            ]
        );

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/charges/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postfields,
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $charge_status = false;
        $charge_text = "Payment is not successfully made, please try again..";
        $transaction = [];
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap get_charge cURL Error #:' . $err);
        } else {
            \Log::info('Tap get_charge response:' . $response);
            $response_json = json_decode($response);
            if ($response_json && isset($response_json->status)) {
                if ($response_json->status=="CAPTURED") {
                    $charge_status = true;
                    $charge_text = "Success";
                } else {
                    if (isset($response_json->response) && isset($response_json->response->message)) {
                        $charge_status = false;
                        if ($response_json->response->message=="Declined, Tap") {
                            $charge_text = "Your card is blocked temporarily, please use another card..";
                        } else {
                            $charge_text = $response_json->response->message;
                        }
                    }
                }
            }
            if ($response_json && isset($response_json->transaction)) {
                $transaction = $response_json->transaction;
            }
        }

        return ["status"=>$charge_status,"text"=>$charge_text,"response"=>$response,"transaction"=>$transaction];

    }

    public function create_charge_for_card_id($card_id, $order_id, $cus_ref, $amount, $pay_type=false)
    {

        if (!$card_id || !$order_id || !$cus_ref || !$amount) {
            return false;
        }
        
        $curl = curl_init();

        $postfields = json_encode(
            [
                "saved_card"=>[
                    "card_id"=>$card_id,
                    "customer_id"=>$cus_ref
                ],
                "client_ip"=>"78.100.51.132"
            ]
        );

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/tokens/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postfields,
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $token_id = false;
        $charge_text = "Payment token is not created successfully. please try again..";
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap get_charge cURL Error #:' . $err);
        } else {
            \Log::info('Tap get_charge response:' . $response);
            $response_json = json_decode($response);
            if ($response_json && isset($response_json->id)) {
                $token_id = $response_json->id;
                $charge_text = "Payment token is created successfully";
            }
        }

        if ($token_id) {
            return $this->create_charge_with_token($token_id, $order_id, $cus_ref, $amount, $pay_type);
        }

        return ["token_id"=>$token_id,"text"=>$charge_text,"response"=>$response,"transaction"=>[]];

    }

    public function get_cards($cus_ref)
    {

        if (!$cus_ref) {
            return false;
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/card/".$cus_ref,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => "{}",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY")
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $carts = [];
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap get_carts cURL Error #:' . $err);
        } else {
            \Log::info('Tap get_carts response:' . $response);
            $response_json = json_decode($response);
            if ($response_json && isset($response_json->data)) {
                if (is_array($response_json->data)) {
                    foreach ($response_json->data as $key=>$cart) {
                        $carts[$key]['id'] = $cart->id;
                        $carts[$key]['brand'] = $cart->brand;
                        $carts[$key]['name'] = $cart->name;
                        $carts[$key]['exp_year'] = $cart->exp_year;
                        $carts[$key]['exp_month'] = $cart->exp_month;
                        $carts[$key]['last_four'] = $cart->last_four;
                    }
                }
            }
        }

        return $carts;

    }

    public function delete_card($card_id, $cus_ref)
    {

        if (!$card_id || !$cus_ref) {
            return false;
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.tap.company/v2/card/$cus_ref/$card_id/",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "DELETE",
          CURLOPT_POSTFIELDS => "{}",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
            "content-type: application/json"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        $response_status = false;
        $response_text = "Card is not successfully deleted, please try again..";
        
        curl_close($curl);
        
        if ($err) {
            \Log::error('Tap delete_saved_card cURL Error #:' . $err);
        } else {
            \Log::info('Tap delete_saved_card response:' . $response);
            $response_json = json_decode($response);
            if ($response_json && isset($response_json->deleted)) {
                if ($response_json->deleted) {
                    $response_status = true;
                    $response_text = "Card is successfully deleted.";
                } 
            }
            if ($response_json && isset($response_json->transaction)) {
                $transaction = $response_json->transaction;
            }
        }

        return ["status"=>$response_status,"text"=>$response_text,"response"=>$response];

    }

}
