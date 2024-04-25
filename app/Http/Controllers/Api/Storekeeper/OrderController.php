<?php

namespace App\Http\Controllers\Api\Storekeeper;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\User;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderStatus;
use App\Model\OrderDriver;
use App\Model\Store;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\Storekeeper;
use App\Model\StorekeeperProduct;
use App\Model\TechnicalSupportProduct;


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

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = [
            'orders', 'order_detail', 'update_order_item_status', 'suggest_replacement_items', 'suggest_order_replacement_items'
        ];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 3)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->storekeeper_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function get_header(Request $request)
    {
        try {
            return response()->json([
                'error' => true,
                'status_code' => 301,
                'message' => "Header data returned",
                'result' => apache_request_headers()
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
    }

    protected function orders(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $keyArr = ['algolia_index_name', 'under_maintenance', 'maintenance_title', 'maintenance_desc'];
            $adminSetting = \App\Model\AdminSetting::whereIn('key', $keyArr)->get()->toArray();
            $arrSettingArr = [];
            foreach ($adminSetting as $value) {
                $arrSettingArr[$value['key']] = $value['value'];
            }

            $storekeeper = Storekeeper::find($this->storekeeper_id);
            $total_orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->select('orders.*')
                ->where('storekeeper_products.fk_storekeeper_id', '=', $this->storekeeper_id)
                ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 4)
                ->where(function($query) use($storekeeper) {
                    $query->when($storekeeper->is_test_user == 1, function ($query) {
                        $query->where('orders.test_order', '=', 1)->orWhere('orders.test_order');
                        $query->orWhere('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                    });

                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('storekeeper_products.fk_order_id')
                ->count();

            $active_orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->select('orders.*','order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date','order_delivery_slots.later_time')
                ->where('storekeeper_products.fk_storekeeper_id', '=', $this->storekeeper_id)
                // ->where('orders.status', '>=', 2)
                ->where('orders.status', '!=', 0)
                ->where('orders.status', '!=', 4)
                ->where('orders.status', '!=', 7)
                ->where('storekeeper_products.status', '=', 0)
                ->where('orders.parent_id', '=', 0)
                ->where(function($query) use($storekeeper) {
                    $query->when($storekeeper->is_test_user == 1, function ($query) {
                        $query->where('orders.test_order', '=', 1)->orWhere('orders.test_order');
                        $query->orWhere('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                    });

                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('storekeeper_products.fk_order_id')
                // ->orderBy('storekeeper_products.updated_at', 'desc')
                ->orderByRaw("CONCAT(order_delivery_slots.delivery_date,' ',LEFT(order_delivery_slots.later_time,LOCATE('-',order_delivery_slots.later_time) - 1))")
                ->get();

            
            $active_orders_arr = [];
            $active_orders_by_slot = [];

            if ($active_orders->count()) {
                foreach ($active_orders as $key => $value) {
                    if ($value->getOrderAddress) {
                        $userInfo = [
                            'name' => $value->getOrderAddress->name ?? '',
                            'mobile' => $value->getOrderAddress->mobile ?? '',
                            'landmark' => $value->getOrderAddress->landmark ?? '',
                            'address_line1' => $value->getOrderAddress->address_line1 ?? '',
                            'address_line2' => $value->getOrderAddress->address_line2 ?? '',
                            'latitude' => $value->getOrderAddress->latitude ?? '',
                            'longitude' => $value->getOrderAddress->longitude ?? '',
                            'address_type' => $value->getOrderAddress->address_type ?? '',
                        ];
                    } else {
                        $userInfo = (object) [];
                    }

                    $active_orders_arr[$key] = [
                        'id' => $value->id,
                        'orderId' => $value->orderId,
                        'sub_total' => $value->sub_total,
                        'total_amount' => $value->total_amount,
                        'delivery_charge' => $value->delivery_charge,
                        'coupon_discount' => $value->coupon_discount ?? '',
                        'item_count' => $value->getOrderProducts->count(),
                        'order_time' => ($value->delivery_time && $value->later_time) ? $value->delivery_date.' '.$value->later_time : 'Time slot not availalble, check with IT department!',
                        'status' => $value->status,
                        'status_text' => $this->order_status[$value->status],
                        'user' => $userInfo
                    ];
                    $active_orders_by_slot_key = ($value->delivery_time==2 && $value->later_time) ? $value->delivery_date.' '.$value->later_time : $value->delivery_date;
                    $active_orders_by_slot[$active_orders_by_slot_key][$key] = $active_orders_arr[$key];
                }
            } 

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Orders";
            $this->result = [
                'total_orders' => $total_orders,
                'active_orders' => $active_orders_arr,
                'active_orders_by_slot' => $active_orders_by_slot,
                'algolia_index_name' => $arrSettingArr['algolia_index_name'],
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

            $this->required_input($request->input(), ['id']);

            // New order after Feb, 1st indicator variables
            $first_order_after_date = '2023-02-01 00:00:00';
            $first_order = null;

            // Order detail variables
            $order = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
                ->select('orders.*')
                ->where('storekeeper_products.fk_storekeeper_id', '=', $this->storekeeper_id)
                ->where('orders.id', '=', $request->input('id'))
                ->orderBy('storekeeper_products.id','asc')
                ->first();
            $order_arr = [];
                
            if ($order) {

                // New order after Feb, 1st indicator
                $first_order = Order::where('created_at','>=',$first_order_after_date)->where('fk_user_id','=',$order->fk_user_id)->where('id','<>',$order->id)->first() ? null : "Add thank you letter";
                
                // Order details
                $order_arr = [
                    'id' => $order->id,
                    'orderId' => $order->orderId,
                    'sub_total' => $order->sub_total,
                    'total_amount' => $order->total_amount,
                    'delivery_charge' => $order->delivery_charge,
                    'coupon_discount' => $order->coupon_discount ?? '',
                    'item_count' => $order->getOrderProducts->count(),
                    'order_time' => date('Y-m-d H:i:s', strtotime($order->created_at)),
                    'status' => $order->status,
                    'status_text' => $this->order_status[$order->status],
                    'shareable_link' => route('storekeeper-sharing', base64url_encode($order->id))
                ];

                // All order products
                $order_products = OrderProduct::leftJoin('storekeeper_products', function($join)
                    {
                        $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
                        $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
                    })
                    ->leftJoin('base_products', 'order_products.fk_product_id', '=', 'base_products.id')
                    ->leftJoin('base_products_store', 'order_products.fk_product_store_id', '=', 'base_products_store.id')
                    ->leftJoin('stores', 'stores.id', '=', 'base_products.fk_store_id')
                    ->select('base_products.*', 'base_products_store.itemcode', 'base_products_store.itemcode', 'base_products_store.barcode', 'base_products_store.product_distributor_price as distributor_price', 'base_products_store.product_store_price AS product_price', 'base_products_store.stock', 'base_products_store.other_names', 'storekeeper_products.status', 'storekeeper_products.id as default_id', 'order_products.sub_products', 'order_products.fk_product_id', 'order_products.fk_store_id',  'order_products.product_quantity', 'storekeeper_products.fk_storekeeper_id', 'stores.back_margin')
                    ->where('storekeeper_products.fk_order_id', '=', $request->input('id'))
                    ->where('storekeeper_products.fk_storekeeper_id', '=', $this->storekeeper_id)
                    ->orderBy('base_products.fk_category_id', 'asc')
                    ->orderBy('base_products.fk_sub_category_id', 'asc')
                    ->get();

                $item_arr = [];
                if (!empty($order_products)) {
                    foreach ($order_products as $key => $value) {
                        // Check whether assigned
                        $assigned = false;
                        if ($value->fk_storekeeper_id==$this->storekeeper_id) {
                            $assigned = true;
                        }
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
                                                'product_price' => $value2->distributor_price ?? 0,
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
                                                'product_price' => $value2->distributor_price ?? 0,
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
                        if ($value->fk_store_id!=0) {
                            $value->product_name_en = $value->product_name_en ?? '';
                            $value->product_name_ar = $value->product_name_ar ?? '';
                            $value->distributor_price = $value->back_margin ? $value->distributor_price*(100+$value->back_margin)/100 : $value->distributor_price;
                            $value->distributor_price = round($value->distributor_price,2);
                            $item_arr[$key] = [
                                'assigned' => $assigned,
                                'id' => $value->default_id ?? 0,
                                'product_id' => $value->id ?? 0,
                                'product_type' => $value->product_type ?? '',
                                'product_name' => $lang == 'ar' ? $value->product_name_ar : $value->product_name_en,
                                'product_image' => !empty($value->product_image_url) ? $value->product_image_url : '',
                                'itemcode' => $value->itemcode ?? '',
                                'barcode' => $value->barcode ?? '',
                                'product_distributor_price' => $value->distributor_price ?? '',
                                'product_price' => $value->distributor_price ?? '',
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
                            
                            $product = Product::find($value->fk_product_id);
                            $product->product_name_en = $product ? $product->product_name_en : '';
                            $product->product_name_ar = $product ? $product->product_name_ar : '';
                            $item_arr[$key] = [
                                'assigned' => $assigned,
                                'id' => $value->default_id ?? 0,
                                'product_id' => $value->fk_product_id ?? 0,
                                'product_type' => $product->product_type ?? '',
                                'product_name' => $lang == 'ar' ? $product->product_name_ar : $product->product_name_en,
                                'product_image' => !empty($product->product_image_url) ? $product->product_image_url : '',
                                'itemcode' => $product->itemcode ?? '',
                                'barcode' => $product->barcode ?? '',
                                'product_distributor_price' => $product->$store_distributor_price ?? '',
                                'product_price' => $product->$store_distributor_price,
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

    protected function update_order_item_status(Request $request)
    {
        try {
            $this->required_input($request->input(), ['id']);

            $storekeeperProduct = StorekeeperProduct::where(['id' => $request->input('id'), 'fk_storekeeper_id' => $this->storekeeper_id])->first();
            if (!$storekeeperProduct) {
                throw new Exception("This order is not assigned to you !", 105);
            }

            if ($storekeeperProduct->status == 1) {
                throw new Exception("You have already collected this item !", 105);
            }

            if ($storekeeperProduct->status == 2) {
                throw new Exception("You have already marked out of stock for this item !", 105);
            }

            $storekeeperOrder = Order::where('id',$storekeeperProduct->fk_order_id)->first();

            if($storekeeperOrder->status > 3){
                throw new Exception("This order is already packed and out for delivery, You cannot collect this item!", 105);
            }

            $update = StorekeeperProduct::find($request->input('id'))->update([
                'status' => 1 //collected
            ]);

            if ($update) {

                // Mark order as invoiced
                $this->mark_order_as_invoiced($request, $storekeeperProduct);

                // Unassign storekeepers
                $totalAssignedItems = StorekeeperProduct::where(['fk_storekeeper_id' => $this->storekeeper_id, 'status' => 0])->count();
                if ($totalAssignedItems == 0) {
                    Storekeeper::find($this->storekeeper_id)->update(['is_available' => 1]);
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = "Item marked as collected !";
            } else {
                throw new Exception('Opps! Something went wrong.Please try again.', 105);
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
    
    protected function suggest_replacement_items(Request $request)
    {
        try {
            $this->required_input($request->input(), ['id']);

            $storekeeperProduct = StorekeeperProduct::where(['id' => $request->input('id'), 'fk_storekeeper_id' => $this->storekeeper_id])->first();
            if (!$storekeeperProduct) {
                throw new Exception("This order is not assigned to you!", 105);
            }

            if ($storekeeperProduct->status == 1) {
                throw new Exception("You have already collected this item!", 105);
            }

            if ($storekeeperProduct->status == 2) {
                throw new Exception("You have already marked out of stock for this item!", 105);
            }

            // Order
            $order = Order::find($storekeeperProduct->fk_order_id);
            $order_product = OrderProduct::where([
                'fk_order_id'=>$storekeeperProduct->fk_order_id,
                'fk_product_id'=>$storekeeperProduct->fk_product_id
                ])->orderBy('id','desc')->first();

            // Check the store number
            $store = Store::find(14); // Temp fix
            $store_no = get_store_no($store->name);
            $company_id = $store->company_id;
            $store_key = 'store'.$store_no;

            // Process the suggetions
            if ($request->input('product_ids') != '') {
                $product_ids_arr = explode(',', $request->input('product_ids'));
                if (count($product_ids_arr) > 3) {
                    throw new Exception('You can only suggest maximum 3 items', 105);
                }
                
                foreach ($product_ids_arr as $key => $value) {
                    // Check the product is available
                    $product = Product::where(['id'=>$value, 'fk_company_id'=>$company_id, 'deleted'=>0])->first();
                    if (!$product) {
                        throw new Exception('Oops! product ('.$value.') is not found in the store ('.$store_no.') of company ('.$company_id.')', 105);
                    }
                    if ($product->$store_key <= 0) {
                        throw new Exception('Oops! there are no stock of the product ('.$value.') in the store ('.$store->id.')', 105);
                    }
                    // Add the products to the customer / technical support
                    TechnicalSupportProduct::create([
                        'fk_order_id' => $storekeeperProduct->fk_order_id,
                        'fk_product_id' => $value,
                        'in_stock' => 1,
                        'suggested_by' => $this->storekeeper_id
                    ]);
                }
            } 
            
            // Mark the product as out of stock in the store
            if ($order_product->fk_product_store_id!=0) {
                BaseProductStore::where('id', '=', $order_product->fk_product_store_id)
                    ->where('stock', '>=', 1)
                    ->update(['stock' => 0]);

                $this->update_base_product($order_product->fk_product_id);

            } else {
                Product::where('id', '=', $storekeeperProduct->fk_product_id)
                    ->where('store' . $store_no, '>=', 1)
                    ->update(['store' . $store_no => 0]);    
            }
            
            // Mark storekeeper assigned product as out of stock
            StorekeeperProduct::find($request->input('id'))->update([
                'status' => 2 //marked out of stock
            ]);

            // $order_product->update(['is_out_of_stock' => 1]);

            // Mark order as invoiced
            $this->mark_order_as_invoiced($request, $storekeeperProduct);

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";

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

    protected function suggest_order_replacement_items(Request $request)
    {
        try {
            $this->required_input($request->input(), ['id']);

            $storekeeperProduct = StorekeeperProduct::where(['id' => $request->input('id'), 'fk_storekeeper_id' => $this->storekeeper_id])->first();
            if (!$storekeeperProduct) {
                throw new Exception("This order is not assigned to you!", 105);
            }

            if ($storekeeperProduct->status == 1) {
                throw new Exception("You have already collected this item!", 105);
            }

            if ($storekeeperProduct->status == 2) {
                throw new Exception("You have already marked out of stock for this item!", 105);
            }

            // Order
            $order = Order::find($storekeeperProduct->fk_order_id);
            $order_product = OrderProduct::where([
                'fk_order_id'=>$storekeeperProduct->fk_order_id,
                'fk_product_id'=>$storekeeperProduct->fk_product_id
                ])->orderBy('id','desc')->first();

            // Check the store number
            $store = Store::find(14); // Temp fix
            // $store_no = get_store_no($store->name);
            // $company_id = $store->company_id;
            // $store_key = 'store'.$store_no;

            // Process the suggetions
            if ($request->input('product_ids') != '') {
                $product_ids_arr = explode(',', $request->input('product_ids'));
                if (count($product_ids_arr) > 3) {
                    throw new Exception('You can only suggest maximum 3 items', 105);
                }
                
                foreach ($product_ids_arr as $key => $value) {
                    // Check the product is available
                    $base_product = BaseProduct::where(['id'=>$value, 'fk_store_id'=>$store->id, 'deleted'=>0])->first();
                    if (!$base_product) {
                        throw new Exception('Oops! product ('.$value.') is not found in the store ('.$store->id.') of company ('.$store->company_name.')', 105);
                    }
                    if ($base_product->product_store_stock <= 0) {
                        throw new Exception('Oops! there are no stock of the product ('.$value.') in the store ('.$store->id.')', 105);
                    }
                    // Add the products to the customer / technical support
                    TechnicalSupportProduct::create([
                        'fk_order_id' => $storekeeperProduct->fk_order_id,
                        'fk_product_id' => $value,
                        'in_stock' => 1,
                        'suggested_by' => $this->storekeeper_id
                    ]);
                }
            }
            
            // Mark the product as out of stock in the store
            if ($order_product->fk_product_store_id!=0) {
                BaseProductStore::where('id', '=', $order_product->fk_product_store_id)
                    ->where('fk_store_id',$store->id)
                    ->update(['stock' => 0]);
            } else {
                BaseProduct::where('id', '=', $storekeeperProduct->fk_product_id)
                    ->where('fk_store_id',$store->id)
                    ->update(['product_store_stock' => 0]);    
            }
            
            // Mark storekeeper assigned product as out of stock
            StorekeeperProduct::find($request->input('id'))->update([
                'status' => 2 //marked out of stock
            ]);

            // Mark order as invoiced
            $this->mark_order_as_invoiced($request, $storekeeperProduct);

            $this->error = false;
            $this->status_code = 200;
            $this->message = "Success";

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
