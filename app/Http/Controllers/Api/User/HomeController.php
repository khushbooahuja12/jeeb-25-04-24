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
use App\Model\UserTracking;
use App\Model\DeliverySlotSetting;
use App\Model\UserSavedCart;
use App\Model\UserSavedCartProduct;

class HomeController extends CoreApiController
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
            'add_to_cart', 'remove_from_cart', 'my_cart', 'save_cart', 'update_saved_cart', 'remove_saved_cart', 'add_favorite', 'home_static', 'home_personalized',
            'check_delivery_area', 'get_cart_count', 'update_cart', 'remove_outofstock_products_from_cart'
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

    protected function home1(Request $request)
    {
        try {
            $google_api_key = base64_encode('AIzaSyBLOYhy8ToGr9KkZ25UyAGIdLbOUHvwxL4');

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
            }

            if ($user_id != '') {
                $user = User::find($user_id);
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store = $nearest_store->id;
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                    $delivery_time = "delivery in " . getDeliveryIn($user->expected_eta);
                } else {
                    $nearest_store = Store::select('id', 'name', 'company_id')
                        ->selectRaw("(
                                        6371 * ACOS(
                                            COS(RADIANS('" . $latitude . "')) * COS(
                                                RADIANS(latitude)
                                            ) * COS(
                                                RADIANS(longitude) - RADIANS('" . $longitude . "')
                                            ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                                RADIANS(latitude)
                                            )
                                        )
                                    ) AS distance")
                        ->where('deleted', '=', 0)
                        ->where('status', '=', 1)
                        ->orderBy('distance', 'asc')
                        ->first();
                    $default_store = $nearest_store->id;
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                    $delivery_time = "delivery in " . ($guest_user_eta ? getDeliveryIn($guest_user_eta) : '50 minutes');
                    $user = User::find($user_id)->update(['nearest_store'=>$default_store]);
                }
            } else {
                $nearest_store = Store::select('id', 'name', 'company_id')
                    ->selectRaw("(
                                    6371 * ACOS(
                                        COS(RADIANS('" . $latitude . "')) * COS(
                                            RADIANS(latitude)
                                        ) * COS(
                                            RADIANS(longitude) - RADIANS('" . $longitude . "')
                                        ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                            RADIANS(latitude)
                                        )
                                    )
                                ) AS distance")
                    ->where('deleted', '=', 0)
                    ->where('status', '=', 1)
                    ->orderBy('distance', 'asc')
                    ->first();
                $default_store = $nearest_store->id;
                $default_store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
                $delivery_time = "delivery in " . ($guest_user_eta ? getDeliveryIn($guest_user_eta) : '50 minutes');
            }

            $categories = Category::where('deleted', '=', 0)
                ->where('parent_id', '=', 0)
                // ->where('id', '!=', 16)
                ->orderBy('category_name_en', 'asc')
                ->get();
            $category_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $row) {
                    $sub_category_arr = [];
                    if (count($row->getSubCategory)) {
                        foreach ($row->getSubCategory as $key1 => $row1) {
                            $sub_category_arr[$key1] = [
                                'id' => $row1->id,
                                'category_name' => $lang == 'ar' ? $row1->category_name_ar : $row1->category_name_en,
                                'category_image' => !empty($row1->getCategoryImage) ? asset('images/category_images') . '/' . $row1->getCategoryImage->file_name : '',
                                'category_image2' => !empty($row1->getCategoryImage2) ? asset('images/category_images') . '/' . $row1->getCategoryImage2->file_name : ''
                            ];
                        }
                    }

                    $category_arr[$key] = [
                        'id' => $row->id,
                        'category_name' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                        'category_image' => !empty($row->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategoryImage->file_name : '',
                        'category_image2' => !empty($row->getCategoryImage2) ? asset('images/category_images') . '/' . $row->getCategoryImage2->file_name : '',
                        'is_highlighted' => $row->is_home_screen,
                        'subcategories' => $sub_category_arr
                    ];
                }
            }

            $brands = Brand::where('is_home_screen', '=', 1)
                ->where('deleted', '=', 0)
                ->orderBy('brand_name_en', 'asc')
                //                ->limit(12)
                ->get();
            $brand_arr = [];
            if ($brands->count()) {
                foreach ($brands as $key => $row) {
                    $brand_arr[$key] = [
                        'id' => $row->id,
                        'brand_name' => $lang == 'ar' ? $row->brand_name_ar : $row->brand_name_en,
                        'brand_image' => !empty($row->getBrandImage) ? asset('images/brand_images') . '/' . $row->getBrandImage->file_name : '',
                        'brand_image2' => !empty($row->getBrandImage2) ? asset('images/brand_images') . '/' . $row->getBrandImage2->file_name : ''
                    ];
                }
            }

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
            $keyArr = ['algolia_index_name', 'under_maintenance', 'maintenance_title', 'maintenance_desc', 'user_ids'];
            $adminSetting = \App\Model\AdminSetting::whereIn('key', $keyArr)->get()->toArray();
            $arrSettingArr = [];
            foreach ($adminSetting as $value) {
                $arrSettingArr[$value['key']] = $value['value'];
            }


            if ($user_id != '') {
                    
                $order_products = \App\Model\OrderProduct::join('orders', 'order_products.fk_order_id', '=', 'orders.id')
                ->join($this->products_table, $this->products_table . '.id', '=', 'order_products.fk_product_id')
                ->join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                ->leftJoin('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                ->select(
                    $this->products_table . '.*',
                    'categories.id as category_id',
                    'categories.category_name_en',
                    'categories.category_name_ar',
                    'brands.id as brand_id',
                    'brands.brand_name_en',
                    'brands.brand_name_ar'
                )
                ->where('orders.fk_user_id', '=', $user_id)
                ->where('orders.fk_store_id', '=', $default_store)
                ->where($this->products_table . '.parent_id', '=', 0)
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.store' . $default_store_no, '=', 1)
                ->orderBy('order_products.created_at', 'desc')
                ->groupBy('order_products.fk_product_id')
                ->limit(10)
                ->get();

                $recent_orders_arr = get_product_dictionary($order_products, $user_id, $lang, $request->header('Authorization'));

            } else {

                $recent_orders_arr = [];

            }

            $records = Homepage::where('index', '<=', 3)->orderBy('index', 'asc')->get();
            $dynamic_contents_arr = [];
            if ($records->count()) {
                foreach ($records as $key => $value) {
                    $dynamic_contents_arr[$key] = [
                        'id' => $value->id,
                        'index' => $value->index,
                        'ui_type' => $value->ui_type,
                        'banner_type' => $value->banner_type ?? '',
                        'title' => $value->title ?? '',
                        'background_color' => $value->background_color ?? '',
                        'background_image' => $value->getBackgroundImage ? asset('/') . $value->getBackgroundImage->file_path . $value->getBackgroundImage->file_name : ''
                    ];

                    $homepage_data_arr = [];
                    if (!empty($value->getHomepageData)) {
                        foreach ($value->getHomepageData as $keyN => $valueN) {
                            if ($value->ui_type == 1) {
                                $homepage_data_arr[$keyN] = [
                                    'id' => $valueN->id,
                                    'title' => $valueN->title ?? '',
                                    'image' => $valueN->getImage ? url('/') . $valueN->getImage->file_path . $valueN->getImage->file_name : '',
                                    'image2' => $valueN->getImage2 ? url('/') . $valueN->getImage2->file_path . $valueN->getImage2->file_name : '',
                                    'keyword' => $valueN->keyword,
                                    'redirection_type' => $valueN->redirection_type
                                ];
                            } elseif ($value->ui_type == 2) {
                                $home_products = Product::join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                                    ->leftJoin('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        $this->products_table . '.*',
                                        'categories.id as category_id',
                                        'categories.category_name_en',
                                        'categories.category_name_ar',
                                        'brands.id as brand_id',
                                        'brands.brand_name_en',
                                        'brands.brand_name_ar'
                                    )
                                    ->where($this->products_table . '.id', '=', $valueN->fk_product_id)
                                    ->where($this->products_table . '.deleted', '=', 0)
                                    ->where($this->products_table . '.fk_company_id', '=', $company_id)
                                    ->where($this->products_table . '.store' . $default_store_no, '>', 0)
                                    ->groupBy($this->products_table . '.id')
                                    ->limit(10)
                                    ->get();

                                if ($home_products->count()) {
                                    $products = get_product_dictionary($home_products, $user_id, $lang, $request->header('Authorization'));

                                    $homepage_data_arr[$keyN] = $products ? $products[0] : (object) [];
                                }
                            }
                        }
                    }

                    if ($value->ui_type == 3) {
                        $homepage_data_arr = $category_arr;
                    } elseif ($value->ui_type == 4) {
                        $homepage_data_arr = $brand_arr;
                    } elseif ($value->ui_type == 5) {
                        $homepage_data_arr = $classification_arr;
                    } elseif ($value->ui_type == 6) {
                        $homepage_data_arr = $recent_orders_arr;
                    }

                    if ($value->ui_type == 1) {
                        $insideKey = 'data';
                    } elseif ($value->ui_type == 2) {
                        $insideKey = 'product';
                    } elseif ($value->ui_type == 3) {
                        $insideKey = 'categories';
                    } elseif ($value->ui_type == 4) {
                        $insideKey = 'featured_brands';
                    } elseif ($value->ui_type == 5) {
                        $insideKey = 'extra_goodies';
                    } elseif ($value->ui_type == 6) {
                        $insideKey = 'recent_orders';
                    }

                    $dynamic_contents_arr[$key][$insideKey] = array_values($homepage_data_arr);
                }
            }

            if (!empty($user_id)) {
                $active_order = Order::join('order_delivery_slots', 'orders.id', '=', 'order_delivery_slots.fk_order_id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->select('orders.*', 'order_delivery_slots.delivery_date', 'order_delivery_slots.delivery_slot', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference', 'order_delivery_slots.expected_eta')
                    ->where('fk_user_id', '=', $user_id)
                    ->whereIn('orders.status', array(0, 1, 2, 3, 6))
                    ->where('order_payments.status', '!=', 'rejected')
                    ->orderByRaw("CONCAT(delivery_date,' ',LEFT(delivery_slot , 5))")
                    ->get();
                $active_order_arr = [];
                if ($active_order->count()) {
                    foreach ($active_order as $key => $value) {
                        $active_order_arr[$key] = [
                            'id' => $value->id,
                            'orderId' => $value->orderId,
                            'sub_total' => $value->sub_total,
                            'total_amount' => $value->total_amount,
                            'delivery_charge' => $value->delivery_charge,
                            'coupon_discount' => $value->coupon_discount ?? '',
                            'item_count' => $value->getOrderProducts->count(),
                            'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                            'status' => $value->status,
                            'change_for' => $value->change_for ?? '',
                            'delivery_date' => $value->delivery_date,
                            'delivery_in' => getDeliveryIn($value->expected_eta),
                            'delivery_time' => $value->delivery_time,
                            'later_time' => $value->later_time,
                            'delivery_preference' => $value->delivery_preference
                        ];
                    }
                }
                // $active_order_arr = $active_order ? [
                //     'id' => $active_order->id,
                //     'orderId' => $active_order->orderId,
                //     'sub_total' => $active_order->sub_total,
                //     'total_amount' => $active_order->total_amount,
                //     'delivery_charge' => $active_order->delivery_charge,
                //     'coupon_discount' => $active_order->coupon_discount ?? '',
                //     'item_count' => $active_order->getOrderProducts->count(),
                //     'order_time' => date('Y-m-d H:i:s', strtotime($active_order->created_at)),
                //     'status' => $active_order->status,
                //     'change_for' => $active_order->change_for ?? '',
                //     'delivery_date' => $active_order->delivery_date,
                //     'delivery_in' => getDeliveryIn($active_order->expected_eta),
                //     'delivery_time' => $active_order->delivery_time,
                //     'later_time' => $active_order->later_time,
                //     'delivery_preference' => $active_order->delivery_preference
                // ] : (object) [];
            } else {
                $active_order_arr = [];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'privacy_policy' => url('/') . '/privacy_policy',
                'terms' => url('/') . '/terms',
                'google_api_key' => $google_api_key,
                'algolia_index_name' => $arrSettingArr['algolia_index_name'],
                // 'under_maintenance' => $user_id != '' && in_array($user_id, explode(',', $arrSettingArr['user_ids'])) ? false : filter_var($arrSettingArr['under_maintenance'], FILTER_VALIDATE_BOOLEAN),
                'under_maintenance' => true,
                'maintenance_title' => $arrSettingArr['maintenance_title'],
                'maintenance_desc' => $arrSettingArr['maintenance_desc'],
                'ios_version' => '2',
                'android_version' => '11',
                'dynamic_contents' => $dynamic_contents_arr,
                'active_order' => $active_order_arr,
                'delivery_time' => $delivery_time,
                'store' => get_store_no_string($default_store_no),
                'company_id' => $company_id,
                'store_id' => $default_store,
                'store_open_time' => '09:00',
                'store_close_time' => '21:00'
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

    protected function home2(Request $request)
    {
        try {
            $google_api_key = base64_encode('AIzaSyBLOYhy8ToGr9KkZ25UyAGIdLbOUHvwxL4');

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
            }

            if ($user_id != '') {
                $user = User::find($user_id);
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store = $nearest_store->id;
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                    $delivery_time = "delivery in " . getDeliveryIn($user->expected_eta);
                } else {
                    $nearest_store = Store::select('id', 'name', 'company_id')
                        ->selectRaw("(
                                        6371 * ACOS(
                                            COS(RADIANS('" . $latitude . "')) * COS(
                                                RADIANS(latitude)
                                            ) * COS(
                                                RADIANS(longitude) - RADIANS('" . $longitude . "')
                                            ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                                RADIANS(latitude)
                                            )
                                        )
                                    ) AS distance")
                        ->where('deleted', '=', 0)
                        ->where('status', '=', 1)
                        ->orderBy('distance', 'asc')
                        ->first();
                    $default_store = $nearest_store->id;
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                    $delivery_time = "delivery in " . ($guest_user_eta ? getDeliveryIn($guest_user_eta) : '50 minutes');
                    $user = User::find($user_id)->update(['nearest_store'=>$default_store]);
                }
            } else {
                $nearest_store = Store::select('id', 'name', 'company_id')
                    ->selectRaw("(
                                    6371 * ACOS(
                                        COS(RADIANS('" . $latitude . "')) * COS(
                                            RADIANS(latitude)
                                        ) * COS(
                                            RADIANS(longitude) - RADIANS('" . $longitude . "')
                                        ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                            RADIANS(latitude)
                                        )
                                    )
                                ) AS distance")
                    ->where('deleted', '=', 0)
                    ->where('status', '=', 1)
                    ->orderBy('distance', 'asc')
                    ->first();
                $default_store = $nearest_store->id;
                $default_store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
                $delivery_time = "delivery in " . ($guest_user_eta ? getDeliveryIn($guest_user_eta) : '50 minutes');
            }

            $categories = Category::where('deleted', '=', 0)
                ->where('parent_id', '=', 0)
                // ->where('id', '!=', 16)
                ->orderBy('category_name_en', 'asc')
                ->get();
            $category_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $row) {
                    $sub_category_arr = [];
                    if (count($row->getSubCategory)) {
                        foreach ($row->getSubCategory as $key1 => $row1) {
                            $sub_category_arr[$key1] = [
                                'id' => $row1->id,
                                'category_name' => $lang == 'ar' ? $row1->category_name_ar : $row1->category_name_en,
                                'category_image' => !empty($row1->getCategoryImage) ? asset('images/category_images') . '/' . $row1->getCategoryImage->file_name : '',
                                'category_image2' => !empty($row1->getCategoryImage2) ? asset('images/category_images') . '/' . $row1->getCategoryImage2->file_name : ''
                            ];
                        }
                    }

                    $category_arr[$key] = [
                        'id' => $row->id,
                        'category_name' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                        'category_image' => !empty($row->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategoryImage->file_name : '',
                        'category_image2' => !empty($row->getCategoryImage2) ? asset('images/category_images') . '/' . $row->getCategoryImage2->file_name : '',
                        'is_highlighted' => $row->is_home_screen,
                        'subcategories' => $sub_category_arr
                    ];
                }
            }

            $brands = Brand::where('is_home_screen', '=', 1)
                ->where('deleted', '=', 0)
                ->orderBy('brand_name_en', 'asc')
                //                ->limit(12)
                ->get();
            $brand_arr = [];
            if ($brands->count()) {
                foreach ($brands as $key => $row) {
                    $brand_arr[$key] = [
                        'id' => $row->id,
                        'brand_name' => $lang == 'ar' ? $row->brand_name_ar : $row->brand_name_en,
                        'brand_image' => !empty($row->getBrandImage) ? asset('images/brand_images') . '/' . $row->getBrandImage->file_name : '',
                        'brand_image2' => !empty($row->getBrandImage2) ? asset('images/brand_images') . '/' . $row->getBrandImage2->file_name : ''
                    ];
                }
            }

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
            $keyArr = ['algolia_index_name', 'under_maintenance', 'maintenance_title', 'maintenance_desc'];
            $adminSetting = \App\Model\AdminSetting::whereIn('key', $keyArr)->get()->toArray();
            $arrSettingArr = [];
            foreach ($adminSetting as $value) {
                $arrSettingArr[$value['key']] = $value['value'];
            }


            if ($user_id!='') {

                $order_products = \App\Model\OrderProduct::join('orders', 'order_products.fk_order_id', '=', 'orders.id')
                    ->join($this->products_table, $this->products_table . '.id', '=', 'order_products.fk_product_id')
                    ->join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                    ->leftJoin('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                    ->select(
                        $this->products_table . '.*',
                        'categories.id as category_id',
                        'categories.category_name_en',
                        'categories.category_name_ar',
                        'brands.id as brand_id',
                        'brands.brand_name_en',
                        'brands.brand_name_ar'
                    )
                    ->where('orders.fk_user_id', '=', $user_id)
                    ->where('orders.fk_store_id', '=', $default_store)
                    ->where($this->products_table . '.parent_id', '=', 0)
                    ->where($this->products_table . '.deleted', '=', 0)
                    ->where($this->products_table . '.store' . $default_store_no, '>', 0)
                    ->orderBy('order_products.created_at', 'desc')
                    ->groupBy('order_products.fk_product_id')
                    ->limit(10)
                    ->get();

                $recent_orders_arr = get_product_dictionary($order_products, $user_id, $lang, $request->header('Authorization'));
                
            } else {

                $recent_orders_arr = [];

            }

            $records = Homepage::where('index', '>', 3)->orderBy('index', 'asc')->get();
            $dynamic_contents_arr = [];
            if ($records->count()) {
                foreach ($records as $key => $value) {
                    $dynamic_contents_arr[$key] = [
                        'id' => $value->id,
                        'index' => $value->index,
                        'ui_type' => $value->ui_type,
                        'banner_type' => $value->banner_type ?? '',
                        'title' => $value->title ?? '',
                        'background_color' => $value->background_color ?? '',
                        'background_image' => $value->getBackgroundImage ? asset('/') . $value->getBackgroundImage->file_path . $value->getBackgroundImage->file_name : ''
                    ];

                    $homepage_data_arr = [];
                    if (!empty($value->getHomepageData)) {
                        foreach ($value->getHomepageData as $keyN => $valueN) {
                            if ($value->ui_type == 1) {
                                $homepage_data_arr[$keyN] = [
                                    'id' => $valueN->id,
                                    'title' => $valueN->title ?? '',
                                    'image' => $valueN->getImage ? url('/') . $valueN->getImage->file_path . $valueN->getImage->file_name : '',
                                    'image2' => $valueN->getImage2 ? url('/') . $valueN->getImage2->file_path . $valueN->getImage2->file_name : '',
                                    'keyword' => $valueN->keyword,
                                    'redirection_type' => $valueN->redirection_type
                                ];
                            } elseif ($value->ui_type == 2) {
                                $home_products = Product::join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                                    ->leftJoin('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        $this->products_table . '.*',
                                        'categories.id as category_id',
                                        'categories.category_name_en',
                                        'categories.category_name_ar',
                                        'brands.id as brand_id',
                                        'brands.brand_name_en',
                                        'brands.brand_name_ar'
                                    )
                                    ->where($this->products_table . '.id', '=', $valueN->fk_product_id)
                                    ->where($this->products_table . '.deleted', '=', 0)
                                    ->where($this->products_table . '.fk_company_id', '=', $company_id)
                                    ->where($this->products_table . '.store' . $default_store_no, '>', 0)
                                    ->groupBy($this->products_table . '.id')
                                    ->limit(10)
                                    ->get();

                                if ($home_products->count()) {
                                    $products = get_product_dictionary($home_products, $user_id, $lang, $request->header('Authorization'));

                                    $homepage_data_arr[$keyN] = $products ? $products[0] : (object) [];
                                }
                            }
                        }
                    }
                    if ($value->ui_type == 3) {
                        $homepage_data_arr = $category_arr;
                    } elseif ($value->ui_type == 4) {
                        $homepage_data_arr = $brand_arr;
                    } elseif ($value->ui_type == 5) {
                        $homepage_data_arr = $classification_arr;
                    } elseif ($value->ui_type == 6 && count($recent_orders_arr) != 0) {
                        $homepage_data_arr = $recent_orders_arr;
                    }

                    if ($value->ui_type == 1) {
                        $insideKey = 'data';
                    } elseif ($value->ui_type == 2) {
                        $insideKey = 'product';
                    } elseif ($value->ui_type == 3) {
                        $insideKey = 'categories';
                    } elseif ($value->ui_type == 4) {
                        $insideKey = 'featured_brands';
                    } elseif ($value->ui_type == 5) {
                        $insideKey = 'extra_goodies';
                    } elseif ($value->ui_type == 6 && count($recent_orders_arr) != 0) {
                        $insideKey = 'recent_orders';
                    }
                    $dynamic_contents_arr[$key][$insideKey] = array_values($homepage_data_arr);
                }
            }


            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'privacy_policy' => url('/') . '/privacy_policy',
                'terms' => url('/') . '/terms',
                'google_api_key' => $google_api_key,
                'algolia_index_name' => $arrSettingArr['algolia_index_name'],
                'under_maintenance' => filter_var($arrSettingArr['under_maintenance'], FILTER_VALIDATE_BOOLEAN),
                'maintenance_title' => $arrSettingArr['maintenance_title'],
                'maintenance_desc' => $arrSettingArr['maintenance_desc'],
                'ios_version' => '2',
                'android_version' => '11',
                'dynamic_contents' => $dynamic_contents_arr,
                'delivery_time' => $delivery_time,
                'store' => get_store_no_string($default_store_no),
                'company_id' => $company_id,
                'store_id' => $default_store,
                'store_open_time' => '09:00',
                'store_close_time' => '21:00'
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

            $lang = $request->header('lang');
            $user_lang_updated = false;

            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                    $user_lang_updated = $user_id && $user_id!='' ? User::find($auth->user_id)->update(['lang_preference'=>$lang]) : false;
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
            }

            if ($user_id!='') {

                $user = User::find($user_id);
                $store = Store::find($user->nearest_store);
                $store_id = $store->id;

                $store_no = get_store_no($store->name);
                $company_id = $store->company_id;

                $order_products = false;
                if ($user && $store) {
                    
                    $order_products = \App\Model\OrderProduct::join('orders', 'order_products.fk_order_id', '=', 'orders.id')
                        ->join($this->products_table, $this->products_table . '.id', '=', 'order_products.fk_product_id')
                        ->leftJoin('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                        ->leftJoin('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                        ->select(
                            $this->products_table . '.*',
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
                        ->where('orders.fk_store_id', '=', $store_id)
                        ->where('orders.status', '=', 7)
                        ->where($this->products_table . '.parent_id', '=', 0)
                        ->where($this->products_table . '.deleted', '=', 0)
                        ->where($this->products_table . '.store' . $store_no, '>', 0)
                        ->where($this->products_table . '.fk_company_id', '=', $company_id)
                        ->orderBy('order_products.id', 'desc')
                        ->groupBy('order_products.fk_product_id')
                        ->limit(10)
                        ->get();
                    
                }
                $recent_orders_arr = get_product_dictionary($order_products, $user_id, $lang, $request->header('Authorization'));
                
                // Active Orders
                $active_order = Order::join('order_delivery_slots', 'orders.id', '=', 'order_delivery_slots.fk_order_id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->select('orders.*', 'order_delivery_slots.delivery_date', 'order_delivery_slots.delivery_slot', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference', 'order_delivery_slots.expected_eta')
                    ->where('fk_user_id', '=', $user_id)
                    ->whereIn('orders.status', array(0, 1, 2, 3, 6))
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->orderByRaw("CONCAT(delivery_date,' ',LEFT(later_time,LOCATE('-',later_time) - 1))")
                    ->get();
                    
                $active_orders_arr = [];
                if ($active_order->count()) {
                    foreach ($active_order as $key => $value) {
                        $store = Store::where(['id' => $value->fk_store_id, 'status' => 1])->first();
                        $store_id = $value->fk_store_id;
                        $store_no = 0;
                        $company_id = 0;
                        // $active_orders_arr[$key] = [
                        //     'store_id' => $store_id
                        // ];
                        if ($value->delivery_time==1) {
                            $delivery_in = getDeliveryInTimeRange($value->expected_eta, $value->created_at, $lang);
                        } else {
                            // Get the sheduled earliest time
                            $later_time = strtok($value->later_time, '-');
                            $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                            $later_delivery_time = $value->delivery_date.' '.$later_time.':00';
                            $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang, $value->later_time);
                        }
                        if ($store) {
                            $store_no = get_store_no($store->name);
                            $company_id = $store->company_id;
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
                                'change_for' => $value->change_for ?? '',
                                'delivery_date' => $value->delivery_date,
                                'delivery_in' => $delivery_in,
                                // 'delivery_in' => ($value->delivery_time==1) ? getDeliveryInTimeRange($value->expected_eta, $value->created_at, $lang) : date("dS M H:i", strtotime($value->delivery_date.' '.substr($value->later_time, 0, 5).':00')),
                                // 'delivery_in' => getDeliveryIn($value->expected_eta),
                                'delivery_time' => $value->delivery_time,
                                'later_time' => $value->later_time,
                                'delivery_preference' => $value->delivery_preference,
                                'store_id' => $store_id,
                                'store' => get_store_no_string($store_no),
                                'company_id' => $company_id
                            ];
                        } 
                    }
                }

            } else {

                $active_orders_arr = [];
                $recent_orders_arr = [];

            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "النجاح" : "Success";
            $this->result = [
                'active_orders' => $active_orders_arr,
                'recent_orders' => $recent_orders_arr,
                'google_api_key' => $google_api_key,
                'google_api_key_enc' => $google_api_key_enc,
                'payment_key' => $payment_key,
                'algolia_key' => $algolia_key,
                'algolia_id' => $algolia_id,
                'algolia_index' => $algolia_index,
                'user_lang_updated' => $user_lang_updated
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

    protected function home_static_1(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            $home_static = \App\Model\HomeStatic::where('lang','=',$lang)->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
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

    private function get_my_cart_data($lang)
    {
        try {
            $cartItems = UserCart::where(['fk_user_id' => $this->user_id])
                ->orderBy('id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000];

            $delivery_cost = get_delivery_cost();
            $mp_amount = get_minimum_purchase_amount();
            $wallet_balance = get_userWallet($this->user_id);

            $cartTotal = UserCart::selectRaw("SUM(total_price) as total_amount")
                ->where('fk_user_id', '=', $this->user_id)
                ->first();

            $user = User::find($this->user_id);
            $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
            if ($nearest_store) {
                $default_store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
                $delivery_time = "delivery in " . getDeliveryIn($user->expected_eta);
            } else {
                UserCart::where(['fk_user_id' => $this->user_id])->delete();   
                $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                throw new Exception($error_message, 106);
            }
            
            $store = 'store' . $default_store_no;
            $store_price = 'store' . $default_store_no . '_price';
            
            // Get my cart data
            $h1_text = $lang=='ar' ? 'هناك تأخير بسيط لطلبك' : 'There is a slight delay for your order';
            $h2_text = $lang=='ar' ? 'قد يتأخر التسليم الخاص بك بسبب منتج معين في سلة التسوق الخاصة بك. انتظر بينما نستعد لتسليم طلبك.' : 'Your delivery may be delayed due to certain product in your cart. Hold tight as we prepare to deliver your order.';
            $blocked_timeslots = 0;
            if ($cartItems->count()) {
                $message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                foreach ($cartItems as $key => $value) {
                    $product = Product::leftjoin('categories','categories.id',$this->products_table.'.fk_sub_category_id')
                                ->select($this->products_table.'.*','categories.blocked_timeslots')
                                ->where($this->products_table.'.id','=',$value->fk_product_id)
                                ->first();

                    // Check if timeslot is blocked for the sub category
                    if ($product->blocked_timeslots > $blocked_timeslots) {
                        $blocked_timeslots = $product->blocked_timeslots;
                    }
                    
                    // If products are not available
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 106);
                    }
                    // If products are not belongs to the company
                    if ($product->product_type=='product' && $product->fk_company_id != $company_id) {
                        UserCart::where(['fk_user_id' => $this->user_id])->delete();       
                        $error_message = $lang == 'ar' ? 'تم تحديث العنوان بنجاح' : 'Location Succesfully changed';
                        throw new Exception($error_message, 106);
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
                                    $sub_product = Product::find($value2->product_id);
                                    $sub_product_arr[] = array (
                                        'product_id' => $sub_product->id,
                                        'product_name' => $lang == 'ar' ? $sub_product->product_name_ar : $sub_product->product_name_en,
                                        'product_image' => $sub_product->product_image_url ?? '',
                                        'product_price' => (string) number_format($sub_product->$store_price, 2),
                                        'item_unit' => $sub_product->unit
                                    );
                                    $sub_product_names .= $lang == 'ar' ? $sub_product->product_name_ar.',' : $sub_product->product_name_en.',';
                                    $sub_product_price += $sub_product->$store_price *  $value2->product_quantity;
                                }
                            }
                        }
                    }
                    $sub_product_names = rtrim($sub_product_names,',');
                    $sub_product_price = (string) number_format($sub_product_price,2);
                    // Checking stocks
                    // For retailmart company id = 2
                    if ($product->fk_company_id == env("RETAILMART_ID") && $product->$store > 0 && $product->$store < $value->quantity) {
                        $cartItemQuantityMismatch[$key] = [
                            'id' => $value->id,
                            'product_id' => $product->id,
                            'type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->$store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->$store,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                    // For other companies
                    elseif ($product->$store > 0) {
                        $cartItemArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $product->id,
                            'type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->$store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->$store,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    } else {
                        $cartItemOutofstockArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $product->id,
                            'type' => $product->product_type,
                            'parent_id' => $product->parent_id,
                            'recipe_id' => $product->recipe_id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->$store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->$store,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                            'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                }
                // ------------------------------------------------------------------------------------------------------
                // Check store closed or not, if closed show the delivery time within the opening time slot
                // ------------------------------------------------------------------------------------------------------
                $delivery_time = get_delivery_time_in_text($user->expected_eta);
                $store_open_time = env("STORE_OPEN_TIME");
                $store_close_time = env("STORE_CLOSE_TIME");
                // ------------------------------------------------------------------------------------------------------
                // Get Sub Category IDs of blocking timeslots
                // ------------------------------------------------------------------------------------------------------
                $sub_category_ids_of_blocking_timeslots = Category::select('id','category_name_en','category_name_ar','blocked_timeslots')->where('blocked_timeslots','>',0)->get();
                // ------------------------------------------------------------------------------------------------------
                // Generate and send time slots
                // ------------------------------------------------------------------------------------------------------
                $time_slots_made = $this->getTimeSlotFromDB($blocked_timeslots);
                // $time_slot_interval = env("STORE_ORDER_TIME_SLOT_INTERVAL");
                // $time_slots_made = $this->getTimeSlot($time_slot_interval, $store_open_time, $store_close_time);
                $time_slots = $time_slots_made[0];
                $active_time_slot = $time_slots_made[1];
                $active_time_slot_value = $time_slots_made[2];
                $active_time_slot_string = $time_slots_made[3];
                
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
                    'current_time' => date('d-m-y h:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'store_open_time' => $store_open_time,
                    'store_close_time' => $store_close_time,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => $time_slots,
                    'active_time_slot' => $active_time_slot,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'sub_category_ids_of_blocking_timeslots' => $sub_category_ids_of_blocking_timeslots,
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
                    'current_time' => date('d-m-y h:i:s'),
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

    protected function my_cart(Request $request)
    {
        $lang = $request->header('lang');
        try {
            $my_cart_data = $this->get_my_cart_data($lang);

            $this->error = false;
            $this->status_code = 200;
            $this->message = $my_cart_data && is_array($my_cart_data) && isset($my_cart_data['message']) ? $my_cart_data['message'] : '';
            $this->result = $my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result']) ? $my_cart_data['result'] : '';
            
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

            $content = $request->getContent();
            $tracking_id = add_tracking_data($this->user_id, 'update_cart', $content, '');

            if ($content != '') {
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'The store is not available');
                    }
                    $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                    throw new Exception($error_message, 106);
                }
                $product_price_key = 'store' . $default_store_no . '_price';

                $content_arr = json_decode($content);

                // if (isset($content_arr[0]->weight) && $content_arr[0]->weight != '') {
                //     \App\Model\User::find($this->user_id)->update(['is_ios_new_version' => 2]);
                // } else {
                //     \App\Model\User::find($this->user_id)->update(['is_ios_new_version' => 1]);
                // }

                UserCart::where(['fk_user_id' => $this->user_id])->delete();

                foreach ($content_arr as $key => $value) {
                    $product = Product::find($value->product_id);
                    
                    // If products are not available
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 106);
                    }
                    // If products are not belongs to the company
                    if ($product->product_type=='product' && $product->fk_company_id != $company_id) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Location Succesfully changed');
                        }
                        $error_message = $lang == 'ar' ? 'تم تحديث العنوان بنجاح' : 'Location Succesfully changed';
                        throw new Exception($error_message, 106);
                    }

                    // Calculate total price
                    $total_price = round($product->$product_price_key * $value->product_quantity, 2);
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = Product::find($value2->product_id);
                                    $sub_product_price = round($sub_product->$product_price_key * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->$product_price_key;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
    
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'fk_product_id' => $value->product_id,
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $product->unit ?? '',
                        'unit' => $product->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];
                    UserCart::create($insert_arr);
                }

                $my_cart_data = $this->get_my_cart_data($lang);
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

    protected function getTimeSlotFromDB($blocked_timeslots=0)
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
        $blocked_timeslots = $address_selected && isset($address_selected['blocked_timeslots']) && $address_selected['blocked_timeslots']>$blocked_timeslots ? $address_selected['blocked_timeslots'] : $blocked_timeslots;
        $interval = $blocked_timeslots*60;

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
            $tracking_id = add_tracking_data($this->user_id, 'save_cart', $content, '');

            if ($request->input('product_json') != '') {
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'The store is not available');
                    }
                    $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                    throw new Exception($error_message, 106);
                }
                $product_price_key = 'store' . $default_store_no . '_price';

                $content_arr = json_decode($request->input('product_json'));
                
                $insert_saved_cart_arr = [
                    'name' => $request->input('name'),
                    'fk_user_id' => $this->user_id,
                    'fk_address_id' => $request->input('address_id'),
                ];

                $create = UserSavedCart::create($insert_saved_cart_arr);

                foreach ($content_arr as $key => $value) {
                    $product = Product::find($value->product_id);
                    
                    // If products are not available
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 106);
                    }
                    // If products are not belongs to the company
                    if ($product->product_type=='product' && $product->fk_company_id != $company_id) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Location Succesfully changed');
                        }
                        $error_message = $lang == 'ar' ? 'تم تحديث العنوان بنجاح' : 'Location Succesfully changed';
                        throw new Exception($error_message, 106);
                    }

                    // Calculate total price
                    $total_price = round($product->$product_price_key * $value->product_quantity, 2);
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = Product::find($value2->product_id);
                                    $sub_product_price = round($sub_product->$product_price_key * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->$product_price_key;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
                    
                    $insert_saved_cart_product_arr = [
                        'fk_product_id' => $value->product_id,
                        'fk_saved_cart_id' => $create->id,
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $product->unit ?? '',
                        'unit' => $product->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    UserSavedCartProduct::create($insert_saved_cart_product_arr);
                }

                $my_cart_data = $this->get_my_cart_data($lang);
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
            $tracking_id = add_tracking_data($this->user_id, 'save_cart', $content, '');

            if ($request->input('product_json') != '') {
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'The store is not available');
                    }
                    $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                    throw new Exception($error_message, 106);
                }
                $product_price_key = 'store' . $default_store_no . '_price';

                $content_arr = json_decode($request->input('product_json'));
                
                $saved_cart = UserSavedCart::find((int)$request->input('saved_cart_id'));
                
                $insert_saved_cart_arr = [
                    'name' => $request->input('name'),
                    'fk_user_id' => (int)$this->user_id,
                    'fk_address_id' => (int)$request->input('address_id'),
                ];

                $saved_cart->update($insert_saved_cart_arr);
                
                UserSavedCartProduct::where(['fk_saved_cart_id' => $saved_cart->id])->delete();
        
                foreach ($content_arr as $key => $value) {
                    $product = Product::find($value->product_id);
                    
                    // If products are not available
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 106);
                    }
                    // If products are not belongs to the company
                    if ($product->product_type=='product' && $product->fk_company_id != $company_id) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Location Succesfully changed');
                        }
                        $error_message = $lang == 'ar' ? 'تم تحديث العنوان بنجاح' : 'Location Succesfully changed';
                        throw new Exception($error_message, 106);
                    }

                    // Calculate total price
                    $total_price = round($product->$product_price_key * $value->product_quantity, 2);
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = Product::find($value2->product_id);
                                    $sub_product_price = round($sub_product->$product_price_key * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->$product_price_key;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
                    
                    $insert_saved_cart_product_arr = [
                        'fk_product_id' => $value->product_id,
                        'fk_saved_cart_id' => (int)$request->input('saved_cart_id'),
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $product->unit ?? '',
                        'unit' => $product->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    UserSavedCartProduct::create($insert_saved_cart_product_arr);
                }

                $my_cart_data = $this->get_my_cart_data($lang);
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
            $saved_cart = UserSavedCartProduct::where(['fk_saved_cart_id' => (int)$request->input('saved_cart_id')])->delete();
            if($saved_cart){
                UserSavedCart::find((int)$request->input('saved_cart_id'))->delete();
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "عربة التسوق" : "My cart";
            
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
            $tracking_id = add_tracking_data($this->user_id, 'update_cart', $content, '');

            if ($content != '') {
                $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                if ($nearest_store) {
                    $default_store_no = get_store_no($nearest_store->name);
                    $company_id = $nearest_store->company_id;
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'The store is not available');
                    }
                    $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                    throw new Exception($error_message, 106);
                }
                $product_price_key = 'store' . $default_store_no . '_price';

                $content_arr = json_decode($content);

                // if (isset($content_arr[0]->weight) && $content_arr[0]->weight != '') {
                //     \App\Model\User::find($this->user_id)->update(['is_ios_new_version' => 2]);
                // } else {
                //     \App\Model\User::find($this->user_id)->update(['is_ios_new_version' => 1]);
                // }


                foreach ($content_arr as $key => $value) {
                    $product = Product::find($value->product_id);
                    
                    // If products are not available
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 106);
                    }
                    // If products are not belongs to the company
                    if ($product->product_type=='product' && $product->fk_company_id != $company_id) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Location Succesfully changed');
                        }
                        $error_message = $lang == 'ar' ? 'تم تحديث العنوان بنجاح' : 'Location Succesfully changed';
                        throw new Exception($error_message, 106);
                    }

                    // Calculate total price
                    $total_price = round($product->$product_price_key * $value->product_quantity, 2);
                    
                    // If sub products for recipe
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = $value->sub_products;
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = Product::find($value2->product_id);
                                    $sub_product_price = round($sub_product->$product_price_key * $value2->product_quantity, 2);
                                    $total_price = $total_price + $sub_product_price;
                                    $value2->product_name_en = $sub_product->product_name_en;
                                    $value2->product_name_ar = $sub_product->product_name_ar;
                                    $value2->unit = $sub_product->unit;
                                    $value2->price = $sub_product->$product_price_key;
                                    $value2->product_image_url = $sub_product->product_image_url;
                                }
                            }
                        }
                    }
                    
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'fk_product_id' => $value->product_id,
                        'quantity' => $value->product_quantity,
                        'total_price' => $total_price,
                        'total_discount' => '',
                        'weight' => $product->unit ?? '',
                        'unit' => $product->unit ?? '',
                        'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                    ];

                    //If product exist in the cart
                    $product_exist = UserCart::where(['fk_user_id' => $this->user_id,'fk_product_id' => $value->product_id])->first();
                    
                    if($product_exist){
                        $insert_arr['quantity'] = $value->product_quantity+$product_exist->quantity;
                        $insert_arr['total_price'] = $total_price+$product_exist->total_price;
                        $product_exist->update($insert_arr);
                    }else{
                        UserCart::create($insert_arr);
                    }
                    
                }

                $my_cart_data = $this->get_my_cart_data($lang);
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
            $product_price_key = 'store' . $user->nearest_store. '_price';
            
            if ($request->product_quantity !=0) {
                
                $product = Product::find($request->product_id);
                $total_price = round($product->$product_price_key * $request->product_quantity, 2);
                
                $insert_arr = [
                    'quantity' => $cartItem->quantity-$request->product_quantity,
                    'total_price' => $total_price,
                ];

                UserCart::find($cartItem->id)->update($insert_arr);
                
                $this->error = false;
                $this->status_code = 200;
                
            } else {
                
                UserCart::find($cartItem->id)->delete();

                $this->error = false;
                $this->status_code = 200;
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
