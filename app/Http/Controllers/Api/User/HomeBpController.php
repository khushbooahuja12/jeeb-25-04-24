<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Brand;
use App\Model\Category;
use App\Model\Product;
use App\Model\UserCart;
use App\Model\AdminSetting;
use App\Model\DeliveryArea;
use App\Model\Homepage;
use App\Model\Offer;
use App\Model\Order;
use App\Model\User;
use App\Model\OauthAccessToken;
use App\Model\Store;
use App\Model\InstantStoreGroup;
use App\Model\InstantStoreGroupStore;
use App\Model\UserTracking;
use App\Model\DeliverySlotSetting;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\UserSavedCart;
use App\Model\UserSavedCartProduct;
use App\Model\UserBuyItForMeCart;
use App\Model\UserBuyItForMeRequest;
use App\Model\Coupon;
use App\Model\CouponUses;
use App\Model\ScratchCardUser;
use App\Model\UserFeedback;
use App\Model\OrderReview;

class HomeBpController extends CoreApiController
{

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

        $methods_arr = [
            'my_features', 'add_to_cart', 'remove_from_cart', 'my_cart', 'save_cart', 'update_saved_cart', 'remove_saved_cart', 'view_saved_cart', 'my_saved_cart', 'add_favorite', 'home_static', 'home_personalized', 'home_personalized_instant',
            'ivp_switching_personalized', 'check_delivery_area', 'get_cart_count', 'update_cart', 'remove_outofstock_products_from_cart', 'buy_it_for_me', 'buy_it_for_me_2', 'buy_it_for_me_cart', 'read_buy_it_for_me_request_status', 'buy_it_for_me_received_requests', 'buy_it_for_me_sent_requests'
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

        $admin_setting = AdminSetting::where(['key' => 'range'])->first();
        if ($admin_setting) {
            $this->default_range = $admin_setting->value;
        } else {
            $this->default_range = 1000;
        }

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function my_features(Request $request)
    {
        try {

            // Check authorizations
            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                } else {
                    $user_id = '';
                }
            } else {
                $user_id = '';
            }

            if ($user_id != '') {
                // Check user level features
                $user = User::find($user_id);
                $features = $this->get_my_features($user_id,$user);
            }  else {
                // Assign guest users features
                $features = [
                    'ivp'=>env("ivp_guest", false),
                    'scratch_card'=>env("scratch_card_guest", false)
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = $features;
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

    protected function home_static_plus(Request $request) {
        try {
            $lang = $request->header('lang');
            $store_key = $request->query('store');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $file_url = false;
            $home_static_type = 'plus';

            $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
            $home_static = $home_static->where('home_static_type','=',$home_static_type);
            if ($store_key) {
                $home_static = $home_static->where('store_key','=',$store_key);
            } else {
                $home_static = $home_static->where(function($query) {
                    return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                });
            }
            $home_static = $home_static->where('home_static_data_feeded','>',0)->orderBy('id', 'desc')->first();

            if (!$home_static) {
                $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
                $home_static = $home_static->where('home_static_type','=',$home_static_type);
                if ($store_key) {
                    $home_static = $home_static->where('store_key','=',$store_key);
                } else {
                    $home_static = $home_static->whereNull('store_key');
                }
                $home_static = $home_static->orderBy('id', 'desc')->first();
            }

            if ($home_static && $home_static->file_name && $home_static->home_static_data_feeded > 0) {
                $split_file_name = explode('/',$home_static->file_name);
                $file_name = explode('.',$split_file_name[1]);
                $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
            } elseif($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            }

            if ($file_url && file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_mall(Request $request) {
        try {
            $lang = $request->header('lang');
            $store_key = $request->query('store');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $file_url = false;
            $home_static_type = 'mall';

            $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
            $home_static = $home_static->where('home_static_type','=',$home_static_type);
            if ($store_key) {
                $home_static = $home_static->where('store_key','=',$store_key);
            } else {
                $home_static = $home_static->where(function($query) {
                    return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                });
            }
            $home_static = $home_static->where('home_static_data_feeded','>',0)->orderBy('id', 'desc')->first();

            if (!$home_static) {
                $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
                $home_static = $home_static->where('home_static_type','=',$home_static_type);
                if ($store_key) {
                    $home_static = $home_static->where('store_key','=',$store_key);
                } else {
                    $home_static = $home_static->whereNull('store_key');
                }
                $home_static = $home_static->orderBy('id', 'desc')->first();
            }

            if ($home_static && $home_static->file_name && $home_static->home_static_data_feeded > 0) {
                $split_file_name = explode('/',$home_static->file_name);
                $file_name = explode('.',$split_file_name[1]);
                $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
            } elseif($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            }

            if ($file_url && file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_instant(Request $request) {
        try {
            $lang = $request->header('lang');
            $group = $request->input('group');
            $store_key = $request->query('store');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $file_url = false;
            $home_static_type = 'instant';

            if (!empty($group)) {
                $home_static_type = $home_static_type;
            } 
            $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
            $home_static = $home_static->where('home_static_type','=',$home_static_type);
            if ($store_key) {
                $home_static = $home_static->where('store_key','=',$store_key);
            } else {
                $home_static = $home_static->where(function($query) {
                    return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                });
            }
            $home_static = $home_static->where('home_static_data_feeded','>',0)->orderBy('id', 'desc')->first();

            if (!$home_static) {
                $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
                $home_static = $home_static->where('home_static_type','=',$home_static_type);
                if ($store_key) {
                    $home_static = $home_static->where('store_key','=',$store_key);
                } else {
                    $home_static = $home_static->whereNull('store_key');
                }
                $home_static = $home_static->orderBy('id', 'desc')->first();
            }

            if ($home_static && $home_static->file_name && $home_static->home_static_data_feeded > 0) {
                $split_file_name = explode('/',$home_static->file_name);
                $file_name = explode('.',$split_file_name[1]);
                $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'_'.$group.'.'.$file_name[1]);
            } elseif($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            }

            if ($file_url && file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $group = $request->input('group');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static_type = isset($group) ? 'home_static_instant' : 'home_static_1';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=> $home_static_type])
                ->where('home_static_data_feeded','>',0)
                ->orderBy('id', 'desc')
                ->first();

            if (!$home_static) {
                $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=> $home_static_type])
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if ($home_static && $home_static->file_name && $home_static->home_static_data_feeded > 0) {
                $split_file_name = explode('/',$home_static->file_name);
                $file_name = explode('.',$split_file_name[1]);
                if($group){
                    $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'_'.$group.'.'.$file_name[1]);
                }else{
                    $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
                }
                
            } elseif($home_static && $home_static->file_name) {
                $file_url = storage_path('app/public/'.$home_static->file_name);
            }else{
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_static_1-ar.json") : storage_path("app/public/home_json/home_static_1.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_store(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $store_key = $request->query('store');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $file_url = false;

            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>$store_key])
                ->where('home_static_data_feeded','>',0)
                ->orderBy('id', 'desc')
                ->first();

            if (!$home_static) {
                $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>$store_key])
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if ($home_static && $home_static->file_name && $home_static->home_static_data_feeded > 0) {
                $split_file_name = explode('/',$home_static->file_name);
                $file_name = explode('.',$split_file_name[1]);
                $file_url = storage_path('app/public/home_static_json_data/'. $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
            } elseif($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            }

            if ($file_url && file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function ivp_switching_personalized(Request $request)
    {
        try {

            $lang = $request->header('lang');

            // Check authorizations
            $user = false;
            $user_id = '';
            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                    $user = User::find($user_id);
                } 
            } 

            // Return data
            if ($lang=='ar') {
                $title = "يا هلا، اختار سرعة طلبك ❤️";
            } else {
                if ($user && $user->name!='') {
                    $title = "Hi $user->name, ";
                } else {
                    $title = "Hi, ";
                }
                $title .= "Start Shopping ❤️";
            }

            // Instant
            $instant = [];
            $instant['title'] = $lang=='ar' ? "جيب بسرعة" : "Jeeb Instant";
            $instant['body'] = $lang=='ar' ? "توصيل ٥٠٠٠ منتج غذائي وما فوق" : "instant delivery of 5000+ grocery items";
            $instant['delivery_in'] = $lang=='ar' ? "15mins" : "15mins";
            $instant['image'] = $lang=='ar' ? "" : "";

            // Plus
            $plus = [];
            $plus['title'] = $lang=='ar' ? "جيب كل شي" : "Jeeb Groceries";
            $plus['body'] = $lang=='ar' ? "توصيل ١٠٠٠٠ منتج وما فوق و التوصيل مجاني" : "delivery of 100,000+ items with free delivery";
            $plus['delivery_in'] = $lang=='ar' ? "3hours" : "3hours";
            $plus['image'] = $lang=='ar' ? "" : "";

            // Current time and date
            $current_time = date('H:i:00');
            $today_date = date('Y-m-d');

            // Bottom Banners & Redirections
            $banners = [];
            $banners['ui_type'] = 1;
            $banners['banner_type'] = 1;
            $banners['title'] = '';
            $banners['background_color'] = '';
            $banners['background_image'] = '';
            $banners['keyword'] = '';
            $banners['filter_tag'] = '';
            $banners['search_type'] = 0;
            $banners['redirection_type'] = 0;
            $banners['data'] = [];
            $banners['data'][0] = [
                "id"=>0,
                "title"=>"",
                "image"=>"https://jeeb.tech/storage/images_uploaded/1688812992_image_file.webp",
                "image2"=>"",
                "keyword"=>"fk_brand_id=378",
                "recipe_category_id"=>0,
                "recipe_id"=>0,
                "category_id"=>0,
                "subcategory_id"=>0,
                "redirection_type"=>2
            ];
            $banners['data'][1] = [
                "id"=>0,
                "title"=>"",
                "image"=>"https://jeeb.tech/storage/images_uploaded/1688813024_image_file.webp",
                "image2"=>"",
                "keyword"=>"fk_brand_id=654",
                "recipe_category_id"=>0,
                "recipe_id"=>0,
                "category_id"=>0,
                "subcategory_id"=>0,
                "redirection_type"=>2
            ];
            $banners = json_encode($banners);

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'title'=>$title,
                'plus'=>$plus,
                'instant'=>$instant,
                'banners'=>$banners,
                'current_time'=>$current_time,
                'today_date'=>$today_date
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

    protected function home_personalized(Request $request)
    {
        try {
            $google_api_key = base64_encode('AIzaSyBLOYhy8ToGr9KkZ25UyAGIdLbOUHvwxL4');

            $google_api_key_enc = encript_api_keys_type1('AIzaSyBLOYhy8ToGr9KkZ25UyAGIdLbOUHvwxL4');
            $algolia_key = encript_api_keys_type1('2bc120e0dd21ae56d826c8b77448792a');
            $algolia_id = '1DUJVKR8FC';
            $algolia_index = 'products';
            $payment_key = encript_api_keys_type1('XYgwpDLlIL3BVm_dvAFepg55uKVdKlz--1bMhtO4qhyAZYFVeqO51O4IPHTwahBXPI-FfA4W0Q3m5QCm9JZ4Ql-Q3LuIPpK88TomNQL7L4X2jCOHRJVMsETrjUsdlYU65p2ET8SaoBbTQbGupAn611tAGlLFNnHaNVo4wbUZwGwJsjGnNBfpEnNZwIRN-6UTzRbvc3lLjQzC5lhIyA-Ai6NymOuWHFRFRwb9LNd96yHp_Z6QhWhPDVJYzSys4ICfCQuKRZ71o1xzlMtpIHaLMkugiiH9Nuu3H_ODjntxtSroC61kpB93-Mj6uJ_L-eOKDSOF4hu88NpO1MgtTQvcENm1FWGVUynKnIbCLfS26c8ScObJcZseiCEtmI_O514GP6qj0a7JscE5l5aSpGE1HgbFtJpkvUmW31OzpJ7AJuyQTNj-nOnfOoR8hcM1FhTx2IU5i9e6Q4Q7IPbYagPWYgQjyMVvsBD-XdtgaILwxIkGz2uMi3dRnCUSwqMAvw9OWD56Pahi_ezdwIoyyujestMBcA615qVJ7SsQfCac6o3B3QYYlzFY0l2UbDfxggG5CN0gwWqulPDkfImEP2mHuZImfVtvNZaC48kjQ32X4IyOZePnjxCI2TmEjCZgwptstFALFdrqBOtUtEHVtHQUOZa6ZdEI2dG3NpQGIbqNh8gGEt6i');

            // Return Data
            $active_orders_arr = [];
            $recent_orders_arr = [];
            $buy_it_for_me_requests_arr = [];
            $buy_it_for_me_sent_requests_status_arr = [];
            $active_scratched_cards_arr = [
                'active_scratch_cards' => 0,
                'active_scratch_cards_title' => 'Rewards',
                'active_scratch_cards_message' => ''
            ];
            $non_rated_delivered_orders_arr = [];

            // Home Static Json version
            $home_static = \App\Model\HomeStatic::orderBy('id','desc')->first();
            $home_static_last_id = $home_static ? $home_static->id : 0;
            $home_static_data_feeded = $home_static ? $home_static->home_static_data_feeded : 0;
            $home_static_version = env("HOME_STATIC_VERSION", false).$home_static_last_id.$home_static_data_feeded;

            // Language and authenticated user
            $lang = $request->header('lang');
            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                } else {
                    $user_id = '';
                }

                $latLong = OauthAccessToken::where(['id' => $access_token])->first();
                if ($latLong) {
                    $latitude = $latLong->latitude;
                    $longitude = $latLong->longitude;
                    $guest_user_eta = $latLong->expected_eta;
                } else {
                    $latitude = '';
                    $longitude = '';
                    $guest_user_eta = '';
                }
            } else {
                $user_id = '';
                $latitude = '';
                $longitude = '';
                $guest_user_eta = '';
            }

            if ($user_id!='') {

                $user = User::find($user_id);
                // If user not found, send session expired message
                if(!$user){
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Your session expired. Please login again..');
                    }
                    $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                    throw new Exception($error_message, 105);
                }

                // Tap ID
                $tap_id = $user->tap_id;

                // Active Scratched Cards
                $active_scratch_cards = ScratchCardUser::where('status', '=', 1)
                ->where('fk_user_id',$user_id)
                ->where('deleted',0)
                ->where('expiry_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->orderBy('created_at', 'asc');
                $active_scratch_cards_count = $active_scratch_cards->count();
                $latest_active_scratch_card = $active_scratch_cards->first();

                if ($active_scratch_cards) {
                    $active_scratched_cards_arr['active_scratch_cards'] = $active_scratch_cards_count;
                    $active_scratched_cards_arr['active_scratch_cards_message'] = 'You have active scratched cards which you can unscratch and get rewards!';
                    $active_scratched_cards_arr['latest_active_scratch_card'] = $latest_active_scratch_card;
                }
                
                // Recent Ordered Products
                $user->nearest_store = 14; // Temp fix for active orders planned model
                $store = Store::find($user->nearest_store);
                $store_id = $store ? $store->id : 0;

                $store_no = $store ? get_store_no($store->name) : 0;
                $company_id = $store ? $store->company_id : 0;

                $order_products = false;
                if ($user && $store) {
                    
                    $order_products = \App\Model\OrderProduct::join('orders', 'order_products.fk_order_id', '=', 'orders.id')
                        ->join('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                        ->leftJoin('categories', 'base_products.fk_category_id', '=', 'categories.id')
                        ->leftJoin('brands', 'base_products.fk_brand_id', '=', 'brands.id')
                        ->select(
                            'base_products.*',
                            'categories.id as category_id',
                            'categories.category_name_en',
                            'categories.category_name_ar',
                            'brands.id as brand_id',
                            'brands.brand_name_en',
                            'brands.brand_name_ar',
                            'orders.created_at as order_created_at',
                            'orders.id as order_id'
                        )
                        ->where('orders.fk_user_id', '=', $user_id)
                        ->where('orders.status', '=', 7)
                        ->where('base_products.product_store_stock', '>', 0)
                        ->where('base_products.parent_id', '=', 0)
                        ->where('base_products.deleted', '=', 0)
                        ->orderBy('order_products.id', 'desc')
                        ->groupBy('order_products.fk_product_id')
                        ->limit(10)
                        ->get();
                    
                }
                $recent_orders_arr = get_product_dictionary_bp($order_products, $user_id, $lang, $request->header('Authorization'));
                
                // Active Orders
                $active_order = Order::join('order_delivery_slots', 'orders.id', '=', 'order_delivery_slots.fk_order_id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->select('orders.*', 'order_delivery_slots.delivery_date', 'order_delivery_slots.delivery_slot', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference', 'order_delivery_slots.expected_eta')
                    ->where(function ($query) {
                            $query->where('fk_user_id', '=', $this->user_id)
                            ->orWhere('bought_by', '=', $this->user_id);
                        })
                    ->whereIn('orders.status', array(1, 2, 3, 6))
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('order_payments.status', '!=', 'pending')
                    ->orderByRaw("CONCAT(delivery_date,' ',LEFT(later_time,LOCATE('-',later_time) - 1))")
                    ->get();
                    
                $active_orders_arr = [];
                if ($active_order->count()) {
                    foreach ($active_order as $key => $value) {
                        $store_id = $value->fk_store_id;
                        $store_no = 0;
                        $company_id = 0;
                        if ($value->delivery_time==1) {
                            $delivery_in = getDeliveryInTimeRange($value->expected_eta, $value->created_at, $lang);
                        } else {
                            // Get the sheduled earliest time
                            $later_time = strtok($value->later_time, '-');
                            $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                            $later_delivery_time = $value->delivery_date.' '.$later_time.':00';
                            $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang, $value->later_time);
                        }

                        switch ($value->status) {
                            case 7:
                                $status_text = $lang=='ar' ? "تم توصيل طلبكم, شكرا لاستخدامكم جيب! 🥳" : "Delivered! Thank you for using Jeeb 🥳";
                                break;
                            case 6:
                                $status_text = $lang=='ar' ? "مسافة الطريق و الطلب يوصلكم 🚚" : "Order is now out for delivery 🚚";
                                break;
                            case 5:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Order is packed and ready to go 📦";
                                break;
                            case 4:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Order is packed and ready to go 📦";
                                break;
                            case 3:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Order is packed and ready to go 📦";
                                break;
                            default:
                                $status_text = $lang=='ar' ? "تم استلام الطلب بنجاح! 🎉" : "Order received successfully! 🎉";
                        }

                        $active_orders_arr[] = [
                            'id' => $value->id,
                            'orderId' => $value->orderId,
                            'sub_total' => $value->sub_total,
                            'total_amount' => $value->total_amount,
                            'delivery_charge' => $value->delivery_charge,
                            'coupon_discount' => $value->coupon_discount ?? '',
                            'item_count' => $value->getOrderProducts->count(),
                            'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                            'status' => $value->status,
                            'status_text' => $status_text,
                            'change_for' => $value->change_for ?? '',
                            'delivery_date' => $value->delivery_date,
                            'delivery_in' => $delivery_in,
                            'delivery_time' => $value->delivery_time,
                            'later_time' => $value->later_time,
                            'delivery_preference' => $value->delivery_preference,
                            'forgot_something_store_id' => env("FORGOT_SOMETHING_STORE_ID"),
                            'forgot_something_store_ids' => $this->forgot_something_store_ids(),
                            'paythem_store_id' => env("PAYTHEM_STORE_ID"),
                            'store_id' => $store_id,
                            'store' => get_store_no_string($store_no),
                            'company_id' => $company_id,
                            'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                            'bought_by' => $value->bought_by ?? 0
                        ];
                    }
                }
                
                // Buy it for me requests
                $buy_it_for_me_requests = UserBuyItForMeRequest::where('to_user_mobile', $user->mobile)->whereIn('status',[0])->orderBy('id','desc')->get();
                $buy_it_for_me_requests_arr = [];
                foreach ($buy_it_for_me_requests as $key => $value) {
                    $requested_user = User::find($value->from_user_id);
                    if ($requested_user) {
                        $buy_it_for_me_requests_arr[] = [
                            'id' => $value->id,
                            'requested_by' => $requested_user->name ?? $requested_user->mobile
                        ];
                    }
                }

                // Buy it for me requests status
                $buy_it_for_me_sent_requests = UserBuyItForMeRequest::where('from_user_id', $this->user_id)->whereIn('status',[1,2])->where('is_read',0)->orderBy('id','desc')->get();
                $buy_it_for_me_sent_requests_status_arr = [];
                foreach ($buy_it_for_me_sent_requests as $key => $value) { 
                    $request_received_user = User::where('mobile', $value->to_user_mobile)->first();
                    if ($request_received_user) {
                        if($request_received_user->name){
                            $msg = $value->status == 1 ?  $request_received_user->name." has "."accepeted your request" : $request_received_user->name." has "."declined your request";
                        }else{
                            $msg = $value->status == 1 ?  $request_received_user->mobile." has "."accepeted your request" : $request_received_user->name." has "."declined your request";
                        }
                        
                        $buy_it_for_me_sent_requests_status_arr[] = [
                            'id' => $value->id,
                            'status' => $value->status,
                            'message' => $msg
                        ];
                    }
                }

            }

            // Last delivered orders
            $delivered_orders = Order::select('orders.*')
                ->where('orders.fk_user_id',$this->user_id)
                ->where('orders.status','=',7)
                ->where('orders.created_at', '>', '2023-11-08 00:00:00')
                ->orderBy('created_at','desc')
                ->limit(3)->get();
            if($delivered_orders){
                foreach ($delivered_orders as $key => $value) {
                    $reviewed = OrderReview::where(['fk_user_id' => $this->user_id, 'fk_order_id' => $value->id])->first();
                    if (!$reviewed && strtotime($value->created_at)>=strtotime('-7 day')) {
                        $non_rated_delivered_orders_arr[] = [
                            'id' => $value->id,
                            'orderId' => $value->orderId,
                        ];
                    }
                }
            }

            $recent_orders_arr = [];

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = [
                'active_scratched_cards' => $active_scratched_cards_arr,
                'active_orders' => $active_orders_arr,
                'recent_orders' => $recent_orders_arr,
                'buy_it_for_me_requests_arr' => $buy_it_for_me_requests_arr,
                'buy_it_for_me_sent_requests_status_arr' => $buy_it_for_me_sent_requests_status_arr,
                'non_rated_delivered_orders_arr' => $non_rated_delivered_orders_arr,
                'google_api_key' => $google_api_key,
                'google_api_key_enc' => $google_api_key_enc,
                'payment_key' => $payment_key,
                'algolia_key' => $algolia_key,
                'algolia_id' => $algolia_id,
                'algolia_index' => $algolia_index,
                'user_id' => $user_id,
                'home_static_version' => $home_static_version,
                'tap_id' => $tap_id ?? ""
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

    protected function home_personalized_1(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_personalized_1'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_personalized_1-ar.json") : storage_path("app/public/home_json/home_personalized_1.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_personalized_2(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_personalized_2'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_personalized_2-ar.json") : storage_path("app/public/home_json/home_personalized_2.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_personalized_3(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_personalized_3'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_personalized_3-ar.json") : storage_path("app/public/home_json/home_personalized_3.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_1(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_static_1'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            }else{
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_static_1-ar.json") : storage_path("app/public/home_json/home_static_1.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_2(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_static_2'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_static_2-ar.json") : storage_path("app/public/home_json/home_static_2.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_static_3(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where(['lang'=>$lang, 'home_static_type'=>'home_static_3'])->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_static_3-ar.json") : storage_path("app/public/home_json/home_static_3.json");
            }

            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function home_reels(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            
            $file_url = $lang=='ar' ? storage_path("app/public/home_json/home_reels-ar.json") : storage_path("app/public/home_json/home_reels.json");
            
            if (file_exists($file_url)) {
                return response()->file($file_url);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    private function get_my_cart_data($lang, $tracking_id=false)
    {
        try {
            $cartItems = UserCart::where(['fk_user_id' => $this->user_id])
                ->orderBy('id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000];

            $wallet_balance = get_userWallet($this->user_id);

            $cartTotal = UserCart::selectRaw("SUM(total_price) as total_amount")
                ->where('fk_user_id', '=', $this->user_id)
                ->first();

            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }
            $mp_amount_for_free_delivery = get_minimum_purchase_amount_for_free_delivery($user->ivp);
            $mp_amount = get_minimum_purchase_amount($user->ivp,$user->mobile,$user->id);
            $delivery_cost = get_delivery_cost($user->ivp,$user->mobile,$user->id);
            $nearest_store = false;

            // For Instant model
            if ($user->ivp=='i') {
                $nearest_store = InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
                ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
                ->where(['instant_store_groups.id'=>$user->nearest_store,'instant_store_groups.deleted'=>0,'instant_store_groups.status'=>1])
                ->first();
            }
            $active_stores_arr = [];
            if ($nearest_store) {
                $active_stores = InstantStoreGroupStore::join('stores','stores.id','instant_store_group_stores.fk_store_id')
                ->where(['instant_store_group_stores.fk_group_id'=>$nearest_store->id,'stores.deleted'=>0,'stores.status'=>1, 'stores.schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            } else {
                // For Planned model
                $active_stores = Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            }
            
            // ------------------------------------------------------------------------------------------------------
            // Sub categories for only online payments
            // ------------------------------------------------------------------------------------------------------
            // Flowers - 175, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192

            // Mobile & Tablets - 198
            // Mobile & Computer - 164
            // Mobile Accessories - 199
            // Gaming - 165
            // Audio & Wearables - 166

            // Seafood - 31
            // Mutton - 32
            // Beef - 100

            // Accessories - 155
            $sub_categories_for_online_payment = [
                198, 164
            ];
            
            // ------------------------------------------------------------------------------------------------------
            // Coupons or scratch card
            // ------------------------------------------------------------------------------------------------------
            $user_coupons = Coupon::where('status', '=', 1)->where('expiry_date', '>', \Carbon\Carbon::now()->format('Y-m-d'))
                            ->whereIn('coupons.fk_user_id',[0,$user->id])->get();
            $having_rewards = $user_coupons->count() ? true : false;
            if (!$having_rewards) {
                $is_scratch_card_enabled = $this->is_scratch_card_enabled($this->user_id, $user);
                if ($is_scratch_card_enabled) {
                    $user_scratch_card = ScratchCardUser::where('fk_user_id',$this->user_id)
                                        ->where('deleted',0)
                                        ->whereIn('status',[0,1])
                                        ->where('expiry_date', '>=', \Carbon\Carbon::now()->format('Y-m-d'))
                                        ->first();
                    $having_rewards = $user_scratch_card ? true : false;
                }
            } 
            $coupon_arr=[];
            if ($user_coupons->count()) {
                foreach ($user_coupons as $value) {
                    // Check for user coupon usage
                    $coupon_used = CouponUses::where(['fk_user_id'=>$user->id,'fk_coupon_id'=>$value->id])->count();
                    if ($coupon_used<$value->uses_limit) {
                        $coupon_arr[] = [
                            'id' => $value->id,
                            'type' => $value->type,
                            'min_amount' => (float) $value->min_amount,
                            'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                            'description' => $lang == 'ar' ? $value->description_ar : $value->description_en,
                            'coupon_code' => $value->coupon_code,
                            'coupon_image' => $value->getCouponImage ? asset('/') . $value->getCouponImage->file_path . $value->getCouponImage->file_name : '',
                            'expiry_date' => $value->expiry_date,
                            'uses_limit' => $value->uses_limit,
                            'coupon_used' => $coupon_used
                        ];
                    }
                }
            }

            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($user->expected_eta, $user->ivp);
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");
            // ------------------------------------------------------------------------------------------------------
            // Generate and send time slots
            // ------------------------------------------------------------------------------------------------------
            $time_slots_made = $this->getTimeSlotFromDB();
            $time_slots = $time_slots_made[0];
            $active_time_slot = $time_slots_made[1];
            $active_time_slot_value = $time_slots_made[2];
            $active_time_slot_string = $time_slots_made[3];

            // Get my cart data
            $h1_text = $lang=='ar' ? 'هناك تأخير بسيط لطلبك' : 'There is a slight delay for your order';
            $h2_text = $lang=='ar' ? 'قد يتأخر التسليم الخاص بك بسبب منتج معين في سلة التسوق الخاصة بك. انتظر بينما نستعد لتسليم طلبك.' : 'Your delivery may be delayed due to certain product in your cart. Hold tight as we prepare to deliver your order.';
            
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $cartItemArr = [];

            if ($cartItems->count()) {
                $message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                foreach ($cartItems as $key => $value) {

                    if ($user->ivp=='i') {
                        $product = BaseProductStore::leftJoin('categories AS A', 'A.id','=', 'base_products_store.fk_category_id')
                            ->leftJoin('categories AS B', 'B.id','=', 'base_products_store.fk_sub_category_id')
                            ->leftJoin('brands','base_products_store.fk_brand_id', '=', 'brands.id')
                            ->select(
                                'base_products_store.*',
                                'base_products_store.fk_product_id AS fk_product_id',
                                'base_products_store.id AS fk_product_store_id',
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
                        // Find alternative for product
                        if ($product && !in_array($product->fk_store_id, $active_stores_arr->toArray())) {
                            $product = $this->update_instant_alternate_product ($value, $active_stores_arr->toArray());
                        }
                        // If not products
                        if (!$product) {
                            $product = BaseProduct::leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                            ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                            ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                            ->select(
                                'base_products.*',
                                'base_products.id AS fk_product_id',
                                'base_products.fk_product_store_id AS fk_product_store_id',
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
                            ->where('base_products.id', '=', $value->fk_product_id)
                            ->first();   
                            if ($product) {
                                $product->product_store_stock = 0;
                            }
                        }
                    } else {
                        $product = BaseProduct::leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                            ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                            ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                            ->select(
                                'base_products.*',
                                'base_products.id AS fk_product_id',
                                'base_products.fk_product_store_id AS fk_product_store_id',
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
                            ->where('base_products.id', '=', $value->fk_product_id)
                            ->first();   
                    }

                    // If sub products for recipe
                    $sub_product_arr = [];
                    $sub_product_names = "";
                    $sub_product_price = 0;
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = BaseProduct::find($value2->product_id);
                                    $sub_product_arr[] = array (
                                        'product_id' => $sub_product->id,
                                        'product_name' => $lang == 'ar' ? $sub_product->product_name_ar : $sub_product->product_name_en,
                                        'product_image' => $sub_product->product_image_url ?? '',
                                        'product_price' => (string) number_format($sub_product->product_store_price, 2),
                                        'item_unit' => $sub_product->unit
                                    );
                                    $sub_product_names .= $lang == 'ar' ? $sub_product->product_name_ar.',' : $sub_product->product_name_en.',';
                                    $sub_product_price += $sub_product->product_store_price *  $value2->product_quantity;
                                }
                            }
                        }
                    }
                    $sub_product_names = rtrim($sub_product_names,',');
                    $sub_product_price = (string) number_format($sub_product_price,2);
                    // Checking stocks
                    if (!$product) {
                        $cartItemOutofstockArr[] = [
                            'id' => $value->id,
                            'product_id' => $value->fk_product_id,
                            'product_type' => '',
                            'parent_id' => 0,
                            'recipe_id' => 0,
                            'product_name' => 'Product (Deleted)',
                            'product_image' => '',
                            'product_price' => '0.00',
                            'product_store_price' => '0.00',
                            'product_total_price' => '0.00',
                            'product_price_before_discount' => '0.00',
                            'cart_quantity' => $value->quantity,
                            'unit' => '',
                            'product_discount' => 0,
                            'stock' => '0',
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => 0,
                            'product_sub_category_id' => 0,
                            'min_scale' => '',
                            'max_scale' => '',
                            'sub_products' => [],
                            'sub_product_names' => '',
                            'sub_product_price' => 0,
                            'fk_store_id' => 0,
                            'fk_product_store_id' => $value->fk_product_store_id
                        ];
                    }
                    elseif (!in_array($product->fk_store_id, $active_stores_arr->toArray())) {
                        $cartItemOutofstockArr[] = [
                            'id' => $value->id,
                            'product_id' => $product->fk_product_id,
                            'product_type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_store_price, 2),
                            'product_store_price' => (string) number_format($product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit ?? '',
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price,
                            'fk_store_id' => $product->fk_store_id,
                            'fk_product_store_id' => $product->fk_product_store_id
                        ];
                    }
                    elseif ($product->product_store_stock > 0 && $product->product_store_stock < $value->quantity) {

                        $cartItemQuantityMismatch[] = [
                            'id' => $value->id,
                            'product_id' => $product->fk_product_id,
                            'product_type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_store_price, 2),
                            'product_store_price' => (string) number_format($product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit ?? '',
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price,
                            'fk_store_id' => $product->fk_store_id,
                            'fk_product_store_id' => $product->fk_product_store_id
                        ];

                    }elseif ($product->product_store_stock > 0 && $product->deleted == 0) {
                        $cartItemArr[] = [
                            'id' => $value->id,
                            'product_id' => $product->fk_product_id,
                            'product_type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_store_price, 2),
                            'product_store_price' => (string) number_format($product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit ?? '',
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price,
                            'fk_store_id' => $product->fk_store_id,
                            'fk_product_store_id' => $product->fk_product_store_id
                        ];
                    } else {
                        $cartItemOutofstockArr[] = [
                            'id' => $value->id,
                            'product_id' => $product->fk_product_id,
                            'product_type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_store_price, 2),
                            'product_store_price' => (string) number_format($product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit ?? '',
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price,
                            'fk_store_id' => $product->fk_store_id,
                            'fk_product_store_id' => $product->fk_product_store_id
                        ];
                    }
                }
                
                $result = [
                    'having_rewards' => $having_rewards,
                    'sub_total' => (string) round($cartTotal->total_amount, 2),
                    'delivery_charge' => $delivery_cost,
                    'total_amount' => (string) ($cartTotal->total_amount + $delivery_cost),
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'minimum_purchase_amount_for_free_delivery' => $mp_amount_for_free_delivery,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'store_open_time' => $store_open_time,
                    'store_close_time' => $store_close_time,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'sub_categories_for_online_payment' => $sub_categories_for_online_payment,
                    'time_slots' => $time_slots,
                    'active_time_slot' => $active_time_slot,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for,
                    'active_stores' => $active_stores_arr,
                    'cart_items' => $cartItemArr,
                    'cart_items_outofstock' => $cartItemOutofstockArr,
                    'cart_items_quantitymismatch' => $cartItemQuantityMismatch,
                    'available_coupons'=>$coupon_arr
                ];
            } else {
                $message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $result = [
                    'sub_total' => '0',
                    'delivery_charge' => '0',
                    'total_amount' => '0',
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'minimum_purchase_amount_for_free_delivery' => $mp_amount_for_free_delivery,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'sub_categories_for_online_payment' => $sub_categories_for_online_payment,
                    'time_slots' => $time_slots,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for,
                    'active_stores' => $active_stores_arr,
                    'cart_items' => [],
                    'cart_items_outofstock' => [],
                    'cart_items_quantitymismatch' => [],
                    'available_coupons'=>$coupon_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => []
            ]);
        }
        return ['message'=>$message, 'result'=>$result];
    }

    protected function my_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {
            $my_cart_data = $this->get_my_cart_data($lang);

            $this->error = false;
            $this->status_code = 200;
            $this->message = $my_cart_data && is_array($my_cart_data) && isset($my_cart_data['message']) ? $my_cart_data['message'] : '';
            $this->result = $my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result']) ? $my_cart_data['result'] : [];
            
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

    protected function update_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }
            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'update_cart_bp', $request, '');

            if ($content != '') {

                $content_arr = json_decode($content);

                UserCart::where(['fk_user_id' => $this->user_id])->delete();

                foreach ($content_arr as $key => $value) {

                    if ($user->ivp == 'i') {
                        $base_product = BaseProductStore::where(['id'=>$value->product_store_id])->first();
                        $base_product_id = $base_product ? $base_product->fk_product_id : $value->product_id;
                        $base_product_store_id = $base_product ? $base_product->id : $value->product_store_id;
                    } else {
                        $base_product = BaseProduct::where(['id'=>$value->product_id])->first();
                        $base_product_id = $base_product ? $base_product->id : $value->product_id;
                        $base_product_store_id = $base_product ? $base_product->fk_product_store_id : $value->product_store_id;
                    }
                    
                    // If products are not available
                    // if (!$base_product) {
                    //     $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                    //     throw new Exception($error_message, 106);
                    // }
                    
                    // Calculate total price
                    $total_price = 0;
                    if ($base_product) {
                        $total_price = round($base_product->product_store_price * $value->product_quantity, 2);
                        // If sub products for recipe
                        if (isset($value->sub_products) && $value->sub_products!='') {
                            $sub_products = $value->sub_products;
                            if ($sub_products && is_array($sub_products)) {
                                foreach ($sub_products as $key => $value2) {
                                    if ($value2->product_id && $value2->product_quantity) {
                                        $sub_product = BaseProduct::find($value2->product_id);
                                        $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                                        $total_price = $total_price + $sub_product_price;
                                        $value2->product_name_en = $sub_product->product_name_en;
                                        $value2->product_name_ar = $sub_product->product_name_ar;
                                        $value2->unit = $sub_product->unit;
                                        $value2->price = $sub_product->product_store_price;
                                        $value2->product_image_url = $sub_product->product_image_url;
                                    }
                                }
                            }
                        }
                    }
                    
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'fk_product_id' => $base_product_id,
                        'fk_product_store_id' => $base_product_store_id,
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $value->weight ?? '',
                        'unit' => $value->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];
                    
                    UserCart::create($insert_arr);
                }

                $my_cart_data = $this->get_my_cart_data($lang, $tracking_id);
                if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                    $my_cart_data['result']['address_list'] = get_userAddressList($this->user_id);
                    $my_cart_data['result']['store_timeslot_blocking'] = get_storeTimeslotBlockings();
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                    $this->result = $my_cart_data['result'];
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

    protected function getTimeSlotFromDB()
    {
        $time = [];

        // Current time
        $current = new \DateTime(date('H:i'));
        $currentTime = $current->format('H:i');
        $currentTime_set = 0;
        
        // Active time
        $activeTime = 'tomorrow';
        $activeTimeString = 'tomorrow';
        $activeTimeValue = false;
        
        // Get address selected
        $address_selected = get_userSelectedAddress($this->user_id);
        $interval = $address_selected && isset($address_selected['blocked_timeslots']) ? $address_selected['blocked_timeslots']*60 : 0;

        // Get Time Slots
        $delivery_slots = DeliverySlotSetting::orderBy('from','asc')->get();
        if ($delivery_slots) {
            foreach ($delivery_slots as $key => $delivery_slot) {
                $start = date('H:i',strtotime($delivery_slot->from));
                $end = date('H:i',strtotime($delivery_slot->to));
                $start_str = date('h:i a',strtotime($delivery_slot->from));
                $end_str = date('h:i a',strtotime($delivery_slot->to));
                // $blockTime = date('H:i',strtotime($delivery_slot->block_time));
                $blockTime = date('H:i',strtotime('-'.$interval.' minutes',strtotime($delivery_slot->block_time)));
                // Set Active Time and Active Time Value
                if ($activeTimeString=='tomorrow') {
                    $activeTimeString = $start_str.' - '.$end_str.' (tomorrow)';
                }
                if (!$activeTimeValue) {
                    $activeTimeValue = $start.'-'.$end;
                }
                if(strtotime($delivery_slot->from) <= strtotime($delivery_slot->to)){
                    if (strtotime($currentTime) <= strtotime($blockTime) && $currentTime_set==0) {
                        $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,'active'];
                        $activeTime = $start_str.' - '.$end_str;
                        $activeTimeValue = $start.'-'.$end;
                        $activeTimeString = $start_str.' - '.$end_str.' (today)';
                        $currentTime_set=1;
                    } else {
                        $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,''];
                    }
                }
            }
        }

        return [$time,$activeTime,$activeTimeValue,$activeTimeString];
    }

    protected function getTimeSlot($interval, $start_time, $end_time)
    {
        $start = new \DateTime($start_time);
        $end = new \DateTime($end_time);
        $startTime = $start->format('H:i');
        $endTime = $end->format('H:i');
        $time = [];
        $reserved_minutes = env("STORE_RESERVE_MINUTES_FOR_LATER_ORDER");

        // Current time
        $current = new \DateTime(date('H:i'));
        $currentTime = $current->format('H:i');
        $currentTime_set = 0;
        
        // Active time
        $activeTime = 'tomorrow';
        $activeTimeValue = false;

        while(strtotime($startTime) <= strtotime($endTime)){
            $start = $startTime;
            $end = date('H:i',strtotime('+'.$interval.' minutes',strtotime($startTime)));
            $start_str = date('h:i a',strtotime($startTime));
            $end_str = date('h:i a',strtotime('+'.$interval.' minutes',strtotime($startTime)));
            $blockTime = date('H:i',strtotime('-'.$reserved_minutes.' minutes',strtotime($startTime)));
            $startTime = date('H:i',strtotime('+'.$interval.' minutes',strtotime($startTime)));
            if (!$activeTimeValue) {
                $activeTimeValue = $start.'-'.$end;
            }
            if(strtotime($startTime) <= strtotime($endTime)){
                if (strtotime($currentTime) <= strtotime($blockTime) && $currentTime_set==0) {
                    $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,'active'];
                    $activeTime = $start_str.' - '.$end_str;
                    $activeTimeValue = $start.'-'.$end;
                    $currentTime_set=1;
                } else {
                    $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,''];
                }
            }

        }
        return [$time,$activeTime,$activeTimeValue];
    }

    protected function remove_outofstock_products_from_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {
            $cartItems = UserCart::where(['fk_user_id' => $this->user_id])
                ->orderBy('id', 'desc')
                ->get();
            $cartItemArr = [];

            $user = User::find($this->user_id);
            $store = 'store' . $user->nearest_store;

            if ($cartItems->count()) {
                foreach ($cartItems as $key => $value) {
                    if ($value->getProduct->$store == 0) {
                        UserCart::find($value->id)->delete();
                    } else {
                        $discount = ($value->getProduct->margin ? ($value->getProduct->margin * $value->getProduct->distributor_price) / 100 : $value->getProduct->distributor_price);

                        $cartItemArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $value->getProduct->id,
                            'product_name' => $lang == 'ar' ? $value->getProduct->product_name_ar : $value->getProduct->product_name_en,
                            'product_image' => !empty($value->getProduct->getProductImage) ? asset('images/product_images') . '/' . $value->getProduct->getProductImage->file_name : '',
                            'product_price' => (string) round($value->getProduct->product_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'quantity' => $value->getProduct->quantity,
                            'unit' => $value->getProduct->unit,
                            'product_discount' => $value->getProduct->margin,
                            'stock' => $value->getProduct->$store
                        ];
                    }
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                $this->result = [
                    'cart_items' => array_values($cartItemArr),
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $this->result = [
                    'cart_items' => []
                ];
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

    protected function check_delivery_area(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['latitude', 'longitude']);

            $deliveryarea = DeliveryArea::first();

            $getdistance = DeliveryArea::select("radius")
                ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $request->input('latitude') . "')) * COS(
                                        RADIANS(latitude)
                                    ) * COS(
                                        RADIANS(longitude) - RADIANS('" . $request->input('longitude') . "')
                                    ) + SIN(RADIANS('" . $request->input('latitude') . "')) * SIN(
                                        RADIANS(latitude)
                                    )
                                )
                            ) AS distance")
                ->havingRaw("radius >= distance")
                ->get();
            if ($getdistance->count()) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "النجاح" : "Success";
                $this->result = [
                    'delivery' => true
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "النجاح" : "Success";
                $this->result = [
                    'delivery' => false
                ];
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

    protected function view_all_classifications(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $classifications = \App\Model\Classification::where('deleted', '=', 0)
                ->where('parent_id', '=', 0)
                ->orderBy('name_en', 'asc')
                ->get();
            $classification_arr = [];
            if ($classifications->count()) {
                foreach ($classifications as $key => $row) {
                    $classification_arr[$key] = [
                        'id' => $row->id,
                        'classification_name' => $lang == 'ar' ? $row->name_ar : $row->name_en,
                        'banner_image' => !empty($row->getBannerImage) ? asset('images/classification_images') . '/' . $row->getBannerImage->file_name : '',
                        'stamp_image' => !empty($row->getStampImage) ? asset('images/classification_images') . '/' . $row->getStampImage->file_name : '',
                    ];
                }
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = [
                'classifications' => $classification_arr,
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

    protected function add_to_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);

            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'add_to_cart_bp', $content, '');

            if ($content != '') {

                $content_arr = json_decode($content);

                foreach ($content_arr as $key => $value) {
                    $product = BaseProduct::find($value->product_id);
                    
                    // Calculate total price
                    $total_price = $product->product_store_price;
                    
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'fk_product_id' => $value->product_id,
                        'fk_product_store_id' => $value->product_store_id,
                        'quantity' => 1,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $value->weight ?? '',
                        'unit' => $value->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    //If product exist in the cart
                    $cartItemExist = UserCart::where(['fk_user_id' => $this->user_id,'fk_product_id' => $value->product_id])->first();
                    
                    if($cartItemExist){
                        $insert_arr['quantity'] = $cartItemExist->quantity+1;
                        $insert_arr['total_price'] = $product->product_store_price + $cartItemExist->total_price;
                        $cartItemExist->update($insert_arr);
                    }else{
                        UserCart::create($insert_arr);
                    }
                }

                $my_cart_data = $this->get_my_cart_data($lang, $tracking_id);
                if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                    $this->result = $my_cart_data['result'];
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

    protected function remove_from_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {
            $cartItem = UserCart::where(['fk_user_id' => $this->user_id,'fk_product_id'=> $request->product_id])->first();
            
            $user = User::find($this->user_id);
            
            if ($cartItem->quantity > 1) {
                
                $product = BaseProduct::find($request->product_id);
                $product_price = $product->product_store_price;
                
                $insert_arr = [
                    'quantity' => $cartItem->quantity-1,
                    'total_price' => $cartItem->total_price - $product_price,
                ];

                UserCart::find($cartItem->id)->update($insert_arr);
                
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Item removed from the cart";
                
            } else {

                UserCart::find($cartItem->id)->delete();

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Item removed from the cart";
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

    //save for later
    protected function save_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);

            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'save_cart', $request, '');

            if ($request->input('product_json') != '') {
                
                $content_arr = json_decode($request->input('product_json'));
                
                $insert_saved_cart_arr = [
                    'name' => $request->input('name'),
                    'fk_user_id' => $this->user_id,
                    'fk_address_id' => $request->input('address_id'),
                ];

                // If saved cart name already taken
                $saved_cart_name_exist = UserSavedCart::where(['fk_user_id' => $this->user_id, 'name' => $request->input('name')])->first();
                if ($saved_cart_name_exist) {
                    $error_message = $lang == 'ar' ? 'السلة بهذا الاسم موجودة بالفعل' : 'Cart with this name already exists';
                    throw new Exception($error_message, 106);
                }

                $create = UserSavedCart::create($insert_saved_cart_arr);
                $user_saved_cart_total = 0;

                foreach ($content_arr as $key => $value) {
                    $product = BaseProduct::where(['id' => $value->product_id])->first();

                    // Calculate total price
                    $total_price = round($product->product_store_price * $value->product_quantity, 2);
                    $user_saved_cart_total += $total_price;
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = BaseProduct::find($value2->product_id);
                                    $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->product_store_price;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
                    
                    $insert_saved_cart_product_arr = [
                        'fk_product_id' => $value->product_id,
                        'fk_product_store_id' => $value->product_store_id,
                        'fk_saved_cart_id' => $create->id,
                        'quantity' => $value->product_quantity,
                        'product_price' => $product->product_store_price,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $value->weight ?? '',
                        'unit' => $value->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    $save_cart = UserSavedCartProduct::create($insert_saved_cart_product_arr);
                }

                $user_saved_cart = UserSavedCart::find($create->id);
                $update_user_saved_cart = $user_saved_cart->update(['total_price' => $user_saved_cart_total]);
                $my_cart_data = $this->get_my_saved_cart_data($lang,$create->id);

                if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                    $my_cart_data['result']['address_list'] = get_userAddressList($this->user_id);
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم حفظ السلة بنجاح" : "Cart saved successfully";
                    $this->result = $my_cart_data['result'];
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

    //update saved cart
    protected function update_saved_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');
            
            $user = User::find($this->user_id);
            
            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'update_saved_cart', $request, '');

            if ($request->input('product_json') != '') {
            
                $content_arr = json_decode($request->input('product_json'));
                
                $saved_cart = UserSavedCart::find((int)$request->input('saved_cart_id'));
                $user_saved_cart_total = 0;

                $insert_saved_cart_arr = [
                    'name' => $request->input('name'),
                    'fk_user_id' => (int)$this->user_id,
                    'fk_address_id' => (int)$request->input('address_id'),
                ];

                $saved_cart->update($insert_saved_cart_arr);
                
                UserSavedCartProduct::where(['fk_saved_cart_id' => $saved_cart->id])->delete();
                
                $user_saved_cart_total = 0;

                foreach ($content_arr as $key => $value) {
                    $base_product = BaseProduct::where(['id'=>$value->product_id])->first();
                    
                    // If products are not available
                    // if (!$base_product) {
                    //     $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                    //     throw new Exception($error_message, 106);
                    // }

                    // Calculate total price
                    $total_price = round($base_product->product_store_price * $value->product_quantity, 2);
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = BaseProduct::find($value2->product_id);
                                    $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->product_store_price;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
                    
                    $insert_saved_cart_product_arr = [
                        'fk_product_id' => $value->product_id,
                        'fk_product_store_id' => $value->product_store_id,
                        'fk_saved_cart_id' => $request->input('saved_cart_id'),
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $value->weight ?? '',
                        'unit' => $value->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    UserSavedCartProduct::create($insert_saved_cart_product_arr);
                }

                $user_saved_cart = UserSavedCart::find($request->input('saved_cart_id'));
                $update_user_saved_cart = $user_saved_cart->update(['total_price' => $user_saved_cart_total]);

                $my_cart_data = $this->get_my_saved_cart_data($lang,$request->input('saved_cart_id'));
                if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                    $my_cart_data['result']['address_list'] = get_userAddressList($this->user_id);
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                    $this->result = $my_cart_data['result'];
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

    //remove saved cart
    protected function remove_saved_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {
            $tracking_id = add_tracking_data($this->user_id, 'remove_saved_cart', $request, '');
            $saved_cart = UserSavedCartProduct::where(['fk_saved_cart_id' => (int)$request->input('saved_cart_id')])->delete();
            if($saved_cart){
                UserSavedCart::find((int)$request->input('saved_cart_id'))->delete();
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "عربة التسوق" : "My cart";
            
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

    protected function view_saved_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);
            
            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'view_saved_cart', $request, '');
            
            $saved_cart_products = UserSavedCart::join('user_saved_cart_products','user_saved_cart_products.fk_saved_cart_id','=','user_saved_cart.id')
                                ->where('user_saved_cart_products.fk_saved_cart_id',$request->input('saved_cart_id'))->get();
            
            foreach ($saved_cart_products as $key => $value) {
                $base_product = BaseProduct::where('id',$value->fk_product_id)->first();

                // Calculate total price
                $total_price = round($base_product->product_store_price * $value->quantity, 2);
                
                // If sub products for recipe
                if (isset($value->sub_products) && $value->sub_products!='') {
                    $sub_products = $value->sub_products;
                    if ($sub_products && is_array($sub_products)) {
                        foreach ($sub_products as $key => $value2) {
                            if ($value2->product_id && $value2->product_quantity) {
                                $sub_product = BaseProduct::find($value2->product_id);
                                $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                                $total_price = $total_price + $sub_product_price;
                                $value2->product_name_en = $sub_product->product_name_en;
                                $value2->product_name_ar = $sub_product->product_name_ar;
                                $value2->unit = $sub_product->unit;
                                $value2->price = $sub_product->product_store_price;
                                $value2->product_image_url = $sub_product->product_image_url;
                            }
                        }
                    }
                }
                
                $insert_saved_cart_product_arr = [
                    'fk_product_id' => $value->fk_product_id,
                    'fk_product_store_id' => $value->fk_product_store_id,
                    'fk_saved_cart_id' => (int)$request->input('saved_cart_id'),
                    'quantity' => $value->quantity,
                    'product_price' => $value->product_price == null ? ($total_price/$value->quantity) : $value->product_price,
                    'total_price' => $total_price,
                    'total_discount' => '',
                    'weight' => $base_product->unit ?? '',
                    'unit' => $base_product->unit ?? '',
                    'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                ];

                $user_saved_cart_product = UserSavedCartProduct::find($value->id);
                $user_saved_cart_product->update($insert_saved_cart_product_arr);
            }

            $my_cart_data = $this->get_my_saved_cart_data($lang,$request->input('saved_cart_id'));
            if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                $my_cart_data['result']['address_list'] = get_userAddressList($this->user_id);
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                $this->result = $my_cart_data['result'];
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

    private function get_my_saved_cart_data($lang,$saved_cart_id)
    {
        try {
            $cartItems = UserSavedCart::join('user_saved_cart_products','user_saved_cart_products.fk_saved_cart_id','=','user_saved_cart.id')
                ->select('user_saved_cart.id as saved_cart_id','user_saved_cart.total_price as total_price','user_saved_cart_products.fk_product_id','user_saved_cart_products.product_price','user_saved_cart_products.quantity','user_saved_cart_products.unit','user_saved_cart_products.weight','user_saved_cart_products.sub_products','user_saved_cart_products.total_price as product_total_price')
                ->where(['user_saved_cart.fk_user_id' => $this->user_id])
                ->where(['user_saved_cart.id' => $saved_cart_id])
                ->orderBy('user_saved_cart_products.id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000];

            $wallet_balance = get_userWallet($this->user_id);

            $cartTotal = UserSavedCart::join('user_saved_cart_products','user_saved_cart_products.fk_saved_cart_id','=','user_saved_cart.id')
                ->selectRaw("user_saved_cart.total_price as total_amount")
                ->where('user_saved_cart.fk_user_id', '=', $this->user_id)
                ->where(['user_saved_cart.id' => $saved_cart_id])
                ->first();

            $user = User::find($this->user_id);
            $delivery_cost = get_delivery_cost($user->ivp);
            $mp_amount = get_minimum_purchase_amount($user->mobile,$user->id,$user->ivp);
            $nearest_store = false;

            // For Instant model
            if ($user->ivp=='i') {
                $nearest_store = InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
                ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
                ->where(['instant_store_groups.id'=>$user->nearest_store,'instant_store_groups.deleted'=>0,'instant_store_groups.status'=>1])
                ->first();
            }
            $active_stores_arr = [];
            if ($nearest_store) {
                $active_stores = InstantStoreGroupStore::join('stores','stores.id','instant_store_group_stores.fk_store_id')
                ->where(['instant_store_group_stores.fk_group_id'=>$nearest_store->id,'stores.deleted'=>0,'stores.status'=>1, 'stores.schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            } else {
                // For Planned model
                $active_stores = Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            }

            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($user->expected_eta, $user->ivp);
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");
            // ------------------------------------------------------------------------------------------------------
            // Generate and send time slots
            // ------------------------------------------------------------------------------------------------------
            $time_slots_made = $this->getTimeSlotFromDB();
            $time_slots = $time_slots_made[0];
            $active_time_slot = $time_slots_made[1];
            $active_time_slot_value = $time_slots_made[2];
            $active_time_slot_string = $time_slots_made[3];
            
            // Get my cart data
            $h1_text = $lang=='ar' ? 'هناك تأخير بسيط لطلبك' : 'There is a slight delay for your order';
            $h2_text = $lang=='ar' ? 'قد يتأخر التسليم الخاص بك بسبب منتج معين في سلة التسوق الخاصة بك. انتظر بينما نستعد لتسليم طلبك.' : 'Your delivery may be delayed due to certain product in your cart. Hold tight as we prepare to deliver your order.';
            
            if ($cartItems->count()) {
                $message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                foreach ($cartItems as $key => $value) {
                    $base_product = BaseProduct::leftjoin('categories','categories.id','base_products.fk_sub_category_id')
                                ->select('base_products.*','categories.blocked_timeslots')
                                ->where('base_products.id','=',$value->fk_product_id)
                                ->first();
                    
                    // If sub products for recipe
                    $sub_product_arr = [];
                    $sub_product_names = "";
                    $sub_product_price = 0;
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = BaseProduct::find($value2->product_id);
                                    $sub_product_arr[] = array (
                                        'product_id' => $sub_product->id,
                                        'product_name' => $lang == 'ar' ? $sub_product->product_name_ar : $sub_product->product_name_en,
                                        'product_image' => $sub_product->product_image_url ?? '',
                                        'product_price' => (string) number_format($sub_product->product_store_price, 2),
                                        'item_unit' => $sub_product->unit
                                    );
                                    $sub_product_names .= $lang == 'ar' ? $sub_product->product_name_ar.',' : $sub_product->product_name_en.',';
                                    $sub_product_price += $sub_product->product_store_price *  $value2->product_quantity;
                                }
                            }
                        }
                    }
                    $sub_product_names = rtrim($sub_product_names,',');
                    $sub_product_price = (string) number_format($sub_product_price,2);

                    $cartItemArr[$key] = [
                        'id' => $value->saved_cart_id,
                        'product_id' => $base_product->id,
                        'type' => $base_product->product_type,
                        'parent_id' => $base_product->parent_id,
                        'recipe_id' => $base_product->recipe_id,
                        'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                        'product_image' => $base_product->product_image_url ?? '',
                        'product_price' => (string) number_format($value->product_price, 2),
                        'product_store_price' => (string) number_format($value->product_price, 2),
                        'product_total_price' => (string) round(($value->product_total_price), 2),
                        'product_price_before_discount' => (string) round($value->product_total_price, 2),
                        'cart_quantity' => $value->quantity,
                        'unit' => $base_product->unit,
                        'product_discount' => $base_product->margin,
                        'stock' => (string) $base_product->product_store_stock,
                        'item_weight' => $value->weight,
                        'item_unit' => $value->unit,
                        'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                        'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                        'min_scale' => $base_product->min_scale ?? '',
                        'max_scale' => $base_product->max_scale ?? '',
                        'sub_products' => $sub_product_arr,
                        'sub_product_names' => $sub_product_names,
                        'sub_product_price' => $sub_product_price,
                        'fk_store_id' => $base_product->fk_store_id,
                        'fk_product_store_id' => $base_product->fk_product_store_id
                    ];

                }
                
                $result = [
                    'cart_items' => array_values($cartItemArr),
                    'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                    'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch),
                    'sub_total' => (string) round($cartTotal->total_amount, 2),
                    'delivery_charge' => $delivery_cost,
                    'total_amount' => (string) ($cartTotal->total_amount + $delivery_cost),
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'store_open_time' => $store_open_time,
                    'store_close_time' => $store_close_time,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => $time_slots,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for
                ];
            } else {
                $message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $result = [
                    'cart_items' => [],
                    'cart_items_outofstock' => [],
                    'cart_items_quantitymismatch' => [],
                    'sub_total' => '0',
                    'delivery_charge' => '0',
                    'total_amount' => '0',
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => [],
                    'active_time_slot_value' => '',
                    'active_time_slot_string' => '',
                    'sub_category_ids_of_blocking_timeslots' => [],
                    'bring_change_for' => $change_for
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => []
            ]);
        }
        return ['message'=>$message, 'result'=>$result];
    }

    protected function my_saved_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {

            $savedCartItems =  DB::table('user_saved_cart')
                                ->select('user_saved_cart.*', DB::raw('COUNT(user_saved_cart_products.id) AS total_items'))
                                ->join('user_saved_cart_products', 'user_saved_cart_products.fk_saved_cart_id', '=', 'user_saved_cart.id')
                                ->where('user_saved_cart.fk_user_id', '=', $this->user_id)
                                ->groupBy('user_saved_cart_products.fk_saved_cart_id')
                                ->orderBy('user_saved_cart_products.id', 'DESC')
                                ->get();

            $savedcartItemArr = [];
            foreach($savedCartItems as $key => $value){

                if($value->total_price == null){
                    $cart_products_total_price = UserSavedCartProduct::where('fk_saved_cart_id',$value->id)->sum('total_price');
                    $cart = UserSavedCart::find($value->id);
                    $cart->update(['total_price' => $cart_products_total_price]);
                }

                $saved_cart = UserSavedCart::find($value->id);

                $savedcartItemArr[$key] = [
                    'id' => $saved_cart->id,
                    'name' => $saved_cart->name,
                    'total_items' => strval($value->total_items),
                    'total_amount' => $saved_cart->total_price,
                    'date' => \Carbon\Carbon::parse($saved_cart->updated_at)->format('d M Y'),
                    'time' => \Carbon\Carbon::parse($saved_cart->updated_at)->format('h:i A')
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = $savedcartItemArr;

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

    // Create buy it for me request
    protected function buy_it_for_me(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data($this->user_id, 'buy_it_for_me', $request, '');

            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }
            
            // Block sending to the self
            if($user->mobile==$request->input('mobile')){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"You can't request to buy it yourself");
                }
                $error_message = $lang == 'ar' ? "لا يمكنك طلب شرائها لنفسك" : "You can't request to buy it yourself";
                throw new Exception($error_message, 105);
            }

            // Check minimum purchase amount
            $cart_sub_total = UserCart::where(['fk_user_id' => $this->user_id])->sum('total_price');
            
             //Test numbners array
            $test_numbers = explode(',', env("TEST_NUMBERS"));
            $minimum_purchase = \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount')->first();
            if (in_array($user->mobile, $test_numbers)) {
                $minimum_purchase_amount = "1";
            } else {
                $minimum_purchase_amount = $minimum_purchase->value;
            }
            if ($cart_sub_total < $minimum_purchase_amount) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Minimum Order is $minimum_purchase_amount QAR");
                }
                throw new Exception($lang == 'ar' ? "الطلب الأدنى $minimum_purchase_amount ريال قطري" : "Minimum Order is $minimum_purchase_amount QAR", 105);
            }

            // Block sending more than twice a day
            // $today = date('Y-m-d 00:00:00');
            // $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d 00:00:00');
            // $today_requests_count = UserBuyItForMeRequest::where('from_user_id','=',$user->id)->where('created_at','>=',$today)->where('created_at','<',$tomorrow)->count();
            // if($today_requests_count>=2){
            //     if (isset($tracking_id) && $tracking_id) {
            //         update_tracking_response($tracking_id,"You can't request to buy more than twice a day. Please contact customer support if you need any support..");
            //     }
            //     $error_message = $lang == 'ar' ? "لا يمكنك طلب شراء أكثر من مرتين في اليوم. يرجى الاتصال بخدمة العملاء إذا كنت بحاجة إلى أي مساعدة .." : "You can't request to buy more than twice a day. Please contact customer support if you need any support..";
            //     throw new Exception($error_message, 105);
            // }
            
            // Create buy it form me request
            $to_user = User::where('mobile',$request->input('mobile'))->first();
            $request_arr = [
                'from_user_id' => $this->user_id,
                'to_user_mobile' => $request->input('mobile'),
                'status' => 0
            ];

            //Send sms to request receiver
            if($user->name){
                $msg = $user->name." has requested you to purchase the cart for them. Download the app http://onelink.to/ekg49s";
            }else{
                $msg = $user->mobile." has requested you to purchase the cart for them. Download the app http://onelink.to/ekg49s";
            }

            $phone = '974' . $request->input('mobile');

            $sent = $this->mobile_sms_curl($msg, $phone);

            if($sent){
                $create_request = UserBuyItForMeRequest::create($request_arr)->id;
                $cartItem = UserCart::where(['fk_user_id' => $this->user_id])->get();

                foreach ($cartItem as $key => $value) {
                
                    $insert_arr = [
                        'fk_request_id' => $create_request,
                        'fk_user_id' => $this->user_id,
                        'fk_product_id' => $value->fk_product_id,
                        'fk_product_store_id' => $value->fk_product_store_id,
                        'quantity' => $value->quantity,
                        'total_price' => $value->total_price,
                        'total_discount' => $value->total_discount,
                        'weight' => $value->weight ?? '',
                        'unit' => $value->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    UserBuyItForMeCart::create($insert_arr);
                }

                UserCart::where(['fk_user_id' => $this->user_id])->delete();

                

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم إرسال الطلب بنجاح" : "Request sent successfully";
                $this->result = ['request'=>$create_request];
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

    // Get buy it for me cart items
    protected function buy_it_for_me_cart(Request $request)
    {
        if ($request->header('test')==true) {
            $time_slots_made = $this->getTimeSlotFromDB();
            return response()->json([
                'error' => $this->error,
                'status_code' => 200,
                'message' => 'qa',
                'result' => $time_slots_made
            ]);
        }
        try {
            $lang = $request->header('lang');
            
            $user = User::find($this->user_id);
            
            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'buy_it_for_me_cart', $request->getRequestUri(), '');
            
            //Update buy it for me requests
            $buy_it_for_me_request = UserBuyItForMeRequest::find($request->input('id'));

            if($request->input('action') == 1){
                $buy_it_for_me_request->update(['status' => 1]);
            }else{
                $buy_it_for_me_request->update(['status' => 2]);
                UserBuyItForMeCart::where(['fk_request_id' => $request->input('id')])->delete();
            }

            $buy_it_for_me_cart = UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                                ->where('user_buy_it_for_me_cart.fk_request_id',$request->input('id'))->get();
            
            foreach ($buy_it_for_me_cart as $key => $value) {
                $base_product = BaseProduct::where('id',$value->fk_product_id)->first();

                // Calculate total price
                $total_price = round($base_product->product_store_price * $value->quantity, 2);
                
                // If sub products for recipe
                if (isset($value->sub_products) && $value->sub_products!='') {
                    $sub_products = $value->sub_products;
                    if ($sub_products && is_array($sub_products)) {
                        foreach ($sub_products as $key => $value2) {
                            if ($value2->product_id && $value2->product_quantity) {
                                $sub_product = BaseProduct::find($value2->product_id);
                                $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                                $total_price = $total_price + $sub_product_price;
                                $value2->product_name_en = $sub_product->product_name_en;
                                $value2->product_name_ar = $sub_product->product_name_ar;
                                $value2->unit = $sub_product->unit;
                                $value2->price = $sub_product->product_store_price;
                                $value2->product_image_url = $sub_product->product_image_url;
                            }
                        }
                    }
                }
                
            }

            $my_cart_data = $this->get_buy_it_for_me_data($lang,$request->input('id'));
            if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                $my_cart_data['result']['address_list'] = get_userAddressList($buy_it_for_me_request->from_user_id);
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                $this->result = $my_cart_data['result'];
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

    private function get_buy_it_for_me_data($lang,$id)
    {
        try {
            $cartItems = UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                ->where('user_buy_it_for_me_cart.fk_request_id', $id)
                ->orderBy('user_buy_it_for_me_cart.id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000];

            $wallet_balance = get_userWallet($this->user_id);

            $cartTotal = UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                    ->selectRaw("SUM(user_buy_it_for_me_cart.total_price) as total_amount")
                    ->where('user_buy_it_for_me_cart.fk_request_id', $id)
                    ->orderBy('user_buy_it_for_me_cart.id', 'desc')
                    ->first();

            $user = User::find($this->user_id);
            $nearest_store = false;
            $delivery_cost = get_delivery_cost($user->ivp,$user->mobile,$user->id);
            $mp_amount = get_minimum_purchase_amount($user->ivp,$user->mobile,$user->id);
            
            // For Instant model
            if ($user->ivp=='i') {
                $nearest_store = InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
                ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
                ->where(['instant_store_groups.id'=>$user->nearest_store,'instant_store_groups.deleted'=>0,'instant_store_groups.status'=>1])
                ->first();
            }
            $active_stores_arr = [];
            if ($nearest_store) {
                $active_stores = InstantStoreGroupStore::join('stores','stores.id','instant_store_group_stores.fk_store_id')
                ->where(['instant_store_group_stores.fk_group_id'=>$nearest_store->id,'stores.deleted'=>0,'stores.status'=>1, 'stores.schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            } else {
                // For Planned model
                $active_stores = Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            }

            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($user->expected_eta, $user->ivp);
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");
            // ------------------------------------------------------------------------------------------------------
            // Generate and send time slots
            // ------------------------------------------------------------------------------------------------------
            $time_slots_made = $this->getTimeSlotFromDB();
            $time_slots = $time_slots_made[0];
            $active_time_slot = $time_slots_made[1];
            $active_time_slot_value = $time_slots_made[2];
            $active_time_slot_string = $time_slots_made[3];
            
            // Get my cart data
            $h1_text = $lang=='ar' ? 'هناك تأخير بسيط لطلبك' : 'There is a slight delay for your order';
            $h2_text = $lang=='ar' ? 'قد يتأخر التسليم الخاص بك بسبب منتج معين في سلة التسوق الخاصة بك. انتظر بينما نستعد لتسليم طلبك.' : 'Your delivery may be delayed due to certain product in your cart. Hold tight as we prepare to deliver your order.';
            
            if ($cartItems->count()) {
                $message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                foreach ($cartItems as $key => $value) {
                    $base_product = BaseProduct::leftjoin('categories','categories.id','base_products.fk_sub_category_id')
                                ->select('base_products.*','categories.blocked_timeslots')
                                ->where('base_products.id','=',$value->fk_product_id)
                                ->first();
                    
                    // If sub products for recipe
                    $sub_product_arr = [];
                    $sub_product_names = "";
                    $sub_product_price = 0;
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = BaseProduct::find($value2->product_id);
                                    $sub_product_arr[] = array (
                                        'product_id' => $sub_product->id,
                                        'product_name' => $lang == 'ar' ? $sub_product->product_name_ar : $sub_product->product_name_en,
                                        'product_image' => $sub_product->product_image_url ?? '',
                                        'product_price' => (string) number_format($sub_product->product_store_price, 2),
                                        'item_unit' => $sub_product->unit
                                    );
                                    $sub_product_names .= $lang == 'ar' ? $sub_product->product_name_ar.',' : $sub_product->product_name_en.',';
                                    $sub_product_price += $sub_product->product_store_price *  $value2->product_quantity;
                                }
                            }
                        }
                    }
                    $sub_product_names = rtrim($sub_product_names,',');
                    $sub_product_price = (string) number_format($sub_product_price,2);
                    // Checking stocks
                    // For retailmart company id = 2
                    if ($base_product->product_store_stock > 0 && $base_product->product_store_stock < $value->quantity) {
                        $cartItemQuantityMismatch[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                    // For other companies
                    elseif ($base_product->product_store_stock > 0 && $base_product->deleted == 0) {
                        $cartItemArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    } else {
                        $cartItemOutofstockArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                }
                
                $result = [
                    'cart_items' => array_values($cartItemArr),
                    'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                    'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch),
                    'sub_total' => (string) round($cartTotal->total_amount, 2),
                    'delivery_charge' => $delivery_cost,
                    'total_amount' => (string) ($cartTotal->total_amount + $delivery_cost),
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'store_open_time' => $store_open_time,
                    'store_close_time' => $store_close_time,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => $time_slots,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for
                ];
            } else {
                $message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $result = [
                    'cart_items' => [],
                    'cart_items_outofstock' => [],
                    'cart_items_quantitymismatch' => [],
                    'sub_total' => '0',
                    'delivery_charge' => '0',
                    'total_amount' => '0',
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => [],
                    'active_time_slot_value' => '',
                    'active_time_slot_string' => '',
                    'sub_category_ids_of_blocking_timeslots' => [],
                    'bring_change_for' => $change_for
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => []
            ]);
        }
        return ['message'=>$message, 'result'=>$result];
    }

    // Read buy it for me requests accept or declined response
    protected function read_buy_it_for_me_request_status(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data($this->user_id, 'buy_it_for_me_read_request_status', $request, '');

            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }
        
            $request = UserBuyItForMeRequest::find($request->input('id'));
            $request->update(['is_read' => 1]);

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = [];
        
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

    // buy it for me received requests
    protected function buy_it_for_me_received_requests(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data($this->user_id, 'buy_it_for_me_received_requests', $request, '');

            $user = User::find($this->user_id);
            // If user not found, send session expired message
            if(!$user){
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'Your session expired. Please login again..');
                }
                $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                throw new Exception($error_message, 105);
            }

            $buy_it_for_me_request_status_arr = [
                ['status' => 0, 'message' => 'pending'],
                ['status' => 1, 'message' => 'accepted'],
                ['status' => 2, 'message' => 'declined'],
                ['status' => 3, 'message' => 'bought'],
            ];
        
            $received_requests = UserBuyItForMeRequest::where('to_user_mobile',$user->mobile)->get();
            $received_requests_arr = [];
            foreach($received_requests as $key => $value){

                $received_from = User::find($value->from_user_id);
                $received_requests_arr[] = [
                    'id' => $value->id,
                    'received_from' => $received_from->name,
                    'received_date' =>  \Carbon\Carbon::parse($value->updated_at)->format('d M Y'),
                    'status' => $value->status
                ];
            }

            $result[''] = 

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = ['request_status' => $buy_it_for_me_request_status_arr, 'received_requests' => $received_requests_arr];
        
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

     // buy it for me sent requests
     protected function buy_it_for_me_sent_requests(Request $request)
     {
         try {
             $lang = $request->header('lang');
             $tracking_id = add_tracking_data($this->user_id, 'buy_it_for_me_sent_requests', $request, '');
 
             $user = User::find($this->user_id);
             // If user not found, send session expired message
             if(!$user){
                 if (isset($tracking_id) && $tracking_id) {
                     update_tracking_response($tracking_id,'Your session expired. Please login again..');
                 }
                 $error_message = $lang == 'ar' ? "انتهت صلاحية دخولك للحساب. الرجاء تسجيل الدخول من جديد" : "Your session expired. Please login again..";
                 throw new Exception($error_message, 105);
             }
 
             $buy_it_for_me_request_status_arr = [
                 ['status' => 0, 'message' => 'pending'],
                 ['status' => 1, 'message' => 'accepted'],
                 ['status' => 2, 'message' => 'declined'],
                 ['status' => 3, 'message' => 'bought'],
             ];
         
             $sent_requests = UserBuyItForMeRequest::where('from_user_id',$this->user_id)->get();
             $sent_requests_arr = [];
             foreach($sent_requests as $key => $value){
  
                 $sent_to = User::where('mobile',$value->to_user_mobile)->first();
                 $sent_requests_arr[] = [
                     'id' => $value->id,
                     'sent_to' => $sent_to->name ?? $sent_to->mobile,
                     'sent_date' =>  \Carbon\Carbon::parse($value->updated_at)->format('d M Y'),
                     'status' => $value->status
                 ];
             }
 
             $result[''] = 
 
             $this->error = false;
             $this->status_code = 200;
             $this->message = $lang == 'ar' ? "النجاح" : "Success";
             $this->result = ['request_status' => $buy_it_for_me_request_status_arr, 'sent_requests' => $sent_requests_arr];
         
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
}
