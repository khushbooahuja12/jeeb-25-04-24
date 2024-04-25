<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\User;
use App\Model\UserOtp;
use App\Model\ApiValidation;
use App\Model\TwoStepTag;
use App\Model\UserPreference;
use App\Model\UserNotification;

class SettingController extends CoreApiController
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

        $methods_arr = [
            'notification_switch', 'get_notifications',
            'change_mobile_number', 'verify_mobile_number', 'send_feedback',
            'delivery_slots', 'saved_cards', 'delete_saved_card'
        ];

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

    protected function saved_cards(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);
            $cards = [];
            $user_id = 0;
            $tap_id = '';
            if ($user && $user->tap_id && $user->tap_id!="") {
                $user_id = $user->id;
                $tap_id = $user->tap_id;
                $cards = $this->get_cards($user->tap_id);
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'user_id' => $user_id,
                'tap_id' => $tap_id,
                'cards' => $cards
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

    protected function delete_saved_card(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['card_id']);

            $user = User::find($this->user_id);
            $cards = [];
            $user_id = 0;
            $tap_id = '';
            $card_id = $request->input('card_id');
            if ($user && $user->tap_id && $user->tap_id!="") {
                $user_id = $user->id;
                $tap_id = $user->tap_id;
                $cards = $this->delete_card($card_id, $user->tap_id);
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'user_id' => $user_id,
                'tap_id' => $tap_id,
                'cards' => $cards
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

    protected function notification_switch(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $preference = UserPreference::where(['fk_user_id' => $this->user_id])->first();
            if (!$preference) {
                $upd_arr = [
                    'fk_user_id' => $this->user_id,
                    'is_notification' => 1
                ];
                UserPreference::create($upd_arr);
                $is_notification = 1;
            } else {
                if ($preference->is_notification == 1) {
                    $upd_arr = [
                        'is_notification' => 0
                    ];
                    UserPreference::find($preference->id)->update($upd_arr);
                    $is_notification = 0;
                } elseif ($preference->is_notification == 0) {
                    $upd_arr = [
                        'is_notification' => 1
                    ];
                    UserPreference::find($preference->id)->update($upd_arr);
                    $is_notification = 1;
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم تحديث الحالة" : "Status updated";
            $this->result = ['is_notification' => $is_notification];
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

    protected function get_notifications(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $notifications = UserNotification::where(['fk_user_id' => $this->user_id])->orderBy('id', 'desc')->get();
            if ($notifications->count()) {
                foreach ($notifications as $key => $value) {
                    if ($value->notification_type == 1) {
                        $icon = asset('/assets/notification_icons/1.png');
                    } elseif ($value->notification_type == 2) {
                        $icon = asset('/assets/notification_icons/3.png');
                    } elseif ($value->notification_type == 3) {
                        $icon = asset('/assets/notification_icons/6.png');
                    } elseif ($value->notification_type == 4){
                        $icon = "";
                    }
                    // Get ticket id and order id
                    $order_id = '';
                    $ticket_id = '';
                    if ($value->data) {
                        $data = json_decode($value->data, true);
                        if ($data && $value->notification_type==1) {
                            $order_id =  $data["orderId"];
                        }
                        if ($data && ($value->notification_type==2 || $value->notification_type==3)) {
                            $ticket_id =  $data["ticket_id"];
                        }
                    }
                    // if ($value->id==21) {
                    //     // var_dump($data["orderId"]);
                    //     // die();
                    //     return response()->json([
                    //         'error' => false,
                    //         'status_code' => 200,
                    //         'message' => "",
                    //         'result' => $order_id
                    //     ]);
                    // }
                    $noti_arr[$key] = [
                        'id' => $value->id,
                        'notification_type' => $value->notification_type ?? '',
                        'notification_title' => $value->notification_title_en,
                        'notification_text' => $value->notification_text_en,
                        'data' => $value->data ?? '',
                        'order_id' => (int)$order_id,
                        'ticket_id' => (int)$ticket_id,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'icon' => $icon
                    ];
                }
            } else {
                $noti_arr = [];
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "إشعارات" : "Notifications";
            $this->result = ['notifications' => $noti_arr];
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

    protected function change_mobile_number(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['country_code', 'mobile']);
            $exist = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $request->input('country_code') . $request->input('mobile'))->first();
            if ($exist) {
                $error_message = $lang == 'ar' ? "رقم الهاتف المحمول موجود بالفعل" : "Mobile number already exist";
                throw new Exception($error_message, 105);
            }
            $otp_detail = UserOtp::where(['user_type' => 1, 'user_id' => $this->user_id, 'type' => 2])->first();
            if ($otp_detail) {
                if (($otp_detail->expiry_date < now() || $request->input('resend') == 1)) {
                    $otp = rand(1000, 9999);
                    $msg = "<#> $otp is your secret One time password (OTP) to change your mobile number on Jeeb";
                    $phone = $request->input('country_code') . $request->input('mobile');

                    $sent = $this->mobile_sms_curl($msg, $phone);
                    if ($sent) {
                        UserOtp::find($otp_detail->id)->update([
                            'otp_number' => $otp,
                            'expiry_date' => \Carbon\Carbon::now()->addMinutes(30),
                            'is_used' => 0
                        ]);

                        $this->error = false;
                        $this->status_code = 200;
                        $this->message = $lang == 'ar' ? "تم ارسال الزمر بنجاح" : "Otp sent successfully";
                        $this->result = [
                            'otp' => $otp
                        ];
                    } else {
                        $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                        throw new Exception($error_message, 105);
                    }
                } else {
                    $error_message = $lang == 'ar' ? "طلبت بالفعل تغيير رقم الهاتف المحمول ، حاول بعد 30 دقيقة" : "Already requested for change mobile number, try after 30 minutes";
                    throw new Exception($error_message, 105);
                }
            } else {
                $otp = rand(1000, 9999);
                $msg = "<#> $otp is your secret One time password (OTP) to change your mobile number on Jeeb";
                $phone = $request->input('country_code') . $request->input('mobile');

                $sent = $this->mobile_sms_curl($msg, $phone);
                if ($sent) {
                    UserOtp::create([
                        'user_type' => 1,
                        'user_id' => $this->user_id,
                        'otp_number' => $otp,
                        'expiry_date' => \Carbon\Carbon::now()->addMinutes(30),
                        'type' => 2,
                        'is_used' => 0
                    ]);

                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم ارسال الزمر بنجاح" : "Otp sent successfully";
                    $this->result = [
                        'otp' => $otp
                    ];
                } else {
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
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

    protected function verify_mobile_number(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['country_code', 'mobile', 'otp']);

            $otp_detail = UserOtp::where(['user_type' => 1, 'user_id' => $this->user_id, 'type' => 2])->first();
            if (!$otp_detail) {
                $error_message = $lang == 'ar' ? "لم يتم العثور على طلب لتغيير رقم الهاتف المحمول" : "No request found to change mobile number";
                throw new Exception($error_message, 105);
            }
            if ($otp_detail->is_used == 0) {
                if ($otp_detail->expiry_date < now()) {
                    throw new Exception($lang == 'ar' ? 'تم انتهاء صلاحية الرمز، يرجى المحاولة مرة اخرى' : 'Otp expired, please send again', 105);
                }
                if ($otp_detail->otp_number != $request->input('otp')) {
                    throw new Exception($lang == 'ar' ? 'الرمز غير صحيح' : 'Invalid Otp', 105);
                }

                UserOtp::find($otp_detail->id)->delete();

                User::find($this->user_id)->update([
                    'country_code' => $request->input('country_code'),
                    'mobile' => $request->input('mobile')
                ]);
            } else {
                throw new Exception("Otp already used", 105);
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم تغيير رقم الجوال" : "Mobile number changed successfully";
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

    protected function get_two_step_tags(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $order_by = $lang == 'ar' ? 'name_ar' : 'name_en';

            $tags = TwoStepTag::orderBy('order_no', 'asc')->orderBy($order_by, 'asc')->get();

            $tag_arr = [];
            if ($tags->count()) {
                foreach ($tags as $key => $value) {
                    $tag_arr[$key] = [
                        'id' => $value->id,
                        'name' => $lang == 'ar' ? $value->name_ar : $value->name_en
                    ];
                }
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['tags' => $tag_arr];
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

    protected function storeBarcode(Request $request)
    {
        try {
            $barcode = [];
            $split_barcodes = explode(',',$request->barcode);
            $data = [
                'store' => $request->store,
                'barcode' => $split_barcodes
            ];
            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = $data;
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
