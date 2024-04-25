<?php

namespace App\Http\Controllers\Api\WhatsApp;

use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\CoreApiController;
use App\Model\ApiValidation;
use App\Model\UserPreference;
use App\Model\AffiliateUser;
use App\Model\DeliverySlot;
use App\Model\DeliverySlotSetting;
use App\Model\Category;
use App\Model\Brand;
use Illuminate\Support\Facades\DB;
use App\Model\ScratchCardUser;
use App\Model\Coupon;
use App\Model\CouponUses;
use App\Model\Order;
use App\Model\UserCart;
use App\Model\OrderProduct;
use App\Model\OrderStatus;
use App\Model\OrderReview;
use App\Model\UserAddress;
use App\Model\OrderAddress;
use App\Model\OrderDriver;
use App\Model\OrderDeliverySlot;
use App\Model\OrderPayment;
use App\Model\User;
use App\Model\Store;
use App\Model\UserDeviceDetail;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\UserBuyItForMeCart;
use App\Model\UserBuyItForMeRequest;

use Illuminate\Support\Facades\Http;

class WhatsAppController extends CoreApiController
{
    use \App\Http\Traits\PushNotification;
    use \App\Http\Traits\OrderProcessing;
    use \App\Http\Traits\TapPayment;

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];
    protected $order_status = [
        'Order placed',
        'Order confirmed',
        'Order assigned',
        'Order invoiced',
        'Cancelled',
        'Order in progress',
        'Out for delivery',
        'Delivered'
    ];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['buy_it_for_me_cart','make_order','address_list','delivery_slots','get_all_categories','get_all_brands','order_status'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 4)
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
        // $address_selected = get_userSelectedAddress($this->user_id);
        $address_selected = get_userSelectedAddress(false);
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

    protected function get_my_cart_data($lang,$user)
    {
        try {
            $cartItems = UserCart::where(['fk_user_id' => $user])
                ->orderBy('id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000];

            $delivery_cost = get_delivery_cost();
            $wallet_balance = get_userWallet($user);

            $cartTotal = UserCart::selectRaw("SUM(total_price) as total_amount")
                ->where('fk_user_id', '=', $user)
                ->first();

            $user = User::find($user);
            $user->nearest_store = 14;
            $mp_amount = get_minimum_purchase_amount($user->mobile,$user->id);
            $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
            if ($nearest_store) {
                $default_store_no = get_store_no($nearest_store->name);
                $company_id = $nearest_store->company_id;
                $delivery_time = "" . getDeliveryIn($user->expected_eta);
            } else {
                UserCart::where(['fk_user_id' => $user])->delete();   
                $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                throw new Exception($error_message, 106);
            }
            
            $store = 'store' . $default_store_no;
            $store_price = 'store' . $default_store_no . '_price';
            
            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($user->expected_eta);
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
                    $product = BaseProduct::leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                        ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                        ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                        ->select(
                            'base_products.*',
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
                    // Not checking the stocks
                    $cartItemArr[$key] = [
                        'id' => $value->id,
                        'product_id' => $product->id,
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
                    // Checking stocks
                    // if ($product->product_store_stock > 0 && $product->product_store_stock < $value->quantity) {

                    //     $cartItemQuantityMismatch[$key] = [
                    //         'id' => $value->id,
                    //         'product_id' => $product->id,
                    //         'product_type' => $product->product_type,
                    //         'parent_id' => $product->parent_id,
                    //         'recipe_id' => $product->recipe_id,
                    //         'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                    //         'product_image' => $product->product_image_url ?? '',
                    //         'product_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_store_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_total_price' => (string) round(($value->total_price), 2),
                    //         'product_price_before_discount' => (string) round($value->total_price, 2),
                    //         'cart_quantity' => $value->quantity,
                    //         'unit' => $product->unit ?? '',
                    //         'product_discount' => $product->margin,
                    //         'stock' => (string) $product->product_store_stock,
                    //         'item_weight' => $value->weight,
                    //         'item_unit' => $value->unit,
                    //         'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                    //         'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                    //         'min_scale' => $product->min_scale ?? '',
                    //         'max_scale' => $product->max_scale ?? '',
                    //         'sub_products' => $sub_product_arr,
                    //         'sub_product_names' => $sub_product_names,
                    //         'sub_product_price' => $sub_product_price,
                    //         'fk_store_id' => $product->fk_store_id,
                    //         'fk_product_store_id' => $product->fk_product_store_id
                    //     ];

                    // }elseif ($product->product_store_stock > 0 && $product->deleted == 0) {
                    //     $cartItemArr[$key] = [
                    //         'id' => $value->id,
                    //         'product_id' => $product->id,
                    //         'product_type' => $product->product_type,
                    //         'parent_id' => $product->parent_id,
                    //         'recipe_id' => $product->recipe_id,
                    //         'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                    //         'product_image' => $product->product_image_url ?? '',
                    //         'product_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_store_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_total_price' => (string) round(($value->total_price), 2),
                    //         'product_price_before_discount' => (string) round($value->total_price, 2),
                    //         'cart_quantity' => $value->quantity,
                    //         'unit' => $product->unit ?? '',
                    //         'product_discount' => $product->margin,
                    //         'stock' => (string) $product->product_store_stock,
                    //         'item_weight' => $value->weight,
                    //         'item_unit' => $value->unit,
                    //         'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                    //         'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                    //         'min_scale' => $product->min_scale ?? '',
                    //         'max_scale' => $product->max_scale ?? '',
                    //         'sub_products' => $sub_product_arr,
                    //         'sub_product_names' => $sub_product_names,
                    //         'sub_product_price' => $sub_product_price,
                    //         'fk_store_id' => $product->fk_store_id,
                    //         'fk_product_store_id' => $product->fk_product_store_id
                    //     ];
                    // } else {
                    //     $cartItemOutofstockArr[$key] = [
                    //         'id' => $value->id,
                    //         'product_id' => $product->id,
                    //         'product_type' => $product->product_type,
                    //         'parent_id' => $product->parent_id,
                    //         'recipe_id' => $product->recipe_id,
                    //         'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                    //         'product_image' => $product->product_image_url ?? '',
                    //         'product_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_store_price' => (string) number_format($product->product_store_price, 2),
                    //         'product_total_price' => (string) round(($value->total_price), 2),
                    //         'product_price_before_discount' => (string) round($value->total_price, 2),
                    //         'cart_quantity' => $value->quantity,
                    //         'unit' => $product->unit ?? '',
                    //         'product_discount' => $product->margin,
                    //         'stock' => (string) $product->product_store_stock,
                    //         'item_weight' => $value->weight,
                    //         'item_unit' => $value->unit,
                    //         'product_category_id' => $product->fk_category_id ? $product->fk_category_id : 0,
                    //         'product_sub_category_id' => $product->fk_sub_category_id ? $product->fk_sub_category_id : 0,
                    //         'min_scale' => $product->min_scale ?? '',
                    //         'max_scale' => $product->max_scale ?? '',
                    //         'sub_products' => $sub_product_arr,
                    //         'sub_product_names' => $sub_product_names,
                    //         'sub_product_price' => $sub_product_price,
                    //         'fk_store_id' => $product->fk_store_id,
                    //         'fk_product_store_id' => $product->fk_product_store_id
                    //     ];
                    // }
                }
                
                $result = [
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
                    'active_time_slot' => $active_time_slot,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for,
                    'cart_items' => array_values($cartItemArr),
                    'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                    'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch)
                ];
            } else {
                $message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $result = [
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
                    'time_slots' => $time_slots,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for,
                    'cart_items' => [],
                    'cart_items_outofstock' => [],
                    'cart_items_quantitymismatch' => []
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

    protected function update_cart($user,$cart_item_arr,$phone)
    {
        try {
            $lang = 'en';
            
            $tracking_id = $this->add_whatsapp_tracking_data($user->id, $phone, 'update_cart', $cart_item_arr, '');
            $content = $cart_item_arr;
            
            if ($content != '') {

                $content_arr = json_decode($content);

                UserCart::where(['fk_user_id' => $user->id])->delete();

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
                    if($base_product->product_store_stock!=0){
                        $insert_arr = [
                            'fk_user_id' => $user->id,
                            'fk_product_id' => $base_product->id,
                            'fk_product_store_id' => $base_product->fk_product_store_id,
                            'quantity' => $value->product_quantity,
                            'total_price' => $total_price,
                            'total_discount' => '',
                            'weight' => $value->weight ?? '',
                            'unit' => $value->unit ?? '',
                            'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                        ];
                    }else{
                        $insert_arr = [
                            'fk_user_id' => $user->id,
                            'fk_product_id' => $value->product_id,
                            'fk_product_store_id' => $value->product_store_id,
                            'quantity' => $value->product_quantity,
                            'total_price' => $total_price,
                            'total_discount' => '',
                            'weight' => $value->weight ?? '',
                            'unit' => $value->unit ?? '',
                            'sub_products' => isset($value->sub_products) && $value->sub_products ? json_encode($value->sub_products) : ''
                        ];
                    }
                    
                    UserCart::create($insert_arr);
                }

                $my_cart_data = $this->get_my_cart_data($lang,$user->id);
                if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                    $my_cart_data['result']['address_list'] = get_userAddressList($user->id);
                    $my_cart_data['result']['store_timeslot_blocking'] = get_storeTimeslotBlockings();
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                    $this->result = $my_cart_data['result'];
                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_response($tracking_id,"An error has been discovered in updating cart");
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    $this->update_whatsapp_tracking_response($tracking_id,"An error has been discovered in updating cart");
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            $this->update_whatsapp_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function make_order(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['country_code', 'mobile']);

            if ($request->input('country_code')!='974' && $request->input('country_code')!='966') {
                throw new Exception('Illegal', 105);
            }

            $user_mobile = $request->input('mobile');
            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $user_mobile;
            $user = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            $phone = '+'.$phone;
            if(!$user){

                $tracking_id = $this->add_whatsapp_tracking_data($user->id, $phone, 'register', $request, '');
                $insert_arr = [
                    'country_code' => $country_code,
                    'mobile' => $request->input('mobile'),
                    'nearest_store' => 14,
                    'status' => 1
                ];
                $user = User::create($insert_arr);
                if ($user) {

                    UserPreference::create([
                        'fk_user_id' => $user->id,
                        'is_notification' => 1,
                        'lang' => 'en'
                    ]);
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_userid_and_key($tracking_id, $phone, 'registered',$user->id);
                    }
                }
            }

            // Select address or create address based on the latitude and longitute
            $tracking_id = $this->add_whatsapp_tracking_data($user->id, $phone, 'select_address', $request, '');
            if (!$request->input('address_id')) {
                if ($request->hasHeader('latitude') && $request->hasHeader('longitude')) {
                    $address = $this->Get_Address_From_Google_Maps($request->header('latitude'), $request->header('longitude'));
                    $insert_arr = [
                        'fk_user_id' => $user->id,
                        'mobile' => $request->input('mobile'),
                        'landmark' => $request->input('landmark'),
                        'address_line1' => $address,
                        'latitude' => $request->header('latitude'),
                        'longitude' => $request->header('longitude'),
                        'address_type' => $request->input('address_type'), //1:office,2:home,3:others
                        'is_default' => 1,
                    ];
                    $address = UserAddress::create($insert_arr);
                }
            } else {
                $address = UserAddress::find($request->input('address_id'));
            }

            if (!$address) {
                if (isset($tracking_id) && $tracking_id) {
                    $this->update_whatsapp_tracking_response($tracking_id,"The address is not selected!");
                }
                $error_message = $lang == 'ar' ? "لم يتم تحديد العنوان!" : "The address is not selected!";
                throw new Exception($error_message, 105);
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    $this->update_whatsapp_tracking_response($tracking_id,"The address is selected successfully!");
                }
            }
            
            $cart_item_arr = $request->input('cart_json');
            if($this->update_cart($user,$cart_item_arr,$phone)){

                $tracking_id = $this->add_whatsapp_tracking_data($user->id,$phone, 'make_order', $request, '');

                $my_cart_data = $this->get_my_cart_data($lang,$user->id);

                if(is_object($my_cart_data)) {
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_response($tracking_id,json_encode($my_cart_data));
                    }
                    return $my_cart_data;
                }
                
                $sub_total = $my_cart_data['result']['sub_total'];
                $delivery_charge = $request->input('delivery_charge');
                $address_id = $address->id;
                $payment_type = $request->input('payment_type');
                $delivery_date = $request->input('delivery_date');
                $delivery_time = $request->input('delivery_time');
                $later_time = $request->input('later_time');
                $buy_it_for_me_request_id = $request->input('buy_it_for_me_request_id');
                $later_time_blocked_slots = $request->input('later_time_blocked_slots');
                $use_wallet_balance = $request->input('use_wallet_balance');
                $coupon_id = $request->input('coupon_id');
                $change_for = $request->input('change_for');
                $delivery_preference = $request->input('delivery_preference');
                $payment_response = $request->input('paymentResponse');
                $coupon_discount = $request->input('coupon_discount');

                return $this->process_make_order('whatsapp',$tracking_id,$user,$lang,$sub_total,$delivery_charge,$address_id,$payment_type,$delivery_date,$delivery_time,$later_time,$buy_it_for_me_request_id,$later_time_blocked_slots,$use_wallet_balance,$coupon_id,$change_for,$delivery_preference,$payment_response,$coupon_discount);
            }

        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            $this->update_whatsapp_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function address_list(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['country_code', 'mobile']);

            if ($request->input('country_code')!='974' && $request->input('country_code')!='966') {
                throw new Exception('Illegal', 105);
            }

            $home_static = \App\Model\HomeStatic::orderBy('id','desc')->first();
            $home_static_last_id = $home_static ? $home_static->id : 0;
            $home_static_data_feeded = $home_static ? $home_static->home_static_data_feeded : 0;

            $home_static_version = env("HOME_STATIC_VERSION", false).$home_static_last_id.$home_static_data_feeded;

            $user_mobile = $request->input('mobile');
            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $user_mobile;
            $user = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            
            if ($user) {
                $all_addresses = UserAddress::where(['fk_user_id' => $user->id, 'deleted' => 0])->orderBy('id', 'desc')->get();
                if ($all_addresses->count()) {
                    foreach ($all_addresses as $key => $row) {
                        $address_arr[$key] = [
                            'id' => $row->id,
                            'name' => $row->name ?? '',
                            'mobile' => $row->mobile ?? '',
                            'landmark' => $row->landmark ?? '',
                            'address_line1' => $row->address_line1 ?? '',
                            'address_line2' => $row->address_line2 ?? '',
                            'latitude' => $row->latitude ?? '',
                            'longitude' => $row->longitude ?? '',
                            'address_type' => $row->address_type,
                            'image' => $row->getAddressImage ? asset('/') . $row->getAddressImage->file_path . $row->getAddressImage->file_name : '',
                            'is_default' => $row->is_default,
                            'created_at' => date('Y-m-d H:i:s', strtotime($row->created_at)),
                            'updated_at' => date('Y-m-d H:i:s', strtotime($row->updated_at))
                        ];
                    }
    
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                    $this->result = [
                        'address_list' => $address_arr,
                        'home_static_version' => $home_static_version
                    ];
                } else {
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "نجحت العملية" : "Success";
                    $this->result = [
                        'address_list' => [],
                        'home_static_version' => $home_static_version
                    ];
                }
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'address_list' => [],
                    'home_static_version' => $home_static_version
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

    protected function delivery_slots(Request $request)
    {
        try {
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Success";
                $this->result = $this->getTimeSlotFromDB()[0];
          
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

    protected function get_all_categories(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $categories = Category::where('deleted', '=', 0)
                ->where('parent_id', '=', 0)
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
                                'store_count' => 2
                            ];
                        }
                    }
                    $category_arr[$key] = [
                        'id' => $row->id,
                        'category_name' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                        'category_image' => !empty($row->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategoryImage->file_name : '',
                        'store_count' => 2,
                        'subcategories' => $sub_category_arr
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'categories' => $category_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد معلومات" : "No data found";
                $this->result = [
                    'categories' => $category_arr
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
    
    protected function get_all_brands(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $brands = Brand::get();
            $brand_arr = [];
            if ($brands->count()) {
                foreach ($brands as $key => $row) {
                    $brand_arr[$key] = [
                        'id' => $row->id,
                        'brand_name' => $lang == 'ar' ? $row->brand_name_ar : $row->brand_name_en,
                        'brand_image' => !empty($row->getBrandImage) ? asset('images/brand_images') . '/' . $row->getBrandImage->file_name : '',
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'brands' => $brand_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد معلومات" : "No data found";
                $this->result = [
                    'brands' => $brand_arr
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

    protected function order_status(Request $request)
    {
        try {
            $lang = $request->header('lang');
            
            $this->required_input($request->input(), ['country_code', 'mobile']);

            $user_mobile = $request->input('mobile');
            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $user_mobile;
            $user = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            $orders_arr = [];

            if($user){
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
                    ->where(['fk_user_id' => $user->id])
                    ->where('orders.status', '>', '0')
                    ->where('orders.status', '!=', '4')
                    ->where('orders.status', '!=', '7')
                    // ->where('orders.created_at', '>=', date('Y-m-d', strtotime('-30 days')))
                    ->get();   
                if ($orders->count()) {
                    foreach ($orders as $order) {
                        $order_item = [];
                        $order_item['id'] = $order->id;
                        $order_item['status'] = $order->status;
                        
                        switch ($order->status) {
                            case 7:
                                $status_text = $lang=='ar' ? "تم توصيل طلبكم, شكرا لاستخدامكم جيب! 🥳" : "Order delivered! Thank you for using Jeeb 🥳";
                                break;
                            case 6:
                                $status_text = $lang=='ar' ? "مسافة الطريق و الطلب بكون على باب البيت 🚚" : "Wait for us! Your order is now out for delivery🚚";
                                break;
                            case 5:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Your order is packed and ready to go 📦";
                                break;
                            case 4:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Your order is packed and ready to go 📦";
                                break;
                            case 3:
                                $status_text = $lang=='ar' ? "تمت فوترة طلبكم وجاهز للتوصيل 📦" : "Your order is packed and ready to go 📦";
                                break;
                            default:
                                $status_text = $lang=='ar' ? "تمت الموافقة على الطلب وهو فيد التجهيز 🎉" : "Jeeb has received your order successfully! 🎉";
                        }

                        $order_item['status_text'] = $status_text;
                        $orders_arr[] = $order_item;
                    }
                } 
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'orders' => $orders_arr,
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

    
    protected function process_make_order($api_call,$tracking_id,$user,$lang,$sub_total,$delivery_charge,$address_id,$payment_type,$delivery_date,$delivery_time,$later_time,$buy_it_for_me_request_id,$later_time_blocked_slots,$use_wallet_balance,$coupon_id,$change_for,$delivery_preference,$payment_response,$coupon_discount)
    {
        try {
            // Find user and assign Tap reference ID if not existing
            $user = \App\Model\User::find($user->id);
            $tap_id = $user->tap_id;
            if ($tap_id=="") {
                $tap_id = $this->create_customer($user);
                User::find($user->id)->update([
                    'tap_id' => $tap_id
                ]);
            }

            //Test numbers array
            $test_numbers = explode(',', env("TEST_NUMBERS"));

            // Check user cart exist paythem product
            $cart_items = UserCart::where('fk_user_id', $user->id)->get();
            $types_in_cart = [];
            foreach ($cart_items as $item) {
                $product = BaseProduct::find($item->fk_product_id);
                if ($product) {
                    $product_type = $product->product_type;
                    if (!in_array($product_type, $types_in_cart)) {
                        $types_in_cart[] = $product_type;
                    }
                }
            }
            $is_paythem_only = (count($types_in_cart) === 1 && isset($types_in_cart[0]) && $types_in_cart[0] == 'paythem') ? 1 : 0;
            
            if ($is_paythem_only) {
                // No minimum and only online payment allowed
                // if($payment_type != 'online'){
                //     throw new Exception($lang == 'ar' ? "الدفع عند الاستلام لا ينطبق على أي من عناصر بطاقات الهدايا في سلة التسوق الخاصة بك." : "Cash on Delivery is not applicable for any Gift Card items in your cart.", 105);
                // }
                // Instant delivery only
                $delivery_time = 1;
                $start_time = date("H:i", strtotime("now"));
                $end_time = date("H:i", strtotime("now") + 1800);
                $later_time = $start_time.'-'.$end_time;
                // Delivery charge is 0
                $delivery_charge = 0;
            }else{
                // Check minimum purchase amount for test numbers 1QAR
                $minimum_purchase_amount = get_minimum_purchase_amount($user->ivp,$user->mobile,$user->id);
                if ($sub_total < $minimum_purchase_amount) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,"Minimum Order is $minimum_purchase_amount QAR");
                    }
                    throw new Exception($lang == 'ar' ? "الطلب الأدنى $minimum_purchase_amount ريال قطري" : "Minimum Order is $minimum_purchase_amount QAR", 105);
                }
                
                // ------------------------------------------------------------------------------------------------------
                // Check store closed or not, if closed reshedule it to next day morning or later in the morning
                // ------------------------------------------------------------------------------------------------------
                $store_open_time = env("STORE_OPEN_TIME");
                $store_close_time = env("STORE_CLOSE_TIME");
                if ($delivery_time == 1) {
                    if(strtotime($store_open_time) > time()) {
                        $delivery_time = 2;
                        $later_time = '09:00-10:00';
                    } elseif (strtotime($store_close_time) < time()) {
                        $delivery_time = 2;
                        $later_time = '09:00-10:00';
                        $datetime = new \DateTime('tomorrow');
                        $datetime = $datetime->format('Y-m-d');
                        $delivery_date = $datetime;
                    } else {
                        $start_time = date("H:i", strtotime("now") + $user->expected_eta);
                        $end_time = date("H:i", strtotime("now") + $user->expected_eta + 1800);
                        $later_time = $start_time.'-'.$end_time;
                    }
                }
                // Format the later time
                if ($delivery_time == 2) {
                    // No timeslot sent from frontend
                    if (!$later_time) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Please select the time slot');
                        }
                        $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Please select the time slot";
                        throw new Exception($error_message, 105);
                    }
                    $time_slot_validity = $this->validateOrderTimeslot($later_time, $delivery_date, $later_time_blocked_slots);
                    // Timeslot is not valid
                    if (!isset($time_slot_validity[0]) || !$time_slot_validity[0]) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Time slot expired, choose different time slot');
                        }
                        $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Time slot expired, choose different time slot";
                        throw new Exception($error_message, 105);
                    }
                    // Timeslot is expired
                    if (!isset($time_slot_validity[1]) || !$time_slot_validity[1]) {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Time slot expired, choose different time slot');
                        }
                        $error_message = $lang == 'ar' ? "انتهت الفترة، اختر وقت ثاني" : "Time slot expired, choose different time slot";
                        throw new Exception($error_message, 105);
                    }
                    // Format the timeslot
                    $later_time = isset($time_slot_validity[2]) ? $time_slot_validity[2] : $later_time;
                }

            }

            // Buy for me feature
            // Check user cart is empty or not
            if($buy_it_for_me_request_id){
                
                $exist = UserBuyItForMeCart::where(['fk_request_id' => $buy_it_for_me_request_id])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }else{

                $exist = UserCart::where(['fk_user_id' => $user->id])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }

            $total_amount = $sub_total + $delivery_charge;
            $payment_type = $payment_type;

            /* Order payment */
            if ($use_wallet_balance == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $user->id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;
    
                        $insert_arr = [
                            'amount' => $cart_payment_amount,
                            'status' => $status,
                            'paymentResponse' => $payment_response
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $status = "pending";

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $payment_response
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $status = "pending";

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $payment_response
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } else {
                    // Considering all others are COD
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                }
            }
            
            /* Order creation */
            if ($paid) {
                
                // Buy for me feature
                if($buy_it_for_me_request_id){
                    $bought_for = UserBuyItForMeRequest::find($buy_it_for_me_request_id);
                    $insert_arr = [
                        'fk_user_id' => $bought_for->from_user_id,
                        'bought_by' => $user->id,
                        'sub_total' => $sub_total,
                        'delivery_charge' => $delivery_charge,
                        'payment_type' => $payment_type, //cod or online
                        'order_type' => 1,
                    ];
                }else{
                    $insert_arr = [
                        'fk_user_id' => $user->id,
                        'sub_total' => $sub_total,
                        'delivery_charge' => $delivery_charge,
                        'payment_type' => $payment_type, //cod or online
                    ];
                }

                // coupon applied
                if ($coupon_id != '') {
                    
                    $insert_arr['fk_coupon_id'] = $coupon_id;
                    $insert_arr['coupon_discount'] = $coupon_discount;

                    $total_amount = $total_amount - $coupon_discount;

                    //arrange data for coupon uses table
                    $coupon_uses = CouponUses::where(['fk_user_id' => $user->id, 'fk_coupon_id' => $coupon_id])->first();
                    if ($coupon_uses) {
                        $uses_count = $coupon_uses->uses_count;
                    } else {
                        $uses_count = 0;
                    }
                    $coupon_uses_arr = [
                        'fk_user_id' => $user->id,
                        'fk_coupon_id' => $coupon_id,
                        'uses_count' => $uses_count + 1,
                    ];
                }

                // Setup the amount from card / COD / wallet
                if ($use_wallet_balance == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $user->id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                        // Deduct wallet
                        \App\Model\UserWallet::where(['fk_user_id' => $user->id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                        $UserWalletPayment = \App\Model\UserWalletPayment::create([
                            'fk_user_id' => $user->id,
                            'amount' => $amount_from_wallet,
                            'status' => $status,
                            'wallet_type' => 2,
                            'transaction_type' => 'debit'
                        ]);
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                        // Deduct wallet only for COD
                        if ($payment_type=='cod') {
                            \App\Model\UserWallet::where(['fk_user_id' => $user->id])->update([
                                'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                            ]);
                            $UserWalletPayment = \App\Model\UserWalletPayment::create([
                                'fk_user_id' => $user->id,
                                'amount' => $amount_from_wallet,
                                'status' => $status,
                                'wallet_type' => 2,
                                'transaction_type' => 'debit'
                            ]);
                        }
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                } else {
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['amount_from_card'] = $insert_arr['amount_from_card'];
                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = $payment_type == "cod" ? 1 : 0;
                $insert_arr['change_for'] = $payment_type == "cod" ? $change_for : '';

                $insert_arr['fk_store_id'] = $user->nearest_store;
                $insert_arr['buy_it_for_me_request_id'] = $buy_it_for_me_request_id;

                $insert_arr['is_paythem_only'] = $is_paythem_only;
                $insert_arr['products_type'] = implode(',',$types_in_cart);

                // Setup test orders
                if (in_array($user->mobile, $test_numbers)) {
                    $insert_arr['test_order'] = 1;
                } else {
                    $insert_arr['test_order'] = 0;
                }

                // Create order
                $create = Order::create($insert_arr);
                if ($create) {

                    // Update order ID
                    $orderId = $this->make_order_number($create->id);
                    $create->update(['orderId' => $orderId]);

                    // Update order payments
                    OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);
                    if ($use_wallet_balance == 1 && isset($UserWalletPayment) && $UserWalletPayment) {
                        \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);

                        OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $create->amount_from_card == 0 ? $orderId : $payment_response]);
                    }

                    // Update order address
                    $address = UserAddress::find($address_id);
                    if ($address) {
                        $order_addr_arr = [
                            'fk_order_id' => $create->id,
                            'name' => $address->name,
                            'mobile' => $address->mobile,
                            'landmark' => $address->landmark,
                            'address_line1' => $address->address_line1,
                            'address_line2' => $address->address_line2,
                            'latitude' => $address->latitude,
                            'longitude' => $address->longitude,
                            'address_type' => $address->address_type, //1:office,2:home,3:others
                        ];
                        OrderAddress::create($order_addr_arr);
                    }

                    // Update coupon usage
                    if ($coupon_id != '') {
                        $coupon_uses_arr['fk_order_id'] = $create->id;
                        CouponUses::create($coupon_uses_arr);
                    }

                    // Update order delivery slot 
                    OrderDeliverySlot::create([
                        'fk_order_id' => $create->id,
                        'delivery_date' => $delivery_date,
                        'delivery_time' => $delivery_time, //1:right now,2:later in the day
                        'later_time' => $later_time,
                        'delivery_preference' => $delivery_preference,
                        'expected_eta' => $user->expected_eta
                    ]);

                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        $this->update_whatsapp_tracking_response($tracking_id,'An error has been discovered');
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }

                // Buy for me feature
                // Adding cart products to order
                if($buy_it_for_me_request_id){
                    $cart_products = UserBuyItForMeCart::where(['fk_request_id' => $buy_it_for_me_request_id])->get();
                }else{
                    $cart_products = UserCart::where(['fk_user_id' => $user->id])->get();
                }
                foreach ($cart_products as $key => $value) { 
                    
                    //Get base products data 
                    $base_product = BaseProductStore::leftJoin('categories AS A', 'A.id','=', 'base_products_store.fk_category_id')
                    ->leftJoin('categories AS B', 'B.id','=', 'base_products_store.fk_sub_category_id')
                    ->leftJoin('brands','base_products_store.fk_brand_id', '=', 'brands.id')
                    ->select(
                        'base_products_store.*',
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
                            'fk_order_id' => $create->id,
                            'fk_product_id' => $value->fk_product_id,
                            'fk_product_store_id' => $value->fk_product_store_id,
                            'fk_store_id' => 0,
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
                        'fk_order_id' => $create->id,
                        'fk_product_id' => $base_product->fk_product_id,
                        'fk_product_store_id' => $base_product->id,
                        'fk_store_id' => $base_product->fk_store_id,
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
                    
                }

                // Set order status
                $order_status = 0;
                if ($create->payment_type=="cod" || $paid->status == "success") {
                    $order_confirmed = $this->order_confirmed($create->id, false, true);
                    $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                    if ($order_status>0) {
                        // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $message = "Paid successfully!";
                        $error_code = 200;
                    } else {
                        if (isset($tracking_id) && $tracking_id) {
                            $this->update_whatsapp_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                        }
                        $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                        throw new Exception($error_message, 105);
                    }
                } else {
                    $message = $lang == 'ar' ? "انتظار الدفع" : "Payment pending!";
                    $error_code = 200;
                }

                // Delete cart items 
                if($buy_it_for_me_request_id){
                    if ($order_status > 0) {
                        UserBuyItForMeCart::where(['fk_request_id' => $buy_it_for_me_request_id])->delete();
                        UserBuyItForMeRequest::find($buy_it_for_me_request_id)->update(['status' => 3]);
                    }
                }else{
                    if ($order_status > 0) {
                        UserCart::where(['fk_user_id' => $user->id])->delete();
                    }
                }
                
                // Send order details
                $order_arr = $this->get_order_arr($lang, $create->id);

                // Send sms to the storekeeper
                $this->send_storekeeper_notification($create);

                // Send vendor new order push notification
                $this->send_vendor_new_order_push_notification($create);

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = [
                    'status' => isset($order_arr) && isset($order_arr["status"]) && $order_arr["status"]>0 ? true : false,
                    'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                    'order' => isset($order_arr) ? $order_arr : [], 
                    'pay_url' => $create->payment_type=='online' ? env('APP_URL','https://jeeb.tech/').'bifm_order/payment/'.base64_encode($create->id) : '', 
                    'tap_id' => $tap_id
                ];
            } else {

                $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                $this->error = true;
                $this->status_code = 201;
                $this->message = $message;
                $this->result = [
                    'status' => false,
                    'orderId' => 0, 
                    'order' => [], 
                    'tap_id' => $tap_id
                ];

            }

        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            $this->update_whatsapp_tracking_response($tracking_id,$this->makeJson());
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
            
            $this->required_input($request->input(), ['country_code', 'mobile', 'id', 'action']);

            $user_mobile = $request->input('mobile');
            $country_code = trim($request->input('country_code'));
            $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
            $phone = $country_code . $user_mobile;
            $user = User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
            
            $tracking_id = $this->add_whatsapp_tracking_data($user->id, $phone, 'buy_it_for_me_cart', $request, '');

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
            $buy_it_for_me_user = User::find($buy_it_for_me_request->from_user_id);
            
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

            $my_cart_data = $this->get_buy_it_for_me_data($lang,$request->input('id'),$user);
            if ($my_cart_data && is_array($my_cart_data) && isset($my_cart_data['result'])) {
                // $my_cart_data['result']['address_list'] = get_userAddressList($buy_it_for_me_request->from_user_id);
                $my_cart_data['result']['payment_url'] = env('APP_URL','https://jeeb.tech/').'bifm_order/'.base64_encode($request->input('id'));
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم تحديث السلة" : "Cart updated successfully";
                $this->result = $my_cart_data['result'];

                // $delivery_charge = 10;
                
                // $sub_total = $my_cart_data['result']['sub_total'];
                // $delivery_charge = $request->input('delivery_charge');
                // $address_id = $address->id;
                // $payment_type = $request->input('payment_type');
                // $delivery_date = $request->input('delivery_date');
                // $delivery_time = $request->input('delivery_time');
                // $later_time = $request->input('later_time');
                // $buy_it_for_me_request_id = $buy_it_for_me_request->id;
                // $later_time_blocked_slots = $request->input('later_time_blocked_slots');
                // $use_wallet_balance = $request->input('use_wallet_balance');
                // $coupon_id = $request->input('coupon_id');
                // $change_for = $request->input('change_for');
                // $delivery_preference = $request->input('delivery_preference');
                // $payment_response = $request->input('paymentResponse');
                // $coupon_discount = $request->input('coupon_discount');


                // return $this->process_make_order(
                //     'whatsapp',
                //     $tracking_id,
                //     $buy_it_for_me_user,
                //     $lang,
                //     $my_cart_data['result']['sub_total'],
                //     $delivery_charge,
                //     $address_id,
                //     $payment_type,
                //     $delivery_date,
                //     $delivery_time,
                //     $later_time,
                //     $buy_it_for_me_request_id,
                //     $later_time_blocked_slots,
                //     $use_wallet_balance,
                //     $coupon_id,
                //     $change_for,
                //     $delivery_preference,
                //     $payment_response,
                //     $coupon_discount
                // );
    
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    $this->update_whatsapp_tracking_response($tracking_id,'An error has been discovered');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            
            
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                $this->update_whatsapp_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            $this->update_whatsapp_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }
    
    private function get_buy_it_for_me_data($lang,$id,$user)
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

            $wallet_balance = get_userWallet($user->id);

            $cartTotal = UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                    ->selectRaw("SUM(user_buy_it_for_me_cart.total_price) as total_amount")
                    ->where('user_buy_it_for_me_cart.fk_request_id', $id)
                    ->orderBy('user_buy_it_for_me_cart.id', 'desc')
                    ->first();

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

}
