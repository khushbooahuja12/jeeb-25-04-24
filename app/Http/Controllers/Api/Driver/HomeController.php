<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Driver;
use App\Model\Order;
use App\Model\OrderProduct;
//use App\Model\DriverReview;
use App\Model\DriverLocation;
use App\Model\OrderDriver;
use App\Model\OrderStatus;
use App\Model\Store;
use App\Model\DriverGroup;
use App\Model\StorekeeperProduct;

class HomeController extends CoreApiController
{

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];
    protected $order_status = [
        'Order successfully placed',
        'Your item packed',
        'Order Accepted',
        'Order Pickedup',
        'Order delivered successfully'
    ];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['home', 'update_location', 'collector_home'];

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
    }

    protected function home(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $completed_orders = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('order_drivers.status', '=', 1)
                ->where('orders.status', '=', 7)
                ->count();

            $new_order = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                ->select('orders.*')
                ->where('order_drivers.fk_driver_id', '=', $this->driver_id)
                ->where('order_drivers.status', '=', 1)
                ->where('orders.status', '=', 1)
                ->first();
            if ($new_order) {
                $order_arr = [
                    'id' => $new_order->id,
                    'orderId' => $new_order->orderId,
                    'sub_total' => $new_order->sub_total,
                    'total_amount' => $new_order->total_amount,
                    'delivery_charge' => $new_order->delivery_charge,
                    'coupon_discount' => $new_order->coupon_discount ?? '',
                    'item_count' => $new_order->getOrderProducts->count(),
                    'order_time' => date('Y-m-d H:i:s', strtotime($new_order->created_at)),
                    'status' => $new_order->status,
                    'status_text' => $this->order_status[$new_order->status]
                ];
                if (!empty($new_order->getOrderAddress)) {
                    $order_arr['delivery'] = $new_order->getOrderAddress;
                } else {
                    $order_arr['delivery'] = (object) [];
                }

                if (!empty($new_order->getOrderProducts)) {
                    foreach ($new_order->getOrderProducts as $key => $value) {
                        $item_arr[$key] = [
                            'product_id' => $value->fk_product_id,
                            'product_name' => $lang == 'ar' ? $value->getProduct->product_name_ar : $value->getProduct->product_name_en,
                            'product_image' => !empty($value->getProduct->getProductImage) ? url('images/product_images') . '/' . $value->getProduct->getProductImage->file_name : '',
                            'unit' => $value->getProduct->unit,
                            'quantity' => $value->getProduct->unit,
                            'product_price' => $value->product_price,
                            'product_discount' => $value->margin,
                            'product_quantity' => $value->product_quantity,
                        ];
                    }
                } else {
                    $item_arr = [];
                }

                $order_arr['order_items'] = $item_arr;

                if (!empty($new_order->getUser)) {
                    $customer_detail = [
                        'id' => $new_order->getUser->id,
                        'name' => $new_order->getUser->name,
                        'email' => $new_order->getUser->email,
                        'country_code' => $new_order->getUser->country_code,
                        'mobile' => $new_order->getUser->mobile,
                        'user_image' => !empty($new_order->getUser->getUserImage) ? asset('images/user_images') . '/' . $new_order->getUser->getUserImage->file_name : ''
                    ];
                } else {
                    $customer_detail = [];
                }

                $order_arr['customer_details'] = $customer_detail;
            } else {
                $order_arr = (object) [];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'complete_orders' => $completed_orders,
                'new_order_request' => $order_arr
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

    protected function update_location(Request $request)
    {
        try {
            $this->required_input($request->input(), ['address', 'latitude', 'longitude']);

            $update = DriverLocation::create([
                'fk_driver_id' => $this->driver_id,
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude')
            ]);
            if ($update) {

                // // Assign next order automatically, removed after order assignment made for manually
                // $driver = Driver::where(['id'=>$this->driver_id,'deleted'=>0,'blocked'=>0])->first();

                // $distance = get_distance($request->input('latitude'), $request->input('longitude'), $driver->getStore->latitude, $driver->getStore->longitude, 'K');
                
                // if ($driver && $distance <= 1) {
                //     $nextOrder = Order::join('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                //         ->select('orders.*', 'order_drivers.*')
                //         ->where('orders.fk_store_id', '=', $driver->fk_store_id)
                //         ->where('orders.status', '=', 3)
                //         ->where('order_drivers.fk_driver_id', '=', 0)
                //         ->where('order_drivers.status', '=', 0)
                //         ->orderBy('order_drivers.created_at', 'asc')
                //         ->first();
                //         // Debug using return
                //         // return response()->json([
                //         //     'error' => true,
                //         //     'status_code' => 200,
                //         //     'message' => 'qa',
                //         //     'result' => $nextOrder
                //         // ]);
                //         // throw new Exception($nextOrder, 105);
                //     if ($nextOrder && $driver->is_available == 1) {
                //         $order_driver = OrderDriver::where(['fk_order_id'=>$nextOrder->fk_order_id])->update(['fk_driver_id' => $this->driver_id, 'status' => 1]);
                //         if ($order_driver) {
                //             Driver::find($this->driver_id)->update(['is_available' => 0]);
                //             Order::find($nextOrder->id)->update(['status' => 2]);
                //             OrderStatus::create([
                //                 'fk_order_id' => $nextOrder->id,
                //                 'status' => 2
                //             ]);
                //         }
                //     }
                // }

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Location updated successfully";
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

    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $pi80 = M_PI / 180;
        $lat1 *= $pi80;
        $lon1 *= $pi80;
        $lat2 *= $pi80;
        $lon2 *= $pi80;
        $r = 6372.797; // radius of Earth in km 6371
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $r * $c;
        return $km;
    }

    protected function collector_home(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $driver_stores = DriverGroup::where('fk_driver_id', $this->driver_id)->pluck('fk_store_id')->toArray();

            $completed_orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->select('orders.*')
                ->where('storekeeper_products.fk_driver_id', $this->driver_id)
                ->where('storekeeper_products.collection_status', 1)
                ->groupBy('storekeeper_products.fk_order_id')
                ->get()
                ->count();

            $new_order = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->select('orders.*')
                ->whereIn('storekeeper_products.fk_store_id', $driver_stores)
                ->where('storekeeper_products.collection_status', '=', 0)
                ->first();

            if ($new_order) {

                $new_order_products = OrderProduct::join('storekeeper_products','storekeeper_products.fk_order_id', '=','order_products.fk_order_id')
                    ->select('order_products.*','storekeeper_products.id as order_collection_id')
                    ->whereIn('storekeeper_products.fk_store_id', $driver_stores)
                    ->where('storekeeper_products.fk_order_id', '=', $new_order->id)
                    ->where('storekeeper_products.collection_status', '=', 0)
                    ->get();

                $order_arr = [
                    'id' => $new_order->id,
                    'orderId' => $new_order->orderId,
                    'item_count' => $new_order->getOrderProducts->count(),
                    'order_time' => date('Y-m-d H:i:s', strtotime($new_order->created_at)),
                    'status' => $new_order->status,
                ];

                if (!empty($new_order_products)) {
                    foreach ($new_order_products as $key => $value) {
                        $item_arr[$key] = [
                            'id' => $value->order_collection_id,
                            'product_id' => $value->fk_product_id,
                            'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                            'product_image' => !empty($value->product_image_url) ? url('images/product_images') . '/' . $value->product_image_url : '',
                            'unit' => $value->unit,
                            'quantity' => $value->unit,
                            'product_price' => $value->product_price,
                            'product_discount' => $value->margin,
                            'product_quantity' => $value->product_quantity,
                        ];
                    }
                } else {
                    $item_arr = [];
                }

                $order_arr['order_items'] = $item_arr;

            } else {
                $order_arr = (object) [];
            }
            
            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";
            $this->result = [
                'complete_orders' => $completed_orders,
                'new_order_request' => $order_arr
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
}
