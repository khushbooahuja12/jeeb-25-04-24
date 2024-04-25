<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\ApiValidation;
use App\Model\Order;

class UserController extends CoreApiController
{

    use \App\Http\Traits\TapPayment;

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['set_lang_preference', 'add_wallet_balance', 'add_wallet_balance_tap', 'get_wallet', 'set_location', 'send_feedback', 'get_referral_info'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->user_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }
        $this->validation_model = new ApiValidation();
    }

    protected function set_lang_preference(Request $request)
    {
        try {
            $lang = $request->header('lang') ? $request->header('lang') : 'en';
            $tracking_id = add_tracking_data(0, 'set_lang_preference', $request, '');
            
            $lang_update = \App\Model\User::find($this->user_id)->update(['lang_preference'=>$lang]);
            if ($lang_update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "تم تغيير اللغة المفضلة بنجاح!" : "Preffered language is changed successfully!";
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'An error has been discovered');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function add_wallet_balance_tap(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['amount', 'charge_id']);
            
            $tracking_id = add_tracking_data($this->user_id, 'add_wallet_balance_tap', $request, '');
            
            // Get charge
            $charge = $this->get_charge($request->input('charge_id'));

            // Add wallet payment
            $insert_arr = [
                'fk_user_id' => $this->user_id,
                'amount' => $request->input('amount'),
                'status' => 'rejected',
                'paymentResponse' => $request->input('charge_id'),
                'transaction_type' => 'credit'
            ];

            // Payment succesful
            if ($charge && isset($charge['status']) && $charge['status']==true) {
                $insert_arr['status'] = 'success';
                
            }

            $paid = \App\Model\UserWalletPayment::create($insert_arr);
            if ($paid) {
                if ($paid->status != "success") {
                    $message = $lang =='ar' ? "الدفع في انتظار ، يرجى الانتظار 15 دقيقة للتأكيد" : "Payment awaiting, please allow 15 mins to confirm";
                    $error_code = 201;
                } else {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
                    if ($user_wallet) {
                        $current_points = $user_wallet->current_points + $request->input('amount');
                        $total_points = $user_wallet->total_points + $request->input('amount');
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'current_points' => $current_points,
                            'total_points' => $total_points,
                        ]);
                    } else {
                        \App\Model\UserWallet::create([
                            'fk_user_id' => $this->user_id,
                            'current_points' => $request->input('amount'),
                            'total_points' => $request->input('amount'),
                        ]);
                    }

                    $message = $lang =='ar' ? "تمت إضافة المبلغ بنجاح!" : "Amount added successfully!";
                    $error_code = 200;
                }

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function add_wallet_balance(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['amount', 'paymentResponse']);

            $paymentResponseArr = json_decode($request->input('paymentResponse'));

            if (empty($request->input('paymentResponse'))) {
                $status = 'rejected';
            } else {
                $paymentResponseArr = json_decode($request->input('paymentResponse'));
                $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);
            }

            $insert_arr = [
                'fk_user_id' => $this->user_id,
                'amount' => $request->input('amount'),
                'status' => $status,
                'paymentResponse' => $request->input('paymentResponse'),
                'transaction_type' => 'credit'
            ];

            $paid = \App\Model\UserWalletPayment::create($insert_arr);
            if ($paid) {
                if ($paid->status != "success") {
                    $message = $lang =='ar' ? "الدفع في انتظار ، يرجى الانتظار 15 دقيقة للتأكيد" : "Payment awaiting, please allow 15 mins to confirm";
                    $error_code = 201;
                } else {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
                    if ($user_wallet) {
                        $current_points = $user_wallet->current_points + $request->input('amount');
                        $total_points = $user_wallet->total_points + $request->input('amount');
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'current_points' => $current_points,
                            'total_points' => $total_points,
                        ]);
                    } else {
                        \App\Model\UserWallet::create([
                            'fk_user_id' => $this->user_id,
                            'current_points' => $request->input('amount'),
                            'total_points' => $request->input('amount'),
                        ]);
                    }

                    $message = $lang =='ar' ? "تمت إضافة المبلغ بنجاح!" : "Amount added successfully!";
                    $error_code = 200;
                }

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_wallet(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $user = \App\Model\User::find($this->user_id);
            $tap_id = '';
            if ($user && $user->tap_id && $user->tap_id!="") {
                $user_id = $user->id;
                $tap_id = $user->tap_id;
            }
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            if ($user_wallet) {
                $wallet_balance = $user_wallet->total_points;
            } else {
                $wallet_balance = "0";
            }

            $rand_ref_id = rand(1111, 9999);

            $recent_orders = \App\Model\UserWalletPayment::where('fk_user_id', '=', $this->user_id)
                ->where('status', '!=', 'rejected')
                ->where('amount','!=',0)
                ->orderBy('id', 'desc')
                ->get();

            // pp($recent_orders);
            $order_arr = [];
            if ($recent_orders) {
                
                foreach ($recent_orders as $key => $value) {
                    $order = Order::where(['orderId' => $value->paymentResponse])->first();
                    if ($order && $order->parent_id != 0) {
                        $parent_order = Order::find($order->parent_id);
                        if ($parent_order) {
                            $paymentResponse = $parent_order->orderId;
                        } else {
                            $paymentResponse = $value->paymentResponse;
                        }
                    } else {
                        $paymentResponse = $value->paymentResponse;
                    }

                    $order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->wallet_type == 2 ? $paymentResponse : "",
                        'ref_id' => $value->ref_id ? (string) $value->ref_id : "",
                        'wallet_balance' => $value->amount,
                        'created_at' => $value->created_at,
                        'status' => $value->status ?? ""
                    ];

                    if ($value->wallet_type == 1) {
                        if ($value->status == 'pending') {
                            $desc = $lang == 'ar' ? "يعالج" : "Processing...";
                        } elseif ($value->status == 'failed') {
                            $desc = $lang == 'ar' ? "فشلت العملية" : "Wallet Top-up failed";
                        } elseif ($value->status == 'success') {
                            $desc = $lang == 'ar' ? "اضافة رصيد" : "Wallet Top-up";
                        }
                    } elseif ($value->wallet_type == 2) {
                        $desc = $lang == 'ar' ? ($value->wallet_type == 2 ? $paymentResponse : "") . "أنفق على " : "Spent on Order ID:" . ($value->wallet_type == 2 ? $paymentResponse : "");
                    } elseif ($value->wallet_type == 3) {
                        $desc = $lang == 'ar' ? ($value->wallet_type == 3 ? $paymentResponse : "") . "مرتجع من " : "Refunded from Order ID:" . ($value->wallet_type == 3 ? $paymentResponse : "");
                    } elseif ($value->wallet_type == 5) {
                        $desc = $lang == 'ar' ? "تم استردادها من بطاقة الخدش" : "Redeemed from Scratch Card";
                    } elseif ($value->wallet_type == 6) {
                        $desc = $lang == 'ar' ? "تم الاضافة من جيب" : "Added by Jeeb";
                    } else {
                        $desc = "N/A";
                    }

                    $order_arr[$key]['description'] = $desc;
                    $order_arr[$key]['transaction_type'] = $value->transaction_type ?? 'error';
                }
            }

            $returndData = getSadadAccessToken();
            $accessInfo = json_decode($returndData);

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'wallet_balance' => $wallet_balance,
                'tap_id' => $tap_id,
                'ref_id' => (string) $rand_ref_id,
                'recent_orders' => $order_arr,
                'sadadAccessToken' => $accessInfo ? $accessInfo->accessToken : uniqid()
            ];
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result  ' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function set_location(Request $request)
    {
        try {   
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['location', 'latitude', 'longitude']);

            $insert_arr = [
                'fk_user_id' => $this->user_id,
                'location' => $request->input('location'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude')
            ];

            $add = UserLocation::create($insert_arr);
            if ($add) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "تم تعيين الموقع بنجاح" : "Location set successfully";
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function send_feedback(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['experience', 'description']);

            $insert_arr = [
                'fk_user_id' => $this->user_id,
                'experience' => $request->input('experience'),
                'description' => $request->input('description')
            ];
            $create = \App\Model\UserFeedback::create($insert_arr);
            if ($create) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم الارسال بنجاح" : "Feedback sent successfully";
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_referral_info(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $ref = \App\Model\UserReferralCode::where(['fk_user_id' => $this->user_id])->first();
            $referral_count = \App\Model\Referral::where(['sender_id' => $this->user_id, 'is_used' => 1])->count();

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'referral_count' => $referral_count,
                'referral_code' => $ref ? $ref->referral_code : ''
            ];
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }
}
