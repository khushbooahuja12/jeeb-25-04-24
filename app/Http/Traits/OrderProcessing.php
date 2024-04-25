<?php

namespace App\Http\Traits;

require __DIR__ . '/../../../vendor/autoload.php';

use Exception;
use App\Model\User;
use App\Model\UserCart;
use App\Model\UserBuyItForMeRequest;
use App\Model\UserBuyItForMeCart;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\StorekeeperProduct;
use App\Model\OrderStatus;
use App\Model\Coupon;
use App\Model\ScratchCard;
use App\Model\ScratchCardUser;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\Invoice;
use App\Model\Storekeeper;
use App\Model\Driver;
use App\Model\CouponUses;

use App\Events\OrderReceived;
use App\Events\OrderCancelled;
use App\Jobs\ProcessOrderJson;

trait OrderProcessing
{
    use \App\Http\Traits\PayThem;

    /* ------------------------------------------------------
    ************ All order related functions ************
    ------------------------------------------------------
    --- Scratch cards ---
    ------------------------------------------------------
    add_new_scratch_card
    is_scratch_card_enabled_in_order

    ------------------------------------------------------
    --- Whatsapp Tracking ---
    ------------------------------------------------------
    add_whatsapp_tracking_data_in_order
    update_whatsapp_tracking_userid_and_key_in_order
    update_whatsapp_tracking_response_in_order
    notify_whatsapp
    send_qonvo_webhook_request

    ------------------------------------------------------
    --- Notification ---
    ------------------------------------------------------
    send_order_notifications
    send_vendor_new_order_push_notification
    send_vendor_cancelled_order_push_notification
    send_storekeeper_notification
    mobile_sms_curl_in_order
    send_push_notification_to_user_in_order

    ------------------------------------------------------
    --- Mark order status ---
    ------------------------------------------------------
    order_confirmed
    order_failed
    order_invoiced
    order_out_for_delivery
    order_delivered
    order_cancelled
    mark_order_as_invoiced

    ------------------------------------------------------
    --- Refund to order ---
    ------------------------------------------------------
    get_wallet_to_refund
    make_refund_in_order

    ------------------------------------------------------
    --- Add products to order ---
    ------------------------------------------------------
    add_order_products
    assign_storekeeper_in_order

    ------------------------------------------------------
    -- Order and order number ---
    ------------------------------------------------------
    get_order_number
    make_order_number
    get_order_arr

    ------------------------------------------------------
    -- Forgot something store IDs ---
    ------------------------------------------------------
    forgot_something_store_ids

    ------------------------------------------------------
    --- Paythem ---
    ------------------------------------------------------
    add_paythem_tracking_data_in_order
    update_paythem_tracking_userid_and_key_in_order
    update_paythem_tracking_response_in_order
    get_paythem_voucher

    ------------------------------------------------------- */

    // ============ Add new scratch card ============== 
    protected function add_new_scratch_card($user_id, $scratch_card, $status=0, $order_id=0) {
        if (!is_object(($scratch_card))) {
            return false;
        }
        $scratch_card_arr = $scratch_card->toArray();
        $scratch_card_arr['id'] = NULL;
        $scratch_card_arr['fk_user_id'] = $user_id;
        $scratch_card_arr['fk_scratch_card_id'] = $scratch_card->id;
        $scratch_card_arr['fk_order_id'] = $order_id;
        $scratch_card_arr['status'] = $status;
        if ($scratch_card->scratch_card_type==1) {
            $scratch_card_arr['coupon_code'] = 'SC'.$user_id.time();
        }
        // Set expiry date
        $expiry_date=date('Y-m-d');
        $expiry_in = intval($scratch_card->expiry_in);
        if ($expiry_in>0) {
            $expiry_date = strtotime("+$expiry_in day", strtotime($expiry_date));
            $expiry_date=date('Y-m-d',$expiry_date);
        }
        $scratch_card_arr['expiry_date'] = $expiry_date;
        $scratch_card_arr['created_at'] = \Carbon\Carbon::now();
        $scratch_card_arr['updated_at'] = \Carbon\Carbon::now();
        return ScratchCardUser::create($scratch_card_arr);
    }

    // Check if scratch card is enabled
    protected function is_scratch_card_enabled_in_order ($user_id=0, $user=false) {
        // Assign logged in users features
        $scratch_card_feature=env("scratch_card", false);
        // Get user
        if (!$user) {
            $user = User::find($user_id);
        }
        // Check user level features
        if ($user && !$scratch_card_feature) {
            $user_features = explode(',',$user->features_enabled);
            if (is_array($user_features) && !empty($user_features) && in_array('scratch_card',$user_features)) {
                // User Specific Scratch Card feature
                $scratch_card_feature = true;
            }
        }
        return $scratch_card_feature;
    }

    // Add whatsapp user tracking data
    function add_whatsapp_tracking_data_in_order($user_id,$mobile,$key,$request,$response,$tracking_id=0) {
        if ($tracking_id==0) {
            $user_tracking = \App\Model\UserWhatsAppTracking::create([
                'user_id'=>$user_id,
                'mobile' =>$mobile,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
            $tracking_id = $user_tracking->id;
        } else {
            \App\Model\UserWhatsAppTracking::find($tracking_id)->update([
                'user_id'=>$user_id,
                'mobile' =>$mobile,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
        }
        return $tracking_id;
    }

    // Update whatsapp user tracking data
    function update_whatsapp_tracking_userid_and_key_in_order($tracking_id,$key,$user_id) {
        $user_tracking = \App\Model\UserWhatsAppTracking::find($tracking_id)->update(['key'=>$key,'user_id'=>$user_id]);
        return $user_tracking;
    }

    function update_whatsapp_tracking_response_in_order($tracking_id,$response) {
        $user_tracking = \App\Model\UserWhatsAppTracking::find($tracking_id)->update(['response'=>$response]);
        return $user_tracking;
    }

    // Notify WhatsApp
    function notify_whatsapp() {
        $notify_whatsapp = env('NOTIFY_WHATSAPP',false);
        return $notify_whatsapp;
    }

    // ============ WhatsApp webhook API call request
    public function send_qonvo_webhook_request($user_id, $phone, $webhookUrl,$webhookData)
    {

        \Log::info('send_qonvo_webhook_request - '.$webhookUrl);
        
        $tracking_id = $this->add_whatsapp_tracking_data_in_order($user_id, $phone, $webhookUrl, json_encode($webhookData), '');
        // Your webhook URL
        // $webhookUrl = 'https://example.com/webhook-endpoint';

        // Data to send in the webhook request (if needed)
        // $webhookData = [
        //     'event' => 'something_happened',
        //     'data' => 'some_data',
        // ];

        // Send the webhook request
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($webhookUrl, [
                'json' => $webhookData, // Use 'json' for JSON data
                // You can add headers and other options here if needed
            ]);

            // Check the response if needed
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response_in_order($tracking_id,$responseBody);
            }

            if ($statusCode == 200) {
                // The webhook request was successful
                // You can process the response as needed
            } else {
                // Handle an unsuccessful response
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Handle request exceptions, such as connection errors
            // Log the exception or perform error handling
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response_in_order($tracking_id,$e->getMessage());
            }
        }
    }

    // ============ Order confirmed ============== 
    protected function order_confirmed($order_id, $order=false, $notify_whatsapp=false) {

        \Log::info('Order confirmed - '.$order_id);
        
        $order_status = 0;
        $order = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')->select("orders.*","order_delivery_slots.delivery_time")->where(['orders.id'=>$order_id])->first();
        $user = User::find($order->fk_user_id);
        $user_mobile = $user ? '+'.$user->country_code.$user->mobile : 0;

        if ($order) {

            // Update order first status
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => 0])->first();
            if (!$exist) {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => 0,
                    'created_at' => $order->created_at
                ];
                OrderStatus::create($insert_arr);
            }

            // Update order status
            $order_status = 1;
            $order->update(['status' => $order_status]);
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
            $notify = false;
            if ($exist) {
                $update_arr = [
                    'status' => $order_status
                ];
                OrderStatus::find($exist->id)->update($update_arr);
            } else {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => $order_status
                ];
                OrderStatus::create($insert_arr);
                $notify = true;
            }

            // Paythem processing
            $types_in_cart = explode(',',$order->products_type);
            if (in_array('paythem',$types_in_cart)) {
                $this->get_paythem_voucher($order_id);
            }

            // Add new scratch card
            $scratch_card_added = false;
            $is_scratch_card_enabled = $this->is_scratch_card_enabled_in_order($order->fk_user_id, $user);
            if ($is_scratch_card_enabled) {
                $user_scratch_card = ScratchCardUser::where('fk_user_id',$order->fk_user_id)
                                    ->where('fk_order_id',$order->fk_order_id)
                                    ->where('deleted',0)
                                    ->first();
                if (!$user_scratch_card) {
                    if ($order->test_order==1) {
                        $scratch_card = ScratchCard::where([
                            'apply_on'=>'order',
                            'deleted'=>0
                        ])
                        ->where('apply_on_min_amount','<=',$order->total_amount)
                        ->orderBy('apply_on_min_amount','desc')
                        ->orderBy('id','desc')
                        ->first();
                    } else {
                        $scratch_card = ScratchCard::where([
                            'status'=>1,
                            'apply_on'=>'order',
                            'deleted'=>0
                        ])
                        ->where('apply_on_min_amount','<=',$order->total_amount)
                        ->orderBy('apply_on_min_amount','desc')
                        ->orderBy('id','desc')
                        ->first();
                    }
                    if ($scratch_card) {
                        $scratch_card_added = $this->add_new_scratch_card($order->fk_user_id, $scratch_card, 0, $order->id);
                    }
                }
            }
            
            // Process coupon
            if($order->fk_coupon_id) {
                // Update coupon usage
                $coupon_uses_arr = [
                    'fk_user_id' => $order->fk_user_id,
                    'fk_coupon_id' => $order->fk_coupon_id,
                    'fk_order_id' => $order->id,
                    'uses_count' => 1
                ];
                CouponUses::create($coupon_uses_arr);
                // Add redeemed coupon value for scratch card
                $coupon=Coupon::find($order->fk_coupon_id);
                if($coupon) {
                    $scratch_card=ScratchCardUser::where([
                        'coupon_code'=>$coupon->coupon_code,
                        'fk_user_id'=>$order->fk_user_id
                    ])->first();
                    if($scratch_card) {
                        $scratch_card->update([
                            'status'=>3,
                            'redeem_amount'=>$order->coupon_discount,
                            'redeemed_at'=>date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            // Delete cart items 
            if($order->buy_it_for_me_request_id){
                UserBuyItForMeCart::where(['fk_request_id' => $order->buy_it_for_me_request_id])->delete();
                UserBuyItForMeRequest::find($order->buy_it_for_me_request_id)->update(['status' => 3]);
            }else{
                UserCart::where(['fk_user_id' => $order->fk_user_id])->delete();
            }

            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);
                    
                // WhatsApp Webhook
                if ($notify_whatsapp && $this->notify_whatsapp()) {
                    $webhookUrl = 'https://app.qonvoai.com/api/iwh/99888455855cad49a26faccaee808e19';
                    $webhookOrderData = [
                        'mobile'=>$user_mobile,
                        'message'=>'Your order #'.$order->orderId.' has been confirmed succesfully!'
                    ];
                    $webhookData = [
                        'event' => 'order_confirmed',
                        'data' => $webhookOrderData
                    ];
                    $this->send_qonvo_webhook_request($order->fk_user_id, $user_mobile, $webhookUrl,$webhookData);
                }

                // ---------------------------------------------------------------
                // Send notification SMS to admins
                // ---------------------------------------------------------------
                $orderId = $order->orderId;
                $payment_amount_text = '';
                if ($order->payment_type=='cod') {
                    $payment_amount_text .= $order->amount_from_wallet>0 ? "COD (QAR. $order->amount_from_card) / Wallet (QAR. $order->amount_from_wallet)" : "COD (QAR. $order->amount_from_card)";
                } else {
                    $payment_amount_text .= $order->amount_from_wallet>0 ? "Card (QAR. $order->amount_from_card) / Wallet (QAR. $order->amount_from_wallet)" : "Card (QAR. $order->amount_from_card)";
                }
                $msg_to_admin = "Order #$orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ";
                $msg_to_admin .= "Success";

                // Send SMS
                $test_order = $order->test_order;
                $send_notification = ($test_order==1 || $order_status==0) ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_5);
                }
                
            }

            // If only paythem - mark as delivered 
            if ($order->is_paythem_only==1) {
                $order_delivered = $this->order_delivered($order->id);
                $order_status = $order_delivered && isset($order_delivered['order_status']) ? $order_delivered['order_status'] : 0;
            }
            
        }

        return [
            'order_status' => $order_status,
            'scratch_card_added' => $scratch_card_added
        ];
        
    }

    // ============ Order failed ============== 
    protected function order_failed($order_id, $order=false, $charge=false, $notify_whatsapp=false) {

        $order_status = 0;
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        $user = User::find($order->fk_user_id);
        $user_mobile = $user ? '+'.$user->country_code.$user->mobile : 0;
        
        if ($order) {

            // Update order status
            $order_status = 0;
            $order->update(['status' => $order_status]);
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
            $notify = false;
            if ($exist) {
                $update_arr = [
                    'status' => $order_status
                ];
                OrderStatus::find($exist->id)->update($update_arr);
            } else {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => $order_status
                ];
                OrderStatus::create($insert_arr);
                $notify = true;
            }

            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);

                // ---------------------------------------------------------------
                // Send notification SMS to admins
                // ---------------------------------------------------------------
                $orderId = $order->orderId;
                $payment_amount_text = '';
                if ($order->payment_type=='cod') {
                    $payment_amount_text .= $order->amount_from_wallet>0 ? "COD (QAR. $order->amount_from_card) / Wallet (QAR. $order->amount_from_wallet)" : "COD (QAR. $order->amount_from_card)";
                } else {
                    $payment_amount_text .= $order->amount_from_wallet>0 ? "Card (QAR. $order->amount_from_card) / Wallet (QAR. $order->amount_from_wallet)" : "Card (QAR. $order->amount_from_card)";
                }
                $msg_to_admin = "Order #$orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ";

                if ($charge && is_array($charge) && isset($charge['text'])) {
                    $msg_to_admin .= $charge['text'];
                } else {
                    $msg_to_admin .= "Failed";
                }

                // Send SMS
                $test_order = $order->test_order;
                $send_notification = ($test_order==1 || $order->status!=0) ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_5);
                }
            }
            
            // WhatsApp Webhook
            if ($notify_whatsapp && $this->notify_whatsapp()) {
                $webhookUrl = 'https://app.qonvoai.com/api/iwh/99888455855cad49a26faccaee808e19';
                $webhookOrderData = [
                    'mobile'=>$user_mobile,
                    'message'=>'Sorry your transaction failed for the order #'.$order->orderId.', please try again..!'
                ];
                $webhookData = [
                    'event' => 'order_failed',
                    'data' => $webhookOrderData
                ];
                $this->send_qonvo_webhook_request($order->fk_user_id, $user_mobile, $webhookUrl,$webhookData);
            }

        }

        return [
            'order_status' => $order_status
        ];
        
    }

    // ============ Order invoiced ============== 
    protected function order_invoiced($order_id, $order=false) {

        $order_status = 0;
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        
        if ($order) {

            // Update order status
            $order_status = 3;
            $order->update(['status' => $order_status]);
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
            $notify = false;
            if ($exist) {
                $update_arr = [
                    'status' => $order_status
                ];
                OrderStatus::find($exist->id)->update($update_arr);
            } else {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => $order_status
                ];
                OrderStatus::create($insert_arr);
                $notify = true;
            }
            
            // Add the order in the que for driver
            \App\Model\OrderDriver::updateOrCreate([
                    'fk_order_id' => $order_id
                ],
                [
                    'fk_order_id' => $order_id,
                    'fk_driver_id' => 0,
                    'status' => 0
                ]
            );

            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);

            }
        }

        return [
            'order_status' => $order_status
        ];
        
    }

    // ============ Order out_for_delivery ============== 
    protected function order_out_for_delivery($order_id, $order=false) {

        $order_status = 0;
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        $user = User::find($order->fk_user_id);
        $user_mobile = $user ? '+'.$user->country_code.$user->mobile : 0;
        
        if ($order) {

            // Update order status
            $order_status = 6;
            $order->update(['status' => $order_status]);
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
            $notify = false;
            if ($exist) {
                $update_arr = [
                    'status' => $order_status
                ];
                OrderStatus::find($exist->id)->update($update_arr);
            } else {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => $order_status
                ];
                OrderStatus::create($insert_arr);
                $notify = true;
            }
            
            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);

                // WhatsApp Webhook
                if ($this->notify_whatsapp()) {
                    $webhookUrl = 'https://app.qonvoai.com/api/iwh/f18163fa219355962e9998fa030f1e23';
                    $webhookOrderData = [
                        'mobile'=>$user_mobile,
                        'message'=>'Your order #'.$order->orderId.' is out for delivery!'
                    ];
                    $webhookData = [
                        'event' => 'order_out_for_delivery',
                        'data' => $webhookOrderData
                    ];
                    $this->send_qonvo_webhook_request($order->fk_user_id, $user_mobile, $webhookUrl,$webhookData);
    
                    return [
                        'order_status' => $order_status
                    ];
                }
                
            }

        }

    }

    // ============ Order delivered ============== 
    protected function order_delivered($order_id, $order=false) {

        $order_status = 0;
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        $user = User::find($order->fk_user_id);
        $user_mobile = $user ? '+'.$user->country_code.$user->mobile : 0;

        if ($order) {

            // Update order status
            $order_status = 7;
            $order->update(['status' => $order_status]);
            $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
            $notify = false;
            if ($exist) {
                $update_arr = [
                    'status' => $order_status
                ];
                OrderStatus::find($exist->id)->update($update_arr);
            } else {
                $insert_arr = [
                    'fk_order_id' => $order_id,
                    'status' => $order_status
                ];
                OrderStatus::create($insert_arr);
                $notify = true;
            }
            
            // Generate order json
            ProcessOrderJson::dispatch($order->id);

            // Enable the scratch card
            $scratch_cards = ScratchCardUser::where([
                'fk_user_id'=> $order->fk_user_id,
                'fk_order_id'=> $order->id,
                'status'=>0,
                'deleted'=>0
            ])->get();
            $scratch_card_enabled = false;
            if ($scratch_cards) {
                foreach ($scratch_cards as $scratch_card) {
                    $scratch_card_enabled = ScratchCardUser::find($scratch_card->id)->update(['status'=>1]);
                }
            }

            // Release driver
            if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0) {
                Driver::find($order->getOrderDriver->fk_driver_id)->update(['is_available' => 1]);
            }

            // Release storekeepers 
            if (StorekeeperProduct::where(['fk_order_id' => $order->id])->first()) {
                StorekeeperProduct::where(['fk_order_id' => $order->id])->update(['status' => 1]);
            }

            //updating the invoices in account panel
            // $purchasePrice = Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            //     ->join('base_products_store', 'order_products.fk_product_store_id', '=', 'base_products_store.id')
            //     ->select('orders.*')
            //     ->selectRaw("SUM(order_products.product_quantity*base_products_store.product_distributor_price) as total_distributor_price")
            //     ->where('orders.id', '=', $order_id)
            //     ->first();
            // Invoice::create([
            //     'date' => date('Y-m-d'),
            //     'time' => date('H:i:s'),
            //     'order_id' => $order->id,
            //     'selling_price' => $order->total_amount,
            //     'purchase_price' => $purchasePrice ? $purchasePrice->total_distributor_price : 0
            // ]);
            
            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);

                // WhatsApp Webhook
                if ($this->notify_whatsapp()) {
                    $webhookUrl = 'https://app.qonvoai.com/api/iwh/20ce1504507057991f554a29c91ac6c0';
                    $webhookOrderData = [
                        'mobile'=>$user_mobile,
                        'message'=>'Your order #'.$order->orderId.' has been delivered succesfully!',
                        'invoice'=>env('APP_URL','https://jeeb.tech/').'customer-invoice-pdf/'.base64url_encode($order->id)
                    ];
                    $webhookData = [
                        'event' => 'order_delivered',
                        'data' => $webhookOrderData
                    ];
                    $this->send_qonvo_webhook_request($order->fk_user_id, $user_mobile, $webhookUrl,$webhookData);
                }

            }
            
        }

        return [
            'order_status' => $order_status,
            'scratch_card_enabled'=>$scratch_card_enabled
        ];
        
    }

    // ============ Order cancelled ============== 
    protected function order_cancelled($order_id, $order=false, $by='admin') {

        $order_status = 0;
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        
        if ($order) {
            
            $wallet_to_refund_arr = $this->get_wallet_to_refund($order_id, $order);
            $wallet_to_refund = is_array($wallet_to_refund_arr) && isset($wallet_to_refund_arr['refundable_amount']) ? $wallet_to_refund_arr['refundable_amount'] : 0;
            $paythem_only_amount = is_array($wallet_to_refund_arr) && isset($wallet_to_refund_arr['giftcard_total_amount']) ? $wallet_to_refund_arr['giftcard_total_amount'] : 0;
            $refunded = false;

            // Refund to wallet if pay by cart or wallet
            if ($wallet_to_refund>0) {
                $refunded = $this->make_refund_in_order('wallet', $order->fk_user_id, 0, $wallet_to_refund, $order->orderId);
            }

            // Paythem processing
            $types_in_cart = explode(',',$order->products_type);
            // if ($order->payment_type=='online' && in_array('paythem',$types_in_cart)) {
            if (in_array('paythem',$types_in_cart)) {
                // If only paythem - mark as delivered 
                $order->update([
                    'is_paythem_only' => 1,
                    'sub_total' => $paythem_only_amount,
                    'total_amount' => $paythem_only_amount,
                    'grand_total' => $paythem_only_amount
                ]);
                $order_products_deleted = OrderProduct::where('fk_order_id',$order->id)
                    ->where('deleted','=',0)
                    ->where('product_type','!=','paythem')
                    ->update(['deleted'=>1]);
                $order_delivered = $this->order_delivered($order->id);
                $order_status = $order_delivered && isset($order_delivered['order_status']) ? $order_delivered['order_status'] : 0;
                return $order_delivered;
            } else {
                // Update order status    
                $order_status = 4;
                $order->update(['status' => $order_status]);
                $exist = OrderStatus::where(['fk_order_id' => $order_id, 'status' => $order_status])->first();
                $notify = false;
                if ($exist) {
                    $update_arr = [
                        'status' => $order_status
                    ];
                    OrderStatus::find($exist->id)->update($update_arr);
                } else {
                    $insert_arr = [
                        'fk_order_id' => $order_id,
                        'status' => $order_status
                    ];
                    OrderStatus::create($insert_arr);
                    $notify = true;
                }
            }

            // Generate order json
            ProcessOrderJson::dispatch($order->id);

            // Cancel the scratch card
            $scratch_cards = ScratchCardUser::where([
                'fk_user_id'=> $order->fk_user_id,
                'fk_order_id'=> $order->id,
                'deleted'=>0
            ])->get();
            $scratch_card_cancelled = false;
            if ($scratch_cards) {
                foreach ($scratch_cards as $scratch_card) {
                    $scratch_card_cancelled = ScratchCardUser::find($scratch_card->id)->update(['status'=>4]);
                    $coupon_cancelled = Coupon::where(['fk_user_id' => $order->fk_user_id, 'coupon_code' => $scratch_card->coupon_code])->delete();
                }
            }

            // Revert Coupon Use
            $coupon_uses = CouponUses::where(['fk_user_id' => $order->fk_user_id, 'fk_order_id' => $order->id])->delete();
            // Cancel redeemed coupon value for scratch card
            if($order->fk_coupon_id) {
                $coupon=Coupon::find($order->fk_coupon_id);
                if($coupon) {
                    $scratch_card=ScratchCardUser::where([
                        'coupon_code'=>$coupon->coupon_code,
                        'fk_user_id'=>$order->fk_user_id
                    ])->first();
                    if($scratch_card) {
                        $scratch_card->update([
                            'status'=>2,
                            'redeem_amount'=>0,
                            'redeemed_at'=>NULL
                        ]);
                    }
                }
            }

            // Release driver
            if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0) {
                Driver::find($order->getOrderDriver->fk_driver_id)->update(['is_available' => 1]);
            }

            // If not notified yet
            if ($notify) {

                // Send order notification
                $this->send_order_notifications($order_id, $order_status, $order);
                
                 // Send cancelled order notification
                $this->send_vendor_cancelled_order_push_notification($order);

                // ---------------------------------------------------------------
                // Send notification SMS to admins
                // ---------------------------------------------------------------
                $test_order = $order->test_order;
                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                $msg = "The order has been cancelled";
                $msg .= $by=='user' ? " by the customer, " : " by the admin, ";
                $msg .= "order number: #".$order->orderId;
                if ($send_notification && $send_notification_number) {
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number);
                }
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_2);
                }
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_3);
                }
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_4);
                }
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number_5);
                }   

            }
        }

        return [
            'order_status' => $order_status,
            'scratch_card_cancelled'=>$scratch_card_cancelled,
            'coupon_cancelled'=>$coupon_cancelled,
            'wallet_to_refund'=>$wallet_to_refund,
            'refunded'=>$refunded,
            'coupon_uses'=>$coupon_uses
        ];
        
    }

    // ============ Get order amount to refund ============== 
    protected function get_wallet_to_refund($order_id, $order=false) {

        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        $total_amount = 0;
        $wallet_to_refund = 0;
        $giftcard_total_amount = 0;
        
        if ($order) {
            
            // Check Main Order
            $total_amount = $order->total_amount;
            if ($order->payment_type == 'cod') {
                if ($order->amount_from_wallet>0) {
                    $wallet_to_refund = $order->amount_from_wallet;
                } 
            } else {
                $wallet_to_refund = $order->total_amount;
            }

            // Check sub orders
            $amount_from_suborders= $order->getSubOrder ? $order->getOrderSubTotal($order->id) : 0;
            $amount_from_card_suborders= $order->getSubOrder ? $order->getOrderPaidByCart($order->id) : 0;
            $amount_from_wallet_suborders = $order->getSubOrder ? $order->getOrderPaidByWallet($order->id) : 0;
            
            $total_amount = $total_amount + $amount_from_suborders;
            $wallet_to_refund = $wallet_to_refund + $amount_from_card_suborders + $amount_from_wallet_suborders;

            //Check any gift card exist in the order then deduct its amount from total order amount
            $order_products = OrderProduct::join('base_products_store','order_products.fk_product_store_id','=','base_products_store.id')->select('order_products.*','base_products_store.product_type')->where('order_products.fk_order_id', $order->id)->get();
            foreach ($order_products as $order_product) {
                if($order_product->product_type == 'paythem'){
                    $giftcard_total_amount += $order_product->total_product_price;
                }
            }
            
            // Remove if giftcard  value is there
            if ($order->amount_from_wallet>0) {
                $wallet_to_refund = $wallet_to_refund-$giftcard_total_amount;
            }

        }

        return [
            'total_amount' => $total_amount,
            'giftcard_total_amount' => $giftcard_total_amount,
            'refundable_amount' => $wallet_to_refund
        ];
        
    }

    // ============ Sending push notification and in-app notification ==============
    protected function send_order_notifications($order_id, $order_status, $order=false) {
        
        $order = $order==false ? Order::select("orders.*")->where(['orders.id'=>$order_id])->first() : $order;
        $user = User::find($order->fk_user_id);
        $lang = $user && $user->lang_preference=='ar' ? 'ar' : 'en';

        $title = '';
        $body = '';

        if ($order_status == 1) {
            $title = $lang=='ar' ? 'تم الطلب 🙌' : 'Success 🙌';
            $body = $lang=='ar' ? 'تمت الموافقة على الطلب وهو فيد التجهيز 🎉' : 'Jeeb has received your order successfully! 🎉';
        } elseif ($order_status == 3) {
            $title = $lang=='ar' ? 'جاهزين ⭐' : 'Ready ⭐';
            $body = $lang=='ar' ? 'تمت فوترة طلبكم وجاهز للتوصيل 📦' : 'Your order is packed and ready to go 📦';
        } elseif ($order_status == 4) {
            $title = $lang=='ar' ? 'تم حذف الطلب ❌' : 'Order cancelled ❌';
            $body = $lang=='ar' ? 'تم حذف طلبكم ❌' : 'Your order has been cancelled ❌';
        } elseif ($order_status == 6) {
            $title = $lang=='ar' ? 'مسافة الطريق ⏱' : 'On our way ⏱';
            $body = $lang=='ar' ? 'مسافة الطريق و الطلب بكون على باب البيت 🚚' : 'Wait for us! Your order is now out for delivery🚚';
        } elseif ($order_status == 7) {
            $title = $lang=='ar' ? 'تم التسليم ✅' : 'Delivered ✅';
            $body = $lang=='ar' ? 'تم توصيل طلبكم, شكرا لاستخدامكم جيب! 🥳' : 'Order delivered! Thank you for using Jeeb 🥳';
        }

        if ($title != '') {
            $data = [
                'status' => $order_status,
                'order_id' => $order_id,
                'user_id' => (string) $order->fk_user_id,
                'orderId' => $order->orderId
            ];
    
            $this->send_inapp_notification_to_user(
                $order->fk_user_id,
                $title,
                "",
                $body,
                "",
                1, //for orders
                $data
            );
    
            $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $order->fk_user_id])->first();
            if ($user_device) {
                $type = 1; //for orders
                $title = $title;
                $body = $body;
    
                $this->send_push_notification_to_user_in_order($user_device->device_token, $type, $title, $body, $data);
            }
        }

    }

    // ============ Mark order as invoiced ============== 
    protected function mark_order_as_invoiced($request, $storekeeperProduct) {
        try {
            // Mark order as invoiced
            $totalCollectedItems = StorekeeperProduct::where(['fk_order_id' => $storekeeperProduct->fk_order_id, 'status' => 1])->count();
            $totalOrderItems = OrderProduct::where(['fk_order_id' => $storekeeperProduct->fk_order_id])->count();
            if ($totalCollectedItems >= $totalOrderItems) {
                // marking order invoiced when all items collected by the storekeeper
                // if out of stock mark it when the ticket closes
                $order_invoiced = $this->order_invoiced($storekeeperProduct->fk_order_id);
                $order_status = $order_invoiced && isset($order_invoiced['order_status']) ? $order_invoiced['order_status'] : 0;
                /* --------------------
                // cancel customer support when order is invoiced
                -------------------- */
                if ($request->input('status') >= 2) {
                    \App\Model\TechnicalSupport::where(['fk_order_id' => $storekeeperProduct->fk_order_id])->update(
                        ['status' => 0]
                    );
                }
            } else {
                $totalReplacedOrderItems = OrderProduct::where(['fk_order_id' => $storekeeperProduct->fk_order_id, 'is_replaced_product'=>1])->count();
                $totalCollectedReplacedOrderItems = OrderProduct::join('storekeeper_products', 'order_products.fk_product_id', '=', 'storekeeper_products.fk_product_id')
                ->where(['order_products.fk_order_id' => $storekeeperProduct->fk_order_id, 'order_products.is_replaced_product'=>1, 'storekeeper_products.status' => 1])
                ->count();

                if ($totalCollectedReplacedOrderItems >= $totalReplacedOrderItems && $totalReplacedOrderItems!=0) {
                    // marking order invoiced when all replaced items collected by the storekeeper
                    $order_invoiced = $this->order_invoiced($storekeeperProduct->fk_order_id);
                    $order_status = $order_invoiced && isset($order_invoiced['order_status']) ? $order_invoiced['order_status'] : 0;
                    /* --------------------
                    // cancel customer support when order is invoiced
                    -------------------- */
                    if ($request->input('status') >= 2) {
                        \App\Model\TechnicalSupport::where(['fk_order_id' => $storekeeperProduct->fk_order_id])->update(
                            ['status' => 0]
                        );
                    }
                } else {
                    // marking order invoiced when all items either collected or out of stock and there are no suggessions by the storekeeper
                    $totalOutOfStockItems = OrderProduct::where(['fk_order_id' => $storekeeperProduct->fk_order_id, 'is_out_of_stock' => 1])->count();
                    $total_suggested_products = \App\Model\TechnicalSupportProduct::where(['fk_order_id'=>$storekeeperProduct->fk_order_id])->count();
                    if ($total_suggested_products==0 && ($totalOutOfStockItems+$totalCollectedItems) >= $totalOrderItems) {
                        $order = \App\Model\Order::find($storekeeperProduct->fk_order_id);
                        $order_invoiced = $this->order_invoiced($storekeeperProduct->fk_order_id,$order);
                        $order_status = $order_invoiced && isset($order_invoiced['order_status']) ? $order_invoiced['order_status'] : 0;
                        /* --------------------
                        // cancel customer support when order is invoiced
                        -------------------- */
                        if ($request->input('status') >= 2) {
                            \App\Model\TechnicalSupport::where(['fk_order_id' => $storekeeperProduct->fk_order_id])->update(
                                ['status' => 0]
                            );
                        }
                        // Send alert to the customer regarding the out of stock products
                        $title = 'Some items are out of stock';
                        $body = 'Some items are out of stock in your order no. #'.$order->orderId;
                        $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $order->fk_user_id])->first();
                        // $data = StorekeeperProduct::where(['fk_order_id' => $storekeeperProduct->fk_order_id, 'status' => 2])->get();
                        $data = [
                            'status' => 3,
                            'order_id' => $order->id,
                            'user_id' => (string) $order->fk_user_id,
                            'orderId' => $order->orderId
                        ];

                        $this->send_inapp_notification_to_user(
                            $order->fk_user_id,
                            $title,
                            "",
                            $body,
                            "",
                            1, //for orders
                            $data
                        );
                        if ($user_device) {
                            $type = 1;
                            $title = $title;
                            $body = $body;
                            $this->send_push_notification_to_user_in_order($user_device->device_token, $type, $title, $body, $data);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => []
            ]);
        }
        return true;
    }

    // ============ Add cart products to order ============== 
    protected function add_order_products($order) {
        
        // Adding cart products to order with Buy for me feature
        $buy_it_for_me_request_id = $order->buy_it_for_me_request_id;
        if($buy_it_for_me_request_id){
            $cart_products = UserBuyItForMeCart::where(['fk_request_id' => $buy_it_for_me_request_id])->get();
        }else{
            $cart_products = UserCart::where(['fk_user_id' => $order->fk_user_id])->get();
        }
        foreach ($cart_products as $value) { 
            
            //Get base products data 
            $base_product = BaseProductStore::leftJoin('categories AS A', 'A.id','=', 'base_products_store.fk_category_id')
            ->leftJoin('categories AS B', 'B.id','=', 'base_products_store.fk_sub_category_id')
            ->leftJoin('brands','base_products_store.fk_brand_id', '=', 'brands.id')
            ->select(
                'base_products_store.*',
                'base_products_store.unit as product_store_unit',
                'base_products_store.product_distributor_price as distributor_price',
                'base_products_store.product_store_price AS product_price',
                'base_products_store.stock as product_store_stock',
                'A.id as category_id',
                'A.category_name_en',
                'A.category_name_ar',
                'B.id as sub_category_id',
                'B.category_name_en as sub_category_name_en',
                'B.category_name_ar as sub_category_name_ar',
                'brands.id as brand_id',
                'brands.brand_name_en',
                'brands.brand_name_ar'
            )
            ->where('base_products_store.id', '=', $value->fk_product_store_id)
            ->first();

            if (!$base_product) {
                // $error_message = $lang == 'ar' ? 'المنتج غير متوفر' : 'The product is not available';
                // throw new Exception($error_message, 106);
                $orderProduct = OrderProduct::create([
                    'fk_order_id' => $order->id,
                    'fk_product_id' => $value->fk_product_id,
                    'fk_product_store_id' => $value->fk_product_store_id,
                    'fk_store_id' => 0,
                    'product_type' => NULL,
                    'paythem_product_id' => 0,
                    'single_product_price' => 0,
                    'total_product_price' => 0,
                    'itemcode' => '',
                    'barcode' => '',
                    'fk_category_id' => 0,
                    'category_name' => '',
                    'category_name_ar' => '',
                    'fk_sub_category_id' => '',
                    'sub_category_name' => '',
                    'sub_category_name_ar' => '',
                    'fk_brand_id' => '',
                    'brand_name' => '',
                    'brand_name_ar' => '',
                    'product_name_en' => '',
                    'product_name_ar' => '',
                    'product_image' => '',
                    'product_image_url' => '',
                    'margin' => '',
                    'distributor_price' => 0,
                    'unit' => '',
                    '_tags' =>'',
                    'tags_ar' => '',
                    'discount' => 0,
                    'product_quantity' => 0,
                    'product_weight' => '',
                    'product_unit' => '',
                    'sub_products' => ''
                ]);
                continue;
            }
            
            $selling_price = number_format($base_product->product_price, 2);
            $orderProduct = OrderProduct::create([
                'fk_order_id' => $order->id,
                'fk_product_id' => $base_product->fk_product_id,
                'fk_product_store_id' => $base_product->id,
                'fk_store_id' => $base_product->fk_store_id,
                'product_type' => $base_product->product_type,
                'paythem_product_id' => $base_product->paythem_product_id,
                'single_product_price' => $selling_price,
                'total_product_price' => $value->total_price,
                'itemcode' => $base_product->itemcode ?? '',
                'barcode' => $base_product->barcode ?? '',
                'fk_category_id' => $base_product->fk_category_id ?? '',
                'category_name' => $base_product->category_name_en ?? '',
                'category_name_ar' => $base_product->category_name_ar ?? '',
                'fk_sub_category_id' => $base_product->sub_category_id ?? '',
                'sub_category_name' => $base_product->sub_category_name_en ?? '',
                'sub_category_name_ar' => $base_product->sub_category_name_ar ?? '',
                'fk_brand_id' => $base_product->fk_brand_id ?? '',
                'brand_name' => $base_product->brand_name_en ?? '',
                'brand_name_ar' => $base_product->brand_name_ar,
                'product_name_en' => $base_product->product_name_en ?? '',
                'product_name_ar' => $base_product->product_name_ar ?? '',
                'product_image' => $base_product->product_image ?? '',
                'product_image_url' => $base_product->product_image_url ?? '',
                'margin' => $base_product->margin ?? '',
                'distributor_price' => $base_product->distributor_price ?? '',
                'unit' => $base_product->product_store_unit ?? '',
                '_tags' => $base_product->_tags ?? '',
                'tags_ar' => $base_product->tags_ar ?? '',
                'discount' => $value->total_discount,
                'product_quantity' => $value->quantity,
                'product_weight' => $base_product->product_store_unit ?? '',
                'product_unit' => $base_product->product_store_unit ?? '',
                'sub_products' => $value->sub_products ?? ''
            ]);
            
            // Assigning storekeeper
            $this->assign_storekeeper_in_order($order, $orderProduct);
            
        }
    }
    
    // ============ Assing Storekeepers ============== 
    protected function assign_storekeeper_in_order($order, $order_product){
        if($order->test_order != 0){
            $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                ->where('storekeepers.deleted', '=', 0)
                ->where('storekeepers.is_test_user', '=', 1)
                ->where('storekeepers.fk_store_id', '=', $order_product->fk_store_id)
                ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $order_product->fk_sub_category_id)
                ->first();
            if ($storekeeper) {
                StorekeeperProduct::create([
                    'fk_storekeeper_id' => $storekeeper->id ?? '',
                    'fk_order_id' => $order_product->fk_order_id,
                    'fk_order_product_id' => $order_product->id,
                    'fk_store_id' => $storekeeper->fk_store_id,
                    'fk_product_id' => $order_product->fk_product_id,
                    'status' => 0
                ]);
            } else {
                $storekeeper = Storekeeper::select("storekeepers.*")
                    ->where('storekeepers.default', '=', 1)
                    ->where('storekeepers.deleted', '=', 0)
                    ->where('storekeepers.is_test_user', '=', 1)
                    ->where('storekeepers.fk_store_id', '=', $order_product->fk_store_id)
                    ->first();
                if ($storekeeper) {
                    StorekeeperProduct::create([
                        'fk_storekeeper_id' => $storekeeper->id ?? '',
                        'fk_order_id' => $order_product->fk_order_id,
                        'fk_order_product_id' => $order_product->id,
                        'fk_store_id' => $storekeeper->fk_store_id,
                        'fk_product_id' => $order_product->fk_product_id,
                        'status' => 0
                    ]);
                }
            }
    
        }else{
    
            $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                ->where('storekeepers.deleted', '=', 0)
                ->where('storekeepers.is_test_user', '!=', 1)
                ->where('storekeepers.fk_store_id', '=', $order_product->fk_store_id)
                ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $order_product->fk_sub_category_id)
                ->first();
            if ($storekeeper) {
                StorekeeperProduct::create([
                    'fk_storekeeper_id' => $storekeeper->id ?? '',
                    'fk_order_id' => $order_product->fk_order_id,
                    'fk_order_product_id' => $order_product->id,
                    'fk_store_id' => $storekeeper->fk_store_id,
                    'fk_product_id' => $order_product->fk_product_id,
                    'status' => 0
                ]);
    
            } else {
                $storekeeper = Storekeeper::select("storekeepers.*")
                    ->where('storekeepers.default', '=', 1)
                    ->where('storekeepers.deleted', '=', 0)
                    ->where('storekeepers.is_test_user', '!=', 1)
                    ->where('storekeepers.fk_store_id', '=', $order_product->fk_store_id)
                    ->first();
                if ($storekeeper) {
                    StorekeeperProduct::create([
                        'fk_storekeeper_id' => $storekeeper->id ?? '',
                        'fk_order_id' => $order_product->fk_order_id,
                        'fk_order_product_id' => $order_product->id,
                        'fk_store_id' => $storekeeper->fk_store_id,
                        'fk_product_id' => $order_product->fk_product_id,
                        'status' => 0
                    ]);
                }
            }
        }
        
    }    

    // ============ Sending notification to vendor about new order recieved ============
    protected function send_vendor_new_order_push_notification($order){
        
        $order = Order::find($order->id);
        $test_order = $order->test_order;
        $send_notification = ($test_order==1 || $order->status==0) ? 0 : 1;
        
        $order_vendors = OrderProduct::where('fk_order_id',$order->id)->groupBy('fk_store_id')->get();
        if($order_vendors){
            foreach ($order_vendors as $key => $value) {
                if ($send_notification) {
                    OrderReceived::dispatch($value);
                }
            }
        }
    }

    // ============ Sending notification to vendor about new order recieved ============
    protected function send_vendor_cancelled_order_push_notification($order){
        
        $order = Order::find($order->id);
        $test_order = $order->test_order;
        $send_notification = ($test_order==1 || $order->status==0) ? 0 : 1;
        
        $order_vendors = OrderProduct::where('fk_order_id',$order->id)->groupBy('fk_store_id')->get();
        if($order_vendors){
            foreach ($order_vendors as $key => $value) {
                if ($send_notification) {
                    OrderCancelled::dispatch($order);
                }
            }
        }
    }

    // ============ Sending push notification and sms to the storekeeper ============
    protected function send_storekeeper_notification($order){
        
        $order = Order::find($order->id);
        $test_order = $order->test_order;
        $send_notification = ($test_order==1 || $order->status==0) ? 0 : 1;
        
        // SMS to Storekeeper
        $storekeeper_products = StorekeeperProduct::where('fk_order_id',$order->id)->groupBy('fk_storekeeper_id')->get();
        if($storekeeper_products){
            foreach ($storekeeper_products as $key => $value) {
                $storekeeper = Storekeeper::where(['id' => $value->fk_storekeeper_id, 'deleted' => 0, 'status' => 1, 'is_test_user' => 0])->first();
                if($storekeeper){
                    $send_notification_number = $storekeeper->country_code . $storekeeper->mobile;
                    if ($send_notification && $send_notification_number) {
                        $msg = "Hello ".$storekeeper->name.", we're excited to share that we have a new order #".$order->orderId." for you.Looking forward to receiving our products from you. Best regards!";
                        $sent = $this->mobile_sms_curl_in_order($msg, $send_notification_number);
                    }
                }
                
            }
        }
    }

    // ============ Sending SMS via Twillio ============
    protected function mobile_sms_curl_in_order($msg, $phone) {
        //echo $phone;die;
        $id = "AC4381447b4e2460bc6aba0c8a27554a96";
        $token = "3a4041c90b36c6396828c0f0ef5db264";
        $url = "https://api.twilio.com/2010-04-01/Accounts/AC4381447b4e2460bc6aba0c8a27554a96/Messages.json";
        $from = "Jeeb";
        // $from = "9389991514";
        $to = "+$phone";
        $msg_title = env("SEND_NOTIFICATION_TITLE","");
        $msg_title = $msg_title=="" ? "" : $msg_title." ";
        $body = $msg_title.$msg;
        $data = array(
            'From' => $from,
            'To' => $to,
            'Body' => $body,
        );
        $post = http_build_query($data);
        $x = curl_init($url);
        curl_setopt($x, CURLOPT_POST, true);
        curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
        curl_setopt($x, CURLOPT_POSTFIELDS, $post);
        $y = curl_exec($x);
        curl_close($x);
        // print_r($y);exit;
        
        return true;
    }

    // ============ Sending push notification to user ============
    protected function send_push_notification_to_user_in_order($device_token, $type, $title, $body, $data = [])
    {
        $server_key = 'AAAAcXL5StM:APA91bFOeAJRdP_C8QyqEouSfJ78t0Agsem9Me4JUjn4dvjlRn90jxywV7VpwlwH-vUknEpyFYHlN15QD7LDSKT5E0gLtKXGmj1U3Q5CyatowpzcnxexJXByuDMuM24cGTfZc2I13yOV';

        $msg_title = env("SEND_NOTIFICATION_TITLE","");
        $msg_title = $msg_title=="" ? "" : $msg_title." ";
        $title = $msg_title.$title;
        
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

    // ============ Get Order No. ===========
    protected function get_order_number($order_id) {
        return '#'.(1000+intval($order_id));
    }
    protected function make_order_number($order_id) {
        return 1000+intval($order_id);
    }

    // ============ Refund to user ============
    protected function make_refund_in_order($refund_source, $user_id, $invoice_id, $amount, $order_id, $wallet_type=3)
    {
        if ($refund_source == 'wallet') {
            (new \App\Model\UserWallet)->updateOrCreate($user_id, $amount, $order_id, $wallet_type);
            return true;
        }
        if ($refund_source == 'card' && $invoice_id != '') {
            // Not ready yet
            return false;
        } else {
            return false;
        }
    }
    
    // ============ Mark out of stock ============
    protected function mark_out_of_stock_in_order($order_id, $order_product_id, $order=false, $refund_to_wallet=false)
    {
        $order = $order==false ? Order::find($order_id) : $order;
        $order_updates = [];
        if ($order) {
            $order_product = OrderProduct::where(['fk_order_id'=>$order->id,'id'=>$order_product_id])->first();
            if ($order_product) {
                $order_product->update([
                    'is_out_of_stock'=>1
                ]);
                $order_updates['sub_total'] = $order->sub_total - $order_product->total_product_price;
                $order_updates['total_amount'] = $order->total_amount - $order_product->total_product_price;
                $order_updates['grand_total'] = $order->grand_total - $order_product->total_product_price;
                if ($refund_to_wallet) {
                    $refunded = $this->make_refund_in_order('wallet', $order->fk_user_id, 0, $order_product->total_product_price, $order->orderId);
                    $order_updates['amount_from_wallet'] = 0-($order_product->total_product_price);
                } else {
                    $order_updates['amount_from_card'] = $order->amount_from_card - $order_product->total_product_price;
                }
                $order->update($order_updates);
            }
        }
        return true;
    }
    
    // ============ Get Order Array ============
    protected function get_order_arr ($lang="en", $order_id=0) {
        $order_arr = [];
        if ($order_id==0) {
            return $order_arr;
        }
        
        // Get order if not sent
        $order = Order::join("order_delivery_slots","orders.id","=","order_delivery_slots.fk_order_id")
                        ->select("orders.*","order_delivery_slots.delivery_time","order_delivery_slots.later_time","order_delivery_slots.delivery_date","order_delivery_slots.delivery_time","order_delivery_slots.delivery_preference")
                        ->where("orders.id","=",$order_id)->first();
        
        // Set delivery time
        $later_time = $order->later_time;
        if ($order->delivery_time==1) {
            $user = User::find($order->fk_user_id);
            $delivery_in = getDeliveryInTimeRange($user->expected_eta, $order->created_at, $lang);
        } else {
            // Get the sheduled earliest time
            // $later_time = strtok($later_time, '-');
            // $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
            // $later_delivery_time = $delivery_date.' '.$later_time.':00';
            // $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang);
            
            $later_time_arr = explode('-', $later_time);
            $later_time_start = is_array($later_time_arr) && isset($later_time_arr[0]) ? date('h:ia',strtotime($later_time_arr[0])) : "";
            $later_time_end = is_array($later_time_arr) && isset($later_time_arr[1]) ? date('h:ia',strtotime($later_time_arr[1])) : "";
            $delivery_in = $later_time_start.'-'.$later_time_end;
        }

        $delivery_in_text = $lang == 'ar' ? $delivery_in." التسليم الساعة" : "delivery by ".$delivery_in;

        $order_arr = [
            'id' => $order->id,
            'orderId' => $order->orderId ? (int) $order->orderId : 0,
            'sub_total' => $order->sub_total,
            'total_amount' => "$order->total_amount",
            'delivery_charge' => $order->delivery_charge,
            'coupon_discount' => $order->coupon_discount ?? '',
            'item_count' => $order->getOrderProducts->count(),
            'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
            'status' => $order->status,
            'delivery_date' => $order->delivery_date ?? "",
            'delivery_in' => $delivery_in_text,
            'delivery_time' => $order->delivery_time ?? "",
            'later_time' => $order->later_time ?? "",
            'delivery_preference' => $order->delivery_preference ?? "",
            'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
            'bought_by' => $order->bought_by ?? 0,
            'forgot_something_store_id' => env("FORGOT_SOMETHING_STORE_ID"),
            'forgot_something_store_ids' => $this->forgot_something_store_ids(),
            'paythem_store_id' => env('PAYTHEM_STORE_ID')
        ];

        return $order_arr;
    }

    // ============ Get Forgot Something Store IDs ============
    protected function forgot_something_store_ids() {
        $forgot_something_store_ids = env("FORGOT_SOMETHING_STORE_IDS");
        $forgot_something_store_ids_arr = array_map('intval', explode(',',$forgot_something_store_ids));
        return $forgot_something_store_ids_arr;
    }

    // ============ Get paythem voucher ============
    protected function get_paythem_voucher($order_id){
        $order = Order::find($order_id);
        $order_products = OrderProduct::where('fk_order_id',$order->id)
            ->where('deleted','=',0)
            ->where('product_type','=','paythem')
            ->where('is_out_of_stock','=',0)
            ->get();

        if($order_products){

            foreach ($order_products as $key => $order_product) {
                
                // Check paythem product stock
                $endpoint = 'get_ProductAvailability';
                $payload = ['PRODUCT_ID' => $order_product->paythem_product_id];
                $tracking_id = $this->add_paythem_tracking_data_in_order($order->fk_user_id, 0, $endpoint, json_encode($payload), '');
                $product_availability = $this->paythem_call_api($endpoint,$payload);
                $this->update_paythem_tracking_response_in_order($tracking_id,$product_availability);
                if (!$product_availability || $product_availability<=0) {
                    $this->mark_out_of_stock_in_order($order_id,$order_product->id,$order,true);
                    continue;
                }
                
                // Get paythem product voucher code
                $get_voucher = false;
                $endpoint = 'get_Vouchers';
                $payload = ['PRODUCT_ID' => $order_product->paythem_product_id, 'QUANTITY' => $order_product->product_quantity];
                $tracking_id = $this->add_paythem_tracking_data_in_order($order->fk_user_id, 0, $endpoint, json_encode($payload), '');
                $result = $this->paythem_call_api($endpoint,$payload);
                $this->update_paythem_tracking_response_in_order($tracking_id,$result);
                if($result){
                    $insert_arr = [
                        "fk_order_id" => $order_product->fk_order_id,
                        "fk_order_product_id" => $order_product->fk_product_id,
                        "transaction_id" => $result['TRANSACTION_ID'],
                        "transaction_voucher_quantity" => $result['TRANSACTION_VOUCHER_QUANTITY'],
                        "transaction_date" => $result['TRANSACTION_DATE'],
                        "transaction_value"  => $result['TRANSACTION_VALUE'],
                        "financial_id" => $result['FINANCIAL_ID'],
                        "client_reference_id" => $result['CLIENT_REFERENCE_ID'],
                        "client_remaining_balance" => $result['CLIENT_REMAINING_BALANCE'],
                        "consignment_limit" => $result['CONSIGNMENT_LIMIT'],
                        "available_balance" => $result['AVAILABLE_BALANCE'],
                        "transaction_currency" => $result['TRANSACTION_CURRENCY'],
                        "oem_id"=> $result['OEM_ID'],
                        "oem_name"=> $result['OEM_Name'],
                        "oem_brand_id"=> $result['OEM_BRAND_ID'],
                        "oem_brand_name"=> $result['OEM_BRAND_Name'],
                        "oem_brand_brand_product_format_id"=> $result['OEM_BRAND_Brand_Product_Format_ID'],
                        "oem_brand_brand_product_format_desc"=> $result['OEM_BRAND_Brand_Product_Format_Desc'],
                        "oem_brand_brand_product_format_fields"=> $result['OEM_BRAND_Brand_Product_Format_Fields'],
                        "oem_product_id"=> $result['OEM_PRODUCT_ID'],
                        "oem_product_name"=> $result['OEM_PRODUCT_Name'],
                        "oem_product_sellprice"=> $result['OEM_PRODUCT_SellPrice'],
                        "oem_product_redemptioninstructions"=> $result['OEM_PRODUCT_RedemptionInstructions'],
                        "oem_product_vvssku"=> $result['OEM_PRODUCT_VVSSKU'],
                        "oem_product_ean"=> $result['OEM_PRODUCT_EAN'],
                        "vouchers"=> json_encode($result['VOUCHERS']),
                    ];

                    \App\Model\PayThemTransaction::create($insert_arr);

                    $data = [
                        'status' => 3,
                        'order_id' => $order->id,
                        'order_product_id' => $order_product->id,
                        'user_id' => (string) $order->fk_user_id,
                        'orderId' => $order->orderId
                    ];
                    
                    if($result['VOUCHERS']){

                        foreach ($result['VOUCHERS'] as $key => $value) {
                            $this->send_inapp_notification_to_user(
                                $order->fk_user_id,
                                "Special Gift Awaits you",
                                "هدية خاصة في انتظارك",
                                "Congratulations on Your Gift Card Purchase! Your Voucher Code is: ".$value['OEM_VOUCHER_PIN'].". Redeem it now and spread joy to your loved ones. Happy gifting.",
                                "تهانينا على شراء بطاقة الهدايا! رمز قسيمتك هو: ".$value['OEM_VOUCHER_PIN'].". استردها الآن وانشر الفرح لأحبائك. إهداء سعيد.",
                                4,
                                json_encode($data)
                            );

                            $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $order->fk_user_id])->first();
                            if ($user_device) {
                                $type = 4; //for gift card
                                $title = "Special Gift Awaits you";
                                $body = "Congratulations on Your Gift Card Purchase! Your Voucher Code is: ".$value['OEM_VOUCHER_PIN'].". Redeem it now and spread joy to your loved ones. Happy gifting.";

                                $this->send_push_notification_to_user(
                                    $user_device->device_token,
                                    $type,
                                    $title,
                                    $body,
                                    $data
                                );
                            }
                            $get_voucher = true;
                        }
                    }
                }
                // if (!$get_voucher) {
                //     $this->mark_out_of_stock_in_order($order_id,$order_product->id,$order,true);
                //     continue;
                // }
            }
        }
    }

    // Add paythem user tracking data
    function add_paythem_tracking_data_in_order($user_id,$mobile,$key,$request,$response,$tracking_id=0) {
        if ($tracking_id==0) {
            $user_tracking = \App\Model\UserPaythemTracking::create([
                'user_id'=>$user_id,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
            $tracking_id = $user_tracking->id;
        } else {
            \App\Model\UserPaythemTracking::find($tracking_id)->update([
                'user_id'=>$user_id,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
        }
        return $tracking_id;
    }

    // Update paythem user tracking data
    function update_paythem_tracking_userid_and_key_in_order($tracking_id,$key,$user_id) {
        $user_tracking = \App\Model\UserPaythemTracking::find($tracking_id)->update(['key'=>$key,'user_id'=>$user_id]);
        return $user_tracking;
    }

    function update_paythem_tracking_response_in_order($tracking_id,$response) {
        $user_tracking = \App\Model\UserPaythemTracking::find($tracking_id)->update(['response'=>$response]);
        return $user_tracking;
    }

}
