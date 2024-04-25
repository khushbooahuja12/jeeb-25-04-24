<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Coupon;
use App\Model\AdminSetting;
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
use App\Model\DeliverySlot;
use App\Model\Driver;
use App\Model\Product;
use App\Model\Storekeeper;
use App\Model\StorekeeperProduct;
use App\Model\TechnicalSupport;
use App\Model\TechnicalSupportChat;
use App\Model\User;
use App\Model\Store;
use App\Model\UserDeviceDetail;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\UserBuyItForMeCart;
use App\Model\UserBuyItForMeRequest;

class OrderBpController extends CoreApiController
{

    use \App\Http\Traits\PushNotification;

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];
    protected $order_status = [
        '0' => 'Processing',
        '1' => 'Order confirmed',
        '2' => 'Order assigned',
        '3' => 'Order invoiced',
        '4' => 'Cancelled',
        '6' => 'Out for delivery',
        '7' => 'Order delivered'
    ];
    protected $order_status_ar = [
        '0' => 'يعالج',
        '1' => 'تم تاكيد الطلب',
        '2' => 'تم تعيين طلبك',
        '3' => 'تم تجهيز طلبك',
        '4' => 'تم الغاء طلبك',
        '6' => 'طلبك على الطريق',
        '7' => 'تم توصيل طلبك'
    ];
    protected $order_preferences = [
        '0' => "Contactless Delivery",
        '1' => "Don't Ring/Knock",
        '2' => "Beware of Pets",
        '3' => "Leave at Door",
    ];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = [
            'get_coupons', 'get_payment_cards', 'add_payment_card',
            'apply_coupon', 'get_delivery_charge', 'place_order', 'my_orders',
            'rate_order', 'rate_product', 'reorder', 'confirm_order', 'cancel_order',
            'order_detail', 'make_order', 'getOrderRefId',
            'get_subtotal_forgot_something_order', 'make_forgot_something_order', 
            'get_subtotal_replacement_order', 'make_replacement_order'
        ];

        //setting user id which will be accessable for all functions
        $this->user_id = 0;
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

        $this->loadApi = new \App\Model\LoadApi();

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function get_coupons(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $user = User::find($this->user_id);

            $coupons = Coupon::where('status', '=', 1)
                ->where('expiry_date', '>', \Carbon\Carbon::now()->format('Y-m-d'))
                ->orderBy('expiry_date', 'asc')->get();

            if ($coupons->count()) {
                foreach ($coupons as $key => $value) {
                    $coupon_arr[$key] = [
                        'id' => $value->id,
                        'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                        'description' => $lang == 'ar' ? $value->description_ar : $value->description_en,
                        'coupon_code' => $value->coupon_code,
                        'coupon_image' => $value->getCouponImage ? asset('/') . $value->getCouponImage->file_path . $value->getCouponImage->file_name : ''
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Coupon List";
                $this->result = [
                    'coupons' => $coupon_arr,
                    'products' => []
                ];
            } else {
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

                if ($user) {
                    
                    $user->nearest_store = 14;
                    $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
                    if ($nearest_store) {
                        $default_store_no = get_store_no($nearest_store->name);
                        $company_id = $nearest_store->company_id;
                    } else {
                        $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                        throw new Exception($error_message, 105);
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
                        ->where($this->products_table . '.store' . $default_store_no, '=', 1)
                        ->where($this->products_table . '.fk_company_id', '=', $company_id)
                        ->where($this->products_table . '.offered', '=', 1)
                        ->groupBy($this->products_table . '.id')
                        ->orderBy($this->products_table . '.id', 'desc')
                        ->offset($offset)
                        ->limit($limit)
                        ->get();
    
                    $product_arr = get_product_dictionary($products, $this->user_id, $lang, $request->header('Authorization'));
    
                } else {
                    $product_arr = [];
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد كوبونات متاحة الآن" : "No coupons are available right now";
                $this->result = [
                    'coupons' => [],
                    'products' => $product_arr
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

    protected function apply_coupon(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['coupon_code']);

            $coupon = Coupon::where(['coupon_code' => $request->input('coupon_code'), 'status' => 1])->first();
            if (!$coupon) {
                throw new Exception($lang == 'ar' ? "الكويون غير صالح" : "Coupon doesn't exist", 105);
            }
            // Check the expiry date
            if ($coupon->expiry_date<date("Y-m-d")) {
                throw new Exception($lang == 'ar' ? "الكوبون منتهي الصلاحية" : "Coupon is expired", 105);
            }

            $coupon_uses = CouponUses::where(['fk_user_id' => $this->user_id, 'fk_coupon_id' => $coupon->id])->count();
            if ($coupon_uses && $coupon_uses >= $coupon->uses_limit) {
                throw new Exception($lang == 'ar' ? "تم تعدي المحاولات المسموحة" : "Coupon Usage exceeded", 105);
            }

            // if ($coupon->type == 1) {
            //     if ($request->input('total_amount') >= $coupon->min_amount) {
            //         $discount = ($request->input('total_amount') * $coupon->discount) / 100;
            //         $amount_after_discount = $request->input('total_amount') - $discount;
            //     } else {
            //         throw new Exception($lang == 'ar' ? "الكويت غير صالح" : "Coupon not valid", 105);
            //     }
            // } elseif ($coupon->type == 2) {
            //     if ($request->input('total_amount') >= $coupon->min_amount) {
            //         $amount_after_discount = $request->input('total_amount') - $coupon->discount;
            //     } else {
            //         throw new Exception($lang == 'ar' ? "الكويت غير صالح" : "Coupon not valid", 105);
            //     }
            // }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم اضافة الكوبون" : "Coupon applied";
            $this->result = [
                // 'total_amount' => $request->input('total_amount'),
                // 'total_amount_after_discount' => (string) round($amount_after_discount, 2),
                // 'coupon_discount' => (string) (round($discount, 2) ?? 0),
                'coupon_id' => $coupon->id,
                'type' => $coupon->type,
                'min_amount' => $coupon->min_amount,
                'discount' => $coupon->discount
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

    protected function delivery_slots(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $isTodaySlotAvailable = DeliverySlot::where('date', '=', \Carbon\Carbon::now()->format('Y-m-d'))
                ->where('from', '>=', \Carbon\Carbon::now()->subMinutes(60)->format('H:i:s'))
                ->count();

            if ($isTodaySlotAvailable == 0) {
                $slot_dates = DeliverySlot::where('date', '<=', \Carbon\Carbon::now()->addDays(3)->format('Y-m-d'))
                    ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                    ->groupBy('date')
                    ->orderBy('id', 'asc')
                    ->get();
            } else {
                $slot_dates = DeliverySlot::where('date', '<=', \Carbon\Carbon::now()->addDays(2)->format('Y-m-d'))
                    ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                    ->groupBy('date')
                    ->orderBy('id', 'asc')
                    ->get();
            }

            // pp($slot_dates);die;

            $slot_arr = [];
            if ($slot_dates->count()) {
                foreach ($slot_dates as $key => $value) {
                    $slot_arr[$key]['date'] = $value->date;

                    // $slots = DeliverySlot::where(['date' => $value->date])
                    //         ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s'))
                    //         ->orderBy('from', 'asc')
                    //         ->get();
                    $slots = DeliverySlot::where(['date' => $value->date])
                        ->where(DB::raw("CONCAT(date,' ',`from`)"), '>=', \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s'))
                        ->orderBy('from', 'asc')
                        ->get();

                    $slot = [];
                    if ($slots->count()) {
                        foreach ($slots as $key1 => $value1) {
                            $noOfOrdersInThisSlot = \App\Model\OrderDeliverySlot::where('delivery_date', '=', $value->date)
                                ->where('delivery_slot', '=', date('H:i', strtotime($value1->from)) . '-' . date('H:i', strtotime($value1->to)))
                                ->orderBy('id', 'desc')
                                ->count();

                            $slot_date_time = $value->date . ' ' . date('H:i:s', strtotime($value1->from));
                            // if ($slot_date_time < \Carbon\Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s')) {
                            if ($slot_date_time < \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s')) {
                                $is_full = 0;
                            } else {
                                $is_full = 0;
                            }

                            $slot[$key1]['is_full'] = $is_full;
                            $slot[$key1]['range'] = date('H:i', strtotime($value1->from)) . '-' . date('H:i', strtotime($value1->to));
                        }
                    }
                    $slot_arr[$key]['slot'] = $slot;
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'slots' => $slot_arr
                ];
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

    protected function getOrderRefId(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // Check and confirm order is not invoiced yet
            if ($request->id && $request->id!="") {
                $parent_order = Order::find($request->input('id'));
                if (!$parent_order) {
                    throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
                }
                elseif ($parent_order->status>=3) {
                    throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
                }
            }

            // $user = \App\Model\User::find($this->user_id);
            // if ($user->is_ios_new_version == 1 && $user->getDeviceToken->device_type == 1) {
            //     throw new Exception($lang == 'en' ? "Free Delivery on Latest Update. Update Jeeb app from App Store" : "توصيلنا صار مجاني. حدث التطبيق", 105);
            // }

            //adding cond for out of stock product
            // $cartProducts = UserCart::where(['fk_user_id' => $this->user_id])->orderBy('id', 'desc')->get();

            // $outOfStockProducts = [];
            // foreach ($cartProducts as $key => $value) {
            //     if ($product->stock == 0) {
            //         array_push($outOfStockProducts, $lang == 'ar' ? $product->product_name_ar : $product->product_name_en);
            //     }
            // }
            // if (count($outOfStockProducts) != 0) {
            //     throw new Exception('Some products are not available', 202);
            // }

            // if ($request->input('id') == '') {
            //     if ($request->input('delivery_date') . ' ' . substr($request->input('delivery_slot'), 0, 5) . ':00' < \Carbon\Carbon::now()->subMinutes(60)->format('Y-m-d H:i:s')) {
            //         throw new Exception("Slots are filled. Please fill your order in next slot", 201);
            //     }
            // }

            $refId = time().$this->user_id;

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = ['refId' => $refId];
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

    protected function make_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['sub_total', 'delivery_charge', 'delivery_date', 'payment_type', 'address_id']);
            
            $tracking_id = add_tracking_data($this->user_id, 'make_order_bp', $request, '');
            
            $user = \App\Model\User::find($this->user_id);

            //Test numbners array
            $test_numbers = explode(',', env("TEST_NUMBERS"));

            // Check minimum purchase amount for test numbers 1QAR
            $minimum_purchase = \App\Model\AdminSetting::where('key', '=', 'minimum_purchase_amount')->first();
            if (in_array($user->mobile, $test_numbers)) {
                $minimum_purchase_amount = "1";
            } else {
                $minimum_purchase_amount = $minimum_purchase->value;
            }
            if ($request->input('sub_total') < $minimum_purchase_amount) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Minimum Order is $minimum_purchase_amount QAR");
                }
                throw new Exception($lang == 'ar' ? "الطلب الأدنى $minimum_purchase_amount ريال قطري" : "Minimum Order is $minimum_purchase_amount QAR", 105);
            }

            /* add/update the device detail */
            if ($request->input('device_token')!= '') {
                $device_detail_arr = [
                    'fk_user_id' => $this->user_id,
                    'device_type' => $request->input('device_type') ?? 0, //1:iOS,2:android
                    'app_version' => $request->input('app_version') ?? 'not_detected',
                    'device_token' => $request->input('device_token') ?? '123456789',
                ];
                if (UserDeviceDetail::where(['fk_user_id' => $this->user_id])->first()) {
                    UserDeviceDetail::where(['fk_user_id' => $this->user_id])->update($device_detail_arr);
                } else {
                    UserDeviceDetail::create($device_detail_arr);
                }
            }

            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed reshedule it to next day morning or later in the morning
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = $request->input('delivery_time');
            $later_time = $request->input('later_time');
            $delivery_date = $request->input('delivery_date');
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");
            if ($delivery_time == 1) {
                if(strtotime($store_open_time) > time()) {
                    $delivery_time = 2;
                    $later_time = '09:00-09:30';
                } elseif (strtotime($store_close_time) < time()) {
                    $delivery_time = 2;
                    $later_time = '09:00-09:30';
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
                // if (!$later_time) {
                //     $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Please select the time slot";
                //     throw new Exception($error_message, 105);
                // }
                $later_time_arr = explode('-', $later_time);
                // if (empty($later_time_arr) || !isset($later_time_arr[0])) {
                //     $error_message = $lang == 'ar' ? "الرجاء تحديد الفترة الزمنية" : "Please select the time slot";
                //     throw new Exception($error_message, 105);
                // }
                if ($later_time && empty($later_time_arr) || !isset($later_time_arr[0])) {
                    $start_time = $later_time_arr[0]<5 ? '0'.$later_time_arr[0] : $later_time_arr[0];
                    $end_time = $later_time_arr[1]<5 ? '0'.$later_time_arr[1] : $later_time_arr[1];
                    $later_time = $start_time.'-'.$end_time;
                }
            }

            // if ($this->user_id == 2) {
            //     throw new Exception("Can't place new order, You are blocked by admin", 105);
            // }

            if($request->input('buy_it_for_me_request_id')){
                
                $exist = UserBuyItForMeCart::where(['fk_request_id' => $request->input('buy_it_for_me_request_id')])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }else{

                $exist = UserCart::where(['fk_user_id' => $this->user_id])->get();
                if (!$exist->count()) {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'Cart is empty');
                    }
                    throw new Exception($lang == 'ar' ? "سلتك فاضية" : "Cart is empty", 105);
                }
            }

            

            $total_amount = $request->input('sub_total') + $request->input('delivery_charge');
            $msg_to_admin = '';
            $payment_amount_text = '';
            $payment_type = $request->input('payment_type');

            /* Order payment */
            if ($request->input('use_wallet_balance') == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "Wallet (QAR. $total_amount)";
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));
    
                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;
    
                        $insert_arr = [
                            'amount' => $cart_payment_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "Card (QAR. $cart_payment_amount) / Wallet (QAR. $user_wallet->total_points)";
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $cart_payment_amount = $total_amount - $user_wallet->total_points;

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "COD (QAR. $cart_payment_amount) / Wallet (QAR. $user_wallet->total_points)";
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));

                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "Card (QAR. $total_amount)";
                    } else {
                        // Considering all others are COD
                        $status = 'success';

                        $insert_arr = [
                            'amount' => $total_amount,
                            'status' => 'success'
                        ];
                        $paid = OrderPayment::create($insert_arr);
                        $payment_amount_text .= "COD (QAR. $total_amount)";
                    }
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));

                    $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $request->input('paymentResponse')
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "Card (QAR. $total_amount)";
                } else {
                    // Considering all others are COD
                    $status = 'success';

                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                    $payment_amount_text .= "COD (QAR. $total_amount)";
                }
            }
            if ($paid) {
                if($request->input('buy_it_for_me_request_id')){

                    $bought_for = UserBuyItForMeRequest::find($request->input('buy_it_for_me_request_id'));
            
                    $insert_arr = [
                        'fk_user_id' => $bought_for->from_user_id,
                        'bought_by' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => $request->input('delivery_charge'),
                        'payment_type' => $request->input('payment_type'), //cod or online
                    ];
                }else{
                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => $request->input('delivery_charge'),
                        'payment_type' => $request->input('payment_type'), //cod or online
                    ];
                }

                if ($request->input('coupon_id') != '') {
                    $this->required_input($request->input(), ['coupon_discount']);

                    $insert_arr['fk_coupon_id'] = $request->input('coupon_id');
                    $insert_arr['coupon_discount'] = $request->input('coupon_discount');

                    $total_amount = $total_amount - $request->input('coupon_discount');

                    //arrange data for coupon uses table
                    $coupon_uses = CouponUses::where(['fk_user_id' => $this->user_id, 'fk_coupon_id' => $request->input('coupon_id')])->first();
                    if ($coupon_uses) {
                        $uses_count = $coupon_uses->uses_count;
                    } else {
                        $uses_count = 0;
                    }
                    $coupon_uses_arr = [
                        'fk_user_id' => $this->user_id,
                        'fk_coupon_id' => $request->input('coupon_id'),
                        'uses_count' => $uses_count + 1,
                    ];
                }

                if ($request->input('use_wallet_balance') == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                    if ($status != 'rejected') {
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                    }

                    $UserWalletPayment = \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $status != 'rejected' ? $amount_from_wallet : '',
                        'status' => $status,
                        'wallet_type' => 2,
                        'transaction_type' => 'debit'
                    ]);
                } else {
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['amount_from_card'] = $insert_arr['amount_from_card'];
                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = $request->input('payment_type') == "cod" ? 1 : 0;
                $insert_arr['change_for'] = $request->input('payment_type') == "cod" ? $request->input('change_for') : '';

                $insert_arr['fk_store_id'] = $user->nearest_store;

                // Setup test orders
                if (in_array($user->mobile, $test_numbers)) {
                    $insert_arr['test_order'] = 1;
                } else {
                    $insert_arr['test_order'] = 0;
                }

                // Create order
                $create = Order::create($insert_arr);
                if ($create) {
                    OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);

                    $orderId = (1000 + $create->id);
                    Order::find($create->id)->update(['orderId' => $orderId]);

                    if ($request->input('use_wallet_balance') == 1) {
                        \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);

                        OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $create->amount_from_card == 0 ? $orderId : $request->input('paymentResponse')]);
                    }

                    $address = UserAddress::find($request->input('address_id'));
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

                    if ($request->input('coupon_id') != '') {
                        $coupon_uses_arr['fk_order_id'] = $create->id;
                        CouponUses::create($coupon_uses_arr);
                    }

                    //update order delivery slot 
                    OrderDeliverySlot::create([
                        'fk_order_id' => $create->id,
                        'delivery_date' => $delivery_date,
                        'delivery_time' => $delivery_time, //1:right now,2:later in the day
                        'later_time' => $later_time,
                        'delivery_preference' => $request->input('delivery_preference'),
                        'expected_eta' => $user->expected_eta
                    ]);

                    $driver_flag = false;
                    if ($delivery_time == 1) {
                        
                        /* -------------------- 
                        We don't assign drivers until the order is invoiced, 
                        order can be invoiced either by storekeeper or by admin 
                        --------------------- */
                        
                        // $driver = Driver::where(['fk_store_id' => $user->nearest_store, 'is_available' => 1])
                        //     ->orderBy('name', 'asc')
                        //     ->first();
                        // if ($driver && $driver->getLocation) {
                        //     $distance = get_distance($driver->getLocation->latitude, $driver->getLocation->longitude, $driver->getStore->latitude, $driver->getStore->longitude, 'K');
                        // } else {
                        //     $distance = '';
                        // }

                        // if ($driver && $distance <= 1 && $distance != '') {
                        //     OrderDriver::create([
                        //         'fk_order_id' => $create->id,
                        //         'fk_driver_id' => $driver->id,
                        //         'status' => 1
                        //     ]);
                        //     Driver::find($driver->id)->update(['is_available' => 0]);
                        //     $driver_flag = true;
                        // } else {
                        //     OrderDriver::create([
                        //         'fk_order_id' => $create->id,
                        //         'fk_driver_id' => 0,
                        //         'status' => 0
                        //     ]);
                        //     $driver_flag = false;
                        // }
                    }

                    // Set delivery time
                    if ($delivery_time==1) {
                        $delivery_in = getDeliveryInTimeRange($user->expected_eta, $create->created_at, $lang);
                    } else {
                        // Get the sheduled earliest time
                        // $later_time = strtok($later_time, '-');
                        // $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                        // $later_delivery_time = $delivery_date.' '.$later_time.':00';
                        // $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang);
                        
                        $later_time_arr = explode('-', $later_time);
                        $later_time_start = is_array($later_time_arr) && isset($later_time_arr[0]) ? date('h:ia',strtotime($later_time_arr[0])) : "";
                        $later_time_end = is_array($later_time_arr) && isset($later_time_arr[1]) ? date('h:ia',strtotime($later_time_arr[1])) : "";
                        $delivery_in = $later_time_start.'-'.$later_time_end;
                    }

                    $delivery_in_text = $lang == 'ar' ? $delivery_in." التسليم الساعة" : "delivery by ".$delivery_in;

                    $order_arr = [
                        'id' => $create->id,
                        'orderId' => $orderId ?? "",
                        'sub_total' => $create->sub_total,
                        'total_amount' => "$create->total_amount",
                        'delivery_charge' => $create->delivery_charge,
                        'coupon_discount' => $create->coupon_discount ?? '',
                        'item_count' => $create->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($create->created_at)),
                        'status' => $create->status,
                        'delivery_date' => $create->getOrderDeliverySlot->delivery_date ?? "",
                        'delivery_in' => $delivery_in_text,
                        'delivery_time' => $create->getOrderDeliverySlot->delivery_time ?? "",
                        'later_time' => $create->getOrderDeliverySlot->later_time ?? "",
                        'delivery_preference' => $create->getOrderDeliverySlot->delivery_preference ?? "",
                        'forgot_something_store_id' => env("FORGOT_SOMETHING_STORE_ID")
                    ];

                } else {
                    if (isset($tracking_id) && $tracking_id) {
                        update_tracking_response($tracking_id,'An error has been discovered');
                    }
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }

                // Set order status
                $order_status = 0;
                $msg_to_admin .= "Order #$orderId - Payment Method: $payment_amount_text.\n\n";
                $msg_to_admin .= "Payment Status: ";
                if ($create->payment_type=="cod") {
                    $order_status = $driver_flag ? 2 : 1;
                    Order::find($create->id)->update(['status' => $order_status]);
                    OrderStatus::create([
                        'fk_order_id' => $create->id,
                        'status' => 0
                    ]);
                    if ($driver_flag) {
                        OrderStatus::create([
                            'fk_order_id' => $create->id,
                            'status' => 1
                        ]);
                    }
                    OrderStatus::create([
                        'fk_order_id' => $create->id,
                        'status' => $order_status
                    ]);

                    $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $error_code = 200;
                    $msg_to_admin .= "Success";
                } else {
                    if ($paid->status != "success") {
                        OrderStatus::create([
                            'fk_order_id' => $create->id,
                            'status' => 0
                        ]);
    
                        if (isset($paymentResponseArr->error_code) && $paymentResponseArr->error_code != 0) {
                            if ($paymentResponseArr->error_message == 'INSUFFICIENT_FUNDS') {
                                $message = $lang == 'ar' ? "رصيدك غير كافي" : "Insufficient funds";
                                $error_code = 201;
                                $msg_to_admin .= "Failed (Insufficient funds)";
                            } else {
                                $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                                $error_code = 201;
                                $msg_to_admin .= "Failed ($paymentResponseArr->error_message)";
                            }
                        } else {
                            $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                            $error_code = 201;
                            $msg_to_admin .= "Failed (Payment error)";
                        }
                    } else {
                        $order_status = $driver_flag ? 2 : 1;
                        Order::find($create->id)->update(['status' => $order_status]);
                        OrderStatus::create([
                            'fk_order_id' => $create->id,
                            'status' => 0
                        ]);
                        if ($driver_flag) {
                            OrderStatus::create([
                                'fk_order_id' => $create->id,
                                'status' => 1
                            ]);
                        }
                        OrderStatus::create([
                            'fk_order_id' => $create->id,
                            'status' => $order_status
                        ]);
    
                        $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $error_code = 200;
                        $msg_to_admin .= "Success";
                    }
                }
                $order_arr['status'] = $order_status;
                $order_arr['forgot_something_store_id'] = env("FORGOT_SOMETHING_STORE_ID");
                
                // ---------------------------------------------------------------
                // Send notification SMS to admins
                // ---------------------------------------------------------------
                $test_order = $create->test_order;
                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                // SMS to Fleet Manager
                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                if ($send_notification && $send_notification_number) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                }
                // SMS to CEO
                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                if ($send_notification && $send_notification_number_2) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                }
                // SMS to CTO
                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                if ($send_notification && $send_notification_number_3) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                }
                // SMS to Backend Developer
                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                if ($send_notification && $send_notification_number_4) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                }
                // SMS to Driver
                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                if ($send_notification && $send_notification_number_5) {
                    $msg = $msg_to_admin;
                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                }
                
                // Adding cart products to order
                if($request->input('buy_it_for_me_request_id')){
                    $cart_products = UserBuyItForMeCart::where(['fk_request_id' => $request->input('buy_it_for_me_request_id')])->get();
                }else{
                    $cart_products = UserCart::where(['fk_user_id' => $this->user_id])->get();
                }
                foreach ($cart_products as $key => $value) { 
                    
                    //Get base products data 
                    $base_product = BaseProductStore::join('base_products', 'base_products_store.fk_product_id', '=', 'base_products.id')
                    ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                    ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                    ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                    ->select(
                        'base_products_store.id as fk_product_store_id',
                        'base_products_store.itemcode',
                        'base_products_store.barcode',
                        'base_products_store.unit as product_store_unit',
                        'base_products_store.margin',
                        'base_products_store.product_distributor_price as distributor_price',
                        'base_products_store.product_store_price AS product_price',
                        'base_products_store.stock as product_store_stock',
                        'base_products_store.other_names',
                        'base_products_store.fk_product_id',
                        'base_products_store.fk_store_id',
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
                    ->where('base_products_store.fk_product_id', '=', $value->fk_product_id)
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
                        'fk_product_store_id' => $base_product->fk_product_store_id,
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

                    // Assign storekeeper based on the products store
                    if ($order_status > 0) {
                        $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                            ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                            ->where('storekeepers.deleted', '=', 0)
                            ->where('storekeepers.fk_store_id', '=', $base_product->fk_store_id)
                            ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $base_product->fk_sub_category_id)
                            ->first();
                        if ($storekeeper) {
                            StorekeeperProduct::create([
                                'fk_storekeeper_id' => $storekeeper->id ?? '',
                                'fk_order_id' => $create->id,
                                'fk_order_product_id' => $orderProduct->id,
                                'fk_store_id' => $storekeeper->fk_store_id,
                                'fk_product_id' => $base_product->fk_product_id,
                                'status' => 0
                            ]);
                            // Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                        } else {
                            $storekeeper = Storekeeper::select("storekeepers.*")
                                ->where('storekeepers.default', '=', 1)
                                ->where('storekeepers.deleted', '=', 0)
                                ->where('storekeepers.fk_store_id', '=', $base_product->fk_store_id)
                                ->first();
                                if ($storekeeper) {
                                    StorekeeperProduct::create([
                                        'fk_storekeeper_id' => $storekeeper->id ?? '',
                                        'fk_order_id' => $create->id,
                                        'fk_order_product_id' => $orderProduct->id,
                                        'fk_store_id' => $storekeeper->fk_store_id,
                                        'fk_product_id' => $base_product->fk_product_id,
                                        'status' => 0
                                    ]);
                                    // Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                                }
                        }
                    }
                }
                // Delete cart items 
                if($request->input('buy_it_for_me_request_id')){
                    if ($order_status > 0) {
                        UserBuyItForMeCart::where(['fk_request_id' => $request->input('buy_it_for_me_request_id')])->delete();
                        UserBuyItForMeRequest::find($request->input('buy_it_for_me_request_id'))->update(['status' => 3]);
                    }
                }else{
                    if ($order_status > 0) {
                        UserCart::where(['fk_user_id' => $this->user_id])->delete();
                    }
                }

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = ['orderId' => $orderId, 'order' => $order_arr];
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

    protected function get_subtotal_forgot_something_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'product_json']);

            // Get order from request
            $order = Order::where(['id'=>$request->input('id')])
                            ->where(function ($query) {
                                    $query->where('fk_user_id', '=', $this->user_id)
                                    ->orWhere('bought_by', '=', $this->user_id);
                                })->first();
            if (!$order) {
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            elseif ($order->status>=3) {
                throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
            }


            // Set price key and price total
            $price_total = 0;
            $price_total_with_qunatity_mismatch = 0;
            $cartItemArr = [];
            $cartItemQuantityMismatch = [];
            $cartItemOutofstockArr = [];
            
            // Calculate price total for the selected replacing products
            if ($request->input('product_json') != '') {
                $content_arr = json_decode($request->input('product_json'));

                foreach ($content_arr as $key => $value) {
                    $product = BaseProductStore::join('base_products', 'base_products_store.fk_product_id', '=', 'base_products.id')
                        ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                        ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                        ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                        ->select(
                            'base_products_store.id as fk_product_store_id',
                            'base_products_store.itemcode',
                            'base_products_store.barcode',
                            'base_products_store.unit as product_store_unit',
                            'base_products_store.margin',
                            'base_products_store.product_distributor_price as distributor_price',
                            'base_products_store.product_store_price AS product_price',
                            'base_products_store.stock as product_store_stock',
                            'base_products_store.other_names',
                            'base_products_store.fk_product_id',
                            'base_products_store.fk_store_id',
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
                        ->where('base_products_store.id', '=', $value->product_store_id)
                        ->first();
                    
                    $value->quantity = $value->product_quantity;
                    
                    // If products are not belongs to the company
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المنتجات غير متوفرة' : 'Products are not available.';
                        throw new Exception($error_message, 105);
                    }

                    // Check product out of stock
                    
                    if ($product->product_store_stock > 0 && $product->product_store_stock < $value->quantity) {
                        $cartItemQuantityMismatch[$key] = [
                            'product_id' => $product->id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                        // Calculate the price and amount
                        $price_total_with_qunatity_mismatch += number_format(($product->product_price * $product->product_store_stock), 2);
                    }
                    // For other companies
                    elseif ($product->product_store_stock > 0) {
                        $cartItemArr[$key] = [
                            'product_id' => $product->id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                        // Calculate the price and amount
                        $price_total += number_format(($product->product_price * $value->product_quantity), 2);
                        $price_total_with_qunatity_mismatch += number_format(($product->product_price * $value->product_quantity), 2);
                    } else {
                        $cartItemOutofstockArr[$key] = [
                            'product_id' => $product->id,
                            'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                            'product_image' => $product->product_image_url ?? '',
                            'product_price' => (string) number_format($product->product_price, 2),
                            'product_total_price' => (string) number_format($product->product_price, 2)*$value->quantity,
                            'cart_quantity' => $value->quantity,
                            'unit' => $product->unit,
                            'product_discount' => $product->margin,
                            'stock' => (string) $product->product_store_stock,
                            'product_category_id' => $product->fk_category_id,
                            'product_sub_category_id' => $product->fk_sub_category_id,
                            'min_scale' => $product->min_scale ?? '',
                            'max_scale' => $product->max_scale ?? ''
                        ];
                    }

                }

            }
            
            // Get user wallet amount
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $wallet_balance = $user_wallet ? $user_wallet->total_points : "0.00";

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم احتساب المجموع بنجاح" : "Sub total calculated successfully";
            $this->result = [
                'sub_total' => (string) number_format($price_total, 2),
                'sub_total_with_quantity_mismatch' => (string) number_format($price_total_with_qunatity_mismatch, 2),
                'wallet_balance' => $wallet_balance,
                'cart_items' => array_values($cartItemArr),
                'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch)
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

    protected function make_forgot_something_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'sub_total']);

            $tracking_id = add_tracking_data($this->user_id, 'make_forgot_something_order_bp', $request, '');
            
            $total_amount = $request->input('sub_total');
            $payment_type = $request->input('payment_type');

            if ($total_amount<=0) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Total_amount is $total_amount QAR");
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Check and confirm order is not invoiced yet
            $parent_order = Order::find($request->input('id'));
            if (!$parent_order) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is not active");
                }
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            elseif ($parent_order->status>=2) {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,"Your previous order is already packed. Please make new order..");
                }
                if ($request->input('paymentResponse') && $request->input('paymentResponse')!='') {
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));
                    $payment = getPaymentStatusAndAmountFromMyFatoorah($paymentResponseArr);
                    if ($payment['status']=="success") {
                        $refund_amount = $payment['amount'];
                        $this->makeRefund('wallet', $this->user_id, null, $refund_amount, $paymentResponseArr);
                    }
                }
                throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
            }

            /* Order payment */
            if ($request->input('use_wallet_balance') == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));

                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount - $user_wallet->total_points,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount - $user_wallet->total_points,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));

                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));

                    $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $request->input('paymentResponse')
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } else {
                    // Considering all others are COD
                    $status = 'success';
                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status
                    ];
                    $paid = OrderPayment::create($insert_arr);
                }
            }
            if ($paid) {

                if($request->input('buy_it_for_me_request_id')){

                    $bought_for = UserBuyItForMeRequest::find($request->input('buy_it_for_me_request_id'));
            
                    $insert_arr = [
                        'fk_user_id' => $bought_for->from_user_id,
                        'bought_by' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => 0,
                        'payment_type' => $request->input('payment_type'), //cod or online
                    ];
                }else{

                    $insert_arr = [
                        'fk_user_id' => $this->user_id,
                        'sub_total' => $request->input('sub_total'),
                        'delivery_charge' => 0,
                        'payment_type' => $request->input('payment_type')
                    ];
                }

                

                if ($request->input('use_wallet_balance') == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                    if ($status != 'rejected') {
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                    }

                    $UserWalletPayment = \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $status != 'rejected' ? $amount_from_wallet : '',
                        'status' => $status,
                        'wallet_type' => 2,
                        'transaction_type' => 'debit'
                    ]);
                } else {
                    $net_total = $total_amount;
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['amount_from_card'] = $insert_arr['amount_from_card'];
                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = 0;
                $insert_arr['change_for'] = $request->input('payment_type') == "cod" ? $request->input('change_for') : '';
                $insert_arr['parent_id'] = $request->input('id');

                $user = \App\Model\User::find($this->user_id);

                $insert_arr['fk_store_id'] = $user->nearest_store;
                
                // Check order payment is successful or not
                if ($paid->status != "success" && $payment_type=='online') {
                    if (isset($paymentResponseArr->error_code) && $paymentResponseArr->error_code != 0) {
                        if ($paymentResponseArr->error_message == 'INSUFFICIENT_FUNDS') {
                            $message = $lang == 'ar' ? "رصيدك غير كافي" : "Insufficient funds";
                            $error_code = 201;
                        } else {
                            $message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                            $error_code = 201;
                        }
                    } else {
                        $message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                        $error_code = 201;
                    }
                } else {
                    $insert_arr['status'] = 1;

                    // Create sub order and  append products
                    $create = Order::create($insert_arr);
                    if ($create) {
                        OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);

                        $orderId = (1000 + $create->id);
                        Order::find($create->id)->update(['orderId' => $orderId]);

                        if ($request->input('use_wallet_balance') == 1) {
                            \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);
                            OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $orderId]);
                        }

                        $order = Order::find($request->input('id'));

                        if ($request->input('product_json') != '') {
                            $content_arr = json_decode($request->input('product_json'));

                            foreach ($content_arr as $key => $value) {
                                $product = BaseProduct::join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')
                                    ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                                    ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                                    ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        'base_products_store.id as fk_product_store_id',
                                        'base_products_store.itemcode',
                                        'base_products_store.barcode',
                                        'base_products_store.unit as product_store_unit',
                                        'base_products_store.margin',
                                        'base_products_store.product_distributor_price as distributor_price',
                                        'base_products_store.stock as product_store_stock',
                                        'base_products_store.other_names',
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
                                    ->where('base_products.id', '=', $value->product_id)
                                    ->first();

                                if (!$product) {
                                    // $error_message = $lang == 'ar' ? 'المنتج غير متوفر' : 'The product is not available';
                                    // throw new Exception($error_message, 106);
                                    $orderProduct = OrderProduct::create([
                                        'fk_order_id' => $create->id,
                                        'fk_product_id' => $value->product_id,
                                        'fk_product_store_id' => $value->product_store_id,
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
                                    throw new Exception($orderProduct->id, 105);
                                    continue;
                                }
                                
                                $selling_price = number_format($product->product_store_price, 2);
                                $orderProduct = OrderProduct::create([
                                    'fk_order_id' => $request->input('id'),
                                    'fk_product_id' => $value->product_id,
                                    'fk_product_store_id' => $product->fk_product_store_id,
                                    'fk_store_id' => $product->fk_store_id,
                                    'single_product_price' => $selling_price,
                                    'total_product_price' => number_format(($selling_price * $value->product_quantity), 2),
                                    'itemcode' => $product->itemcode ?? '',
                                    'barcode' => $product->barcode ?? '',
                                    'fk_category_id' => $product->fk_category_id ?? '',
                                    'category_name' => $product->category_name_en ?? '',
                                    'category_name_ar' => $product->category_name_ar ?? '',
                                    'fk_sub_category_id' => $product->fk_sub_category_id ?? '',
                                    'sub_category_name' => $product->sub_category_name_en ?? '',
                                    'sub_category_name_ar' => $product->sub_category_name_ar ?? '',
                                    'fk_brand_id' => $product->fk_brand_id ?? '',
                                    'brand_name' => $product->brand_name_en ?? '',
                                    'brand_name_ar' => $product->brand_name_ar,
                                    'product_name_en' => $product->product_name_en ?? '',
                                    'product_name_ar' => $product->product_name_ar ?? '',
                                    'product_image' => $product->product_image ?? '',
                                    'product_image_url' => $product->product_image_url ?? '',
                                    'margin' => $product->margin ?? '',
                                    'distributor_price' => $product->distributor_price ?? '',
                                    'unit' => $product->product_store_unit ?? '',
                                    '_tags' => $product->_tags ?? '',
                                    'tags_ar' => $product->tags_ar ?? '',
                                    'discount' => '',
                                    'product_quantity' => $value->product_quantity,
                                    'product_weight' => $product->product_store_unit ?? '',
                                    'product_unit' => $product->product_store_unit ?? '',
                                    'is_missed_product' => 1
                                ]);

                                // Assign storekeeper based on the products store
                                $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                                ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                                ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                                ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                                ->where('storekeepers.deleted', '=', 0)
                                ->where('storekeepers.fk_store_id', '=', $product->fk_store_id)
                                ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $product->fk_sub_category_id)
                                ->first();
                                if ($storekeeper) {
                                    StorekeeperProduct::create([
                                        'fk_storekeeper_id' => $storekeeper->id ?? '',
                                        'fk_order_id' => $request->input('id'),
                                        'fk_order_product_id' => $orderProduct->id,
                                        'fk_store_id' => $storekeeper->fk_store_id,
                                        'fk_product_id' => $product->id,
                                        'status' => 0
                                    ]);
                                    // Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                                } else {
                                    $storekeeper = Storekeeper::select("storekeepers.*")
                                        ->where('storekeepers.default', '=', 1)
                                        ->where('storekeepers.deleted', '=', 0)
                                        ->where('storekeepers.fk_store_id', '=', $product->fk_store_id)
                                        ->first();
                                        if ($storekeeper) {
                                            StorekeeperProduct::create([
                                                'fk_storekeeper_id' => $storekeeper->id ?? '',
                                                'fk_order_id' => $request->input('id'),
                                                'fk_order_product_id' => $orderProduct->id,
                                                'fk_store_id' => $storekeeper->fk_store_id,
                                                'fk_product_id' => $product->id,
                                                'status' => 0
                                            ]);
                                            // Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                                        }
                                }
                    
                            }
                        }

                        $delivery_in_text = $lang=='ar' ? "التسليم الساعة ".getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0) : "delivery by ".getDeliveryIn($order->getOrderDeliverySlot->expected_eta ?? 0);

                        $order_arr = [
                            'id' => $order->id,
                            'orderId' => $order->orderId ?? "",
                            'sub_total' => (string) $order->getSubOrder ? number_format($order->sub_total + $order->getOrderSubTotal($request->input('id')), 2) : number_format($order->sub_total, 2),
                            'total_amount' => (string) $order->getSubOrder ? number_format(($order->total_amount + $order->getOrderSubTotal($request->input('id'))), 2) : number_format($order->total_amount, 2),
                            'delivery_charge' => $order->delivery_charge,
                            'coupon_discount' => $order->coupon_discount ?? '',
                            'item_count' => $order->getOrderProducts->count(),
                            'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                            'status' => $order->status,
                            'delivery_date' => $order->getOrderDeliverySlot->delivery_date ?? "",
                            'delivery_slot' => $order->getOrderDeliverySlot->delivery_slot ?? "",
                            'delivery_in' => $delivery_in_text
                        ];
                        
                        // successful message
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,"Paid successfully!");
                        }
                        $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $error_code = 200;

                    } else {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,"An error has been discovered");
                        }
                        $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                        throw new Exception($error_message, 105);
                    }
                }
                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = ['orderId' => $request->input('id'), 'order' => isset($order_arr) ? $order_arr : [] ];
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

    protected function get_subtotal_replacement_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'product_json', 'ticket_id']);

            // Get order from request
            $order = Order::find($request->input('id'));
            if (!$order) {
                $error_message = $lang == 'ar' ? 'لم يتم العثور على الطلب' : 'Order is not found';
                throw new Exception($error_message, 105);
            }

            // Set price key and price total
            $price_total = 0;
            
            // Calculate price total for the selected replacing products
            if ($request->input('product_json') != '') {
                $content_arr = json_decode($request->input('product_json'));

                foreach ($content_arr as $key => $value) {
                    $product = BaseProductStore::find($value->product_store_id);
                    
                    // If products are not belongs to the company
                    if (!$product) {
                        $error_message = $lang == 'ar' ? 'المتجر غير متوفر' : 'The store is not available';
                        throw new Exception($error_message, 106);
                    }

                    // Calculate the price and amount
                    $price_total += number_format(($product->product_price * $value->product_quantity), 2);
                }

            }
            
            // Get user wallet amount
            $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            $wallet_balance = $user_wallet ? $user_wallet->total_points : "0.00";

            // Get out of stock total
            $out_of_stock_total = 0;
            $out_of_stock_products = OrderProduct::where(['fk_order_id'=>$request->input('id'),'is_out_of_stock'=>1])->get();
            if ($out_of_stock_products) {
                foreach ($out_of_stock_products as $key => $product) {
                    $out_of_stock_total += number_format(($product->total_product_price), 2);
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم احتساب المجموع الفرعي بنجاح" : "Sub total calculated successfully";
            $this->result = [
                'out_of_stock_total' => floatval($out_of_stock_total),
                'price_total' => floatval(number_format($price_total, 2)),
                'wallet_balance' => floatval($wallet_balance),
                'sub_total' => (string) number_format($price_total-$out_of_stock_total, 2),
                'sub_total_double' => floatval(number_format($price_total-$out_of_stock_total, 2))
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

    protected function make_replacement_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'sub_total', 'ticket_id']);

            $total_amount = $request->input('sub_total');
            $payment_type = $request->input('payment_type');
            // $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($request->input('id'));

            if ($total_amount<=0) {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }

            // Check and confirm order is not invoiced yet
            $parent_order = Order::find($request->input('id'));
            if (!$parent_order) {
                throw new Exception($lang == 'ar' ? "طلبك السابق غير نشط" : "Your previous order is not active", 105);
            }
            elseif ($parent_order->status>=2) {
                if ($request->input('paymentResponse') && $request->input('paymentResponse')!='') {
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));
                    $payment = getPaymentStatusAndAmountFromMyFatoorah($paymentResponseArr);
                    if ($payment['status']=="success") {
                        $refund_amount = $payment['amount'];
                        $this->makeRefund('wallet', $this->user_id, null, $refund_amount, $paymentResponseArr);
                    }
                }
                throw new Exception($lang == 'ar' ? "تم بالفعل تعبئة طلبك السابق. الرجاء تقديم طلب جديد" : "Your previous order is already packed. Please make new order..", 105);
            }

            /* Order payment */
            if ($request->input('use_wallet_balance') == 1) {
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                    $status = 'success';

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => 'success'
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));

                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount - $user_wallet->total_points,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount - $user_wallet->total_points,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                } else {

                    if ($payment_type=='online') {
                        // Process cart payment
                        $paymentResponseArr = json_decode($request->input('paymentResponse'));

                        $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status,
                            'paymentResponse' => $request->input('paymentResponse')
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    } else {
                        // Considering all others are COD
                        $status = 'success';
                        $insert_arr = [
                            'parent_order_id' => $request->input('id'),
                            'amount' => $total_amount,
                            'status' => $status
                        ];
                        $paid = OrderPayment::create($insert_arr);
                    }
                    
                }
            } else {
                if ($payment_type=='online') {
                    // Process cart payment
                    $paymentResponseArr = json_decode($request->input('paymentResponse'));

                    $status = getPaymentStatusFromMyFatoorah($paymentResponseArr);

                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $request->input('paymentResponse')
                    ];
                    $paid = OrderPayment::create($insert_arr);
                } else {
                    // Considering all others are COD
                    $status = 'success';
                    $insert_arr = [
                        'parent_order_id' => $request->input('id'),
                        'amount' => $total_amount,
                        'status' => $status
                    ];
                    $paid = OrderPayment::create($insert_arr);
                }
            }

            // If paid only process 
            if ($paid) {
                $insert_arr = [
                    'fk_user_id' => $this->user_id,
                    'sub_total' => $request->input('sub_total'),
                    'delivery_charge' => 0,
                    'payment_type' => $request->input('payment_type')
                ];

                if ($request->input('use_wallet_balance') == 1) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();

                    if ($user_wallet && $user_wallet->total_points >= $total_amount) {
                        $insert_arr['amount_from_wallet'] = $total_amount;
                        $insert_arr['amount_from_card'] = 0;
                        $amount_from_wallet = $total_amount;
                    } elseif ($user_wallet && $user_wallet->total_points < $total_amount && $user_wallet->total_points > 0) {
                        $insert_arr['amount_from_wallet'] = $user_wallet->total_points;
                        $insert_arr['amount_from_card'] = $total_amount - $user_wallet->total_points;
                        $amount_from_wallet = $user_wallet->total_points;
                    } else {
                        $insert_arr['amount_from_wallet'] = 0;
                        $insert_arr['amount_from_card'] = $total_amount;

                        $amount_from_wallet = 0;
                    }

                    if ($status != 'rejected') {
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                    }

                    $UserWalletPayment = \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $status != 'rejected' ? $amount_from_wallet : '',
                        'status' => $status,
                        'wallet_type' => 2,
                        'transaction_type' => 'debit'
                    ]);
                } else {
                    $insert_arr['amount_from_wallet'] = 0;
                    $insert_arr['amount_from_card'] = $total_amount;
                }

                $insert_arr['total_amount'] = $total_amount;
                $insert_arr['grand_total'] = $total_amount;
                $insert_arr['status'] = 0;
                $insert_arr['parent_id'] = $request->input('id');

                $user = \App\Model\User::find($this->user_id);

                $insert_arr['fk_store_id'] = $user->nearest_store;

                $create = Order::create($insert_arr);
                if ($create) {
                    OrderPayment::find($paid->id)->update(['fk_order_id' => $create->id]);

                    $orderId = (1000 + $create->id);
                    Order::find($create->id)->update(['orderId' => $orderId]);

                    if ($request->input('use_wallet_balance') == 1) {
                        \App\Model\UserWalletPayment::find($UserWalletPayment->id)->update(['paymentResponse' => $orderId]);
                        OrderPayment::where(['fk_order_id' => $create->id])->update(['paymentResponse' => $orderId]);
                    }

                    $order = Order::find($request->input('id'));

                    if ($request->input('product_json') != '') {
                        $content_arr = json_decode($request->input('product_json'));

                        foreach ($content_arr as $key => $value) {
                            $product = BaseProduct::join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')
                                ->leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                                ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                                ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                                ->select(
                                    'base_products_store.id as fk_product_store_id',
                                    'base_products_store.itemcode',
                                    'base_products_store.barcode',
                                    'base_products_store.unit as product_store_unit',
                                    'base_products_store.margin',
                                    'base_products_store.product_distributor_price as distributor_price',
                                    'base_products_store.stock as product_store_stock',
                                    'base_products_store.other_names',
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
                                ->where('base_products.id', '=', $value->product_id)
                                ->first();

                            $selling_price = number_format($product->product_store_price, 2);
                            $orderProduct = OrderProduct::create([
                                'fk_order_id' => $request->input('id'),
                                'fk_product_id' => $value->product_id,
                                'fk_product_store_id' => $product->fk_product_store_id,
                                'fk_store_id' => $product->fk_store_id,
                                'single_product_price' => $selling_price,
                                'total_product_price' => number_format(($selling_price * $value->product_quantity), 2),
                                'itemcode' => $product->itemcode ?? '',
                                'barcode' => $product->barcode ?? '',
                                'fk_category_id' => $product->fk_category_id ?? '',
                                'category_name' => $product->category_name_en ?? '',
                                'category_name_ar' => $product->category_name_ar ?? '',
                                'fk_sub_category_id' => $product->fk_sub_category_id ?? '',
                                'sub_category_name' => $product->sub_category_name_en ?? '',
                                'sub_category_name_ar' => $product->sub_category_name_ar ?? '',
                                'fk_brand_id' => $product->fk_brand_id ?? '',
                                'brand_name' => $product->brand_name_en ?? '',
                                'brand_name_ar' => $product->brand_name_ar,
                                'product_name_en' => $product->product_name_en ?? '',
                                'product_name_ar' => $product->product_name_ar ?? '',
                                'product_image' => $product->product_image ?? '',
                                'product_image_url' => $product->product_image_url ?? '',
                                'margin' => $product->margin ?? '',
                                'distributor_price' => $product->distributor_price ?? '',
                                'unit' => $product->unit ?? '',
                                '_tags' => $product->_tags ?? '',
                                'tags_ar' => $product->tags_ar ?? '',
                                'discount' => '',
                                'product_quantity' => $value->product_quantity,
                                'product_weight' => $product->unit ?? '',
                                'product_unit' => $product->unit ?? '',
                                'is_replaced_product' => 1
                            ]);

                            // Assign storekeeper based on the products store
                            $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                            ->join('storekeeper_sub_categories', 'storekeepers.id', '=', 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->select("storekeepers.*", 'storekeeper_sub_categories.fk_storekeeper_id')
                            ->groupBy('storekeeper_sub_categories.fk_sub_category_id')
                            ->where('storekeepers.deleted', '=', 0)
                            ->where('storekeepers.fk_store_id', '=', $product->fk_store_id)
                            ->where('storekeeper_sub_categories.fk_sub_category_id', '=', $product->fk_sub_category_id)
                            ->first();
                            if ($storekeeper) {
                                StorekeeperProduct::create([
                                    'fk_storekeeper_id' => $storekeeper->id ?? '',
                                    'fk_order_id' => $request->input('id'),
                                    'fk_order_product_id' => $orderProduct->id,
                                    'fk_product_id' => $product->id,
                                    'status' => 0
                                ]);
                                Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                            } else {
                                $storekeeper = Storekeeper::join('stores', 'storekeepers.fk_store_id', '=', 'stores.id')
                                    ->select("storekeepers.*")
                                    ->where('storekeepers.default', '=', 1)
                                    ->where('storekeepers.deleted', '=', 0)
                                    ->where('storekeepers.fk_store_id', '=', $product->fk_store_id)
                                    ->first();
                                    if ($storekeeper) {
                                        StorekeeperProduct::create([
                                            'fk_storekeeper_id' => $storekeeper->id ?? '',
                                            'fk_order_id' => $request->input('id'),
                                            'fk_order_product_id' => $orderProduct->id,
                                            'fk_product_id' => $product->id,
                                            'status' => 0
                                        ]);
                                        Storekeeper::find($storekeeper->id)->update(['is_available' => 0]);
                                    }
                            }

                        }
                    }

                    $order_arr = [
                        'id' => $order->id,
                        'orderId' => $order->orderId ?? "",
                        'sub_total' => $order->getSubOrder ? $order->sub_total + $order->getOrderSubTotal($request->input('id')) : $order->sub_total,
                        'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($request->input('id'))) : (string) $order->total_amount,
                        'delivery_charge' => $order->delivery_charge,
                        'coupon_discount' => $order->coupon_discount ?? '',
                        'item_count' => $order->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                        'status' => $order->status,
                        'delivery_date' => $order->getOrderDeliverySlot->delivery_date ?? "",
                        'delivery_slot' => $order->getOrderDeliverySlot->delivery_slot ?? ""
                    ];
                } else {
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }
                if ($paid->status != "success" && $payment_type=='online') {
                    if (isset($paymentResponseArr->error_code) && $paymentResponseArr->error_code != 0) {
                        if ($paymentResponseArr->error_message == 'INSUFFICIENT_FUNDS') {
                            $message = $lang == 'ar' ? "رصيدك غير كافي" : "Insufficient funds";
                            $error_code = 201;
                        } else {
                            $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                            $error_code = 201;
                        }
                    } else {
                        $message = $lang == 'ar' ? "يوجد خلل، يرجى المحاولة مرة اخرى" : "Something went wrong, please try again";
                        $error_code = 201;
                    }
                } else {
                    Order::find($create->id)->update(['status' => 1]);

                    $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                    $error_code = 200;

                    $last_message = $lang=='ar' ? "تم استبدال المنتج الخاص بك": "Your product has been replaced";

                    //updating technical support chat table
                    $chat_message = $lang=='ar' ? 'لقد اخترت المنتجات التالية' : 'I have selected following products';
                    TechnicalSupportChat::create([
                        'ticket_id' => $request->input('ticket_id'),
                        'chatbox_type' => 4,
                        'message_type' => 'sent',
                        'message' => $chat_message,
                        'action' => 'payment'
                    ]);

                    TechnicalSupportChat::create([
                        'ticket_id' => $request->input('ticket_id'),
                        'message_type' => 'received',
                        'message' => $last_message
                    ]);

                    TechnicalSupport::find($request->input('ticket_id'))->update(['status' => 0]);
                }

                $this->error = false;
                $this->status_code = $error_code;
                $this->message = $message;
                $this->result = ['orderId' => $orderId, 'order' => $order_arr];
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

    protected function my_orders(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $past_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->where('order_payments.status', '!=', 'rejected')
                ->where('order_payments.status', '!=', 'blocked')
                ->where('orders.status', '=', 7)
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();
            $current_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->whereIn('orders.status', array(0, 1, 2, 3, 6))
                ->where('order_payments.status', '!=', 'rejected')
                ->where('order_payments.status', '!=', 'blocked')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();
            $cancelled_orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where(function ($query) {
                        $query->where('fk_user_id', '=', $this->user_id)
                        ->orWhere('bought_by', '=', $this->user_id);
                    })
                ->where('order_payments.status', '!=', 'rejected')
                ->where('order_payments.status', '!=', 'blocked')
                ->where('orders.status', '=', 4)
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->get();

            $past_order_arr = [];
            if ($past_orders->count()) {
                foreach ($past_orders as $key => $value) {
                    $past_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            $current_order_arr = [];
            if ($current_orders->count()) {
                foreach ($current_orders as $key => $value) {
                    $current_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }

            $cancelled_order_arr = [];
            if ($cancelled_orders->count()) {
                foreach ($cancelled_orders as $key => $value) {
                    $cancelled_order_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'change_for' => $value->change_for ?? '',
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'is_buy_it_for_me_order' => $value->bought_by !=null ? true : false,
                        'bought_by' => $value->bought_by ?? 0
                    ];
                }
            }


            if ($lang == "ar") {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status_ar[0]],
                    ['status' => 1, 'message' => $this->order_status_ar[1]],
                    ['status' => 2, 'message' => $this->order_status_ar[1]],
                    ['status' => 3, 'message' => $this->order_status_ar[1]],
                    ['status' => 4, 'message' => $this->order_status_ar[4]],
                    ['status' => 6, 'message' => $this->order_status_ar[6]],
                    ['status' => 7, 'message' => $this->order_status_ar[7]],
                ];
            } else {
                $status_arr = [
                    ['status' => 0, 'message' => $this->order_status[0]],
                    ['status' => 1, 'message' => $this->order_status[1]],
                    ['status' => 2, 'message' => $this->order_status[1]],
                    ['status' => 3, 'message' => $this->order_status[1]],
                    ['status' => 4, 'message' => $this->order_status[4]],
                    ['status' => 6, 'message' => $this->order_status[6]],
                    ['status' => 7, 'message' => $this->order_status[7]],
                ];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "طلباتي" : "My Orders";
            $this->result = ['order_status' => $status_arr, 'past' => $past_order_arr, 'current' => $current_order_arr, 'cancelled' => $cancelled_order_arr];
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

    protected function order_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $order = Order::leftJoin('orders as ord', 'ord.parent_id', '=', 'orders.id')
                ->select('orders.*')
                ->selectRaw("SUM(ord.amount_from_wallet) as total_amount_from_wallet")
                ->selectRaw("SUM(ord.amount_from_card) as total_amount_from_card")
                ->where('orders.id', '=', $request->input('id'))
                ->first();
            if (!$order) {
                throw new Exception("Order doesn't exist", 105);
            }
            
            // Store
            $store = Store::where(['id' => $order->fk_store_id, 'status' => 1, 'deleted' => 0])->first();
            if ($store) {
                $store_no = get_store_no($store->name);
                $company_id = $store->company_id;
            } else {
                $store_no = 0;
                $company_id = 0;
            }
            
            // Set delivery time
            $orderDelivery = $order->getOrderDeliverySlot;
            if ($orderDelivery->delivery_time==1) {
                $delivery_in = getDeliveryInTimeRange($orderDelivery->expected_eta, $orderDelivery->created_at, $lang);
            } else {
                // Get the sheduled earliest time
                // $later_time = strtok($orderDelivery->later_time, '-');
                // $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                // $later_delivery_time = $orderDelivery->delivery_date.' '.$later_time.':00';
                // $delivery_in = getDeliveryInTimeRange(0, $later_delivery_time, $lang);
                
                $later_time_arr = explode('-', $orderDelivery->later_time);
                $later_time_start = is_array($later_time_arr) && isset($later_time_arr[0]) ? date('h:ia',strtotime($later_time_arr[0])) : "";
                $later_time_end = is_array($later_time_arr) && isset($later_time_arr[1]) ? date('h:ia',strtotime($later_time_arr[1])) : "";
                $delivery_in = $later_time_start.'-'.$later_time_end;
            }
            $order_arr = [
                'id' => $order->id,
                'orderId' => $order->orderId,
                'sub_total' => $order->getSubOrder ? (string) ($order->sub_total + $order->getOrderSubTotal($request->input('id'))) : (string) $order->sub_total,
                'total_amount' => $order->getSubOrder ? (string) ($order->total_amount + $order->getOrderSubTotal($request->input('id'))) : (string) $order->total_amount,
                'delivery_charge' => $order->delivery_charge,
                'coupon_discount' => $order->coupon_discount ?? '',
                'item_count' => $order->getOrderProducts->count(),
                'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                'status' => $order->status,
                'delivery_date' => $order->getOrderDeliverySlot->delivery_date ?? "",
                'change_for' => $order->change_for ?? '',
                'amount_from_wallet' => $order->amount_from_wallet ? (string) ($order->amount_from_wallet + $order->total_amount_from_wallet) : '0',
                'amount_from_card' => number_format($order->total_amount_from_card, 2),
                'delivery_in' => $delivery_in,
                'delivery_time' => $order->delivery_time,
                'later_time' => $order->later_time,
                'delivery_preference' => $order->delivery_preference,
                'store_id' => $order->fk_store_id,
                'store' => 'store'.$store_no,
                'company_id' => $company_id,
                'is_buy_it_for_me_order' => $order->bought_by !=null ? true : false,
                'bought_by' => $order->bought_by ?? 0,
                'forgot_something_store_id' => env("FORGOT_SOMETHING_STORE_ID")
            ];

            $order_products = OrderProduct::leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                ->select('base_products.*', 'order_products.product_unit', 'order_products.product_unit', 'order_products.product_image_url', 'order_products.product_weight', 'order_products.total_product_price', 'order_products.single_product_price', 'order_products.product_quantity', 'order_products.discount', 'order_products.is_out_of_stock', 'order_products.sub_products')
                ->where('order_products.fk_order_id', '=', $request->input('id'))
                // ->where('order_products.is_out_of_stock', '=', 0)
                ->get();

            $item_arr = [];
            $item_outofstock_arr = [];
            $item_n = 0;
            $item_outofstock_n = 0;
            if ($order_products->count()) {
                foreach ($order_products as $key => $value) {
                    // If sub products for recipe
                    $sub_product_arr = [];
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($lang == 'ar') {
                                    $sub_product_arr[$key2] = array (
                                        'product_id' => $value2->product_id ? (int) $value2->product_id : 0,
                                        'product_quantity' => $value2->product_quantity ? (int) $value2->product_quantity : 0,
                                        'product_name' => $value2->product_name_ar ?? '',
                                        'product_image' => $value2->product_image_url ?? '',
                                        'product_price' => $value2->price ?? 0,
                                        'item_unit' => $value2->unit ?? ''
                                    );
                                } else {
                                    $sub_product_arr[$key2] = array (
                                        'product_id' => $value2->product_id ? (int) $value2->product_id : 0,
                                        'product_quantity' => $value2->product_quantity ? (int) $value2->product_quantity : 0,
                                        'product_name' => $value2->product_name_ar ?? '',
                                        'product_image' => $value2->product_image_url ?? '',
                                        'product_price' => $value2->price ?? 0,
                                        'item_unit' => $value2->unit ?? ''
                                    );
                                }
                            }
                        }
                    }
                    // Set product unit
                    // if ($value->product_unit == 'gr' && $value->product_weight >= 1000) {
                    //     $product_weight = ($value->product_weight / 1000) . ' kg';
                    // } else {
                    //     $product_weight =  $value->product_unit ? round($value->product_weight) . ' ' . $value->product_unit : $value->product_weight;
                    // }
                    $product_weight = preg_replace("/ gr$/", '', $value->product_unit);
                    if (str_ends_with($value->product_unit, ' gr') && is_numeric($product_weight) && $product_weight >= 1000) {
                        $product_weight = ($product_weight / 1000) . ' kg';
                    } else {
                        $product_weight =  $value->product_unit;
                    }
                    if ($value->is_out_of_stock==1) {
                        $item_outofstock_arr[$item_outofstock_n] = [
                            'product_id' => $value->id,
                            'type' => $value->product_type,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                            'unit' => $value->product_unit,
                            'quantity' => $product_weight,
                            'product_price' => $value->total_product_price,
                            'single_product_price' => $value->single_product_price,
                            'product_discount' => $value->discount,
                            'product_quantity' => $value->product_quantity,
                            'in_stock' => 0,
                            'sub_products' => $sub_product_arr
                        ];
                        $item_outofstock_n++;
                    } else {
                        $item_arr[$item_n] = [
                            'product_id' => $value->id,
                            'type' => $value->product_type,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                            'unit' => $value->product_unit,
                            'quantity' => $product_weight,
                            'product_price' => $value->total_product_price,
                            'single_product_price' => $value->single_product_price,
                            'product_discount' => $value->discount,
                            'product_quantity' => $value->product_quantity,
                            'in_stock' => 1,
                            'sub_products' => $sub_product_arr
                        ];
                        $item_n++;
                    }
                }
            } 

            $order_driver = OrderDriver::where('fk_order_id', '=', $request->input('id'))
                ->where('fk_driver_id', '!=', 0)
                ->first();

            if ($order_driver) {
                $driver_arr = [
                    'id' => $order_driver->fk_driver_id,
                    'driver_name' => $order_driver->getDriver->name,
                    'driver_email' => $order_driver->getDriver->email,
                    'driver_image' => !empty($order_driver->getDriver->getDriverImage) ? asset('images/common_images') . '/' . $order_driver->getDriver->getDriverImage->file_name : '',
                    'mobile' => $order_driver->getDriver->country_code . $order_driver->getDriver->mobile,
                ];
            } else {
                $driver_arr = (object) [];
            }

            $orderAdr = OrderAddress::where(['fk_order_id' => $request->input('id')])->first();
            if ($orderAdr) {
                $order_addr_arr = $orderAdr;
            } else {
                $order_addr_arr = (object) [];
            }
            $status_arr = [];
            for ($i = 0; $i <= count($this->order_status); $i++) {
                if ($i != 5) {
                    $status = OrderStatus::where('fk_order_id', '=', $request->input('id'))
                        ->where('status', '=', $i)
                        ->first();

                    if ($status) {
                        $checked = 1;
                        $datetime = date('Y-m-d H:i:s', strtotime($status->created_at));
                    } else {
                        $checked = 0;
                        $datetime = '';
                    }
                    $status_arr[$i] = [
                        'status' => $i,
                        'message' => $lang == "en" ? $this->order_status[$i] : $this->order_status_ar[$i],
                        'datetime' => $datetime,
                        'checked' => $checked,
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang=='ar' ? "تفاصيل الطلب" : "Order detail";
            $this->result = [
                'order_detail' => $order_arr,
                'order_items' => $item_arr,
                'order_outofstock_items' => $item_outofstock_arr,
                'order_address' => $order_addr_arr,
                'order_status' => array_values($status_arr),
                'assigned_driver' => $driver_arr
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

    protected function rate_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'rating', 'review']);

            $reviewed = OrderReview::where(['fk_user_id' => $this->user_id, 'fk_order_id' => $request->input('id')])->first();
            if ($reviewed) {
                $error_message = $lang == 'ar' ? "استعرضت هذا الطلب بالفعل" : "Already reviewed this order";
                throw new Exception($error_message, 105);
            }
            $update = OrderReview::create([
                'fk_user_id' => $this->user_id,
                'fk_order_id' => $request->input('id'),
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
            ]);
            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang=='ar' ? "تمت المراجعة بنجاح!" : "Reviewed successfully!";
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

    protected function reorder(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['id']);

            $order = Order::find($request->input('id'));
            if (!$order) {
                $error_message = $lang=='ar' ? "الطلب غير موجود" : "Order doesn't exist";
                throw new Exception($error_message, 105);
            }

            $user = User::find($this->user_id);
            if ($order->getOrderProducts->count()) {
                foreach ($order->getOrderProducts as $key => $value) {
                    $product = BaseProduct::select(
                        'base_products.id',
                        'base_products.fk_product_store_id',
                        'base_products.fk_store_id',
                        'base_products.product_store_price',
                        'base_products.product_store_stock',
                    )
                    ->where('base_products.id', '=', $value->fk_product_id)
                    ->first();
                    
                    $selling_price = number_format($product->product_store_price, 2);

                    $product_exist = UserCart::where(['fk_product_id' => $value->fk_product_id, 'fk_user_id' => $this->user_id])->first();
                    
                    if ($product_exist) {
                        $insert_arr = [
                            'fk_user_id' => $this->user_id,
                            'fk_product_id' => $value->fk_product_id,
                            'fk_product_store_id' => $value->fk_product_store_id,
                            'quantity' => $value->product_quantity+$product_exist->quantity,
                            'total_price' => ($selling_price * $value->product_quantity),
                            'total_discount' => '',
                            'weight' => $value->product_weight,
                            'unit' => $value->product_unit
                        ];

                        $product_exist->update($insert_arr);

                    }else{

                        $insert_arr = [
                            'fk_user_id' => $this->user_id,
                            'fk_product_id' => $value->fk_product_id,
                            'fk_product_store_id' => $value->fk_product_store_id,
                            'quantity' => $value->product_quantity,
                            'total_price' => ($selling_price * $value->product_quantity),
                            'total_discount' => '',
                            'weight' => $value->product_weight,
                            'unit' => $value->product_unit
                        ];

                        UserCart::create($insert_arr);
                    }
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم الإضافة الى السلة" : "Ordered items added into cart!";
            } else {
                $error_message = $lang=='ar' ? "لا يوجد منتج موجود بالترتيب" : "No product exist in the order";
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

    protected function cancel_order(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id']);

            $order = Order::find($request->input('id'));
            if (!$order) { 
                $error_message = $lang=='ar' ? "الطلب غير موجود" : "Order doesn't exist";
                throw new Exception($error_message, 105);
            }
            //            $diff = strtotime(\Carbon\Carbon::now()) - strtotime($order->updated_at);

            /* Case 1: if the user cancels the order within 3 mins after placing it 
             * then the refund amount will be refunded back to their original mode of payment. */
            //            if ($diff <= 180) {
            //                $message = "Order cancelled and refund is initiated";
            //                /* Case 2: if the user cancels an order after 3 mins and before the order 
            //                 * is invoiced then the refund amount will be credited into their wallet. */
            //            } elseif ($diff > 180 && $order->status != 3 && $order->status < 4) {
            //                if ($order->payment_type == 'cod') {
            //                    $message = $lang=='ar' ? "تم الغاء الطلب" : "Order cancelled!";
            //                } else {
            //                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
            //                    if ($user_wallet) {
            //                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
            //                            'current_points' => $order->total_amount,
            //                            'total_points' => $order->total_amount
            //                        ]);
            //                    } else {
            //                        \App\Model\UserWallet::create([
            //                            'fk_user_id' => $this->user_id,
            //                            'current_points' => $order->total_amount,
            //                            'total_points' => $order->total_amount
            //                        ]);
            //                    }
            //                    $message = "Order cancelled and refund has been credited into your wallet";
            //                }
            //            } elseif ($diff > 180 && $order->status >= 3) {
            //                throw new Exception("You cannot cancel order now", 105);
            //                /* Case 3: After the order gets invoiced then, the user cannot cancel it. */
            //            } else {
            //                throw new Exception("Some error found", 105);
            //            }



            if ($order->status == 4) {
                $error_message = $lang=='ar' ? "تم إلغاء الطلب بالفعل" : "Order already cancelled";
                throw new Exception($error_message, 105);
            }

            if ($order->status < 2) {
                $wallet_to_refund = 0;
                // Check Main Order
                if ($order->payment_type == 'cod') {
                    if ($order->amount_from_wallet>0) {
                        $wallet_to_refund = $order->amount_from_wallet;
                    } 
                } else {
                    $wallet_to_refund = $order->total_amount;
                }
                // Check sub orders
                $amount_from_card_suborders= $order->getSubOrder ? $order->getOrderPaidByCart($order->id) : 0;
                $amount_from_wallet_suborders = $order->getSubOrder ? $order->getOrderPaidByWallet($order->id) : 0;
                $wallet_to_refund = $wallet_to_refund + $amount_from_card_suborders + $amount_from_wallet_suborders;
                // Refund to wallet if pay by cart or wallet
                if ($wallet_to_refund>0) {
                    $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->first();
                    if ($user_wallet) {
                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                            'current_points' => $wallet_to_refund,
                            'total_points' => $user_wallet->total_points + $wallet_to_refund
                        ]);
                    } else {
                        \App\Model\UserWallet::create([
                            'fk_user_id' => $this->user_id,
                            'current_points' => $wallet_to_refund,
                            'total_points' => $wallet_to_refund
                        ]);
                    }
                    \App\Model\UserWalletPayment::create([
                        'fk_user_id' => $this->user_id,
                        'amount' => $wallet_to_refund,
                        'status' => 'success',
                        'wallet_type' => 3,
                        'transaction_type' => 'credit',
                        'paymentResponse' => $order->orderId
                    ]);
                    $message = $lang=='ar' ? "تم إلغاء الطلب وتم إعادة الأموال إلى محفظتك" : "Order cancelled and refund has been credited into your wallet";
                } else {
                    $message = $lang=='ar' ? "تم الغاء الطلب" : "Order cancelled!";
                }
            } else {
                $error_message = $lang=='ar' ? "لا يمكنك إلغاء الطلب الآن" : "You cannot cancel order now";
                throw new Exception($error_message, 105);
            }

            Order::where(['id' => $request->input('id')])->update([
                'status' => 4
            ]);
            OrderStatus::create([
                'fk_order_id' => $request->input('id'),
                'status' => 4
            ]);

            // Release storekeeper
            if (StorekeeperProduct::where(['fk_order_id' => $request->input('id')])->first()) {
                StorekeeperProduct::where(['fk_order_id' => $request->input('id')])->delete();
            }

            // ---------------------------------------------------------------
            // Send notification SMS to admins
            // ---------------------------------------------------------------
            $test_order = $order->test_order;
            $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
            $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
            if ($send_notification && $send_notification_number) {
                $msg = "The order has been cancelled by the customer, order number: ".$request->input('id');
                $sent = $this->mobile_sms_curl($msg, $send_notification_number);
            }
            $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
            if ($send_notification && $send_notification_number_2) {
                $msg = "The order has been cancelled by the customer, order number: ".$request->input('id');
                $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
            }
            $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
            if ($send_notification && $send_notification_number_3) {
                $msg = "The order has been cancelled by the customer, order number: ".$request->input('id');
                $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
            }
            $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
            if ($send_notification && $send_notification_number_4) {
                $msg = "The order has been cancelled by the customer, order number: ".$request->input('id');
                $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
            }
            $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
            if ($send_notification && $send_notification_number_5) {
                $msg = "The order has been cancelled by the customer, order number: ".$request->input('id');
                $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
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
