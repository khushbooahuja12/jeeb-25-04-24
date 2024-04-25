<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\User;
use App\Model\Store;
use App\Model\Driver;
use App\Model\Order;
use App\Model\OrderDriver;
use App\Model\OrderProduct;
use App\Model\OrderStatus;
use App\Model\AdminSetting;
use App\Model\Invoice;
use App\Model\DriverGroup;
use App\Model\OrderDeliverySlot;
use App\Model\StorekeeperProduct;
use Carbon\Carbon;

//use App\Model\DriverReview;

class OrderController extends CoreApiController
{

    use \App\Http\Traits\PushNotification;
    use \App\Http\Traits\OrderProcessing;

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
            'orders', 'order_detail', 'update_order_status','instant_orders','instant_order_detail','update_instant_orders','collector_orders','collector_stores','mark_store_order_out_for_delivery','collector_time_slot_order_detail','mark_time_slot_order_out_for_delivery','mark_store_order_delivered'
        ];

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

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost()=='localhost' ? 'dev_products' : 'products';
    }

    protected function orders(Request $request)
    {
        try {
            $total_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->select('orders.*')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 4)
                ->count();

            $ongoing_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 4)
                ->where('orders.status', '!=', 7)
                // ->orderBy('order_drivers.updated_at', 'desc')
                ->orderByRaw("CONCAT(order_delivery_slots.delivery_date,' ',LEFT(order_delivery_slots.later_time,LOCATE('-',order_delivery_slots.later_time) - 1))")
                ->get();
            
            $ongoing_orders_arr = [];
            $ongoing_orders_by_slot = [];

            if ($ongoing_orders->count()) {
                foreach ($ongoing_orders as $key => $value) {

                    $delivery_preferences_text = $value && $value->change_for!='' ? 'Bring change for '.$value->change_for.'QAR,' : '';
                    if($value && $value->payment_type=='cod') {
                        $amount_from_card = $value->getSubOrder ? $value->amount_from_card + $value->getOrderPaidByCOD($value->id) : $value->amount_from_card;
                        $delivery_preferences_text .= 'Collect money '.floor($amount_from_card).'QAR from customer,';
                    }
                    $delivery_preferences = $value->delivery_preference ? explode(',',$value->delivery_preference) : [];
                    if (is_array($delivery_preferences) && !empty($delivery_preferences)) {
                        foreach ($delivery_preferences as $delivery_key=>$delivery_preference) {
                            if ($delivery_preference==1) {
                                if (isset($this->order_preferences) && isset($this->order_preferences[$delivery_key])) {
                                    $delivery_preferences_text .= $this->order_preferences[$delivery_key].',';
                                }
                            }
                        }
                    }
                    $userInfo = [
                        'name' => $value->getOrderAddress->name ?? '',
                        'mobile' => $value->getOrderAddress->mobile ?? '',
                        'landmark' => $value->getOrderAddress->landmark ?? '',
                        'address_line1' => $value->getOrderAddress->address_line1 ?? '',
                        'address_line2' => $value->getOrderAddress->address_line2 ?? '',
                        'latitude' => $value->getOrderAddress->latitude ?? '',
                        'longitude' => $value->getOrderAddress->longitude ?? '',
                        'address_type' => $value->getOrderAddress->address_type ?? ''
                    ];

                    $ongoing_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' =>  ($value->delivery_time && $value->later_time) ? $value->delivery_date.' '.$value->later_time : 'Time slot not availalble, check with IT department!',
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'delivery_preferences_text' => $delivery_preferences_text,
                        'user' => $userInfo,
                        'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($value->id)
                    ];

                    $ongoing_orders_by_slot_key = ($value->delivery_time==2 && $value->later_time) ? $value->delivery_date.' '.$value->later_time : $value->delivery_date;
                    $ongoing_orders_by_slot[$ongoing_orders_by_slot_key][$key] = $ongoing_orders_arr[$key];
                }
            } 
            
            $completed_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->select('orders.*')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('order_drivers.status', '=', 1)
                ->where('orders.status', '=', 4);

            if ($request->input('month') != '') {
                $completed_orders = $completed_orders
                    ->whereYear('orders.created_at', date('Y', strtotime($request->input('month'))))
                    ->whereMonth('orders.created_at', date('m', strtotime($request->input('month'))));
            } else {
                $completed_orders = $completed_orders
                    ->whereYear('orders.created_at', \Carbon\Carbon::now()->year)
                    ->whereMonth('orders.created_at', \Carbon\Carbon::now()->month);
            }

            $completed_orders = $completed_orders
                ->orderBy('order_drivers.updated_at', 'desc')
                ->get();

            if ($completed_orders->count()) {
                foreach ($completed_orders as $key => $value) {
                    $completed_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->sub_total,
                        'total_amount' => $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($value->id)
                    ];
                }
            } else {
                $completed_orders_arr = [];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "My Orders";
           
            $this->result = [
                'total_orders' => $total_orders,
                'orders' => $ongoing_orders_arr,
                'ongoing_orders_by_slot' => $ongoing_orders_by_slot
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

    protected function order_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // New order after Feb, 1st indicator variables
            $first_order_after_date = '2023-02-01 00:00:00';
            $first_order = null;

            // Order detail variables
            $this->required_input($request->input(), ['id']);
            $order = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.id', '=', $request->input('id'))
                ->first();
            $order_arr = [];

            if ($order) {

                // New order after Feb, 1st indicator
                $first_order = Order::where('created_at','>=',$first_order_after_date)->where('fk_user_id','=',$order->fk_user_id)->where('id','<>',$order->id)->first() ? null : "Add thank you letter";
                
                // Order details
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
                    'status_text' => $this->order_status[$order->status],
                    'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($order->id)
                ];
                if (!empty($order->getOrderAddress)) {
                    $order_arr['delivery'] = $order->getOrderAddress;
                } else {
                    $order_arr['delivery'] = (object) [];
                }

                // All order products
                $order_products = OrderProduct::leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                    ->leftJoin('base_products_store', 'order_products.fk_product_store_id', '=', 'base_products_store.id')
                    ->leftJoin('stores', 'order_products.fk_store_id', '=', 'stores.id')
                    ->leftJoin('storekeeper_products', function($join)
                    {
                        $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
                        $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
                    })
                    ->select('base_products.*', 'base_products_store.itemcode', 'base_products_store.itemcode', 'base_products_store.barcode', 'base_products_store.product_distributor_price as distributor_price', 'base_products_store.product_store_price AS product_price', 'base_products_store.stock', 'base_products_store.other_names', 'order_products.sub_products', 'order_products.fk_product_id', 'order_products.fk_store_id', 'order_products.product_quantity', 'stores.company_name', 'stores.name', 'storekeeper_products.id as default_id', 'storekeeper_products.status as order_product_status')
                    ->where('order_products.fk_order_id', '=', $order->id)
                    ->orderBy('base_products.fk_category_id', 'asc')
                    ->orderBy('base_products.fk_sub_category_id', 'asc')
                    ->get();

                $item_arr = [];
                $item_out_of_stock_arr = [];
                if (!empty($order_products)) {
                    foreach ($order_products as $key => $value) {
                        // If sub products for recipe
                        $sub_product_arr = [];
                        if (isset($value->sub_products) && $value->sub_products!='') {
                            $sub_products = json_decode($value->sub_products);
                            if ($sub_products && is_array($sub_products)) {
                                foreach ($sub_products as $key2 => $value2) {
                                    if ($value->fk_store_id!=0) {
                                        $value->product_name_en = $value->product_name_en ?? '';
                                        $value->product_name_ar = $value->product_name_ar ?? '';
                                        if ($lang == 'ar') {
                                            $sub_product_arr[$key2] = array (
                                                'product_id' => $value2->product_id ?? 0,
                                                'product_quantity' => $value2->product_quantity ?? 0,
                                                'product_name' => $lang == 'ar' ? $value2->product_name_ar : $value2->product_name_en,
                                                'product_image' => $value2->product_image_url ?? '',
                                                'product_distributor_price' => $value2->distributor_price ?? 0,
                                                'product_price' => $value2->product_price ?? 0,
                                                'item_unit' => $value2->unit ?? '',
                                                'other_names' => $value2->other_names ?? 'none'
                                            );
                                        } else {
                                            $sub_product_arr[$key2] = array (
                                                'product_id' => $value2->product_id ?? 0,
                                                'product_quantity' => $value2->product_quantity ?? 0,
                                                'product_name' => $lang == 'ar' ? $value2->product_name_ar : $value2->product_name_en,
                                                'product_image' => $value2->product_image_url ?? '',
                                                'product_distributor_price' => $value2->distributor_price ?? 0,
                                                'product_price' => $value2->product_price ?? 0,
                                                'item_unit' => $value2->unit ?? '',
                                                'other_names' => $value2->other_names ?? 'none'
                                            );
                                        }
                                    } else {
                                        
                                    }
                                }
                            }
                        }
                        // All ingredients for recipe
                        $ingredients = [];
                        if ($value->product_type=='recipe' && isset($value->recipe_variant_id) && $value->recipe_variant_id!=0) {
                            $recipe_varient = \App\Model\RecipeVariant::find($value->recipe_variant_id);
                            $ingredients = $recipe_varient ? json_decode($recipe_varient->ingredients) : [];
                        }
                        // Check new base products or old product
                        if ($value->fk_store_id!=0 && $value->order_product_status !=2) {
                            $value->product_name_en = $value->product_name_en ?? '';
                            $value->product_name_ar = $value->product_name_ar ?? '';
                            $item_arr[] = [
                                'id' => $value->default_id ?? 0,
                                'product_id' => $value->id ?? 0,
                                'store_id' => $value->fk_store_id ?? 0,
                                'store_name' => $value->company_name.' '.$value->name,
                                'product_type' => $value->product_type ?? '',
                                'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                                'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                                'itemcode' => $value->itemcode ?? '',
                                'barcode' => $value->barcode ?? '',
                                'product_distributor_price' => $value->distributor_price ?? '',
                                'product_price' => $value->product_price ?? '',
                                'unit' => $value->unit ?? '',
                                'quantity' => $value->product_quantity ?? 0,
                                'other_names' => $value->other_names ?? 'none',
                                'status' => $value->status ?? 0,
                                'sub_products' => $sub_product_arr ?? [],
                                'ingredients' => $ingredients ?? []
                            ];
                        }elseif ($value->fk_store_id!=0 && $value->order_product_status ==2) {
                            $value->product_name_en = $value->product_name_en ?? '';
                            $value->product_name_ar = $value->product_name_ar ?? '';
                            $item_out_of_stock_arr[] = [
                                'id' => $value->default_id ?? 0,
                                'product_id' => $value->id ?? 0,
                                'store_id' => $value->fk_store_id ?? 0,
                                'store_name' => $value->company_name.' '.$value->name,
                                'product_type' => $value->product_type ?? '',
                                'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                                'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                                'itemcode' => $value->itemcode ?? '',
                                'barcode' => $value->barcode ?? '',
                                'product_distributor_price' => $value->distributor_price ?? '',
                                'product_price' => $value->product_price ?? '',
                                'unit' => $value->unit ?? '',
                                'quantity' => $value->product_quantity ?? 0,
                                'other_names' => $value->other_names ?? 'none',
                                'status' => $value->status ?? 0,
                                'sub_products' => $sub_product_arr ?? [],
                                'ingredients' => $ingredients ?? []
                            ];
                        } else {
                            // Find the store of the order
                            $store = Store::where(['id' => $order->fk_store_id, 'status' => 1, 'deleted' => 0])->first();
                            if (!$store) {
                                throw new Exception("The store is not available", 105);
                            } else {  
                                $store_no = get_store_no($store->name);
                            }
                            $store_distributor_price = 'store' . $store_no . '_distributor_price';
                            $store_price = 'store' . $store_no . '_price';
                            
                            $product = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
                                ->select(
                                    'base_products.*',
                                    'base_products_store.itemcode',
                                    'base_products_store.barcode',
                                    'base_products_store.product_distributor_price as distributor_price',
                                    )
                                ->find($value->fk_product_id);
                            $product->product_name_en = $product ? $product->product_name_en : '';
                            $product->product_name_ar = $product ? $product->product_name_ar : '';
                            $item_arr[] = [
                                'id' => $value->default_id ?? 0,
                                'store_id' => 0,
                                'store_name' => '',
                                'product_id' => $value->fk_product_id ?? 0,
                                'product_type' => $product->product_type ?? '',
                                'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                                'product_image' => !empty($product->product_image_url) ? $product->product_image_url : '',
                                'itemcode' => $product->itemcode ?? '',
                                'barcode' => $product->barcode ?? '',
                                'product_distributor_price' => $product->distributor_price ?? '',
                                'product_price' => $product->product_store_price,
                                'unit' => $product->unit ?? '',
                                'quantity' => $value->product_quantity ?? 0,
                                'other_names' => $value->other_names ?? 'none',
                                'status' => $value->status ?? 0,
                                'sub_products' => $sub_product_arr ?? [],
                                'ingredients' => $ingredients ?? []
                            ];
                        }
                    }
                } 
                $order_arr['order_items'] = $item_arr;
                $order_arr['order_items_outofstock'] = $item_out_of_stock_arr;

                if (!empty($order->getUser)) {
                    $customer_detail = [
                        'id' => $order->getUser->id,
                        'name' => $order->getUser->name,
                        'email' => $order->getUser->email,
                        'country_code' => $order->getUser->country_code,
                        'mobile' => $order->getUser->mobile,
                        'user_image' => !empty($order->getUser->getUserImage) ? asset('images/user_images') . '/' . $order->getUser->getUserImage->file_name : ''
                    ];
                } else {
                    $customer_detail = [];
                }
                $order_arr['customer_details'] = $customer_detail;

                //                $driver_review = DriverReview::where(['fk_order_id' => $order->id, 'fk_driver_id' => $this->driver_id])->first();
                //                if ($driver_review) {
                //                    $driver_review_arr = [
                //                        'user_id' => $driver_review->fk_user_id,
                //                        'user_name' => $driver_review->getUser->name,
                //                        'user_image' => !empty($driver_review->getUser->getUserImage) ? asset('images/user_images') . '/' . $driver_review->getUser->getUserImage->file_name : '',
                //                        'rating' => $driver_review->rating,
                //                        'review' => $driver_review->review,
                //                    ];
                //                } else {
                //                    $driver_review_arr = (object) [];
                //                }
                //                $order_arr['customer_reviews'] = $driver_review_arr;
            } 
            $order_arr['first_order_after_date'] = $first_order_after_date;
            $order_arr['first_order'] = $first_order;

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Order detail";
            $this->result = [
                'order_detail' => $order_arr
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

    protected function update_order_status(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'status']);

            $order_id = $request->input('id');
            $order = Order::find($request->input('id'));

            $isOrderAssigned = OrderDriver::where(['fk_order_id' => $request->input('id'), 'fk_driver_id' => $this->driver_id])->first();
            if (!$isOrderAssigned) {
                throw new Exception("This order is not assigned to you !", 105);
            }

            $driver = Driver::find($this->driver_id);

            if ($request->input('status') == 6) { //order loaded

                $loaded = Order::where(['id' => $request->input('id'), 'status' => 6])->first();
                if ($loaded) {
                    throw new Exception("Already loaded this order", 105);
                }

                $order_out_for_delivery = $this->order_out_for_delivery($order_id);
                $order_status = $order_out_for_delivery && isset($order_out_for_delivery['order_status']) ? $order_out_for_delivery['order_status'] : 0;
                if ($order_status!=6) {
                    throw new Exception("Something went wrong", 105);
                }

                $message = "Order loaded !";

            } elseif ($request->input('status') == 7) { //order delivered

                // Showing error in case of slot not started
                $delivered = Order::where(['id' => $request->input('id'), 'status' => 7])->first();
                if ($delivered) {
                    throw new Exception("Already delivered this order", 105);
                }

                $order_delivered = $this->order_delivered($order_id);
                $order_status = $order_delivered && isset($order_delivered['order_status']) ? $order_delivered['order_status'] : 0;
                if ($order_status!=7) {
                    throw new Exception("Something went wrong", 105);
                }
                
                $message = "Order delivered !";

            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = (object) [];
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

    protected function instant_orders(Request $request)
    {
        try {
            $total_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->select('orders.*')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 4)
                ->count();

            $ongoing_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->join('order_products', 'order_products.fk_order_id', '=', 'orders.id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 4)
                ->where('orders.status', '!=', 7)
                ->groupBy('order_products.fk_store_id')
                ->orderBy('order_drivers.updated_at', 'desc')
                ->orderByRaw("CONCAT(order_delivery_slots.delivery_date,' ',LEFT(order_delivery_slots.later_time,LOCATE('-',order_delivery_slots.later_time) - 1))")
                ->get();
            
            $ongoing_orders_arr = [];
            $ongoing_orders_by_slot = [];

            if ($ongoing_orders->count()) {
                foreach ($ongoing_orders as $key => $value) {

                    $delivery_preferences_text = $value && $value->change_for!='' ? 'Bring change for '.$value->change_for.'QAR,' : '';
                    if($value && $value->payment_type=='cod') {
                        $amount_from_card = $value->getSubOrder ? $value->amount_from_card + $value->getOrderPaidByCOD($value->id) : $value->amount_from_card;
                        $delivery_preferences_text .= 'Collect money '.floor($amount_from_card).'QAR from customer,';
                    }
                    $delivery_preferences = $value->delivery_preference ? explode(',',$value->delivery_preference) : [];
                    if (is_array($delivery_preferences) && !empty($delivery_preferences)) {
                        foreach ($delivery_preferences as $delivery_key=>$delivery_preference) {
                            if ($delivery_preference==1) {
                                if (isset($this->order_preferences) && isset($this->order_preferences[$delivery_key])) {
                                    $delivery_preferences_text .= $this->order_preferences[$delivery_key].',';
                                }
                            }
                        }
                    }
                    $userInfo = [
                        'name' => $value->getOrderAddress->name ?? '',
                        'mobile' => $value->getOrderAddress->mobile ?? '',
                        'landmark' => $value->getOrderAddress->landmark ?? '',
                        'address_line1' => $value->getOrderAddress->address_line1 ?? '',
                        'address_line2' => $value->getOrderAddress->address_line2 ?? '',
                        'latitude' => $value->getOrderAddress->latitude ?? '',
                        'longitude' => $value->getOrderAddress->longitude ?? '',
                        'address_type' => $value->getOrderAddress->address_type ?? ''
                    ];

                    $ongoing_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->getSubOrder ? (string) ($value->sub_total + $value->getOrderSubTotal($value->id)) : (string) $value->sub_total,
                        'total_amount' => $value->getSubOrder ? (string) ($value->total_amount + $value->getOrderSubTotal($value->id)) : (string) $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' =>  ($value->delivery_time && $value->later_time) ? $value->delivery_date.' '.$value->later_time : 'Time slot not availalble, check with IT department!',
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                        'delivery_preference' => $value->delivery_preference ?? '',
                        'delivery_preferences_text' => $delivery_preferences_text,
                        'user' => $userInfo,
                        'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($value->id)
                    ];

                    $ongoing_orders_by_slot_key = ($value->delivery_time==2 && $value->later_time) ? $value->delivery_date.' '.$value->later_time : $value->delivery_date;
                    $ongoing_orders_by_slot[$ongoing_orders_by_slot_key][$key] = $ongoing_orders_arr[$key];
                }
            } 
            
            $completed_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->select('orders.*')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('order_drivers.status', '=', 1)
                ->where('orders.status', '=', 4);

            if ($request->input('month') != '') {
                $completed_orders = $completed_orders
                    ->whereYear('orders.created_at', date('Y', strtotime($request->input('month'))))
                    ->whereMonth('orders.created_at', date('m', strtotime($request->input('month'))));
            } else {
                $completed_orders = $completed_orders
                    ->whereYear('orders.created_at', \Carbon\Carbon::now()->year)
                    ->whereMonth('orders.created_at', \Carbon\Carbon::now()->month);
            }

            $completed_orders = $completed_orders
                ->orderBy('order_drivers.updated_at', 'desc')
                ->get();

            if ($completed_orders->count()) {
                foreach ($completed_orders as $key => $value) {
                    $completed_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->sub_total,
                        'total_amount' => $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($value->id)
                    ];
                }
            } else {
                $completed_orders_arr = [];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "My Orders";

            $this->result = [
                'total_orders' => $total_orders,
                'orders' => $ongoing_orders_arr,
                'ongoing_orders_by_slot' => $ongoing_orders_by_slot
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

    protected function update_instant_orders(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'status']);

            $order = Order::find($request->input('id'));

            $isOrderAssigned = OrderDriver::where(['fk_order_id' => $request->input('id'), 'fk_driver_id' => $this->driver_id])->first();
            if (!$isOrderAssigned) {
                throw new Exception("This order is not assigned to you !", 105);
            }

            $driver = Driver::find($this->driver_id);

            if ($request->input('status') == 6) { //order loaded
                $loaded = Order::where(['id' => $request->input('id'), 'status' => 6])->first();
                if ($loaded) {
                    throw new Exception("Already loaded this order", 105);
                }

                Order::find($request->input('id'))->update(['status' => 6]);
                OrderStatus::create([
                    'fk_order_id' => $request->input('id'),
                    'status' => 6
                ]);
                $message = "Order loaded !";

                $title = 'Out for delivery';
                $body = 'Your order is out for delivery #' . $order->orderId;

                $data = [
                    'status' => 6,
                    'order_id' => $request->input('id'),
                    'user_id' => (string) $order->fk_user_id,
                    'orderId' => $order->orderId
                ];

                $this->send_inapp_notification_to_user(
                    $order->fk_user_id,
                    $title,
                    "",
                    $body,
                    "",
                    1,
                    $data
                );

                $user_device = \App\Model\UserDeviceDetail::where(['fk_user_id' => $order->fk_user_id])->first();
                if ($user_device) {
                    $type = 1;
                    $title = $title;
                    $body = $body;

                    $this->send_push_notification_to_user($user_device->device_token, $type, $title, $body, $data);
                }
            } elseif ($request->input('status') == 7) { //order delivered
                //showing error in case of slot not started
                
                $delivered = Order::where(['id' => $request->input('id'), 'status' => 7])->first();
                if ($delivered) {
                    throw new Exception("Already delivered this order", 105);
                }

                Order::find($request->input('id'))->update(['status' => 7]);
                OrderStatus::create([
                    'fk_order_id' => $request->input('id'),
                    'status' => 7
                ]);
                $message = "Order delivered !";


                Driver::find($this->driver_id)->update(['is_available' => 1]);

            }

            // $orderN = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
            //     ->select('orders.*')
            //     ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
            //     ->where('orders.id', '=', $request->input('id'))
            //     ->first();

            // if ($orderN) {
            //     $orderN_arr = [
            //         'id' => $orderN->id,
            //         'orderId' => $orderN->orderId,
            //         'sub_total' => $orderN->sub_total,
            //         'total_amount' => $orderN->total_amount,
            //         'delivery_charge' => $orderN->delivery_charge,
            //         'coupon_discount' => $orderN->coupon_discount ?? '',
            //         'item_count' => $orderN->getOrderProducts->count(),
            //         'order_time' => date('Y-m-d H:i:s', strtotime($orderN->created_at)),
            //         'status' => $orderN->status,
            //         'status_text' => $this->order_status[$orderN->status]
            //     ];
            //     if (!empty($orderN->getOrderAddress)) {
            //         $orderN_arr['delivery'] = $orderN->getOrderAddress;
            //     } else {
            //         $orderN_arr['delivery'] = (object) [];
            //     }

            //     if (!empty($orderN->getOrderProducts)) {
            //         foreach ($orderN->getOrderProducts as $key => $value) {
            //             $item_arr[$key] = [
            //                 'product_id' => $value->fk_product_id,
            //                 'product_name' => $lang == 'ar' ? $value->getProduct->product_name_ar : $value->getProduct->product_name_en,
            //                 'product_image' => !empty($value->getProduct->getProductImage) ? url('images/product_images') . '/' . $value->getProduct->getProductImage->file_name : '',
            //                 'unit' => $value->getProduct->unit,
            //                 'quantity' => $value->getProduct->unit,
            //                 'product_price' => $value->product_price,
            //                 'product_discount' => $value->discount,
            //                 'product_quantity' => $value->product_quantity,
            //             ];
            //         }
            //     } else {
            //         $item_arr = [];
            //     }

            //     $orderN_arr['order_items'] = $item_arr;

            //     if (!empty($orderN->getUser)) {
            //         $customer_detail = [
            //             'id' => $orderN->getUser->id,
            //             'name' => $orderN->getUser->name,
            //             'email' => $orderN->getUser->email,
            //             'country_code' => $orderN->getUser->country_code,
            //             'mobile' => $orderN->getUser->mobile,
            //             'user_image' => !empty($orderN->getUser->getUserImage) ? asset('images/user_images') . '/' . $orderN->getUser->getUserImage->file_name : ''
            //         ];
            //     } else {
            //         $customer_detail = [];
            //     }
            //     $orderN_arr['customer_details'] = $customer_detail;
            // } else {
            //     $orderN_arr = [];
            // }

            //updating the invoices in account panel
            $store = Store::find($order->fk_store_id);
            if (!$store) { 
                throw new Exception("The store is not available", 105);
            }
            $store_no = get_store_no($store->name);
            $purchasePrice = Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->join($this->products_table, 'order_products.fk_product_id', '=', $this->products_table . '.id')
                ->select($this->products_table.'.*','orders.*')
                ->selectRaw("SUM(order_products.product_quantity*".$this->products_table.".store".$store_no."_distributor_price) as total_distributor_price")
                ->where('orders.id', '=', $request->input('id'))
                ->first();
            Invoice::create([
                'date' => date('Y-m-d'),
                'time' => date('H:i:s'),
                'order_id' => $order->id,
                'selling_price' => $order->total_amount,
                'purchase_price' => $purchasePrice->total_distributor_price
            ]);

            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = (object) [];
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

    protected function instant_order_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // New order after Feb, 1st indicator variables
            $first_order_after_date = '2023-02-01 00:00:00';
            $first_order = null;

            // Order detail variables
            $this->required_input($request->input(), ['id']);
            $order = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('orders.*', 'order_delivery_slots.delivery_time', 'order_delivery_slots.later_time', 'order_delivery_slots.delivery_preference')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('orders.id', '=', $request->input('id'))
                ->first();
            $order_arr = [];

            if ($order) {

                // New order after Feb, 1st indicator
                $first_order = Order::where('created_at','>=',$first_order_after_date)->where('fk_user_id','=',$order->fk_user_id)->where('id','<>',$order->id)->first() ? null : "Add thank you letter";
                
                // Order details
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
                    'status_text' => $this->order_status[$order->status],
                    'customer_invoice' => env('APP_URL','https://jeeb.tech/').'customer-invoice/'.base64url_encode($order->id)
                ];
                if (!empty($order->getOrderAddress)) {
                    $order_arr['delivery'] = $order->getOrderAddress;
                } else {
                    $order_arr['delivery'] = (object) [];
                }

                if (!empty($order->getOrderProducts)) {
                    foreach ($order->getOrderProducts->where('is_out_of_stock', '=', 0) as $key => $value) {
                        $item_arr[$key] = [
                            'store' => $value->getStore,
                            'product_id' => $value->fk_product_id,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->getProductImage) ? url('images/product_images') . '/' . $value->file_name : '',
                            'unit' => $value->unit,
                            'quantity' => $value->product_quantity,
                            'product_price' => $value->total_product_price,
                            'itemcode' => $value->itemcode,
                            'barcode' => $value->barcode,
                            'product_discount' => $value->margin,
                            'product_quantity' => $value->product_quantity,
                        ];
                    }
                } else {
                    $item_arr = [];
                }

                $order_arr['order_items'] = array_values($item_arr);

                if (!empty($order->getUser)) {
                    $customer_detail = [
                        'id' => $order->getUser->id,
                        'name' => $order->getUser->name,
                        'email' => $order->getUser->email,
                        'country_code' => $order->getUser->country_code,
                        'mobile' => $order->getUser->mobile,
                        'user_image' => !empty($order->getUser->getUserImage) ? asset('images/user_images') . '/' . $order->getUser->getUserImage->file_name : ''
                    ];
                } else {
                    $customer_detail = [];
                }
                $order_arr['customer_details'] = $customer_detail;

                //                $driver_review = DriverReview::where(['fk_order_id' => $order->id, 'fk_driver_id' => $this->driver_id])->first();
                //                if ($driver_review) {
                //                    $driver_review_arr = [
                //                        'user_id' => $driver_review->fk_user_id,
                //                        'user_name' => $driver_review->getUser->name,
                //                        'user_image' => !empty($driver_review->getUser->getUserImage) ? asset('images/user_images') . '/' . $driver_review->getUser->getUserImage->file_name : '',
                //                        'rating' => $driver_review->rating,
                //                        'review' => $driver_review->review,
                //                    ];
                //                } else {
                //                    $driver_review_arr = (object) [];
                //                }
                //                $order_arr['customer_reviews'] = $driver_review_arr;
            } 
            $order_arr['first_order_after_date'] = $first_order_after_date;
            $order_arr['first_order'] = $first_order;

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Order detail";
            $this->result = [
                'order_detail' => $order_arr
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

    protected function collector_orders(Request $request)
    {
        try {

            $driver_stores = DriverGroup::where('fk_driver_id',$this->driver_id)->pluck('fk_store_id')->toArray();

            $total_orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->select('orders.*')
                ->whereIn('storekeeper_products.fk_store_id', $driver_stores)
                ->groupBy('storekeeper_products.fk_order_id')
                ->get()
                ->count();

            $ongoing_orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('orders.*')
                ->whereIn('storekeeper_products.fk_store_id', $driver_stores)
                ->where('storekeeper_products.collection_status', 0)
                ->groupBy('storekeeper_products.fk_order_id')
                ->orderByRaw("CONCAT(order_delivery_slots.delivery_date,' ',LEFT(order_delivery_slots.later_time,LOCATE('-',order_delivery_slots.later_time) - 1))")
                ->get();
                
            $ongoing_orders_arr = [];
            $ongoing_orders_by_slot = [];

            if ($ongoing_orders->count()) {
                foreach ($ongoing_orders as $key => $value) {

                    $ongoing_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' =>  ($value->delivery_time && $value->later_time) ? $value->delivery_date.' '.$value->later_time : 'Time slot not availalble, check with IT department!',
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'delivery_in' => getDeliveryIn($value->getOrderDeliverySlot->expected_eta ?? 0),
                        'delivery_time' => $value->delivery_time,
                        'later_time' => $value->later_time ?? '',
                    ];

                }
            } 
        
            $this->error = false;
            $this->status_code = 200;
            $this->message = "My Orders";
            
            $this->result = [
                'total_orders' => $total_orders,
                'orders' => $ongoing_orders_arr,
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

    protected function collector_stores(Request $request)
    {
        try {

            $collectionOrders = OrderDeliverySlot::join('orders','orders.id','=','order_delivery_slots.fk_order_id')
                ->where('delivery_date', '=', Carbon::now()->format('Y-m-d'))
                ->whereNotIn('orders.status', array(4, 5))
                ->orderBy('order_delivery_slots.id', 'desc')
                ->get()->pluck('fk_order_id')->toArray();

            $collectionStores = DriverGroup::join('stores','driver_groups.fk_store_id','=','stores.id')
                ->join('storekeeper_products','storekeeper_products.fk_store_id','=','driver_groups.fk_store_id')
                ->where('driver_groups.fk_driver_id', $this->driver_id)
                ->where('stores.status','=',1)
                ->whereIn('storekeeper_products.fk_order_id', $collectionOrders)
                ->groupBy('storekeeper_products.fk_store_id')
                ->get();

            $collectionOrderStoresArr = [];
            if ($collectionStores->count()) {
                foreach ($collectionStores as $key => $value) {
                    $collectionOrderStoresArr[$key] = [
                        'id' => $value->fk_store_id,
                        'name' => $value->name.' '.$value->company_name,
                        'latitude' => $value->latitude,
                        'longitude' => $value->longitude,
                        'address' => $value->address,
                        'city' => $value->city,
                    ];
                }
            }

            //check all store items are collected
            $flag = 0;
            $orderCollections = StorekeeperProduct::where('fk_driver_id', $this->driver_id)->get();
            foreach ($orderCollections as $key => $value) {
                if($value->collection_status == 0){
                    $flag = 0;
                }elseif($value->collection_status != 1){
                    $flag = 1;
                }else{
                    $flag = 0;
                }
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = "My Stores";
            
            $this->result = [
                'status' => $flag,
                'orders' => [],
                'stores' => $collectionOrderStoresArr,
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

    protected function mark_store_order_out_for_delivery(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'status']);

            $driver = Driver::find($this->driver_id);
            
            $driver_store_orders = StorekeeperProduct::where(['fk_store_id' => $request->input('id'), 'fk_order_id' => $request->input('order_id')])->update([
                'fk_driver_id' => $this->driver_id,
                'collection_status' => 1,
                'collected_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $message = "Order item collected !";
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = (object) [];
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

    protected function collector_time_slot_order_detail(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // Order detail variables
            $this->required_input($request->input(), ['id']);

            // Order timeslots
            $timeSlots = OrderDeliverySlot::join('storekeeper_products', 'order_delivery_slots.fk_order_id', '=', 'storekeeper_products.fk_order_id')
                ->leftJoin('orders', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('order_delivery_slots.*')
                ->where('storekeeper_products.fk_store_id', '=', $request->input('id'))
                ->where('order_delivery_slots.delivery_date', Carbon::now()->format('Y-m-d'))
                ->where('orders.status','!=', 4)
                ->groupBy('order_delivery_slots.later_time')
                ->orderByRaw("CONCAT(order_delivery_slots.delivery_date,' ',LEFT(order_delivery_slots.later_time,LOCATE('-',order_delivery_slots.later_time) - 1))")
                ->get();

            $timeSlotsArr = [];
            
            if($timeSlots){
                
                foreach ($timeSlots as $key => $value) {
                    
                    $timeSlotOrders = OrderDeliverySlot::join('storekeeper_products', 'order_delivery_slots.fk_order_id', '=', 'storekeeper_products.fk_order_id')
                        ->join('orders', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                        ->select('orders.*','storekeeper_products.collection_status','storekeeper_products.fk_store_id')
                        ->where('order_delivery_slots.later_time', '=', $value->later_time)
                        ->where('order_delivery_slots.delivery_date', Carbon::now()->format('Y-m-d'))
                        ->where('orders.status','!=',4)
                        ->groupBy('storekeeper_products.fk_order_id')
                        ->get();

                    $timeSlotOrdersArr = [];
                    
                    foreach ($timeSlotOrders as $key2 => $value2) {
                        
                        $timeSlotOrderProducts = OrderProduct::leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                            ->leftJoin('base_products_store', 'order_products.fk_product_store_id', '=', 'base_products_store.id')
                            ->leftJoin('stores', 'order_products.fk_store_id', '=', 'stores.id')
                            ->leftJoin('storekeeper_products', function($join)
                            {
                                $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
                                $join->on('storekeeper_products.fk_order_product_id', '=', 'order_products.id');
                            })
                            ->select('base_products.*', 'base_products_store.itemcode', 'base_products_store.itemcode', 'base_products_store.barcode', 'base_products_store.product_distributor_price', 'base_products_store.product_store_price', 'base_products_store.stock', 'base_products_store.other_names', 'order_products.sub_products','order_products.fk_order_id', 'order_products.fk_product_id', 'order_products.fk_store_id', 'order_products.product_quantity', 'stores.company_name', 'stores.name', 'storekeeper_products.id as default_id','storekeeper_products.collection_status','storekeeper_products.status')
                            ->where('storekeeper_products.fk_order_id','=', $value2->id)
                            ->where('storekeeper_products.fk_store_id', '=', $request->input('id'))
                            ->orderBy('base_products.fk_category_id', 'asc')
                            ->orderBy('base_products.fk_sub_category_id', 'asc')
                            ->get();

                            $timeSlotOrderProductsArr = [];

                        if (!empty($timeSlotOrderProducts)) {
                            foreach ($timeSlotOrderProducts as $key3 => $value3) { 
                                // If sub products for recipe
                                $sub_product_arr = [];
                                if (isset($value3->sub_products) && $value3->sub_products!='') {
                                    $sub_products = json_decode($value3->sub_products);
                                    if ($sub_products && is_array($sub_products)) {
                                        foreach ($sub_products as $key4 => $value4) {
                                            if ($value3->fk_store_id!=0) {
                                                $value3->product_name_en = $value3->product_name_en ?? '';
                                                $value3->product_name_ar = $value3->product_name_ar ?? '';
                                                if ($lang == 'ar') {
                                                    $sub_product_arr[$key4] = array (
                                                        'product_id' => $value4->product_id ?? 0,
                                                        'product_quantity' => $value4->product_quantity ?? 0,
                                                        'product_name' => $lang == 'ar' ? $value4->product_name_ar : $value2->product_name_en,
                                                        'product_image' => $value4->product_image_url ?? '',
                                                        'item_unit' => $value4->unit ?? '',
                                                        'other_names' => $value4->other_names ?? 'none'
                                                    );
                                                } else {
                                                    $sub_product_arr[$key4] = array (
                                                        'product_id' => $value4->product_id ?? 0,
                                                        'product_quantity' => $value4->product_quantity ?? 0,
                                                        'product_name' => $lang == 'ar' ? $value4->product_name_ar : $value2->product_name_en,
                                                        'product_image' => $value4->product_image_url ?? '',
                                                        'item_unit' => $value4->unit ?? '',
                                                        'other_names' => $value4->other_names ?? 'none'
                                                    );
                                                }
                                            } else {
                                                
                                            }
                                        }
                                    }
                                }
                                // All ingredients for recipe
                                $ingredients = [];
                                if ($value3->product_type=='recipe' && isset($value3->recipe_variant_id) && $value3->recipe_variant_id!=0) {
                                    $recipe_varient = \App\Model\RecipeVariant::find($value3->recipe_variant_id);
                                    $ingredients = $recipe_varient ? json_decode($recipe_varient->ingredients) : [];
                                }
                                // Check new base products or old product
                                if ($value3->fk_store_id!=0 && $value3->order_product_status !=2) {
                                    $value3->product_name_en = $value3->product_name_en ?? '';
                                    $value3->product_name_ar = $value3->product_name_ar ?? '';
                                    $timeSlotOrderProductsArr[] = [
                                        'id' => $value3->default_id ?? 0,
                                        'order_id' => $value3->fk_order_id,
                                        'product_id' => $value3->id ?? 0,
                                        'store_id' => $value3->fk_store_id ?? 0,
                                        'store_name' => $value3->company_name.' '.$value3->name,
                                        'product_type' => $value3->product_type ?? '',
                                        'product_name' => $lang == 'ar' ? $value3->product_name_ar : $value3->product_name_en,
                                        'product_image' => !empty($value3->product_image_url) ? $value3->product_image_url : '',
                                        'itemcode' => $value3->itemcode ?? '',
                                        'barcode' => $value3->barcode ?? '',
                                        'unit' => $value3->unit ?? '',
                                        'quantity' => $value3->product_quantity ?? 0,
                                        'other_names' => $value3->other_names ?? 'none',
                                        'status' => $value3->status ?? 0,
                                        'sub_products' => $sub_product_arr ?? [],
                                        'ingredients' => $ingredients ?? [],
                                        'status_text' => $value3->status == 1 ? 'collected' : ($value3->status == 2 ? 'out of stock' : 'not collected'),
                                    ];
                                }else {
                                    // Find the store of the order
                                    $store = Store::where(['id' => $value2->fk_store_id, 'status' => 1, 'deleted' => 0])->first();
                                    if (!$store) {
                                        throw new Exception("The store is not available", 105);
                                    } else {  
                                        $store_no = get_store_no($store->name);
                                    }
                                    $store_distributor_price = 'store' . $store_no . '_distributor_price';
                                    $store_price = 'store' . $store_no . '_price';
                                    
                                    $product = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
                                        ->select(
                                            'base_products.*',
                                            'base_products_store.itemcode',
                                            'base_products_store.barcode',
                                            'base_products_store.product_distributor_price',
                                            )
                                        ->find($value3->fk_product_id);
                                    $product->product_name_en = $product ? $product->product_name_en : '';
                                    $product->product_name_ar = $product ? $product->product_name_ar : '';
                                    $timeSlotOrderProductsArr[] = [
                                        'id' => $value3->default_id ?? 0,
                                        'store_id' => 0,
                                        'store_name' => '',
                                        'product_id' => $value3->fk_product_id ?? 0,
                                        'product_type' => $product->product_type ?? '',
                                        'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                                        'product_image' => !empty($product->product_image_url) ? $product->product_image_url : '',
                                        'itemcode' => $product->itemcode ?? '',
                                        'barcode' => $product->barcode ?? '',
                                        'unit' => $product->unit ?? '',
                                        'quantity' => $value3->product_quantity ?? 0,
                                        'other_names' => $value3->other_names ?? 'none',
                                        'status' => $value3->status ?? 0,
                                        'sub_products' => $sub_product_arr ?? [],
                                        'ingredients' => $ingredients ?? [],
                                        'status_text' => $value3->status == 1 ? 'collected' : ($value3->status == 2 ? 'out of stock' : 'not collected')
                                    ];
                                }
                            }
                        }

                        $totalStorekeeperCollectedItems = StorekeeperProduct::where(['fk_order_id' => $value2->id, 'status' => 1])->count();

                        $timeSlotOrdersArr[] = [
                            'id' => $value2->id,
                            'store_id' => $value2->fk_store_id,
                            'orderId' => $value2->orderId,
                            'status' => $value2->collection_status,
                            'storekeeper_packed' => $totalStorekeeperCollectedItems >= $timeSlotOrderProducts->count() ? 1 : 0,
                            'order_items' => $timeSlotOrderProductsArr
                        ];
                    }

                    $timeSlotsArr[] = [
                        'id' => $value->id,
                        'delivery_date' => $value->delivery_date,
                        'delivery_slot' => $value->later_time,
                        'order_detail' => $timeSlotOrdersArr
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Order detail";
            $this->result = [
                'time_slots' => $timeSlotsArr
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

    protected function mark_time_slot_order_out_for_delivery(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['id', 'order_id', 'status']);

            $driver = Driver::find($this->driver_id);

            $driver_store_orders = StorekeeperProduct::where(['fk_store_id' => $request->input('id'), 'fk_order_id' => $request->input('order_id')])->update([
                'fk_driver_id' => $this->driver_id,
                'collection_status' => 1,
                'collected_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            $message = "Order item collected !";
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = (object) [];
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

    protected function mark_store_order_delivered(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['status']);

            $driver = Driver::find($this->driver_id);

            $driver_store_orders = StorekeeperProduct::where(['fk_driver_id' => $this->driver_id])->update([
                'delivered_status' => 1,
                'delivered_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            //delivered orders
            $delvered_orders = DB::table('storekeeper_products as o')
                ->select('o.*')
                ->where('o.status', 2)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('storekeeper_products')
                        ->whereRaw('fk_order_id = o.fk_order_id')
                        ->where('status', '!=', 2);
                })->get();

            //
            if($delvered_orders){
                $order_status = 5;
                foreach ($delvered_orders as $key => $order) {
                    Order::find($order->fk_order_id)->update(['status' => $order_status]);
                    OrderStatus::create([
                        'fk_order_id' => $order->fk_order_id,
                        'status' => 0
                    ]);
                }
            }

            $message = "Order item delivered !";
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = (object) [];
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
