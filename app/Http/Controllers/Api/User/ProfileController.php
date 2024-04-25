<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\Rule;

class ProfileController extends CoreApiController
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

        $methods_arr = ['get_profile', 'update_profile'];

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
    }

    protected function get_profile(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $user = User::find($this->user_id);
            if ($user) {
                $profile_arr = [
                    'id' => $user->id,
                    'name' => $user->name ?? '',
                    'email' => $user->email ?? '',
                    'country_code' => $user->country_code,
                    'mobile' => $user->mobile,
                    'user_image' => !empty($user->getUserImage) ? asset('images/user_images') . '/' . $user->getUserImage->file_name : '',
                    'status' => $user->status
                ];                           
            } else {
                $profile_arr = [
                    'id' => '',
                    'name' => '',
                    'email' => '',
                    'country_code' => '',
                    'mobile' => '',
                    'user_image' => '',
                    'status' => ''
                ];
            }
             //get the social media links from db
                $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
                $socialAccounts = AdminSetting::whereIn('key', $names)->get();
                $social_links = [];
                if ($socialAccounts->count()) {
                    foreach ($socialAccounts as $key => $value) {
                        $social_links[$value->key] = $value->value ?? "";
                    }
                }    

            $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'profile' => $profile_arr,
                    'social_links' => $social_links,
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

    protected function update_profile(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['name', 'email']);
            $user = User::find($this->user_id);

            $validator = Validator::make($request->all(), [
                // 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user_id, "id")]
                'email' => ['required', 'email']
            ]);
            if ($validator->fails()) {
                // throw new Exception($validator->messages()->first(), 105);
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            $info = User::where('email', '=', $request->input('email'))
                    ->where('id', '!=', $this->user_id)
                    ->first();
            if ($info) {
                $error_message = $lang == 'ar' ? "البريد الإلكتروني تم أخذه" : "The email has already been taken";
                throw new Exception($error_message, 105);
            }

            $update_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
            ];
            if ($request->hasFile('user_image')) {
                $path = "/images/user_images/";
                $check = $this->uploadFile($request, 'user_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    if ($user->user_image != '') {
                        $destinationPath = public_path("images/user_images/");
                        if (!empty($user->getUserImage) && file_exists($destinationPath . $user->getUserImage->file_name)) {
                            unlink($destinationPath . $user->getUserImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $user->user_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }
                    $update_arr['user_image'] = $returnArr->id;
                endif;
            }

            $update = User::find($this->user_id)->update($update_arr);
            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "تم تحديث الملف الشخصي بنجاح" : "Profile updated successfully";
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
