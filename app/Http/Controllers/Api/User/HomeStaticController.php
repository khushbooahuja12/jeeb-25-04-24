<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\AdminSetting;
use App\Model\HomeStatic;
use App\Model\User;

class HomeStaticController extends CoreApiController
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

        $methods_arr = [
            'upload_home_static'
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
            $lang = $request->header('lang');
            \App::setlocale($lang);
        }
        $admin_setting = AdminSetting::where(['key' => 'range'])->first();
        if ($admin_setting) {
            $this->default_range = $admin_setting->value;
        } else {
            $this->default_range = 1000;
        }

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost()=='localhost' ? 'dev_products' : 'products';
    }

    protected function upload_home_static(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->getHttpHost() != 'staging.jeeb.tech' && env('APP_ENV') != 'production') {
                $error_message = $lang == 'ar' ? "هذا غير مسموح به" : "This is not allowed";
                throw new Exception($error_message, 105);
            }

            if ($request->hasFile('home_static_file')) {
                
                // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
                $json_url_base = "home_static_json/"; 

                $file_name = time().'_home_static_1.json';
                $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

                $insert_arr = [
                    'lang' => $lang,
                    'file_name' => $json_url_base.$file_name,
                    'IP' => $request->ip(),
                ];
                $insert = HomeStatic::create($insert_arr);
                
                if ($insert) {
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang =='ar' ? "تم تحميل ملف Home_static بنجاح" : "Home_static file is uploaded successfully";
                } else {
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }

            } else {
                $error_message = $lang == 'ar' ? "مطلوب ملف Home_static" : "Home_static file is required";
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
