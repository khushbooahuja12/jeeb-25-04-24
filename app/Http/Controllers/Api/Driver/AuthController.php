<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Model\OauthAccessToken;
use App\Model\OauthRefreshToken;
use App\Model\ApiValidation;
use App\Model\Driver;
use App\Model\UserOtp;
use App\Model\DriverDeviceDetail;
use App\Model\DriverLocation;

class AuthController extends CoreApiController
{

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

        $methods_arr = ['logout'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 2)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->driver_id = $auth->user_id;
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
            $this->required_input($request->input(), ['country_code', 'mobile']);

            $otp = rand(1000, 9999);
            $msg = "<#> $otp is your secret One time password (OTP) to login to your Jeeb Account";
            $phone = $request->input('country_code') . $request->input('mobile');

            $info = Driver::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->where('deleted','=',0)->first();
            if ($info) {
                $otp_detail = UserOtp::where(['user_type' => 2, 'user_id' => $info->id, 'type' => 1])->first();

                if ($otp_detail) {
                    $sent = $this->mobile_sms_curl($msg, $phone);
                    if ($sent) {
                        UserOtp::find($otp_detail->id)->update(['otp_number' => $otp, 'expiry_date' => \Carbon\Carbon::now()->addMinutes(30), 'is_used' => 0]);
                    } else {
                        throw new Exception('Oops! Something went wrong. Please try again.', 105);
                    }
                } else {
                    $sent = $this->mobile_sms_curl($msg, $phone);
                    if ($sent) {
                        UserOtp::create([
                            'user_type' => 2,
                            'user_id' => $info->id,
                            'otp_number' => $otp,
                            'expiry_date' => \Carbon\Carbon::now()->addMinutes(30),
                            'type' => 1,
                            'is_used' => 0
                        ]);
                    }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = 'Otp sent successfully';
                $this->result = [
                    'otp' => $otp
                ];
            } else {
                throw new Exception("You're not registered in Jeeb.", 105);
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

    protected function verify_otp(Request $request)
    {
        try {
            $this->required_input($request->input(), ['country_code', 'mobile', 'otp']);

            $driver = Driver::where(DB::raw('CONCAT(country_code,mobile)'), '=', $request->input('country_code') . $request->input('mobile'))
            ->where('deleted','=',0)
            ->first();
            if (!$driver) {
                throw new Exception("Oops! Something went wrong. Please try again.", 105);
            }
            if ($driver->status==0) {
                throw new Exception("You have been blocked by admin. Please contact admin.", 105);
            }

            $otp_detail = UserOtp::where(['user_type' => 2, 'user_id' => $driver->id, 'type' => 1])->first();
            if (!$otp_detail) {
                throw new Exception("driver doesn't exist", 105);
            }
            if ($otp_detail->is_used == 0) {
                if ($otp_detail->expiry_date < date('Y-m-d')) {
                    throw new Exception('Otp expired, please send again', 105);
                }
                if ($otp_detail->otp_number != $request->input('otp')) {
                    throw new Exception('Invalid Otp', 105);
                }

                UserOtp::find($otp_detail->id)->update(['is_used' => 1]);

                ## Start creating Access Token & Refresh Token
                // if ($driver->status == 1) {
                $prev_auth = DB::table('oauth_access_tokens')->where(['user_id' => $driver->id, 'user_type' => 2])->orderBy('created_at', 'desc')->first();

                if ($prev_auth) {
                    OauthAccessToken::where(['user_id' => $driver->id, 'user_type' => 2])->update([
                        'id' => \Str::random(80),
                        'client_id' => 1,
                        'name' => 'jeebToken',
                        'scopes' => '',
                        'revoked' => 0,
                        'expires_at' => \Carbon\Carbon::now()->addDays(1)
                    ]);
                } else {
                    OauthAccessToken::create([
                        'id' => \Str::random(80),
                        'user_id' => $driver->id,
                        'user_type' => 2,
                        'client_id' => 1,
                        'name' => 'jeebToken',
                        'scopes' => '',
                        'revoked' => 0,
                        'expires_at' => \Carbon\Carbon::now()->addDays(1)
                    ]);
                }

                $oauth = DB::table('oauth_access_tokens')->where(['user_id' => $driver->id, 'user_type' => 2])->orderBy('created_at', 'desc')->first();
                $oauth_data = [
                    'access_token' => $oauth->id,
                    'created_at' => $oauth->created_at,
                    'expires_in' => strtotime($oauth->expires_at) - strtotime($oauth->created_at),
                ];
                // }
                ## End creating access token and refresh token

                /* add/update the device detail */
                $device_detail_arr = [
                    'fk_driver_id' => $driver->id,
                    'device_type' => $request->input('device_type') ?? 1, //1:iOS,2:android
                    'device_token' => $request->input('device_token') ?? '123456789',
                ];
                if (DriverDeviceDetail::where(['fk_driver_id' => $driver->id])->first()) {
                    DriverDeviceDetail::where(['fk_driver_id' => $driver->id])->update($device_detail_arr);
                } else {
                    DriverDeviceDetail::create($device_detail_arr);
                }

                /* getting profile data */
                $profile = Driver::find($driver->id);
                $profile_arr = [
                    'id' => $profile->id,
                    'name' => $profile->name ?? '',
                    'email' => $profile->email ?? '',
                    'country_code' => $profile->country_code,
                    'mobile' => $profile->mobile,
                    'driving_licence_no' => $profile->driving_licence_no,
                    'driver_image' => !empty($profile->getDriverImage) ? asset('images/driver_images') . '/' . $profile->getDriverImage->file_name : '',
                    'driving_licence' => !empty($profile->getDrivingLicence) ? asset('images/driver_images') . '/' . $profile->getDrivingLicence->file_name : '',
                    'national_id' => !empty($profile->getNationalId) ? asset('images/driver_images') . '/' . $profile->getNationalId->file_name : '',
                    'status' => $profile->status
                ];

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Otp verified successfully";
                $this->result = [
                    'oauth_data' => $oauth_data ?? (object) [],
                    'profile' => $profile_arr
                ];
            } else {
                throw new Exception("Otp already verified", 105);
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

    protected function resend_otp(Request $request)
    {
        try {
            $this->required_input($request->input(), ['country_code', 'mobile']);

            $phone = $request->input('country_code') . $request->input('mobile');
            $info = Driver::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            if ($info) {
                $otp_detail = UserOtp::where(['user_type' => 2, 'user_id' => $info->id, 'type' => 1])->first();
                if ($otp_detail) {
                    $otp = $otp_detail->otp_number;
                    $msg = "<#> $otp is your secret One time password (OTP) to register to your Jeeb Account";

                    $sent = $this->mobile_sms_curl($msg, $phone);
                    if ($sent) {
                        UserOtp::find($otp_detail->id)->update(['expiry_date' => \Carbon\Carbon::now()->addMinutes(30), 'is_used' => 0]);

                        $this->error = false;
                        $this->status_code = 200;
                        $this->message = "Otp sent successfully";
                        $this->result = ['otp' => $otp];
                    } else {
                        throw new Exception("Oops! Something went wrong. Please try again.", 105);
                    }
                } else {
                    throw new Exception("Oops! Something went wrong. Please try again.", 105);
                }
            } else {
                throw new Exception("Oops! Something went wrong. Please try again.", 105);
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

    protected function logout(Request $request)
    {
        try {
            $access_token = $request->header('Authorization');

            ## removing the authentication detail
            OauthRefreshToken::where(['access_token_id' => $access_token])->delete();

            $auth_del = DB::table('oauth_access_tokens')
                ->where('user_type', '=', 2)
                ->where('id', '=', "$access_token")
                ->delete();

            if ($auth_del) {
                ## deleting the device information when doing logout
                DriverDeviceDetail::where(['fk_driver_id' => $this->driver_id])->delete();

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Logged out successfully";
            } else {
                throw new Exception("Oops! Something went wrong. Please try again.", 105);
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
