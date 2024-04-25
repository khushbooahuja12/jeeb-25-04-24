<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Company;
use App\Model\Store;
use App\Model\StoreSchedule;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderDriver;
use App\Model\OrderStatus;
use App\Model\Driver;
use App\Model\DriverLocation;
use App\Model\StorekeeperProduct;
use App\Model\Invoice;
use App\Model\VendorOrder;
use App\Model\OrderPayment;
use App\Model\Product;
use App\Model\Storekeeper;
use App\Model\TechnicalSupport;
use App\Model\TechnicalSupportChat;
use App\Model\TechnicalSupportProduct;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\Admin;
use App\Model\StoreGroup;
use App\Model\InstantStoreGroup;
use App\Model\InstantStoreGroupStore;
use App\Model\DriverGroup;
use DB;

class FleetController extends CoreApiController
{

    use \App\Http\Traits\PushNotification;
    use \App\Http\Traits\OrderProcessing;

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
    protected $order_status_ar = [
        'تم الطلب',
        'تم تاكيد الطلب',
        'ترتيب معين',
        'طلب فوترة',
        'ألغيت',
        'الطلب قيد التقدم',
        'خارج للتوصيل',
        'تم التوصيل'
    ];
    protected $order_product_status = [
        'Not collected',
        'Collected',
        'Out of stock'
    ];
    protected $driver_status = [
        'Account not verified',
        'Active',
        'Not approved',
        'Blocked',
        'Rejected'
    ];
    protected $order_preferences = [
        '0' => "Contactless Delivery",
        '1' => "Don't Ring/Knock",
        '2' => "Beware of Pets",
        '3' => "Leave at Door",
    ];

    protected $order_product_collection_status = [
        'Not collected',
        'Collected',
        'Delivered'
    ];

    public function __construct(Request $request)
    {
        $this->loadApi = new \App\Model\LoadApi();

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $stores = Store::where(['deleted'=>0, 'status'=>1])
                ->where('company_name', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $stores = Store::where(['deleted'=>0, 'status'=>1])
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        if ($stores && !empty($stores)) {
            foreach ($stores as $store) {
                $store->no_available_products = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
                                                    ->where('base_products.fk_store_id',$store->id)
                                                    ->where('base_products.deleted',0)
                                                    ->where('base_products_store.deleted',0)
                                                    ->where('base_products.product_type','product')
                                                    ->count();
                $store->no_instock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                                                    ->where('base_products.product_store_stock','>',0)
                                                    ->where('base_products.product_type','product')
                                                    ->where('base_products_store.fk_store_id',$store->id)
                                                    ->count();
                $store->no_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                                                    ->where('base_products.product_type','product')
                                                    ->where('base_products_store.fk_store_id',$store->id)
                                                    ->count();
                $store->no_available_recipes = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
                                                    ->where('base_products.deleted',0)
                                                    ->where('base_products_store.deleted',0)
                                                    ->whereIn('base_products.product_type',['recipe','pantry_item'])
                                                    ->where('base_products.fk_store_id',$store->id)
                                                    ->count();
                $store->no_recipes = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                                                    ->whereIn('base_products.product_type',['recipe','pantry_item'])
                                                    ->where('base_products_store.fk_store_id',$store->id)
                                                    ->count();
            }
        }

        return view('admin.fleet.index', [
            'stores' => $stores,
            'filter' => $filter
        ]);
    }

    protected function store(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store) {
            die("Store is not found!");
        }
        return view('admin.fleet.store', [
            'store' => $store
        ]);
    }

    protected function storekeeper_detail(Request $request, $storekeeper_id)
    {
        $storekeeper = Storekeeper::where(['id'=>$storekeeper_id, 'deleted'=>0])->first();
        if (!$storekeeper) {
            die("Storekeeper is not found!");
        }
        $store = Store::where(['id'=>$storekeeper->fk_store_id])->first();
        if (!$store) {
            die("Store is not found!");
        }

        $orders = Order::join('storekeeper_products', 'orders.id', '=', 'storekeeper_products.fk_order_id')
            ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
            ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
            ->where('storekeeper_products.fk_storekeeper_id', '=', $storekeeper_id)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('storekeeper_products.fk_order_id')
            ->orderBy('storekeeper_products.updated_at', 'desc')
            ->paginate(50);

        return view('admin.fleet.storekeeper-detail', [
            'store' => $store,
            'orders' => $orders,
            'order_status' => $this->order_status,
            'storekeeper' => $storekeeper,
            'order_product_status' => $this->order_product_status
        ]);
    }

    protected function storekeepers(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }
        if ($id!=0) {
            $storekeepers = Storekeeper::where(['fk_store_id'=>$id, 'deleted'=>0])->get();
        } else {
            $storekeepers = Storekeeper::where(['deleted'=>0])->get();
        }
        if (!$storekeepers) {
            die("Storekeepers are not found!");
        }
        return view('admin.fleet.storekeepers', [
            'store' => $store,
            'store_id' => $id,
            'storekeepers' => $storekeepers
        ]);
    }

    protected function driver_detail(Request $request, $driver_id)
    {
        $driver = Driver::leftJoin('order_drivers', 'order_drivers.fk_order_id', '=', 'drivers.id')
        ->leftJoin('driver_locations', 'driver_locations.fk_driver_id', '=', 'drivers.id')
        ->select('drivers.*', 'driver_locations.latitude', 'driver_locations.longitude', 'driver_locations.updated_at')
        ->where(['drivers.id'=>$driver_id, 'drivers.deleted'=>0])
        ->groupBy('drivers.id')
        ->orderBy('driver_locations.id','desc')
        ->first();
        if (!$driver) {
            die("Driver is not found!");
        }
        $store = Store::where(['id'=>$driver->fk_store_id])->first();
        if (!$store) {
            die("Store is not found!");
        }

        $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->join('order_drivers', 'order_drivers.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
            ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
            ->where('order_drivers.fk_driver_id', '=', $driver_id)
            ->where('orders.parent_id', '=', 0)
            // ->where('order_delivery_slots.delivery_time', '=', 1)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->orderBy('orders.id', 'desc')
            ->paginate(50);

        return view('admin.fleet.driver-detail', [
            'store' => $store,
            'orders' => $orders,
            'order_status' => $this->order_status,
            'driver' => $driver,
            'driver_status' => $this->driver_status,
            'order_product_status' => $this->order_product_status
        ]);
    }

    protected function drivers(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }
        // $drivers = Driver::leftJoin('order_drivers', 'order_drivers.fk_order_id', '=', 'drivers.id')
        // ->leftJoin('orders', 'orders.id', '=', 'order_drivers.fk_order_id')
        // ->select('orders.status as order_status', 'drivers.*')
        // ->where(['drivers.fk_store_id'=>$id, 'drivers.deleted'=>0])
        // ->get();
        if ($id!=0) {
            $drivers = Driver::leftJoin('order_drivers', 'order_drivers.fk_order_id', '=', 'drivers.id')
            ->select('drivers.*')
            ->where(['drivers.fk_store_id'=>$id, 'drivers.deleted'=>0])
            ->groupBy('drivers.id')
            ->get();
        } else {
            $drivers = Driver::leftJoin('order_drivers', 'order_drivers.fk_order_id', '=', 'drivers.id')
            ->select('drivers.*')
            ->where(['drivers.deleted'=>0])
            ->groupBy('drivers.id')
            ->get();
        }
        if (!$drivers) {
            die("Drivers are not found!");
        }
        return view('admin.fleet.drivers', [
            'store' => $store,
            'store_id' => $id,
            'drivers' => $drivers,
            'driver_status' => $this->driver_status,
        ]);
    }

    protected function order_detail(Request $request, $order_id)
    {
        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        ->where(['orders.id'=>$order_id])
        ->first();
        if (!$order) {
            die("Order is not found!");
        }
        $delivery_preferences_selected = $order->delivery_preference ? explode(',',$order->delivery_preference) : [];
        $order_products = OrderProduct::leftJoin('storekeeper_products', function($join)
        {
            $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
            $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
            $join->on('storekeeper_products.fk_order_product_id', '=', 'order_products.id');
        })
        ->leftJoin('storekeepers', 'storekeeper_products.fk_storekeeper_id', '=', 'storekeepers.id')
        ->leftJoin('drivers', 'storekeeper_products.fk_driver_id', '=', 'drivers.id')
        ->select('order_products.*', 'storekeepers.id as storekeeper_id', 'storekeepers.name as storekeeper_name', 'storekeeper_products.status as collected_status', 'storekeeper_products.fk_storekeeper_id','storekeeper_products.collected_at as driver_collected_time','storekeeper_products.collection_status as driver_collection_status','drivers.name as driver_name','storekeeper_products.fk_driver_id')
        ->where(['order_products.fk_order_id'=>$order_id])
        // ->groupBy('storekeeper_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        // ->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        ->orderBy('order_products.id','asc')
        ->get();

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where('base_products' . '.deleted', '=', 0)
            // ->where('base_products' . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy('base_products' . '.product_name_en', 'asc')
            ->get();
        $sub_order = Order::where(['parent_id' => $order_id])->get();

        $technical_support = TechnicalSupport::where('fk_order_id','=',$order_id)->first();
        $technical_support_chat = [];
        if ($technical_support) {
            $technical_support_chat = TechnicalSupportChat::where('ticket_id','=',$technical_support->id)->get();
        }

        

        $driver = false;
        $driver_location = false;

        if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0 && $order->getOrderDriver->fk_order_id != 0) {
            $driver = $order->getOrderDriver;
            if ($driver) {
                $driver_location = DriverLocation::where(['fk_driver_id'=>$order->getOrderDriver->fk_driver_id])->orderBy('id', 'desc')->first();
            }
        }
        
        $storekeepers = Storekeeper::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_storekeepers = Storekeeper::where(['deleted'=>0])->get();
        $drivers = Driver::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.order-detail', [
            // 'store' => $store,
            'order' => $order,
            'order_products' => $order_products,
            'replaced_products' => $replaced_products,
            'order_status' => $this->order_status,
            'order_product_status' => $this->order_product_status,
            'driver'=>$driver,
            'driver_location'=>$driver_location,
            'delivery_preferences_selected'=>$delivery_preferences_selected,
            'order_preferences'=>$this->order_preferences,
            'technical_support'=>$technical_support,
            'technical_support_chat'=>$technical_support_chat,
            'storekeepers' => $storekeepers,
            'all_storekeepers' => $all_storekeepers,
            'drivers' => $drivers,
            'all_drivers' => $all_drivers
        ]);
    }

    protected function invoice_order(Request $request) 
    {
        $id = $request->input('order_id');
        $order_status = false;
        // Reduce amount from order & refund to wallet
        $order = Order::find($id);
        // Checking storekeeper collected all products in the order
        $storekeeperNotCollectedProducts = StorekeeperProduct::where(['fk_order_id' => $order->id, 'status' => 0])->count();
        if($storekeeperNotCollectedProducts == 0){
            if ($order && $order->status<3) {
                $order_invoiced = $this->order_invoiced($id);
                $order_status = $order_invoiced && isset($order_invoiced['order_status']) ? $order_invoiced['order_status'] : 0;
            }
            if ($order_status==3) {
                // Close technical support ticket
                TechnicalSupport::where(['fk_order_id' => $order->id])->update(
                    ['status' => 0]
                );
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Order invoiced successfully', 'data' => $order]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while invoicing the order']);
            }
        }else{
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please collect all the ordered products before invoicing']);
        }

    }

    protected function mark_out_of_stock(Request $request) 
    {
        $order_product_id = $request->input('order_product_id');
        $fk_storekeeper_id = $request->input('fk_storekeeper_id');
        $fk_order_id = $request->input('fk_order_id');
        $fk_product_id = $request->input('fk_product_id');
        $storekeeper_product = StorekeeperProduct::where([
            'fk_order_id' => $fk_order_id,
            'fk_product_id' => $fk_product_id
        ])->update([
            'status' => 2
        ]);
        $order_product = OrderProduct::find($order_product_id);
        $update = $order_product ? $order_product->update([
            'is_out_of_stock' => 1
        ]) : false;
        
        // Reduce amount from order & refund to wallet
        $order = Order::find($fk_order_id);
        if ($order) {
            $sub_total = $order->sub_total - $order_product->total_product_price;
            $total_amount = $sub_total+$order->delivery_charge;
            $grand_total = $total_amount;
            
            // Add to wallet
            if ($order->payment_type=='online') {
                $this->makeRefund('wallet', $order->fk_user_id, null, $order_product->total_product_price, $order->orderId);
                $amount_from_wallet = $order->amount_from_wallet - $order_product->total_product_price;
                $amount_from_card = $order->amount_from_card;
            } else {
                $amount_from_cod_suborders= $order->getSubOrder ? $order->getOrderPaidByCOD($order->id) : 0;
                $amount_from_cod = $order->amount_from_card + $amount_from_cod_suborders;

                if ($amount_from_cod>=$order_product->total_product_price) {
                    $amount_from_card = $order->amount_from_card - $order_product->total_product_price;
                    $amount_from_wallet = $order->amount_from_wallet;
                } else {
                    $amount_diff = $order_product->total_product_price - $amount_from_cod;
                    $this->makeRefund('wallet', $order->fk_user_id, null, $amount_diff, $order->orderId);
                    $amount_from_card = $order->amount_from_card - $amount_from_cod;
                    $amount_from_wallet = $order->amount_from_wallet - $amount_diff;
                }
            }
            $order->update([
                'sub_total' => $sub_total, 
                'total_amount' => $total_amount, 
                'grand_total' => $grand_total, 
                'amount_from_card' => $amount_from_card, 
                'amount_from_wallet' => $amount_from_wallet
            ]);
        }
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product marked out of stock successfully', 'data' => $order_product]);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking out of stock']);
        }
    }

    protected function revert_marked_out_of_stock(Request $request)
    {
        $order_product_id = $request->input('order_product_id');
        $fk_storekeeper_id = $request->input('fk_storekeeper_id');
        $fk_order_id = $request->input('fk_order_id');
        $fk_product_id = $request->input('fk_product_id');
        $storekeeper_product = StorekeeperProduct::where([
            'fk_order_id' => $fk_order_id,
            'fk_product_id' => $fk_product_id
        ])->update([
            'status' => 0
        ]);
        $order_product = OrderProduct::find($order_product_id);
        $update = $order_product ? $order_product->update([
            'is_out_of_stock' => 0
        ]) : false;

        $order = Order::find($fk_order_id);

        // Add amount to order & deduct from wallet
        if ($order) {
            $sub_total = $order->sub_total + $order_product->total_product_price;
            $total_amount = $sub_total+$order->delivery_charge;
            $grand_total = $total_amount;
            
            // deduct from wallet
            if ($order->payment_type=='online') {
                $this->makeFundDeduction('wallet', $order->fk_user_id, null, $order_product->total_product_price, $order->orderId);
                $amount_from_wallet = $order->amount_from_wallet + $order_product->total_product_price;
                $amount_from_card = $order->amount_from_card;
            } else {
                $amount_from_cod_suborders= $order->getSubOrder ? $order->getOrderPaidByCOD($order->id) : 0;
                $amount_from_cod = $order->amount_from_card + $amount_from_cod_suborders;
                
                if ($amount_from_cod>=$order_product->total_product_price) {
                    $amount_from_card = $order->amount_from_card+$order_product->total_product_price;
                    $amount_from_wallet = $order->amount_from_wallet;
                } else {
                    $amount_diff = $order_product->total_product_price + $amount_from_cod;
                    $this->makeRefund('wallet', $order->fk_user_id, null, $amount_diff, $order->orderId);
                    $amount_from_card = $order->amount_from_card - $amount_from_cod;
                    $amount_from_wallet = $order->amount_from_wallet - $amount_diff;
                }
            }
            $order->update([
                'sub_total' => $sub_total, 
                'total_amount' => $total_amount, 
                'grand_total' => $grand_total, 
                'amount_from_card' => $amount_from_card, 
                'amount_from_wallet' => $amount_from_wallet
            ]);
        }

        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Revert marked out of stock successfully', 'data' => $order_product]);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking out of stock']);
        }
    }

    protected function assign_storekeeper(Request $request) 
    {
        
        $fk_storekeeper_id = $request->input('fk_storekeeper_id');
        $fk_order_id = $request->input('fk_order_id');
        $fk_order_product_id = $request->input('order_product_id');
        $fk_product_id = $request->input('fk_product_id');
        $storekeeper_product = StorekeeperProduct::where([
            'fk_order_id' => $fk_order_id,
            'fk_order_product_id' => $fk_order_product_id,
            'fk_product_id' => $fk_product_id
        ])->first();
        $storekeeper = Storekeeper::find($fk_storekeeper_id);

        if ($storekeeper_product) {
            $update_arr = [
                'fk_storekeeper_id' => $fk_storekeeper_id,
                'fk_store_id' => $storekeeper->fk_store_id,
                'status' => 0
            ];
            $update = $storekeeper_product->update($update_arr);
        
            if ($update) {
                $order_product = OrderProduct::find($fk_order_product_id);
                $order_product->update(['fk_store_id' => $storekeeper->fk_store_id]);

                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Storekeeper assigned successfully', 'data' => $storekeeper]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning storekeeper']);
            }
        } else {
            $create_arr = [
                'fk_order_id' => $fk_order_id,
                'fk_order_product_id' => $fk_order_product_id,
                'fk_product_id' => $fk_product_id,
                'fk_store_id' => $storekeeper->fk_store_id,
                'fk_storekeeper_id' => $fk_storekeeper_id
            ];
            $create = StorekeeperProduct::create($create_arr);

            if ($create) {
                $order_product = OrderProduct::find($fk_order_product_id);
                $order_product->update(['fk_store_id' => $storekeeper->fk_store_id]);

                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Storekeeper assigned successfully', 'data' => $storekeeper]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning storekeeper']);
            }
        }
    }

    protected function assign_driver(Request $request)
    {
        $fk_order_id = $request->input('fk_order_id');
        $fk_driver_id = $request->input('fk_driver_id');
        
        $order = Order::find($fk_order_id);
        if (!$order || $order->status >= 4) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Order is not found or already canceled or being delivered']);
        }
        $driver = Driver::find($fk_driver_id);
        if (!$driver) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Driver is not found']);
        }

        $already_assigned = OrderDriver::where(['fk_order_id' => $fk_order_id])->first();
        if ($already_assigned) {
            $update_arr = [
                'fk_driver_id' => $fk_driver_id
            ];
            $update = $already_assigned->update($update_arr);
        
            if ($update) {
                OrderStatus::create([
                    'fk_order_id' => $fk_order_id,
                    'status' => 2
                ]);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        } else {
            $insert_arr = [
                'fk_order_id' => $fk_order_id,
                'fk_driver_id' => $fk_driver_id,
                'status' => 1
            ];
            $create = OrderDriver::create($insert_arr);
            if ($create) {
                OrderStatus::create([
                    'fk_order_id' => $fk_order_id,
                    'status' => 2
                ]);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        }

    }

    protected function orders(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }

        $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
        // ->where('order_payments.status', '!=', 'rejected')
        ->where('orders.parent_id', '=', 0)
        ->orderBy('orders.id', 'desc');

        if ($id!=0) {
            $orders = $orders->where('orders.fk_store_id', '=', $id);
        }
        if ($_GET) {
            if (isset($order_type) && $order_type != '' && $order_type != 0) {
                $orders = $orders->where('order_delivery_slots.delivery_time', '=', $order_type);
            }
            if (isset($_GET['delivery_from']) && $request->query('delivery_from') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '>=', date('Y-m-d', strtotime($request->input('delivery_from'))));
            }
            if (isset($_GET['delivery_to']) && $request->query('delivery_to') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '<=', date('Y-m-d', strtotime($request->input('delivery_to'))));
            }
            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && ( $request->query('status') == 0 || $request->query('status')==4 || $request->query('status')==7 ) ) {
                $orders = $orders->where('orders.status', '=', 100000);
            } elseif (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } else {
                // $orders = $orders->whereNotIn('orders.status',[0,4,7]);
            }
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
        } 
        if (!$_GET || !isset($_GET['test_order'])) {
            $orders = $orders->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            });
        }

        $orders = $orders->paginate(50);

        $orders->appends(['delivery_date' => $request->query('delivery_date')]);
        $orders->appends(['orderId' => $request->query('orderId')]);
        $orders->appends(['status' => $request->query('status')]);
        
        $drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.orders', [
            'store' => $store,
            'store_id' => $id,
            'orders' => $orders,
            'drivers' => $drivers,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function active_orders(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }
        
        $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
        // ->where('order_payments.status', '!=', 'rejected')
        ->where('orders.parent_id', '=', 0)
        ->orderBy('orders.id', 'desc');
        $orders = $orders->whereNotIn('orders.status',[0,4,7]);

        if ($id!=0) {
            $orders = $orders->where('orders.fk_store_id', '=', $id);
        }
        if ($_GET) {
            if (isset($order_type) && $order_type != '' && $order_type != 0) {
                $orders = $orders->where('order_delivery_slots.delivery_time', '=', $order_type);
            }
            if (isset($_GET['delivery_from']) && $request->query('delivery_from') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '>=', date('Y-m-d', strtotime($request->input('delivery_from'))));
            }
            if (isset($_GET['delivery_to']) && $request->query('delivery_to') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '<=', date('Y-m-d', strtotime($request->input('delivery_to'))));
            }
            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } 
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
        } 
        if (!$_GET || !isset($_GET['test_order'])) {
            $orders = $orders->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            });
        }

        $orders = $orders->paginate(50);

        $orders->appends(['delivery_date' => $request->query('delivery_date')]);
        $orders->appends(['orderId' => $request->query('orderId')]);
        $orders->appends(['status' => $request->query('status')]);
        
        $drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.orders', [
            'store' => $store,
            'store_id' => $id,
            'orders' => $orders,
            'drivers' => $drivers,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function detail(Request $request, $id)
    {
        $id = base64url_decode($id);
        $order = Order::find($id);

        $sub_order = Order::where(['parent_id' => $id])->first();

        $drivers = Driver::orderBy('id', 'desc')->get();

        $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($order->id);

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $id])
            ->where('base_products' . '.deleted', '=', 0)
            // ->where('base_products' . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy('base_products' . '.product_name_en', 'asc')
            ->get();
        // pp($replaced_products);

        return view('admin.orders.detail', [
            'order' => $order,
            'order_status' => $this->order_status,
            'drivers' => $drivers,
            'totalOutOfStockProduct' => $totalOutOfStockProduct,
            'sub_order' => $sub_order,
            'replaced_products' => $replaced_products
        ]);
    }

    protected function ordered_product_detail($id = null)
    {
        $id = base64url_decode($id);
        $op = OrderProduct::find($id);

        return view('admin.orders.ordered_product_detail', [
            'op' => $op,
            'order_status' => $this->order_status
        ]);
    }

    protected function update_status(Request $request, $id)
    {
        $id = base64url_decode($id);
        $order = Order::find($id);

        if ($request->input('status') == 4) {
            return back()->withInput()->with('error', 'Not allowed');
        }
        
        // If the order is confirmed
        elseif ($request->input('status') == 1) {
            $order_confirmed = $this->order_confirmed($id);
            $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
        }

        // If the order is invoiced
        elseif ($request->input('status') == 3) {
            $order_invoiced = $this->order_invoiced($id);
            $order_status = $order_invoiced && isset($order_invoiced['order_status']) ? $order_invoiced['order_status'] : 0;
        }

        // If the order is out_for_delivery
        elseif ($request->input('status') == 6) {
            $order_out_for_delivery = $this->order_out_for_delivery($id);
            $order_status = $order_out_for_delivery && isset($order_out_for_delivery['order_status']) ? $order_out_for_delivery['order_status'] : 0;
        }

        // If the order is delivered
        elseif ($request->input('status') == 7) {
            $order_delivered = $this->order_delivered($id);
            $order_status = $order_delivered && isset($order_delivered['order_status']) ? $order_delivered['order_status'] : 0;
        }

        // Else
        else {
            $order_status = $request->input('status');
            $update = Order::find($id)->update(['status' => $request->input('status')]);
            if ($update) {
                $exist = OrderStatus::where(['fk_order_id' => $id, 'status' => $request->input('status')])->first();
                if ($exist) {
                    $update_arr = [
                        'status' => $request->input('status')
                    ];
                    OrderStatus::find($exist->id)->update($update_arr);
                } else {
                    $insert_arr = [
                        'fk_order_id' => $id,
                        'status' => $request->input('status')
                    ];
                    OrderStatus::create($insert_arr);
                }
            }
        }

        /* --------------------
        // If the order is invoiced or marked as delievered, close the customer support ticket
        -------------------- */
        if ($request->input('status') >= 2) {
            TechnicalSupport::where(['fk_order_id' => $order->id])->update(
                ['status' => 0]
            );
        }

        $order = $order->refresh();

        if ($order_status==0 || $order->status!=$order_status) {
            return back()->withInput()->with('error', 'Error while updating status');
        } else {
            return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Status updated successfully');
        }
    }

    protected function allocate_order(Request $request)
    {
        $order_products = OrderProduct::select('*')->selectRaw("SUM(product_price) as sub_total")
            ->where(['fk_order_id' => $request->input('order_id'), 'deleted' => 0])
            ->groupBy('fk_vendor_id')
            ->get();

        if ($order_products->count()) {
            foreach ($order_products as $key => $value) {
                $insert_arr = [
                    'fk_order_id' => $request->input('order_id'),
                    'fk_vendor_id' => 1,
                    'sub_total' => $value->sub_total
                ];
                $create = VendorOrder::create($insert_arr);

                VendorOrder::find($create->id)->update(['sub_orderId' => 'SJEEB' . $request->input('order_id') . $create->id]);
            }

            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => 'Order allocated successfully'
            ]);
        } else {
            return response()->json([
                'error' => true,
                'status_code' => 404,
                'message' => 'Some error found'
            ]);
        }
    }

    protected function payments(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
        } else {
            $payments = OrderPayment::where('status', '!=', 'rejected')
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }

        return view('admin.orders.payments', [
            'payments' => $payments
        ]);
    }

    protected function payment_detail(Request $request, $id)
    {
        $id = base64url_decode($id);
        $payment = OrderPayment::find($id);

        return view('admin.orders.payment_detail', [
            'payment' => $payment,
            'order_status' => $this->order_status
        ]);
    }

    protected function update_driver(Request $request, $id)
    {
        $id = base64url_decode($id);

        $driver = Driver::find($request->input('fk_driver_id'));

        // $distance = get_distance($driver->getLocation->latitude, $driver->getLocation->longitude, $request->input('longitude'), $driver->getStore->latitude, $driver->getStore->longitude, 'K');

        if (!$driver) {
            return back()->withInput()->with('error', "This driver is not found");
        } elseif ($driver->is_available == 0) {
            return back()->withInput()->with('error', "This driver is already occupied");
        }

        $update = OrderDriver::where(['fk_order_id' => $id])
            ->update(['fk_driver_id' => $request->input('fk_driver_id'), 'status' => 1]);

        if ($update) {
            Order::find($id)->update(['status' => 2]);
            $insert_arr = [
                'fk_order_id' => $id,
                'status' => 2
            ];
            OrderStatus::create($insert_arr);

            Driver::find($request->input('fk_driver_id'))->update(['is_available' => 0]);

            return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
        }
        return back()->withInput()->with('error', 'Error while assigning status');
    }

    protected function refund_order_amount(Request $request)
    {
        $id = $request->input('order_id');
        $order = Order::find($id);

        if ($order->getOrderPayment) {
            $paymentResponse = json_decode($order->getOrderPayment->paymentResponse);
            $invoice_id = $paymentResponse->InvoiceId;
        } else {
            $invoice_id = '';
        }

        $res = $this->makeRefund($request->input('refund_source'), $order->fk_user_id, $invoice_id, $request->input('refund_amount'), $order->orderId);
        if ($res) {
            if ($request->input('refund_source') == 'wallet') {
                $error_message = 'Refund processed';
            } elseif ($request->input('refund_source') == 'card') {
                $error_message = 'Refund processed';
            }
        } else {
            return response()->json([
                'error' => true,
                'status_code' => 105,
                'message' => 'Something went wrong'
            ]);
        }

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => $error_message
        ]);
    }

    protected function replacement_options(Request $request, $id = null)
    {
        $op = OrderProduct::join('base_products', 'order_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.id', 'base_products' . '.fk_sub_category_id', 'order_products.is_out_of_stock', 'order_products.deleted')
            ->where('order_products.fk_order_id', '=', $id)
            ->get();

        $sub_category_ids = [];
        foreach ($op as $key => $value) {
            if ($value->is_out_of_stock == 1 and $value->deleted == 0) {
                array_push($sub_category_ids, $value->fk_sub_category_id);
            }
        }

        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->whereIn('fk_sub_category_id', $sub_category_ids)
                ->sortable(['id' => 'desc'])
                ->simplePaginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->whereIn('fk_sub_category_id', $sub_category_ids)
                ->orderBy('product_name_en', 'asc')
                ->sortable(['id' => 'desc'])
                ->simplePaginate(50);
        }
        $products->appends(['filter' => $filter]);

        return view('admin.orders.replacement_options', ['products' => $products, 'filter' => $filter, 'order_id' => $id]);
    }

    protected function add_remove_replaced_products(Request $request)
    {
        $ts = TechnicalSupport::where(['fk_order_id' => $request->input('order_id')])->first();

        $exist = TechnicalSupportProduct::where(['fk_order_id' => $request->input('order_id'), 'fk_product_id' => $request->input('product_id')])
            ->first();
        if ($exist) {
            TechnicalSupportProduct::find($exist->id)->delete();
            $message = "Product removed from this section";
        } else {
            TechnicalSupportProduct::create([
                'ticket_id' => $ts ? $ts->id : NULL,
                'fk_order_id' => $request->input('order_id'),
                'fk_product_id' => $request->input('product_id'),
                'in_stock' => 1
            ]);
            $message = "Product added to this section";
        }
        return response()->json([
            'status_code' => 200,
            'message' => $message
        ]);
    }

    protected function customer_invoice(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $order = \App\Model\Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select('orders.*')
            ->selectRaw("SUM(order_products.total_product_price) as total_product_price")
            ->where('orders.id', '=', $id)
            ->first();

        // if ($order->amount_from_card!=0.00 &&!empty($order->getOrderPayment->paymentResponse)) {
        //     $paymentResArr = json_decode($order->getOrderPayment->paymentResponse);

        //     if ($paymentResArr->InvoiceId != '') {
        //         $invoiceId = $paymentResArr->InvoiceId;

        //         $apiURL = 'https://api.myfatoorah.com/v2/';
        //         $apiKey = 'XYgwpDLlIL3BVm_dvAFepg55uKVdKlz--1bMhtO4qhyAZYFVeqO51O4IPHTwahBXPI-FfA4W0Q3m5QCm9JZ4Ql-Q3LuIPpK88TomNQL7L4X2jCOHRJVMsETrjUsdlYU65p2ET8SaoBbTQbGupAn611tAGlLFNnHaNVo4wbUZwGwJsjGnNBfpEnNZwIRN-6UTzRbvc3lLjQzC5lhIyA-Ai6NymOuWHFRFRwb9LNd96yHp_Z6QhWhPDVJYzSys4ICfCQuKRZ71o1xzlMtpIHaLMkugiiH9Nuu3H_ODjntxtSroC61kpB93-Mj6uJ_L-eOKDSOF4hu88NpO1MgtTQvcENm1FWGVUynKnIbCLfS26c8ScObJcZseiCEtmI_O514GP6qj0a7JscE5l5aSpGE1HgbFtJpkvUmW31OzpJ7AJuyQTNj-nOnfOoR8hcM1FhTx2IU5i9e6Q4Q7IPbYagPWYgQjyMVvsBD-XdtgaILwxIkGz2uMi3dRnCUSwqMAvw9OWD56Pahi_ezdwIoyyujestMBcA615qVJ7SsQfCac6o3B3QYYlzFY0l2UbDfxggG5CN0gwWqulPDkfImEP2mHuZImfVtvNZaC48kjQ32X4IyOZePnjxCI2TmEjCZgwptstFALFdrqBOtUtEHVtHQUOZa6ZdEI2dG3NpQGIbqNh8gGEt6i';

        //         $endpointURL = $apiURL . 'getPaymentStatus';

        //         $postFields = [
        //             'Key' => $invoiceId,
        //             'KeyType' => "invoiceId"
        //         ];
        //         $res = callAPI($endpointURL, $apiKey, $postFields);
        //         if ($res) {
        //             $cardNumber = $res->Data->InvoiceTransactions[0]->CardNumber ?? 'N/A';
        //         } else {
        //             $cardNumber = 'N/A';
        //         }
        //     } else {
        //         $invoiceId = 'N/A';
        //         $cardNumber = 'N/A';
        //     }
        // } else {
        //     $invoiceId = 'N/A';
        //     $cardNumber = 'N/A';
        // }
        
        $invoiceId = 'N/A';
        $cardNumber = 'N/A';

        return view('admin.orders.customer_invoice', [
            'order' => $order,
            'invoiceId' => $invoiceId,
            'cardNumber' => $cardNumber
        ]);
    }

    // Products stock update from store (For veg and fruits)
    protected function update_stock_multiple(Request $request, $id = 0)
    {
        $filter = $request->query('filter');

        $store = Store::find($id);

        $company_id = $store ? $store->company_id : 0;

        $no_of_items = 50;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        if (!empty($company_id) && $company_id!=0) {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->whereIn('fk_sub_category_id', [74,75])
                ->where('deleted', '=', 0)
                ->where('fk_company_id', '=', $company_id);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->whereIn('fk_sub_category_id', [74,75])
                ->where('deleted', '=', 0);
        }
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('id', 'desc')
        ->sortable(['id' => 'desc'])
        ->paginate($no_of_items);

        return view('admin.fleet.update_stock_multiple', [
            'filter' => $filter,
            'products' => $products,
            'company_id' => $company_id,
            'store' => $store,
            'starting_no' => $starting_no
        ]);
    }

    protected function update_stock_multiple_save(Request $request)
    {

        $id = $request->input('id');
        $product = Product::find($id);
        if ($product) {

            $update_arr = [
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                'store1_distributor_price' => $request->store1_distributor_price,
                'store1' => $request->store1,
            ];

            if ($request->store1_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($request->store1_distributor_price);
                $update_arr['store1_price'] = $priceArr[0];
            }

            if ($request->hasFile('image')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'image', $path);
                
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $duplicate = Product::selectRaw("COUNT(*) > 1")
                        ->where('product_image', '=', $product->product_image)
                        ->first();

                    if ($product->product_image != '' && !$duplicate) {
                        $destinationPath = public_path("images/product_images/");
                        if (!empty($product->getProductImage) && file_exists($destinationPath . $product->getProductImage->file_name)) {
                            unlink($destinationPath . $product->getProductImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $product->product_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }

                    $update_arr['product_image'] = $returnArr->id;
                    $update_arr['product_image_url'] = asset('images/product_images') . '/' . $check;
                endif;
            }

            $update = $product->update($update_arr);
        
            if ($update) {
                $product = Product::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    protected function store_orders_map($id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }
        if ($_GET) {
            if ($id!=0) {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('orders.fk_store_id', '=', $id)
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '=', 7)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc')
                    ->get();
            } else {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '=', 7)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc')
                    ->get();
            }

            $orders->get();

        } else {
            
            if ($id!=0) {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('orders.fk_store_id', '=', $id)
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '=', 7)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->where(function($query) {
                        $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                    })
                    ->orderBy('orders.id', 'desc')
                    ->get();
            } else {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '=', 7)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->where(function($query) {
                        $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                    })
                    ->orderBy('orders.id', 'desc')
                    ->get();
            }
        }

        return view('admin.fleet.orders-map', [
            'store' => $store,
            'store_id' => $id,
            'orders' => $orders,
            'order_status' => $this->order_status,
        ]);
    }

    protected function store_orders(Request $request)
    {
        $stores = Store::where('deleted', 0)->get();
        if ($_GET) {
        
            if(isset($_GET['status']) && $_GET['status'] == 'placed'){
                $_GET['status'] = 0;
            }

            if(isset($_GET['store']) && $request->query('store') != 0){

                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                    ->leftJoin('stores', 'order_products.fk_store_id', '=', 'stores.id')
                    ->select("orders.*", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time','stores.name as store_name','stores.company_name as store_company_name')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '>=', 1)
                    ->groupBy('orders.id')
                    ->orderBy('orders.id', 'desc');
            }else{
                
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '>=', 1)
                    ->orderBy('orders.id', 'desc');
            }
            
            if (isset($_GET['store']) && $request->query('store') != 0) {
                $orders = $orders->where('order_products.fk_store_id', '=', $_GET['store']);
            }

            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            }
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
            if (!isset($_GET['test_order'])) {
                $orders = $orders->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                });
            }

            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
            $orders->appends(['store' => $request->query('store')]);
        } else {
            
            $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_payments.status', '!=', 'rejected')
                ->where('order_payments.status', '!=', 'blocked')
                ->where('orders.parent_id', '=', 0)
                ->where('orders.status', '>=', 1)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
            
        }
        $drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.all-store-orders', [
            'stores' => $stores,
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'store' => $request->query('store') ? $request->query('store') : ''
        ]);
    }

    protected function store_order_detail(Request $request, $order_id, $store_id)
    {
        $order_id = base64url_decode($order_id);
        $store_id = base64url_decode($store_id);

        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
            ->where('order_products.fk_order_id',$order_id)
            ->first();
        if (!$order) {
            die("Order is not found!");
        }
        $delivery_preferences_selected = $order->delivery_preference ? explode(',',$order->delivery_preference) : [];
        $order_products = OrderProduct::leftJoin('storekeeper_products', function($join)
        {
            $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
            $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
            $join->on('storekeeper_products.fk_order_product_id', '=', 'order_products.id');
        })
        ->leftJoin('storekeepers', 'storekeeper_products.fk_storekeeper_id', '=', 'storekeepers.id')
        ->select('order_products.*', 'storekeepers.id as storekeeper_id', 'storekeepers.name as storekeeper_name', 'storekeeper_products.status as collected_status', 'storekeeper_products.fk_storekeeper_id')
        ->where(['order_products.fk_order_id'=>$order_id]);
        
        if($store_id != 0){
            $order_products = $order_products->where(['order_products.fk_store_id'=>$store_id])
                                ->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
                                ->orderBy('order_products.id','asc')
                                ->get();
        }else{

            $order_products = $order_products->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
                                ->orderBy('order_products.id','asc')
                                ->get();
        }

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where('base_products' . '.deleted', '=', 0)
            ->orderBy('base_products' . '.product_name_en', 'asc')
            ->get();
        $sub_order = Order::where(['parent_id' => $order_id])->get();

        $technical_support = TechnicalSupport::where('fk_order_id','=',$order_id)->first();
        $technical_support_chat = [];
        if ($technical_support) {
            $technical_support_chat = TechnicalSupportChat::where('ticket_id','=',$technical_support->id)->get();
        }
        
        if($store_id !=0){
            $store = Store::where(['id'=>$store_id, 'deleted'=>0])->first();
            if (!$store) {
                die("Store is not found!");
            }
        }else{
            $store = [];
        }
        

        $driver = false;
        $driver_location = false;

        if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0 && $order->getOrderDriver->fk_order_id != 0) {
            $driver = $order->getOrderDriver;
            if ($driver) {
                $driver_location = DriverLocation::where(['fk_driver_id'=>$order->getOrderDriver->fk_driver_id])->orderBy('id', 'desc')->first();
            }
        }

        return view('admin.fleet.store-order-detail', [
            'store' => $store,
            'order' => $order,
            'order_products' => $order_products,
            'replaced_products' => $replaced_products,
            'order_status' => $this->order_status,
            'order_product_status' => $this->order_product_status,
            'driver'=>$driver,
            'driver_location'=>$driver_location,
            'delivery_preferences_selected'=>$delivery_preferences_selected,
            'order_preferences'=>$this->order_preferences,
            'technical_support'=>$technical_support,
            'technical_support_chat'=>$technical_support_chat,
        ]);
    }

    protected function mark_collected(Request $request)
    {
        $this->required_input($request->input(), ['id','fk_storekeeper_id']);

        $storekeeperProduct = StorekeeperProduct::where(['fk_order_product_id' => $request->input('id'), 'fk_storekeeper_id' => $request->input('fk_storekeeper_id')])->first();

        $storekeeperOrder = Order::where('id',$storekeeperProduct->fk_order_id)->first();

        $update = $storekeeperProduct->update([
            'status' => 1 //collected
        ]);
        if ($update) {

            // Unassign storekeepers
            $totalAssignedItems = StorekeeperProduct::where(['fk_storekeeper_id' => $request->input('fk_storekeeper_id'), 'status' => 0])->count();
            if ($totalAssignedItems == 0) {
                Storekeeper::find($request->input('fk_storekeeper_id'))->update(['is_available' => 1]);
            }

            if ($update) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product marked collected successfully']);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking collected']);
            }
        }

    }

    protected function revert_mark_collected(Request $request)
    {
        $this->required_input($request->input(), ['id','fk_storekeeper_id']);

        $storekeeperProduct = StorekeeperProduct::where(['fk_order_product_id' => $request->input('id'), 'fk_storekeeper_id' => $request->input('fk_storekeeper_id')])->first();

        $storekeeperOrder = Order::where('id',$storekeeperProduct->fk_order_id)->first();

        $update = $storekeeperProduct->update([
            'status' => 0 // not collected
        ]);

        if ($update) {

            if ($update) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Marked item not collected successfully']);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking collected']);
            }
        }

    }

    protected function search_new_product(Request $request){

        $base_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->select('base_products.*')
            ->where('base_products.product_store_stock','>',0)
            ->where('base_products.product_type','product')
            ->where('base_products_store.deleted',0)
            ->where('base_products.product_name_en', 'like', '%%' . $request->query('search') . '%%')
            ->groupBy('base_products.id')
            ->limit(6)
            ->get();
        
        return $base_products;
    }

    protected function add_order_product(Request $request)
    {
            // Check and confirm order is not invoiced yet
            $parent_order = Order::find($request->input('fk_order_id'));
            if (!$parent_order) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Your previous order is not active']);
            }

            $base_product = BaseProduct::find($request->input('fk_product_id'));
            
            $insert_arr = [
                'fk_user_id' => $parent_order->fk_user_id,
                'sub_total' => round($base_product->product_store_price * $request->input('product_qauntity'), 2),
                'total_amount' => round($base_product->product_store_price * $request->input('product_qauntity'), 2),
                'grand_total' => round($base_product->product_store_price * $request->input('product_qauntity'), 2),
                'amount_from_card' => $base_product->product_store_price,
                'delivery_charge' => 0,
                'payment_type' => 'cod',
                'status' => 1,
                'parent_id' => $request->input('fk_order_id')
            ];

            $user = \App\Model\User::find($parent_order->fk_user_id);

            $insert_arr['fk_store_id'] = $user->nearest_store;

            $create = Order::create($insert_arr);
            if ($create) {
                
                $orderId = (1000 + $create->id);
                Order::find($create->id)->update(['orderId' => $orderId]);

                $base_product = BaseProduct::join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')
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
                    ->where('base_products.id', '=', $request->input('fk_product_id'))
                    ->first();

                $selling_price = number_format($base_product->product_store_price, 2);
                $orderProduct = OrderProduct::create([
                    'fk_order_id' => $request->input('fk_order_id'),
                    'fk_product_id' => $request->input('fk_product_id'),
                    'fk_product_store_id' => $base_product->fk_product_store_id,
                    'fk_store_id' => $base_product->fk_store_id,
                    'single_product_price' => $selling_price,
                    'total_product_price' => number_format($base_product->product_store_price * $request->input('product_qauntity'), 2),
                    'itemcode' => $base_product->itemcode ?? '',
                    'barcode' => $base_product->barcode ?? '',
                    'fk_category_id' => $base_product->fk_category_id ?? '',
                    'category_name' => $base_product->category_name_en ?? '',
                    'category_name_ar' => $base_product->category_name_ar ?? '',
                    'fk_sub_category_id' => $base_product->fk_sub_category_id ?? '',
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
                    'unit' => $base_product->unit ?? '',
                    '_tags' => $base_product->_tags ?? '',
                    'tags_ar' => $base_product->tags_ar ?? '',
                    'discount' => '',
                    'product_quantity' => $request->input('product_qauntity'),
                    'product_weight' => $base_product->unit ?? '',
                    'product_unit' => $base_product->unit ?? '',
                    'is_added_product' => 1
                ]);
                
                //assign storekeeper
                $this->assignStoreKeeper($create,$orderProduct);

            } else {

                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'An error has been discovered']);
            }
            
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Replacement product successfully added']);
    }

    protected function storekeeper_orders(Request $request)
    {
        if ($_GET) {
            
            $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('orders.status', '!=', '0')
                ->where('orders.status', '!=', '4')
                ->where('orders.status', '!=', '7')
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc');
            

            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            }
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }

            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            
            $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('orders.status', '!=', '0')
                ->where('orders.status', '!=', '4')
                ->where('orders.status', '!=', '7')
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                // ->where('order_delivery_slots.delivery_time', '=', 1)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
            
        }
        $drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.storekeeper-orders', [
            
            'orders' => $orders,
            'drivers' => $drivers,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function storekeeper_order_detail(Request $request, $order_id)
    {
        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        ->where(['orders.id'=>$order_id])
        ->first();
        if (!$order) {
            die("Order is not found!");
        }
        $delivery_preferences_selected = $order->delivery_preference ? explode(',',$order->delivery_preference) : [];
        $order_products = OrderProduct::leftJoin('storekeeper_products', function($join)
        {
            $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
            $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
            $join->on('storekeeper_products.fk_order_product_id', '=', 'order_products.id');
        })
        ->leftJoin('storekeepers', 'storekeeper_products.fk_storekeeper_id', '=', 'storekeepers.id')
        ->select('order_products.*', 'storekeepers.id as storekeeper_id', 'storekeepers.name as storekeeper_name', 'storekeeper_products.status as collected_status', 'storekeeper_products.fk_storekeeper_id')
        ->where(['order_products.fk_order_id'=>$order_id])
        // ->groupBy('storekeeper_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        // ->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        ->orderBy('order_products.id','asc')
        ->get();

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where('base_products' . '.deleted', '=', 0)
            // ->where('base_products' . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy('base_products' . '.product_name_en', 'asc')
            ->get();
        $sub_order = Order::where(['parent_id' => $order_id])->get();

        $technical_support = TechnicalSupport::where('fk_order_id','=',$order_id)->first();
        $technical_support_chat = [];
        if ($technical_support) {
            $technical_support_chat = TechnicalSupportChat::where('ticket_id','=',$technical_support->id)->get();
        }

        // $store = Store::where(['id'=>$order->fk_store_id, 'deleted'=>0])->first();
        // if (!$store) {
        //     die("Store is not found!");
        // }

        $driver = false;
        $driver_location = false;

        if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0 && $order->getOrderDriver->fk_order_id != 0) {
            $driver = $order->getOrderDriver;
            if ($driver) {
                $driver_location = DriverLocation::where(['fk_driver_id'=>$order->getOrderDriver->fk_driver_id])->orderBy('id', 'desc')->first();
            }
        }
        
        $storekeepers = Storekeeper::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_storekeepers = Storekeeper::where(['deleted'=>0])->get();
        $drivers = Driver::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.storekeeper-order-detail', [
            // 'store' => $store,
            'order' => $order,
            'order_products' => $order_products,
            'replaced_products' => $replaced_products,
            'order_status' => $this->order_status,
            'order_product_status' => $this->order_product_status,
            'driver'=>$driver,
            'driver_location'=>$driver_location,
            'delivery_preferences_selected'=>$delivery_preferences_selected,
            'order_preferences'=>$this->order_preferences,
            'technical_support'=>$technical_support,
            'technical_support_chat'=>$technical_support_chat,
            'storekeepers' => $storekeepers,
            'all_storekeepers' => $all_storekeepers,
            'drivers' => $drivers,
            'all_drivers' => $all_drivers
        ]);
    }

    public function stores(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->orWhere('company_name', 'like', '%' . $filter . '%')
                ->orWhere('address', 'like', '%' . $filter . '%')   
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $stores = Store::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        return view('admin.fleet.all-stores', ['stores' => $stores, 'filter' => $filter]);
    }

    public function store_location($id)
    {
        $id = base64_decode($id);
        $store = Store::find($id);
        return view('admin.fleet.store-location', ['store' => $store]);
    }

    public function store_schedules($id)
    {
        $store = Store::find($id);

        if ($store) {
            $monday_slots = $store->getSlotsByDay('monday');
            $monday_slots_24hours_open = $store->getSlotsByDay_24hours_open('monday');
            $monday_slots_24hours_close = $store->getSlotsByDay_24hours_close('monday');
            $tuesday_slots = $store->getSlotsByDay('tuesday');
            $tuesday_slots_24hours_open = $store->getSlotsByDay_24hours_open('tuesday');
            $tuesday_slots_24hours_close = $store->getSlotsByDay_24hours_close('tuesday');
            $wednesday_slots = $store->getSlotsByDay('wednesday');
            $wednesday_slots_24hours_open = $store->getSlotsByDay_24hours_open('wednesday');
            $wednesday_slots_24hours_close = $store->getSlotsByDay_24hours_close('wednesday');
            $thursday_slots = $store->getSlotsByDay('thursday');
            $thursday_slots_24hours_open = $store->getSlotsByDay_24hours_open('thursday');
            $thursday_slots_24hours_close = $store->getSlotsByDay_24hours_close('thursday');
            $friday_slots = $store->getSlotsByDay('friday');
            $friday_slots_24hours_open = $store->getSlotsByDay_24hours_open('friday');
            $friday_slots_24hours_close = $store->getSlotsByDay_24hours_close('friday');
            $saturday_slots = $store->getSlotsByDay('saturday');
            $saturday_slots_24hours_open = $store->getSlotsByDay_24hours_open('saturday');
            $saturday_slots_24hours_close = $store->getSlotsByDay_24hours_close('saturday');
            $sunday_slots = $store->getSlotsByDay('sunday');
            $sunday_slots_24hours_open = $store->getSlotsByDay_24hours_open('sunday');
            $sunday_slots_24hours_close = $store->getSlotsByDay_24hours_close('sunday');
        } else {
            die('Store not found!');
        }

        return view('admin.fleet.store-schedules', [
            'store' => $store,
            'monday_slots' => $monday_slots,
            'monday_slots_24hours_open' => $monday_slots_24hours_open,
            'monday_slots_24hours_close' => $monday_slots_24hours_close,
            'tuesday_slots' => $tuesday_slots,
            'tuesday_slots_24hours_open' => $tuesday_slots_24hours_open,
            'tuesday_slots_24hours_close' => $tuesday_slots_24hours_close,
            'wednesday_slots' => $wednesday_slots,
            'wednesday_slots_24hours_open' => $wednesday_slots_24hours_open,
            'wednesday_slots_24hours_close' => $wednesday_slots_24hours_close,
            'thursday_slots' => $thursday_slots,
            'thursday_slots_24hours_open' => $thursday_slots_24hours_open,
            'thursday_slots_24hours_close' => $thursday_slots_24hours_close,
            'friday_slots' => $friday_slots,
            'friday_slots_24hours_open' => $friday_slots_24hours_open,
            'friday_slots_24hours_close' => $friday_slots_24hours_close,
            'saturday_slots' => $saturday_slots,
            'saturday_slots_24hours_open' => $saturday_slots_24hours_open,
            'saturday_slots_24hours_close' => $saturday_slots_24hours_close,
            'sunday_slots' => $sunday_slots,
            'sunday_slots_24hours_open' => $sunday_slots_24hours_open,
            'sunday_slots_24hours_close' => $sunday_slots_24hours_close
        ]);
    }

    public function store_schedules_update(Request $request, $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return back()->withInput()->with('error', 'Store does not exist');
        }
        elseif(!$request->input('schedule_day')) {
            return back()->withInput()->with('error', 'Day is not valid');
        }
        else{
            $slot_updated = false;
            $day = $request->input('schedule_day');
            $date = NULL;
            $schedule_24hours = $request->input('schedule_24hours') ? 1 : 0;
            $schedule_24hours_close = $request->input('schedule_24hours_close') ? 1 : 0;
            $slot_open = $request->input('slot_open') ?? NULL;
            $slot_close = $request->input('slot_close') ?? NULL;
            // Shedules
            if ($schedule_24hours) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'day' => $day,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                $create_arr = [
                    'fk_store_id' => $id,
                    'date' => $date,
                    'day' => $day,
                    '24hours_open' => 1,
                    'from' => NULL,
                    'to' => NULL
                ];
                $slot_updated = StoreSchedule::create($create_arr);
            }
            elseif ($schedule_24hours_close) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'day' => $day,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                $create_arr = [
                    'fk_store_id' => $id,
                    'date' => $date,
                    'day' => $day,
                    '24hours_open' => 0,
                    'from' => NULL,
                    'to' => NULL
                ];
                $slot_updated = StoreSchedule::create($create_arr);
            }
            elseif ($slot_open != null && $slot_close != null && count($slot_open)>0 && count($slot_close)>0) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'day' => $day,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                for ($i = 0; $i < count($slot_open); $i++) {
                    if ($slot_open[$i] && $slot_close[$i]) {
                        $create_arr = [
                            'fk_store_id' => $id,
                            'date' => $date,
                            'day' => $day,
                            '24hours_open' => 0,
                            'from' => $slot_open[$i],
                            'to' => $slot_close[$i]
                        ];
                        $slot_updated = StoreSchedule::create($create_arr);
                    }
                }
            } else {
                return back()->withInput()->with('error', 'Error occured in store shedule data');
            }
            if ($slot_updated) {
                return back()->with('success', 'Store schedule updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating store');
        }
        
    }
    
    public function store_special_schedules($id)
    {
        $store = Store::find($id);
        $special_slots = StoreSchedule::where(['fk_store_id'=>$id, 'deleted'=>0])->whereRaw('Date(date) >= CURDATE()')->groupBy('date')->orderBy('date','asc')->paginate(10);
        
        foreach ($special_slots as $key => $slot) {
            $special_slots[$key]->is24hours_open = StoreSchedule::where(['fk_store_id'=>$id, 'deleted'=>0, 'date'=>$slot->date, '24hours_open'=>1, 'from'=>NULL, 'to'=>NULL])->count();
            $special_slots[$key]->is24hours_close = StoreSchedule::where(['fk_store_id'=>$id, 'deleted'=>0, 'date'=>$slot->date, '24hours_open'=>0, 'from'=>NULL, 'to'=>NULL])->count();
            $special_slots[$key]->schedules = StoreSchedule::where(['fk_store_id'=>$id, 'deleted'=>0, 'date'=>$slot->date])->orderBy('from','asc')->get();
        }

        return view('admin.fleet.store-special-schedules', [
            'store' => $store,
            'special_slots' => $special_slots
        ]);
    }

    public function store_special_schedules_update(Request $request, $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return back()->withInput()->with('error', 'Store does not exist');
        }
        elseif(!$request->input('schedule_date')) {
            return back()->withInput()->with('error', 'Special date is not valid');
        }
        else{
            $slot_updated = false;
            $day = NULL;
            $date = $request->input('schedule_date');
            $schedule_24hours = $request->input('schedule_24hours') ? 1 : 0;
            $schedule_24hours_close = $request->input('schedule_24hours_close') ? 1 : 0;
            $slot_open = $request->input('slot_open') ?? NULL;
            $slot_close = $request->input('slot_close') ?? NULL;
            // Shedules
            if ($schedule_24hours) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'date' => $date,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                $create_arr = [
                    'fk_store_id' => $id,
                    'date' => $date,
                    'day' => $day,
                    '24hours_open' => 1,
                    'from' => NULL,
                    'to' => NULL
                ];
                $slot_updated = StoreSchedule::create($create_arr);
            }
            elseif ($schedule_24hours_close) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'date' => $date,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                $create_arr = [
                    'fk_store_id' => $id,
                    'date' => $date,
                    'day' => $day,
                    '24hours_open' => 0,
                    'from' => NULL,
                    'to' => NULL
                ];
                $slot_updated = StoreSchedule::create($create_arr);
            }
            elseif ($slot_open != null && $slot_close != null && count($slot_open)>0 && count($slot_close)>0) {
                // Remove existing schedules
                StoreSchedule::where([
                    'fk_store_id' => $id,
                    'date' => $date,
                    'deleted'=>0
                ])->update(['deleted'=>1]);
                // Add new shedule
                for ($i = 0; $i < count($slot_open); $i++) {
                    if ($slot_open[$i] && $slot_close[$i]) {
                        $create_arr = [
                            'fk_store_id' => $id,
                            'date' => $date,
                            'day' => $day,
                            '24hours_open' => 0,
                            'from' => $slot_open[$i],
                            'to' => $slot_close[$i]
                        ];
                        $slot_updated = StoreSchedule::create($create_arr);
                    }
                }
            } else {
                return back()->withInput()->with('error', 'Error occured in store shedule data');
            }
            if ($slot_updated) {
                return back()->with('success', 'Store schedule updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating store');
        }
        
    }
    
    public function stores_catalog(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->orWhere('company_name', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $stores = Store::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        return view('admin.fleet.stores-catalog', ['stores' => $stores, 'filter' => $filter]);
    }

    public function stores_catalog_with_filter(Request $request)
    {
        $stores = Store::join('base_products_store', function($join){
                $join->on('stores.id', '=', 'base_products_store.fk_store_id')
                    ->where('base_products_store.deleted','=',0)
                    ->where('base_products_store.fk_product_id','>',0);
            })
            ->selectRaw('stores.*, COUNT(base_products_store.id) AS all_products')
            ->where('stores.deleted', '=', 0)
            ->groupBy('stores.id')
            ->sortable(['id' => 'asc'])
            ->paginate(100);

        return view('admin.fleet.stores-catalog-with-filter', ['stores' => $stores]);
    }

    public function stores_sales(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->orWhere('company_name', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            
            $stores = Store::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        return view('admin.fleet.stores-sales', ['stores' => $stores, 'filter' => $filter]);
    }

    public function stores_high_sale_products(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->orWhere('company_name', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            
            $stores = Store::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        return view('admin.fleet.stores-high-sale-products', ['stores' => $stores, 'filter' => $filter]);
    }

    public function store_edit($id = null)
    {
        $id = base64url_decode($id);
        $store = Store::find($id);
        $storekeepers = Storekeeper::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $added_storekeepers = Storekeeper::where('fk_store_id', '=', $id)->pluck('id')->toArray();
        $drivers = Driver::where(['is_available' => 1])->orderBy('name', 'asc')->get();
        $added_drivers = Driver::where('fk_store_id', '=', $id)->pluck('id')->toArray();
        $companies = Company::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        return view('admin.fleet.stores-edit', ['store' => $store, 'storekeepers' => $storekeepers, 'added_storekeepers' => $added_storekeepers, 'drivers' => $drivers, 'added_drivers' => $added_drivers,'companies' => $companies]);
    }

    public function store_update(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        
        $store = Store::where(['email' => $request->input('email'),['id','!=', $id],'deleted' => 0])->get();
        if (count($store) > 0) {
            return back()->withInput()->with('error', 'Email already exist');
        }else{
            
            $update_arr = [
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'pin' => $request->input('pin'),
                'api_url' => $request->input('api_url'),
                'last_api_updated_at' => isset($request->api_url) ? Carbon::now()->format('d-M-y_h_i_s') : null,
            ];
            $store = Store::find($id);
            if($store->password || isset($request->password)){
                $company_name = Company::find($store->company_id);
                $update_arr['company_name'] = $company_name->name;

                if(isset($request->password)){
                    $update_arr['password'] = bcrypt($request->password);
                }

                $update = Store::find($id)->update($update_arr);
                if ($update) {
                    if ($request->input('storekeeper_ids') != null) {
                        Storekeeper::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                        foreach ($request->input('storekeeper_ids') as $value) {
                            Storekeeper::find($value)->update(['fk_store_id' => $id]);
                        }
                    }else{
                        Storekeeper::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                    }

                    if ($request->input('driver_ids') != null) {
                        Driver::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                        foreach ($request->input('driver_ids') as $value) {
                            Driver::find($value)->update(['fk_store_id' => $id]);
                        }
                    }else{
                        Driver::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                    }

                    //updating store administrator
                    $store_admin = Admin::where('fk_store_id',$id)->first();
                    if($store_admin){
                        $store_admin->update($update_arr);
                    }else{
                        $store_product_stock_update_role = Role::where('slug','store-product-stock-update')->first();
                        $insert_arr = [
                            'name' => $store->name,
                            'email' => $store->email,
                            'password' => $store->password,
                            'fk_role_id' => $store_product_stock_update_role->id,
                            'fk_store_id' => $id
                        ];

                        Admin::create($insert_arr);
                    }
                    

                    return redirect('admin/fleet/stores')->with('success', 'Store updated successfully');
                }
            }else{

                return back()->withInput()->with('error', 'Please enter a password');
            }
            
            return back()->withInput()->with('error', 'Error while updating store');
        }
        
    }


    protected function instant_model(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $instant_store_groups = InstantStoreGroup::where(['deleted'=>0, 'status'=>1])
                ->where('name', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $instant_store_groups = InstantStoreGroup::where(['deleted'=>0, 'status'=>1])
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $instant_store_groups->appends(['filter' => $filter]);

        $instant_store_group_hubs = InstantStoreGroup::where(['deleted'=>0, 'status'=>1])->get()->pluck('fk_hub_id')->toArray();

        $stores = Store::where(['deleted'=>0, 'status'=>1])
                ->whereIn('id', $instant_store_group_hubs)
                ->get();
        
        return view('admin.fleet.instant_model.index', [
            'instant_store_groups' => $instant_store_groups,
            'filter' => $filter,
            'stores' => $stores
        ]);
    }

    protected function instant_model_active_orders(Request $request, $id)
    {
        $group = InstantStoreGroup::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$group && $id!=0) {
            die("Group is not found!");
        }

        $group_stores = InstantStoreGroupStore::join('instant_store_groups','instant_store_groups.id','=','instant_store_group_stores.fk_group_id')
                    ->where('instant_store_groups.deleted','=',0)
                    ->groupBy('fk_store_id')
                    ->get()->pluck('fk_store_id')->toArray();

        
        $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
        ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
        // ->where('order_payments.status', '!=', 'rejected')
        ->where('orders.parent_id', '=', 0)
        ->orderBy('orders.id', 'desc');
        $orders = $orders->where('order_delivery_slots.delivery_time', '=', 1);
        $orders = $orders->whereNotIn('orders.status',[0,4,7]);

        if ($id!=0) {
            $orders = $orders->whereIn('orders.fk_store_id', $group_stores);
        }
        if ($_GET) {
            if (isset($_GET['delivery_from']) && $request->query('delivery_from') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '>=', date('Y-m-d', strtotime($request->input('delivery_from'))));
            }
            if (isset($_GET['delivery_to']) && $request->query('delivery_to') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '<=', date('Y-m-d', strtotime($request->input('delivery_to'))));
            }
            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } 
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
        } 
        if (!$_GET || !isset($_GET['test_order'])) {
            $orders = $orders->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            });
        }

        $orders = $orders->paginate(50);

        $drivers = Driver::join('driver_instant_groups','driver_instant_groups.fk_driver_id','=','drivers.id')
            ->select('drivers.*')
            ->where('driver_instant_groups.fk_group_id',$id)
            ->where('drivers.deleted',0)->get();

        return view('admin.fleet.instant_model.orders', [
            'group' => $group,
            'group_id' => $id,
            'orders' => $orders,
            'drivers' => $drivers,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function instant_model_orders(Request $request, $id)
    {
        $group = InstantStoreGroup::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$group && $id!=0) {
            die("Group is not found!");
        }

        $group_stores = InstantStoreGroupStore::join('instant_store_groups','instant_store_groups.id','=','instant_store_group_stores.fk_group_id')
                    ->where('instant_store_groups.deleted','=',0)
                    ->groupBy('fk_store_id')
                    ->get()->pluck('fk_store_id')->toArray();

                    $group_stores = InstantStoreGroupStore::join('instant_store_groups','instant_store_groups.id','=','instant_store_group_stores.fk_group_id')
                    ->where('instant_store_groups.deleted','=',0)
                    ->groupBy('fk_store_id')
                    ->get()->pluck('fk_store_id')->toArray();

        
        $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
        ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
        // ->where('order_payments.status', '!=', 'rejected')
        ->where('orders.parent_id', '=', 0)
        ->orderBy('orders.id', 'desc');
        $orders = $orders->where('order_delivery_slots.delivery_time', '=', 1);

        if ($id!=0) {
            $orders = $orders->whereIn('orders.fk_store_id', $group_stores);
        }
        if ($_GET) {
            if (isset($_GET['delivery_from']) && $request->query('delivery_from') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '>=', date('Y-m-d', strtotime($request->input('delivery_from'))));
            }
            if (isset($_GET['delivery_to']) && $request->query('delivery_to') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '<=', date('Y-m-d', strtotime($request->input('delivery_to'))));
            }
            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } 
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
        } 
        if (!$_GET || !isset($_GET['test_order'])) {
            $orders = $orders->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            });
        }

        $orders = $orders->paginate(50);

        $drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.instant_model.orders', [
            'group' => $group,
            'group_id' => $id,
            'orders' => $orders,
            'drivers' => $drivers,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function instant_model_order_detail(Request $request, $order_id)
    {
        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        ->where(['orders.id'=>$order_id])
        ->first();
        if (!$order) {
            die("Order is not found!");
        }
        $delivery_preferences_selected = $order->delivery_preference ? explode(',',$order->delivery_preference) : [];
        $order_products = OrderProduct::leftJoin('storekeeper_products', function($join)
        {
            $join->on('storekeeper_products.fk_order_id', '=', 'order_products.fk_order_id');
            $join->on('storekeeper_products.fk_product_id', '=', 'order_products.fk_product_id');
            $join->on('storekeeper_products.fk_order_product_id', '=', 'order_products.id');
        })
        ->leftJoin('storekeepers', 'storekeeper_products.fk_storekeeper_id', '=', 'storekeepers.id')
        ->select('order_products.*', 'storekeepers.id as storekeeper_id', 'storekeepers.name as storekeeper_name', 'storekeeper_products.status as collected_status', 'storekeeper_products.fk_storekeeper_id')
        ->where(['order_products.fk_order_id'=>$order_id])
        // ->groupBy('storekeeper_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        // ->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        ->orderBy('order_products.id','asc')
        ->get();

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products' . '.id')
            ->select('base_products' . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where('base_products' . '.deleted', '=', 0)
            // ->where('base_products' . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy('base_products' . '.product_name_en', 'asc')
            ->get();
        $sub_order = Order::where(['parent_id' => $order_id])->get();

        $technical_support = TechnicalSupport::where('fk_order_id','=',$order_id)->first();
        $technical_support_chat = [];
        if ($technical_support) {
            $technical_support_chat = TechnicalSupportChat::where('ticket_id','=',$technical_support->id)->get();
        }

        $store = Store::where(['id'=>$order->fk_store_id, 'deleted'=>0])->first();
        // if (!$store) {
        //     die("Store is not found!");
        // }

        $driver = false;
        $driver_location = false;

        if ($order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0 && $order->getOrderDriver->fk_order_id != 0) {
            $driver = $order->getOrderDriver;
            if ($driver) {
                $driver_location = DriverLocation::where(['fk_driver_id'=>$order->getOrderDriver->fk_driver_id])->orderBy('id', 'desc')->first();
            }
        }
        
        $storekeepers = Storekeeper::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_storekeepers = Storekeeper::where(['deleted'=>0])->get();
        $drivers = Driver::where(['fk_store_id'=>$order->fk_store_id, 'deleted'=>0])->get();
        $all_drivers = Driver::where(['deleted'=>0])->get();

        return view('admin.fleet.instant_model.order-detail', [
            'store' => $store,
            'order' => $order,
            'order_products' => $order_products,
            'replaced_products' => $replaced_products,
            'order_status' => $this->order_status,
            'order_product_status' => $this->order_product_status,
            'driver'=>$driver,
            'driver_location'=>$driver_location,
            'delivery_preferences_selected'=>$delivery_preferences_selected,
            'order_preferences'=>$this->order_preferences,
            'technical_support'=>$technical_support,
            'technical_support_chat'=>$technical_support_chat,
            'storekeepers' => $storekeepers,
            'all_storekeepers' => $all_storekeepers,
            'drivers' => $drivers,
            'all_drivers' => $all_drivers
        ]);
    }

    protected function store_groups(Request $request){

        $groups_store_ids = StoreGroup::where('deleted','=',0)->pluck('fk_store_id')->toArray();
        $stores = Store::where(['deleted' => 0])->whereNotIn('id',$groups_store_ids)->get();
        return view('admin.fleet.store_groups', [
            'stores' => $stores
        ]);
    }

    protected function create_store_groups(Request $request){

        if($request->input('stores')){
            
            $group_stores = array_unique($request->input('stores'));
            
            $store_groups = StoreGroup::where('deleted','=',0)->get();
            if(count($store_groups) > 0){
                $latest_group = StoreGroup::latest()->first();
                foreach ($group_stores as $key => $value) {
                    
                    StoreGroup::create([
                        'group_id' => $latest_group->group_id + 1,
                        'group_name' => $request->input('group_name'),
                        'fk_store_id' => $value
                    ]);
                }
            }else{
                
                foreach ($group_stores as $key => $value) {
                    
                    StoreGroup::create([
                        'group_id' => 1,
                        'group_name' => $request->input('group_name'),
                        'fk_store_id' => $value
                    ]);
                }
            }

            return back()->withInput()->with('success', 'Store group successfully created');

        }else{
            
            return back()->withInput()->with('error', 'Error while creating store group');
        }
    }
    
    protected function delete_group_store($id) {
        
        $store_group = StoreGroup::find($id);
        $store_group->update(['deleted' => 1]);

        return back()->withInput()->with('success', 'store successfully removed from the group');
    }

    protected function fleet_map(Request $request)
    {

        $filter = $request->query('filter');

        $drivers = DriverLocation::join('drivers','driver_locations.fk_driver_id','=','drivers.id')
            ->select('drivers.*','driver_locations.latitude','driver_locations.longitude')
            ->orderBy('driver_locations.updated_at','asc')
            ->groupBy('driver_locations.fk_driver_id')
            ->get();

        $delivery_hubs = DeliveryHub::join('stores','delivery_hubs.fk_store_id','=','stores.id')
            ->select('stores.*')
            ->get();

        $stores = Store::where('deleted','=',0)->get();


        $store_groups = \App\Model\StoreGroup::where('deleted','=',0)->groupBy('group_id')->get();
        $polygons = array();
        foreach ($store_groups as $key => $store_group) {
            $group_stores = \App\Model\StoreGroup::join('stores','store_groups.fk_store_id','=','stores.id')->where('group_id',$store_group->group_id)->get();
            $store_polygons = array();
            foreach ($group_stores as $key => $group_store) {
                $store_polygons[] = array(
                    'lat' => floatval($group_store->latitude),
                    'lng' => floatval($group_store->longitude),
                );
            }
            $polygons[] = $store_polygons;
        }

        
        
        return view('admin.fleet.fleet-map', [
            'drivers' => $drivers,
            'delivery_hubs' => $delivery_hubs,
            'stores' => $stores,
            'polygons' => $polygons
            
        ]);
    }

    protected function store_list(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }

        $stores = Store::where(['status' => 1,'deleted' => 0])->paginate(20);

        //collector drivers
        $drivers = Driver::where(['role' => 0, 'deleted' => 0])->get();
        
        return view('admin.fleet.store-drivers', [
            'store' => $store,
            'stores' => $stores,
            'drivers' => $drivers
        ]);
    }

    protected function driver_list(Request $request)
    {
        $store_groups = StoreGroup::where(['status' => 1, 'deleted' => 0])->groupBy('group_id')->get();
        $drivers = Driver::where(['role' => 0, 'deleted' => 0])->paginate(20);
        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('name', 'asc')->get();
        
        return view('admin.fleet.driver-stores', [
            'stores' => $stores,
            'drivers' => $drivers,
            'store_groups' => $store_groups
        ]);
    }

    protected function storekeeper_store_list(Request $request, $id)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }

        $stores = Store::where(['status' => 1,'deleted' => 0])->paginate(20);

        //collector drivers
        $driver_stores = DriverGroup::pluck('fk_driver_id')->toArray();
        $drivers = Driver::where(['role' => 0, 'deleted' => 0])->get();
        
        return view('admin.fleet.storekeeper-store-drivers', [
            'store' => $store,
            'stores' => $stores,
            'drivers' => $drivers
        ]);
    }

    protected function assign_store_driver(Request $request)
    {
        $fk_store_id = $request->input('fk_store_id');
        $fk_driver_id = $request->input('fk_driver_id');
        
        $already_assigned = DriverGroup::where(['fk_store_id' => $fk_store_id])->first();
        $driver = Driver::find($fk_driver_id);
        if (!$driver) {
            $already_assigned->delete();
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver is not found']);
        }

        if ($already_assigned) {
            $update_arr = [
                'fk_driver_id' => $fk_driver_id
            ];
            $update = $already_assigned->update($update_arr);
        
            if ($update) {
                
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        } else {

            $store_group = StoreGroup::where('fk_store_id',$fk_store_id)->first();
            $insert_arr = [
                'fk_store_id' => $fk_store_id,
                'fk_driver_id' => $fk_driver_id,
                'group_id' => $store_group ? $store_group->group_id : 0,
                'status' => 1
            ];
            
            $create = DriverGroup::create($insert_arr);
            if ($create) {
                
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        }

    }
     
    protected function instant_model_store_groups(Request $request)
    {
        $instant_store_group_stores = InstantStoreGroupStore::join('instant_store_groups','instant_store_group_stores.fk_group_id','=','instant_store_groups.id')
            ->where('instant_store_groups.deleted','=',0)
            ->orderBy('instant_store_groups.id', 'desc')->get()->pluck('fk_store_id')->toArray();
        
        $stores = Store::where(['status' => 1, 'deleted' => 0])->whereNotIn('id',$instant_store_group_stores)->get();

        return view('admin.fleet.instant_model.store_grouping', [
            'stores' => $stores
        ]);
    }

    protected function create_instant_model_store_group(Request $request)
    {
        if($request->input('stores') && (count($request->input('stores')) >= 2)){

            $insert_group_arr = [
                'name' => $request->input('name'),
                'fk_hub_id' => $request->input('fk_hub_id'),
            ];
            
            $create_group = InstantStoreGroup::create($insert_group_arr);

            if($create_group){

                $stores = $request->input('stores');
                $stores[] = $request->input('fk_hub_id');
                foreach (array_unique($stores) as $key => $value) {
                    $insert_arr = [
                        'fk_group_id' => $create_group->id,
                        'fk_store_id'=> $value
                    ];

                    InstantStoreGroupStore::create($insert_arr);
                }

                return redirect('admin/fleet/instant_model/store_groups')->with('success', 'Store Created successfully');
            }

        }else{
            return back()->withInput()->with('error', 'Please select atleast 2 store to create a group');
        }
    }

    protected function delete_instant_model_store_group($id)
    {
        $instant_store_group = InstantStoreGroup::find($id);
        $update = $instant_store_group->update(['deleted' => 1]);
        if($update){
            return redirect('admin/fleet/instant_model/store_groups')->with('success', 'Store Created successfully');
        }else{
            return back()->withInput()->with('error', 'Error while deleting store');
        }
    }

    protected function edit_store_groups(Request $request)
    {
        $group_id = $request->input('group');
        $groups_store_ids = StoreGroup::where(['group_id' => $group_id, 'deleted' => 0])->pluck('fk_store_id')->toArray();
        $stores = Store::whereIn('id', $groups_store_ids)->where('deleted', 0)->get();
        $group = StoreGroup::where('group_id', $group_id)->first();
        
        $data = array(
            'group' => $group,
            'stores' => $stores
        );

        return json_encode($data);
    }

    protected function update_store_groups(Request $request)
    {
        if($request->input('stores')){
            
            $group_stores = array_unique($request->input('stores'));
            
            $store_groups = StoreGroup::where('group_id','=',$request->input('group_id'))->delete();
            if(count($group_stores) > 0){
                $latest_group = StoreGroup::latest()->first();
                foreach ($group_stores as $key => $value) {
                    StoreGroup::create([
                        'group_id' => $request->input('group_id'),
                        'group_name' => $request->input('group_name'),
                        'fk_store_id' => $value
                    ]);
                }
            }
            return back()->withInput()->with('success', 'Store group successfully updated');

        }else{
            
            return back()->withInput()->with('error', 'Error while creating store group');
        }
    }

    protected function get_group_stores(Request $request)
    {
        $driver = $request->input('fk_driver_id');
        $driver_group = $request->input('driver_group');
    
        $group_stores = StoreGroup::join('stores', 'stores.id', '=', 'store_groups.fk_store_id')
            ->select('stores.*')
            ->where('store_groups.group_id', $driver_group)
            ->where('store_groups.deleted', 0)
            ->where('stores.status', 1)
            ->where('stores.deleted', 0)
            ->pluck('stores.id')
            ->toArray();
        
        $stores = Store::select('stores.*')
                ->where('stores.status', 1)
                ->where('stores.deleted', 0)
                ->get();

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'data' => array(
                'stores' => $stores, 
                'group_stores' => $group_stores,
                'fk_driver_id' => $driver
            )
        ]);
    }

    protected function update_driver_store(Request $request) 
    {
        $collector_driver = $request->input('fk_driver_id');
        $collector_driver_group = $request->input('collector_driver_group');
        $collector_driver_group_stores = $request->input('collector_driver_group_store');
        $collector_driver_assined_groups = DriverGroup::where('fk_driver_id', $collector_driver)->get();

        if ($collector_driver_assined_groups->count() > 0) {
            // Delete existing collector driver groups
            $collector_driver_assined_groups->each(function ($collector_driver_assined_groups) {
                $collector_driver_assined_groups->delete();
            });
        }

        if ($collector_driver_group_stores) {
            foreach ($collector_driver_group_stores as $key => $collector_driver_group_store) {
                DriverGroup::create([
                    'fk_driver_id' => $collector_driver,
                    'group_id' => $collector_driver_group,
                    'fk_store_id' => $collector_driver_group_store,
                ]);
            }
        }

        return redirect('admin/fleet/driver/list')->with('success', 'Driver Stores Updated successfully');
    }

    protected function delete_driver_store(Request $request, $id) 
    {
        $collector_driver_assined_groups = DriverGroup::where('fk_driver_id', $id)->get();

        if ($collector_driver_assined_groups->count() > 0) {
            // Delete existing collector driver groups
            $collector_driver_assined_groups->each(function ($collector_driver_assined_groups) {
                $collector_driver_assined_groups->delete();
            });
        }

        return redirect('admin/fleet/driver/list')->with('success', 'Driver Stores Deleted Successfully');
    }

    protected function storekeeper_assign_store_driver(Request $request)
    {
        $fk_store_id = $request->input('fk_store_id');
        $fk_driver_id = $request->input('fk_driver_id');
        
        $already_assigned = DriverGroup::where(['fk_store_id' => $fk_store_id])->first();
        $driver = Driver::find($fk_driver_id);
        if (!$driver) {
            $already_assigned->delete();
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver is not found']);
        }

        if ($already_assigned) {
            $update_arr = [
                'fk_driver_id' => $fk_driver_id
            ];
            $update = $already_assigned->update($update_arr);
        
            if ($update) {
                
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        } else {

            $store_group = StoreGroup::where('fk_store_id',$fk_store_id)->first();
            $insert_arr = [
                'fk_store_id' => $fk_store_id,
                'fk_driver_id' => $fk_driver_id,
                'group_id' => $store_group ? $store_group->group_id : 0,
                'status' => 1
            ];
            
            $create = DriverGroup::create($insert_arr);
            if ($create) {
                
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Driver assigned successfully', 'data' => $driver]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning driver']);
            }
        }
    }

    protected function storekeeper_driver_list(Request $request)
    {
        $store_groups = StoreGroup::where(['status' => 1, 'deleted' => 0])->groupBy('group_id')->get();
        $drivers = Driver::where(['role' => 0, 'deleted' => 0])->paginate(20);
        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('name', 'asc')->get();
        
        return view('admin.fleet.storekeeper-driver-stores', [
            'stores' => $stores,
            'drivers' => $drivers,
            'store_groups' => $store_groups
        ]);
    }

    protected function storekeeper_update_driver_store(Request $request) 
    {
        $collector_driver = $request->input('fk_driver_id');
        $collector_driver_group = $request->input('collector_driver_group');
        $collector_driver_group_stores = $request->input('collector_driver_group_store');
        $collector_driver_assined_groups = DriverGroup::where('fk_driver_id', $collector_driver)->get();

        if ($collector_driver_assined_groups->count() > 0) {
            // Delete existing collector driver groups
            $collector_driver_assined_groups->each(function ($collector_driver_assined_groups) {
                $collector_driver_assined_groups->delete();
            });
        }

        if ($collector_driver_group_stores) {
            foreach ($collector_driver_group_stores as $key => $collector_driver_group_store) {
                DriverGroup::create([
                    'fk_driver_id' => $collector_driver,
                    'group_id' => $collector_driver_group,
                    'fk_store_id' => $collector_driver_group_store,
                ]);
            }
        }

        return redirect('admin/fleet/storekeeper/driver/list')->with('success', 'Driver Stores Updated successfully');
    }

    protected function storekeeper_delete_driver_store(Request $request, $id) 
    {
        $collector_driver_assined_groups = DriverGroup::where('fk_driver_id', $id)->get();

        if ($collector_driver_assined_groups->count() > 0) {
            // Delete existing collector driver groups
            $collector_driver_assined_groups->each(function ($collector_driver_assined_groups) {
                $collector_driver_assined_groups->delete();
            });
        }

        return redirect('admin/fleet/storekeeper/driver/list')->with('success', 'Driver Stores Deleted Successfully');
    }

}
