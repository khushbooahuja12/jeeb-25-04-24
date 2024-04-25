<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\OauthAccessToken;
use App\Model\OauthRefreshToken;
use App\Model\ApiValidation;
use App\Model\Product;
use App\Model\Store;
use App\Model\UserCart;
use App\User;
use App\Model\DeletedUser;
use App\Model\UserOtp;
use App\Model\UserDeviceDetail;
use App\Model\UserPreference;
use App\Model\UserWishlist;
use App\Model\AffiliateUser;
use App\Model\ScratchCard;

class AuthController extends CoreApiController
{

    use \App\Http\Traits\TapPayment;
    use \App\Http\Traits\OrderProcessing;

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

        $methods_arr = ['logout', 'cancel_myaccount'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 1)
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

    protected function register(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data(0, 'register', $request, '');

            $this->required_input($request->input(), ['country_code', 'mobile']);

            $user_mobile = $request->input('mobile');
            if ($request->input('country_code')!='974' && $request->input('country_code')!='966') {
                $error_message = $lang == 'ar' ? "غير مقبول" : "Illegal";
                throw new Exception($error_message, 105);    
            } else {
                if ($request->input('country_code')=='974' && strlen((string)$user_mobile)!=8) {
                    $error_message = $lang == 'ar' ? "من فضلك ادخل رقم الهاتف المكون من ٨ ارقام" : "Please enter 8 digits of mobile number";
                    throw new Exception($error_message, 105);   
                } elseif ($request->input('country_code')=='966' && strlen((string)$user_mobile)!=9) {
                    throw new Exception('Please enter 9 digits of mobile number', 105);    
                    $error_message = $lang == 'ar' ? "من فضلك ادخل رقم الهاتف المكون من ٩ ارقام" : "Please enter 9 digits of mobile number";
                    throw new Exception($error_message, 105);   
                }
            }

            $scratch_card_added = false;

            $otp = rand(1000, 9999);
            $test_numbers = explode(',', env("TEST_NUMBERS"));
            $user_mobile = $request->input('mobile');
            if ($user_mobile=='33059088' || in_array($user_mobile, $test_numbers)) {
                $otp = 1234;
            }
            // User 19590 - 66900993 not receiving OTP
            if ($user_mobile=='66900993') {
                $otp = 8692;
            }
            if ($user_mobile=='567770595') {
                $otp = 1588;
            }
            $msg = "Your Jeeb OTP is $otp";
            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $user_mobile;

            $info = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            if ($info) {
                $otp_detail = UserOtp::where(['user_type' => 1, 'user_id' => $info->id, 'type' => 1])->first();

                if ($otp_detail) {
                    // User 19590 - 66900993 not receiving OTP
                    if ($user_mobile!='66900993') {
                        $this->mobile_sms_curl($msg, $phone, $country_code);
                    }
                    // if ($sent) {
                    UserOtp::find($otp_detail->id)->update(['otp_number' => $otp, 'expiry_date' => \Carbon\Carbon::now()->addMinutes(30), 'is_used' => 0]);
                    // } else {
                    //     throw new Exception($lang == 'ar' ? 'يوجد خلل، الرجاء المحاولة مرة اخرى' : 'some error found, please try again', 105);
                    // }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? 'تم ارسال الزمر بنجاح' : 'Otp sent successfully';
                $this->result = [
                    'otp' => (env('APP_ENV')=='production') ? 0000 : $otp,
                ];
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_userid_and_key($tracking_id,'login',$info->id);
                }
            } else {
                $this->mobile_sms_curl($msg, $phone, $country_code);
                // if ($sent) {
                $insert_arr = [
                    'country_code' => $country_code,
                    'mobile' => $request->input('mobile'),
                    'nearest_store' => 14,
                ];
                $user = User::create($insert_arr);
                if ($user) {
                    UserOtp::create([
                        'user_type' => 1,
                        'user_id' => $user->id,
                        'otp_number' => $otp,
                        'expiry_date' => \Carbon\Carbon::now()->addMinutes(30),
                        'type' => 1,
                        'is_used' => 0
                    ]);
                    UserPreference::create([
                        'fk_user_id' => $user->id,
                        'is_notification' => 1,
                        'lang' => 'en'
                    ]);
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_userid_and_key($tracking_id,'registered',$user->id);
                    }

                    // Add new scratch card
                    $is_scratch_card_enabled = $this->is_scratch_card_enabled($user->id, $user);
                    if ($is_scratch_card_enabled) {
                        if (in_array($user_mobile, $test_numbers)) {
                            $scratch_card = ScratchCard::where([
                                'apply_on'=>'register',
                                'deleted'=>0
                            ])->where('scratch_card_type','!=',0)->inRandomOrder()->first();
                        } else {
                            $scratch_card = ScratchCard::where([
                                'status'=>1,
                                'apply_on'=>'register',
                                'deleted'=>0
                            ])->where('scratch_card_type','!=',0)->inRandomOrder()->first();
                        }
                        if ($scratch_card) {
                            $scratch_card_added = $this->add_new_scratch_card($user->id, $scratch_card, 1, 0);
                        }
                    }
                        
                    // Update affilites user table
                    $affiliate_user = AffiliateUser::where(['user_mobile'=>$country_code.$request->input('mobile')])->first();
                    if ($affiliate_user) {
                        $affiliate_user->update(['user_registered'=>1]);
                    }

                    //updating into referrals table                        
                    // $codeExist = \App\Model\UserReferralCode::where(['referral_code' => $request->input('referral_code')])->first();
                    // if (!$codeExist) {
                    //     throw new Exception('Referral code invalid', 105);
                    // } else {
                    //     $sender_id = filter_var($request->input('referral_code'), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                    //     $exist = \App\Model\Referral::where(['sender_id' => $sender_id, 'receiver_id' => $user->id])->first();
                    //     if (!$exist) {
                    //         \App\Model\Referral::create([
                    //             'sender_id' => $sender_id - 100,
                    //             'receiver_id' => $user->id,
                    //             'referral_code' => $request->input('referral_code'),
                    //         ]);
                    //     }
                    // }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم ارسال الزمر بنجاح" : "Otp sent successfully";
                $this->result = [
                    'otp' => (env('APP_ENV')=='production') ? 0000 : $otp,
                    'scratch_card_added' => $scratch_card_added
                ];
                // } else {
                //     throw new Exception($lang == 'ar' ? 'يوجد خلل، الرجاء المحاولة مرة اخرى' : 'some error found, please try again', 105);
                // }
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

    protected function verify_otp(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data(0, 'verify_otp', $request, '');

            $this->required_input($request->input(), ['country_code', 'mobile', 'otp']);

            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            
            $users = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $country_code . $request->input('mobile'))->orderBy('id','asc')->get();
            $user_ids = [];
            if ($users && count($users)) {
                foreach ($users as $key => $user) {
                    $user_ids[] = $user->id;
                    if ($key!=0) {
                        $user = User::find($user->id);
                        if ($user) {
                            $user_arr = $user->toArray();
                            $user_arr['reason'] = "Duplicated profile";
                            DeletedUser::insert($user_arr);
                            $user->delete();
                        }
                    }
                }
                $user = $users[0];
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_userid($tracking_id,$user->id);
                }
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"User doesn't exist");
                }
                throw new Exception($lang == 'ar' ? "لا يوجد مستخدم" : "User doesn't exist", 105);
            }

            $otp_detail = UserOtp::whereIn('user_id', $user_ids)
                    ->whereDate('expiry_date','>=','"'.date('Y-m-d H:i:s').'"')
                    ->where(['user_type' => 1, 'type' => 1, 'is_used' => 0, 'otp_number' => $request->input('otp')])
                    ->first();
            // $otp_detail = UserOtp::where(['user_type' => 1, 'user_id' => $user->id, 'type' => 1])->first();
            
            if (!$otp_detail) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Invalid Otp");
                }
                throw new Exception($lang == 'ar' ? 'الرمز غير صحيح' : 'Invalid Otp', 105);
            }
            if ($otp_detail->is_used == 0) {

                UserOtp::find($otp_detail->id)->update(['user_id' => $user->id, 'is_used' => 1]);

                // Create customer reference for Tap Payment
                $tap_id = $user->tap_id;
                if ($tap_id=="") {
                    $tap_id = $this->create_customer($user);
                }
                User::find($user->id)->update([
                    'status' => 1,
                    'tap_id' => $tap_id
                ]);

                // Update affilites user table
                $affiliate_user = AffiliateUser::where(['user_mobile'=>$country_code.$request->input('mobile')])->first();
                if ($affiliate_user) {
                    $affiliate_user->update(['otp_verified'=>1]);
                }

                ## Start creating Access Token & Refresh Token
                $prev_auth = DB::table('oauth_access_tokens')
                    ->where(['user_id' => $user->id, 'user_type' => 1])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($prev_auth) {
                    OauthAccessToken::where(['user_id' => $user->id, 'user_type' => 1])->update([
                        'id' => \Str::random(80),
                        'client_id' => 1,
                        'name' => 'jeebToken',
                        'scopes' => '',
                        'revoked' => 0,
                        'expires_at' => \Carbon\Carbon::now()->addDays(1)
                    ]);

                    $nearestStore = Store::select('id')
                        ->selectRaw("(
                                    6371 * ACOS(
                                        COS(RADIANS('" . $prev_auth->latitude . "')) * COS(
                                            RADIANS(latitude)
                                        ) * COS(
                                            RADIANS(longitude) - RADIANS('" . $prev_auth->longitude . "')
                                        ) + SIN(RADIANS('" . $prev_auth->latitude . "')) * SIN(
                                            RADIANS(latitude)
                                        )
                                    )
                                ) AS distance")
                        ->where('deleted', '=', 0)
                        ->where('status', '=', 1)
                        ->orderBy('distance', 'asc')
                        ->first();
                    $product_price_key = 'store' . $nearestStore->id . '_price';
                } else {
                    OauthAccessToken::create([
                        'id' => \Str::random(80),
                        'user_id' => $user->id,
                        'user_type' => 1,
                        'client_id' => 1,
                        'name' => 'jeebToken',
                        'scopes' => '',
                        'revoked' => 0,
                        'expires_at' => \Carbon\Carbon::now()->addDays(1)
                    ]);
                    $product_price_key = 'product_price';
                }

                $oauth = DB::table('oauth_access_tokens')->where(['user_id' => $user->id, 'user_type' => 1])->orderBy('created_at', 'desc')->first();
                $oauth_data = [
                    'access_token' => $oauth->id,
                    'created_at' => $oauth->created_at,
                    'expires_in' => strtotime($oauth->expires_at) - strtotime($oauth->created_at),
                ];
                ## End creating access token and refresh token

                /* add/update the device detail */
                $device_detail_arr = [
                    'fk_user_id' => $user->id,
                    'device_type' => $request->input('device_type') ?? 0, //1:iOS,2:android
                    'app_version' => $request->input('app_version') ?? 'not_detected',
                    'device_token' => $request->input('device_token') ?? '123456789',
                ];
                if (UserDeviceDetail::where(['fk_user_id' => $user->id])->first()) {
                    UserDeviceDetail::where(['fk_user_id' => $user->id])->update($device_detail_arr);
                } else {
                    UserDeviceDetail::create($device_detail_arr);
                }

                /* getting profile data */
                $profile = User::find($user->id);

                $userpref = UserPreference::where(['fk_user_id' => $user->id])->first();
                
                // Check user level features
                $features = $this->get_my_features($user->id,$user);

                $profile_arr = [
                    'id' => $user->id,
                    'name' => $profile->name ?? '',
                    'email' => $profile->email ?? '',
                    'country_code' => $profile->country_code,
                    'mobile' => $profile->mobile,
                    'user_image' => !empty($profile->getUserImage) ? asset('images/user_images') . '/' . $profile->getUserImage->file_name : '',
                    'status' => $profile->status,
                    'is_notification' => $userpref ? $userpref->is_notification : '',
                    'tap_id' => $tap_id
                ];

                //setting user referral code
                $exist = \App\Model\UserReferralCode::where(['fk_user_id' => $user->id])->first();
                if (!$exist) {
                    \App\Model\UserReferralCode::create([
                        'fk_user_id' => $user->id,
                        'referral_code' => 'JRS' . (100 + $user->id)
                    ]);
                }

                // adding guest user's favorite product into its actual user id
                if ($request->input('fav_product_ids') != '') {
                    $productJson = explode(',', $request->input('fav_product_ids'));
                    foreach ($productJson as $key => $value) {
                        $exist = UserWishlist::where(['fk_user_id' => $user->id, 'fk_product_id' => $value])->first();
                        if (!$exist) {
                            UserWishlist::create(['fk_user_id' => $user->id, 'fk_product_id' => $value]);
                        }
                    }
                }
                // updating cart data
                // if (!$request->input('cart_json') || $request->input('cart_json') == '' || empty($request->input('cart_json')) || $request->input('cart_json')==false) {
                //     // No cart data available
                //     // So no need to process, just skip
                // } else {
                //     UserCart::where(['fk_user_id' => $user->id])->delete();
                //     // $content_arr = json_decode($request->input('cart_json'));
                    
                //     $cart_json = $request->input('cart_json');
                //     if (!is_array($cart_json)) {
                //         $cart_json = stripslashes($cart_json);
                //         $cart_json = str_replace ( ':', '=>', $cart_json );
                //         $cart_json = str_replace ( '\"', '"', $cart_json );
                //     }
                //     // $cart_json_arr = var_export($cart_json, true);
                //     $content_arr = [];
                //     eval("\$content_arr = $cart_json;");
                    
                //     if (!empty($content_arr)) {
                //         foreach ($content_arr as $key => $value) {
                            
                //             if ($value && isset($value['product_id'])) {
                //                 $product = Product::find($value['product_id']);
        
                //                 $insert_arr = [
                //                     'fk_user_id' => $user->id,
                //                     'fk_product_id' => $value['product_id'],
                //                     'quantity' => $value['product_quantity'],
                //                     'total_price' => number_format(($product->$product_price_key * $value['product_quantity']), 2),
                //                     'total_discount' => '',
                //                     'weight' => $product->unit ?? '',
                //                     'unit' => $product->unit ?? ''
                //                 ];
                //                 UserCart::create($insert_arr);
                //             }

                //         }
                //     }
                // }



                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم التحقق من OTP بنجاح" : "Otp verified successfully";
                $this->result = [
                    'oauth_data' => $oauth_data, 
                    'profile' => $profile_arr, 
                    'features' => $features
                ];
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'An error has been discovered');
                }
                throw new Exception($lang == 'ar' ? "تم التحقق بنجاح" : "Invalid Otp, please resend", 105);
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

    protected function resend_otp(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data(0, 'resend', $request, '');

            $this->required_input($request->input(), ['country_code', 'mobile']);

            if ($request->input('country_code')!='974' && $request->input('country_code')!='966') {
                throw new Exception('Illegal', 105);    
            }

            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $request->input('mobile');
            
            $info = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            if ($info) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_userid($tracking_id,$info->id);
                }
                $otp_detail = UserOtp::where(['user_type' => 1, 'user_id' => $info->id, 'type' => 1])->first();
                if ($otp_detail) {
                    $otp = $otp_detail->otp_number;
                    //                    $msg = "<#> $otp is your secret One time password (OTP) to register to your Jeeb Account";
                    $msg = "Your Jeeb OTP is $otp";

                    $sent = $this->mobile_sms_curl($msg, $phone, $country_code);
                    if ($sent) {
                        UserOtp::find($otp_detail->id)->update(['expiry_date' => \Carbon\Carbon::now()->addMinutes(30), 'is_used' => 0]);

                        $this->error = false;
                        $this->status_code = 200;
                        $this->message = $lang == 'ar' ? "تم ارسال الزمر بنجاح" : "Otp sent successfully";
                        $this->result = ['otp' => $otp];
                    } else {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'An error has been discovered');
                        }
                        $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                        throw new Exception($error_message, 105);
                    }
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'An error has been discovered');
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'An error has been discovered');
                }
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
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function logout(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $access_token = $request->header('Authorization');

            ## removing the authentication detail
            OauthRefreshToken::where(['access_token_id' => $access_token])->delete();

            $auth_del = DB::table('oauth_access_tokens')
                ->where('user_type', '=', 1)
                ->where('id', '=', "$access_token")
                ->delete();

            if ($auth_del) {
                ## deleting the device information when doing logout
                UserDeviceDetail::where(['fk_user_id' => $this->user_id])->delete();

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم تسجيل الخروج" : "Logged out successfully";
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

    protected function guest_login(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['device_type', 'device_token']);

            $prev_auth = DB::table('oauth_access_tokens')
                ->where([
                    'device_type' => $request->input('device_type'),
                    'device_token' => $request->input('device_token')
                ])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($prev_auth) {
                OauthAccessToken::where([
                    'device_type' => $request->input('device_type'),
                    'device_token' => $request->input('device_token')
                ])
                    ->update([
                        'id' => \Str::random(80),
                        'expires_at' => \Carbon\Carbon::now()->addDays(1)
                    ]);
            } else {
                OauthAccessToken::create([
                    'id' => \Str::random(80),
                    'user_type' => 1,
                    'client_id' => 1,
                    'name' => 'jeebToken',
                    'expires_at' => \Carbon\Carbon::now()->addDays(1),
                    'device_type' => $request->input('device_type'),
                    'device_token' => $request->input('device_token')
                ]);
            }

            $oauth = DB::table('oauth_access_tokens')
                ->where([
                    'device_type' => $request->input('device_type'),
                    'device_token' => $request->input('device_token')
                ])
                ->orderBy('created_at', 'desc')
                ->first();
            $oauth_data = [
                'access_token' => $oauth->id,
                'created_at' => $oauth->created_at,
                'expires_in' => strtotime($oauth->expires_at) - strtotime($oauth->created_at),
            ];

            /* add/update the device detail */
            $device_detail_arr = [
                'device_type' => $request->input('device_type') ?? 1, //1:iOS,2:android
                'device_token' => $request->input('device_token') ?? '123456789',
            ];
            $exist = \App\Model\GuestDeviceDetail::where([
                'device_type' => $request->input('device_type'),
                'device_token' => $request->input('device_token')
            ])
                ->first();
            if ($exist) {
                \App\Model\GuestDeviceDetail::find($exist->id)->update($device_detail_arr);
            } else {
                \App\Model\GuestDeviceDetail::create($device_detail_arr);
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم تسجيل الدخول كضيف بنجاح" : "Logged in as guest successfully!";
            $this->result = ['oauth_data' => $oauth_data];
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
    
    protected function cancel_myaccount(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $access_token = $request->header('Authorization');

            ## removing the authentication detail
            OauthRefreshToken::where(['access_token_id' => $access_token])->delete();

            $auth_del = DB::table('oauth_access_tokens')
                ->where('user_type', '=', 1)
                ->where('id', '=', "$access_token")
                ->delete();

            if ($auth_del) {
                ## deleting the device information when doing logout
                UserDeviceDetail::where(['fk_user_id' => $this->user_id])->delete();

                $user = User::find($this->user_id);
                if ($user) {
                    $user_arr = $user->toArray();
                    $user_arr['reason'] = "Deleted by user";
                    DeletedUser::insert($user_arr);
                    $user->delete();
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم حذف حساب المستخدم بنجاح" : "User account is deleted successfully";
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

}
