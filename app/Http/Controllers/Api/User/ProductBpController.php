<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Product;
use App\Model\Ads;
use App\Model\UserCart;
use App\Model\UserWishlist;
use App\Model\AdminSetting;
use App\Model\Homepage;
use App\Model\HomepageBannerProduct;
use App\Model\Homepagedata;
use App\Model\OauthAccessToken;
use App\Model\ProductSuggestion;
use App\Model\Store;
use App\Model\User;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;

class ProductBpController extends CoreApiController
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
            'add_favorite', 'view_all_favorite_products', 'view_all_essential_products', 'get_category_products',
            'get_brand_products', 'get_notes', 'remove_notes', 'move_notes_to_cart',
            'buy_again_products', 'suggest_new_product', 'view_all_suggestions',
            'view_all_recent_orders', 'frequently_bought_together'
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

    protected function view_all_products(Request $request)
    {
        try {
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
                } else {
                    $latitude = '';
                    $longitude = '';
                }
            } else {
                $user_id = '';
            }

            if ($user_id != '') {
                $user = User::find($user_id);
                $default_store = $user->nearest_store;
            } else {
                $nearestStore = Store::select('id')
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
                    ->where('status','=',1)
                    ->orderBy('distance', 'asc')
                    ->first();
                $default_store = $nearestStore->id;
            }

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 18;
            $offset = ($page - 1) * $limit;

            $user = User::find($user_id);

            $products = Homepagedata::join($this->products_table, $this->products_table . '.id', '=', 'homepage_data.fk_product_id')
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
                ->where('homepage_data.fk_homepage_id', '=', $request->input('id'))
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.store' . $default_store, '=', 1)
                ->groupBy($this->products_table . '.id')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $product_arr = get_product_dictionary($products, $user_id, $lang, $request->header('Authorization'));

            $ads = Ads::select(
                'ads.id AS ads_id',
                'ads.name',
                'ads.image',
                'ads.redirect_type'
            )
                ->groupBy('ads.id')
                ->where('deleted', '=', 0)
                ->orderBy('ads.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $ads_arr = [];
            if ($ads->count()) {
                foreach ($ads as $key => $row) {
                    $image = \App\Model\File::find($row->image);
                    // if($row->redirect_type == 1){
                    //     $ads_type = 'Offer';
                    // }else{
                    //     $ads_type = 'Product';
                    // }
                    $ads_arr[$key] = [
                        'ad_id' => $row->ads_id,
                        'ad_name' => $row->name,
                        'redirect_type' => $row->redirect_type,
                        'ad_image' => !empty($image) ? asset('images/ads_images') . '/' . $image->file_name : '',
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => $product_arr, 'ads_arr' => $ads_arr];
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

    protected function view_all_banner_products(Request $request)
    {
        try {
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
                } else {
                    $latitude = '';
                    $longitude = '';
                }
            } else {
                $user_id = '';
                $latitude = '';
                $longitude = '';
            }

            if ($user_id != '') {
                $user = User::find($user_id);
                $default_store = $user->nearest_store;
            } else {
                $nearestStore = Store::select('id')
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
                    ->where('status','=',1)
                    ->orderBy('distance', 'asc')
                    ->first();
                $default_store = $nearestStore->id;
            }

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 18;
            $offset = ($page - 1) * $limit;

            $products = HomepageBannerProduct::join($this->products_table, $this->products_table . '.id', '=', 'homepage_banner_products.fk_product_id')
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
                ->where('homepage_banner_products.fk_homepage_data_id', '=', $request->input('id'))
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.store' . $default_store, '=', 1)
                ->groupBy($this->products_table . '.id')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $product_arr = get_product_dictionary($products, $user_id, $lang, $request->header('Authorization'));

            $ads = Ads::select(
                'ads.id AS ads_id',
                'ads.name',
                'ads.image',
                'ads.redirect_type'
            )
                ->groupBy('ads.id')
                ->where('deleted', '=', 0)
                ->orderBy('ads.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $ads_arr = [];
            if ($ads->count()) {
                foreach ($ads as $key => $row) {
                    $image = \App\Model\File::find($row->image);
                    // if($row->redirect_type == 1){
                    //     $ads_type = 'Offer';
                    // }else{
                    //     $ads_type = 'Product';
                    // }
                    $ads_arr[$key] = [
                        'ad_id' => $row->ads_id,
                        'ad_name' => $row->name,
                        'redirect_type' => $row->redirect_type,
                        'ad_image' => !empty($image) ? asset('images/ads_images') . '/' . $image->file_name : '',
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => $product_arr, 'ads_arr' => $ads_arr];
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

    protected function view_all_favorite_products(Request $request)
    {
        try {
            $lang = $request->header('lang');

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $user = User::find($this->user_id);
            $user->nearest_store = 14;

            $products = BaseProduct::leftJoin('categories', 'base_products.fk_category_id', '=', 'categories.id')
                ->leftJoin('brands', 'base_products.fk_brand_id', '=', 'brands.id')
                ->join('user_wishlist', 'base_products.id', '=', 'user_wishlist.fk_product_id')
                ->select(
                    'base_products.*',
                    'base_products.id as fk_product_id',
                    'base_products.fk_product_store_id as product_store_id',
                    'base_products.product_distributor_price AS distributor_price',
                    'categories.id as category_id',
                    'categories.category_name_en',
                    'categories.category_name_ar',
                    'brands.id as brand_id',
                    'brands.brand_name_en',
                    'brands.brand_name_ar',
                )
                ->where('base_products.parent_id', '=', 0)
                ->where('base_products.deleted', '=', 0)
                ->where('user_wishlist.fk_user_id', '=', $this->user_id)
                ->groupBy('base_products.id')
                ->orderBy('user_wishlist.id', 'desc')
                // ->offset($offset)
                // ->limit($limit)
                ->get();

            $product_arr = get_product_dictionary_bp($products, $this->user_id, $lang, $request->header('Authorization'));

            $ads = Ads::select(
                'ads.id AS ads_id',
                'ads.name',
                'ads.image',
                'ads.redirect_type'
            )
                ->groupBy('ads.id')
                ->where('deleted', '=', 0)
                ->orderBy('ads.id', 'desc')
                ->get();

            $ads_arr = [];
            if ($ads->count()) {
                foreach ($ads as $key => $row) {
                    $image = \App\Model\File::find($row->image);
                    // if($row->redirect_type == 1){
                    //     $ads_type = 'Offer';
                    // }else{
                    //     $ads_type = 'Product';
                    // }
                    $ads_arr[$key] = [
                        'ad_id' => $row->ads_id,
                        'ad_name' => $row->name,
                        'redirect_type' => $row->redirect_type,
                        'ad_image' => !empty($image) ? asset('images/ads_images') . '/' . $image->file_name : '',
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => $product_arr, 'ads_arr' => $ads_arr];
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

    protected function add_favorite(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['product_id']);

            $product = BaseProduct::find($request->input('product_id'));
            
            if($this->user_id == ''){
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
            
            $exist = UserWishlist::where(['fk_user_id' => $this->user_id, 'fk_product_id' => $request->input('product_id')])->first();
            if ($exist) {
                UserWishlist::find($exist->id)->delete();
                $message = $lang == 'ar' ? "تمت إزالته من قائمة المفضلة" : "Removed from favorite list";
                $is_favorite = 0;
            } else {
                $insert_arr = [
                    'fk_user_id' => $this->user_id,
                    'fk_product_id' => $request->input('product_id'),
                    'fk_product_store_id' => $request->input('product_store_id')
                ];
                UserWishlist::create($insert_arr);
                $message = $lang == 'ar' ? "تمت إضافته إلى قائمة المفضلة" : "Added into the favorite list";
                $is_favorite = 1;
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = [
                'is_favorite' => $is_favorite
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

    protected function buy_again_products(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);

            $order_products = \App\Model\OrderProduct::join('orders', 'order_products.fk_order_id', '=', 'orders.id')
                ->select("order_products.*")
                ->where('orders.fk_user_id', '=', $this->user_id)
                ->where($this->products_table . '.store' . $user->nearest_store, '=', 1)
                ->groupBy('order_products.fk_product_id')
                ->get();

            $product_arr = [];
            if ($order_products->count()) {
                foreach ($order_products as $key => $row) {
                    if ($row->getProduct) {
                        $image = \App\Model\File::find($row->getProduct->product_image);

                        $is_fav = UserWishlist::where([
                            'fk_user_id' => $this->user_id,
                            'fk_product_id' => $row->fk_product_id
                        ])
                            ->first();
                        if ($is_fav) {
                            $is_favorite = 1;
                        } else {
                            $is_favorite = 0;
                        }

                        $product_arr[$key] = [
                            'product_id' => $row->fk_product_id,
                            'product_category_id' => $row->getProduct->category_id,
                            'product_category' => $lang == 'ar' ? $row->getProduct->category_name_ar : $row->getProduct->category_name_en,
                            'product_brand_id' => $row->getProduct->brand_id ?? 0,
                            'product_brand' => $lang == 'ar' ? $row->getProduct->brand_name_ar ?? "" : $row->getProduct->brand_name_en ?? "",
                            'product_name' => $lang == 'ar' ? $row->getProduct->product_name_ar : $row->getProduct->product_name_en,
                            'product_image' => !empty($image) ? asset('images/product_images') . '/' . $image->file_name : '',
                            'product_price' => number_format($row->getProduct->product_price, 2),
                            'product_price_before_discount' => number_format($row->getProduct->product_price, 2),
                            'quantity' => $row->getProduct->unit,
                            'unit' => $row->getProduct->unit,
                            'is_favorite' => $is_favorite,
                            'product_discount' => $row->getProduct->discount,
                        ];

                        $sub_products = Product::join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
                            ->join('brands', $this->products_table . '.fk_brand_id', '=', 'brands.id')
                            ->select(
                                $this->products_table . '.*',
                                'categories.id as category_id',
                                'categories.category_name_en',
                                'categories.category_name_ar',
                                'brands.id as brand_id',
                                'brands.brand_name_en',
                                'brands.brand_name_ar'
                            )
                            ->where($this->products_table . '.parent_id', '=', $row->fk_product_id)
                            ->orderBy($this->products_table . '.id', 'desc')
                            ->get();

                        if ($sub_products->count()) {
                            foreach ($sub_products as $key1 => $row1) {
                                $sub_prod_image = \App\Model\File::find($row1->product_image);

                                $sub_prod_arr[$key1] = [
                                    'product_id' => $row1->id,
                                    'product_category_id' => $row1->category_id,
                                    'product_category' => $lang == 'ar' ? $row1->category_name_ar : $row1->category_name_en,
                                    'product_brand_id' => $row1->brand_id ?? 0,
                                    'product_brand' => $lang == 'ar' ? $row1->brand_name_ar : $row1->brand_name_en,
                                    'product_name' => $lang == 'ar' ? $row1->product_name_ar : $row1->product_name_en,
                                    'product_image' => !empty($sub_prod_image) ? asset('images/product_images') . '/' . $sub_prod_image->file_name : '',
                                    'product_price' => number_format($row1->product_price, 2),
                                    'product_price_before_discount' => number_format($row1->product_price, 2),
                                    'quantity' => $row1->unit,
                                    'unit' => $row1->unit,
                                    'is_favorite' => 0,
                                    'product_discount' => $row1->discount,
                                ];

                                if ($this->user_id != '') {
                                    $cart_product = UserCart::where(['fk_user_id' => $this->user_id, 'fk_product_id' => $row1->id])->first();
                                    if ($cart_product) {
                                        $sub_prod_arr[$key1]['cart_quantity'] = $cart_product['quantity'];
                                    } else {
                                        $sub_prod_arr[$key1]['cart_quantity'] = 0;
                                    }
                                } else {
                                    $sub_prod_arr[$key1]['cart_quantity'] = 0;
                                }
                            }
                        } else {
                            $sub_prod_arr = [];
                        }
                        $product_arr[$key]['items'] = $sub_prod_arr;

                        if ($this->user_id != '') {
                            $cart_product = UserCart::where(['fk_user_id' => $this->user_id, 'fk_product_id' => $row->product_id])->first();
                            if ($cart_product) {
                                $single_product_quantity = $cart_product['quantity'];
                            } else {
                                $single_product_quantity = 0;
                            }
                        } else {
                            $single_product_quantity = 0;
                        }
                        $product_arr[$key]['cart_quantity'] = $single_product_quantity;
                    }
                }
            }

            $ads = Ads::select(
                'ads.id AS ads_id',
                'ads.name',
                'ads.image',
                'ads.redirect_type'
            )
                ->groupBy('ads.id')
                ->where('deleted', '=', 0)
                ->orderBy('ads.id', 'desc')
                ->get();

            $ads_arr = [];
            if ($ads->count()) {
                foreach ($ads as $key => $row) {
                    $image = \App\Model\File::find($row->image);
                    // if($row->redirect_type == 1){
                    //     $ads_type = 'Offer';
                    // }else{
                    //     $ads_type = 'Product';
                    // }
                    $ads_arr[$key] = [
                        'ad_id' => $row->ads_id,
                        'ad_name' => $row->name,
                        'redirect_type' => $row->redirect_type,
                        'ad_image' => !empty($image) ? asset('images/ads_images') . '/' . $image->file_name : '',
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => array_values($product_arr), 'ads_arr' => $ads_arr];
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

    protected function get_classified_products(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['classification_id']);

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

            //setting the page, limit and offset
            if ($request->input('page') == '0') {
                $page = 1;
                $limit = 10000;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }

                $limit = 10;
            }
            $offset = ($page - 1) * $limit;

            if ($user_id != '') {
                $user = User::find($user_id);
                $default_store = $user->nearest_store;
            } else {
                $nearestStore = Store::select('id')
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
                    ->where('status','=',1)
                    ->orderBy('distance', 'asc')
                    ->first();
                $default_store = $nearestStore->id;
            }

            $products = Product::join('classified_products', 'classified_products.fk_product_id', '=', $this->products_table . '.id')
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
                ->where($this->products_table . '.parent_id', '=', 0)
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.store' . $default_store, '=', 1)
                ->where('classified_products.fk_classification_id', '=', $request->input('classification_id'))
                ->groupBy($this->products_table . '.id')
                ->orderBy($this->products_table . '.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $subclassification = \App\Model\Classification::where(['parent_id' => $request->input('classification_id')])
                ->orderBy('name_en', 'asc')
                ->get();
            if ($subclassification->count()) {
                $sub_classification_arr = [];
                foreach ($subclassification as $key1 => $row1) {
                    $sub_classification_arr[$key1] = [
                        'id' => $row1->id,
                        'name' => $lang == 'ar' ? $row1->name_ar : $row1->name_en,
                        'banner_image' => !empty($row1->getBannerImage) ? asset('images/classification_images') . '/' . $row1->getBannerImage->file_name : '',
                        'stamp_image' => !empty($row1->getStampImage) ? asset('images/classification_images') . '/' . $row1->getStampImage->file_name : ''
                    ];
                }
            } else {
                if ($request->input('is_sub_classification') == 1) {
                    $products = Product::join('classified_products', 'classified_products.fk_product_id', '=', $this->products_table . '.id')
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
                        ->where($this->products_table . '.parent_id', '=', 0)
                        ->where($this->products_table . '.deleted', '=', 0)
                        ->where($this->products_table . '.store' . $default_store, '=', 1)
                        ->where('classified_products.fk_sub_classification_id', '=', $request->input('classification_id'))
                        ->groupBy($this->products_table . '.id')
                        ->orderBy($this->products_table . '.id', 'desc')
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
                } else {
                    $products = Product::join('classified_products', 'classified_products.fk_product_id', '=', $this->products_table . '.id')
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
                        ->where($this->products_table . '.parent_id', '=', 0)
                        ->where($this->products_table . '.deleted', '=', 0)
                        ->where($this->products_table . '.store' . $default_store, '=', 1)
                        ->where('classified_products.fk_classification_id', '=', $request->input('classification_id'))
                        ->groupBy($this->products_table . '.id')
                        ->orderBy($this->products_table . '.id', 'desc')
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
                }
            }

            $product_arr = get_product_dictionary($products, $user_id, $lang, $request->header('Authorization'));

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => $product_arr ?? [], 'subclassifications' => $sub_classification_arr ?? []];
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

    protected function suggest_new_product(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['product_company', 'product_name', 'product_description']);

            $insertArr = [
                'fk_user_id' => $this->user_id,
                'product_company' => $request->input('product_company'),
                'product_name' => $request->input('product_name'),
                'product_description' => $request->input('product_description')
            ];

            if ($request->hasFile('product_image')) {
                $path = "/images/product_suggested_images/";
                $check = $this->uploadFile($request, 'product_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insertArr['product_image'] = $returnArr->id;
                endif;
            }

            $add = ProductSuggestion::create($insertArr);

            if ($add) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "لاحظ اقتراحك. سنحرص على إطلاعك على المستجدات في أقرب وقت ممكن" : "Your suggestion is noted. We'll make sure to update you in the earliest time possible";
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

    protected function view_all_suggestions(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $suggestions = ProductSuggestion::where(['fk_user_id' => $this->user_id])
                ->orderBy('id', 'desc')
                ->get();

            $suggestion_arr = [];
            if ($suggestions->count()) {
                foreach ($suggestions as $key => $value) {
                    $suggestion_arr[$key] = [
                        'product_company' => $value->product_company,
                        'product_name' => $value->product_name,
                        'product_description' => $value->product_description,
                        'product_image' => $value->getProductSuggestionImage ? asset('/') . $value->getProductSuggestionImage->file_path . $value->getProductSuggestionImage->file_name : '',
                        'status' => $value->status
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = ['suggestions' => $suggestion_arr];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = "No suggestions";
                $this->result = ['suggestions' => $suggestion_arr];
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

    protected function view_all_recent_orders(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);

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
                ->where('orders.fk_user_id', '=', $this->user_id)
                ->where($this->products_table . '.parent_id', '=', 0)
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.store' . $user->nearest_store, '=', 1)
                ->orderBy('order_products.created_at', 'desc')
                ->groupBy('order_products.fk_product_id')
                ->get();

            $recent_orders_arr = get_product_dictionary($order_products, $this->user_id, $lang, $request->header('Authorization'));

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['recent_orders' => $recent_orders_arr];
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

    protected function frequently_bought_together(Request $request)
    {
        try {
            $lang = $request->header('lang');

            //setting the page, limit and offset
            if ($request->input('page') == '') {
                $page = 1;
            } else {
                $page = $request->input('page');
                $this->check_numeric($request->input(), ['page']);
                if ($request->input('page') < -1) {
                    throw new Exception('page should not less than -1', 105);
                }
            }
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $user = User::find($this->user_id);
            $user->nearest_store = 14;
            $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
            if ($nearest_store) {
                $store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
            } else {
                $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                throw new Exception($error_message, 106);
            }
            $products = Product::join('categories', $this->products_table . '.fk_category_id', '=', 'categories.id')
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
                ->where($this->products_table . '.parent_id', '=', 0)
                ->where($this->products_table . '.deleted', '=', 0)
                ->where($this->products_table . '.frequently_bought_together', '=', 1)
                ->where($this->products_table . '.store' . $store_no, '=', 1)
                ->groupBy($this->products_table . '.id')
                ->orderBy($this->products_table . '.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            $product_arr = get_product_dictionary($products, $this->user_id, $lang, $request->header('Authorization'));

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['products' => $product_arr];
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
