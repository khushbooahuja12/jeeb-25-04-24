<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\User;
use App\Model\Company;
use App\Model\Store;
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
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\Category;
use App\Model\Brand;
use App\Model\VendorRequestedProduct;

class FleetBpController extends CoreApiController
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
                $store->no_available_products = Product::where(['fk_company_id'=>$store->company_id, 'deleted'=>0, 'stock'=>1])
                                                ->where('store'.get_store_no($store->name),'>',0)
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
        })
        ->leftJoin('storekeepers', 'storekeeper_products.fk_storekeeper_id', '=', 'storekeepers.id')
        ->select('order_products.*', 'storekeepers.id as storekeeper_id', 'storekeepers.name as storekeeper_name', 'storekeeper_products.status as collected_status', 'storekeeper_products.fk_storekeeper_id')
        ->where(['order_products.fk_order_id'=>$order_id])
        // ->groupBy('storekeeper_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        ->groupBy('order_products.fk_product_id','order_products.is_missed_product','order_products.is_replaced_product')
        ->orderBy('order_products.id','asc')
        ->get();

        // dd($order_products);

        $replaced_products = TechnicalSupportProduct::join($this->products_table, 'technical_support_products.fk_product_id', '=', $this->products_table . '.id')
            ->select($this->products_table . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where($this->products_table . '.deleted', '=', 0)
            // ->where($this->products_table . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy($this->products_table . '.product_name_en', 'asc')
            ->get();
        $sub_order = Order::where(['parent_id' => $order_id])->get();

        $technical_support = TechnicalSupport::where('fk_order_id','=',$order_id)->first();
        $technical_support_chat = [];
        if ($technical_support) {
            $technical_support_chat = TechnicalSupportChat::where('ticket_id','=',$technical_support->id)->get();
        }

        $store = Store::where(['id'=>$order->fk_store_id, 'deleted'=>0])->first();
        if (!$store) {
            die("Store is not found!");
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

    protected function assign_storekeeper(Request $request) 
    {
        
        $fk_storekeeper_id = $request->input('fk_storekeeper_id');
        $fk_order_id = $request->input('fk_order_id');
        $fk_product_id = $request->input('fk_product_id');
        $storekeeper_product = StorekeeperProduct::where([
            'fk_order_id' => $fk_order_id,
            'fk_product_id' => $fk_product_id
        ])->first();
        $storekeeper = Storekeeper::find($fk_storekeeper_id);
        if ($storekeeper_product) {
            $update_arr = [
                'fk_storekeeper_id' => $fk_storekeeper_id
            ];
            $update = $storekeeper_product->update($update_arr);
        
            if ($update) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Storekeeper assigned successfully', 'data' => $storekeeper]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while assigning storekeeper']);
            }
        } else {
            $create_arr = [
                'fk_order_id' => $fk_order_id,
                'fk_product_id' => $fk_product_id,
                'fk_storekeeper_id' => $fk_storekeeper_id
            ];
            $create = StorekeeperProduct::create($create_arr);
        
            if ($create) {
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
                    ->where('orders.status', '>=', 1)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc');
            } else {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '>=', 1)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc');
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

            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
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
                    ->where('orders.status', '>=', 1)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc')
                    ->paginate(50);
            } else {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('order_payments.status', '!=', 'blocked')
                    ->where('orders.parent_id', '=', 0)
                    ->where('orders.status', '>=', 1)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc')
                    ->paginate(50);
            }
        }
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
        if ($_GET) {
            if ($id!=0) {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('orders.fk_store_id', '=', $id)
                    ->where('orders.status', '!=', '0')
                    ->where('orders.status', '!=', '4')
                    ->where('orders.status', '!=', '7')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('orders.parent_id', '=', 0)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc');
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
                    ->orderBy('orders.id', 'desc');
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

            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            if ($id!=0) {
                $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
                    ->select("orders.*", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type", 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                    ->where('orders.fk_store_id', '=', $id)
                    ->where('orders.status', '!=', '0')
                    ->where('orders.status', '!=', '4')
                    ->where('orders.status', '!=', '7')
                    ->where('order_payments.status', '!=', 'rejected')
                    ->where('orders.parent_id', '=', 0)
                    // ->where('order_delivery_slots.delivery_time', '=', 1)
                    ->orderBy('orders.id', 'desc')
                    ->paginate(50);
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
                    ->orderBy('orders.id', 'desc')
                    ->paginate(50);
            }
        }
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

    protected function later_orders(Request $request)
    {
        if ($_GET) {
            $orders = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", "order_delivery_slots.delivery_date", "order_delivery_slots.later_time")
                ->where('orders.status', '!=', '0')
                ->where('orders.status', '!=', '4')
                ->where('orders.status', '!=', '7')
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
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
                ->select("orders.*", "order_delivery_slots.delivery_date", "order_delivery_slots.later_time")
                ->where('orders.status', '!=', '0')
                ->where('orders.status', '!=', '4')
                ->where('orders.status', '!=', '7')
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('admin.orders.later_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function all_orders(Request $request, $slot = null)
    {
        // $slot = base64url_decode($slot);



        // if ($slot != '') {
        //     $date_and_slot = explode('|', $slot);

        //     $delivery_slot_arr = explode('-', $date_and_slot[1]);
        //     $new_slot = date('H:i', strtotime($delivery_slot_arr[0])) . '-' . date('H:i', strtotime($delivery_slot_arr[1]));

        //     $orders = $orders->where('order_delivery_slots.delivery_slot', '=', $new_slot)
        //         ->where('order_delivery_slots.delivery_date', '=', $date_and_slot[0]);
        // }

        // if (isset($_GET['delivery_date']) && $_GET['delivery_date'] != '') {
        //     $delivery_slots = \App\Model\DeliverySlot::where(['date' => date('Y-m-d', strtotime($_GET['delivery_date']))])
        //         ->orderBy('from', 'asc')
        //         ->get();
        // }

        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*")
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
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
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*")
                ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('admin.orders.all_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            // 'delivery_slots' => $delivery_slots ?? '',
            // 'delivery_date' => $slot ? date('d-m-Y', strtotime($date_and_slot[0])) : "",
            // 'from' => $slot ? date('h:i A', strtotime($delivery_slot_arr[0])) : "",
            // 'to' => $slot ? date('h:i A', strtotime($delivery_slot_arr[1])) : "",
            // 'slot' => $slot,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : ''
        ]);
    }

    protected function get_slots(Request $request)
    {
        $slots = get_slots(date('Y-m-d', strtotime($request->input('date'))));
        if ($slots->count()) {
            $slot_arr = [];

            foreach ($slots as $key => $value) {
                $slot_arr[$key] = [
                    'id' => $value->id,
                    'slot' => date('H:i', strtotime($value->from)) . '-' . date('H:i', strtotime($value->to))
                ];
            }

            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => 'Success',
                'slots' => $slot_arr
            ]);
        } else {
            return response()->json([
                'error' => true,
                'status_code' => 404,
                'message' => 'Some error found'
            ]);
        }
    }

    protected function detail(Request $request, $id)
    {
        $id = base64url_decode($id);
        $order = Order::find($id);

        $sub_order = Order::where(['parent_id' => $id])->first();

        $drivers = Driver::orderBy('id', 'desc')->get();

        $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($order->id);

        $replaced_products = TechnicalSupportProduct::join($this->products_table, 'technical_support_products.fk_product_id', '=', $this->products_table . '.id')
            ->select($this->products_table . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $id])
            ->where($this->products_table . '.deleted', '=', 0)
            // ->where($this->products_table . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy($this->products_table . '.product_name_en', 'asc')
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

        // print_r($update);
        if ($update) {
            Order::find($id)->update(['status' => 2]);
            $insert_arr = [
                'fk_order_id' => $id,
                'status' => 2
            ];
            OrderStatus::create($insert_arr);

            Driver::find($request->input('fk_driver_id'))->update(['is_available' => 0]);

            // $vendor_order = VendorOrder::where(['fk_order_id' => $id])->first();
            // if (!$vendor_order) {
            //     VendorOrder::create([
            //         'fk_order_id' => $id,
            //         'fk_vendor_id' => 1,
            //         'order_link' => '/vendor/' . base64url_encode($id)
            //     ]);
            // }

            //updating driver availability
            // updateDriverAvailability($request->input('fk_driver_id'));

            // event(new \App\Events\MyEvent('New order assigned'));

            return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
        }
        return back()->withInput()->with('error', 'Error while assigning status');
    }

    protected function replacement_options(Request $request, $id = null)
    {
        $op = OrderProduct::join($this->products_table, 'order_products.fk_product_id', '=', $this->products_table . '.id')
            ->select($this->products_table . '.id', $this->products_table . '.fk_sub_category_id', 'order_products.is_out_of_stock', 'order_products.deleted')
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

    // Products stock update from store (For veg and fruits, pet care)
    protected function update_stock_multiple(Request $request, $id = 0, $category)
    {
        $filter = $request->query('filter');

        $store = Store::find($id);
        
        $no_of_items = 20;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        $categories = Category::where('parent_id',$category)->get();
        
        $sub_category_ids = [];
        foreach ($categories as $key => $value) {
            array_push($sub_category_ids, $value->id);
        }

        $products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->select(
                'base_products.*',
                'base_products_store.id as product_store_id',
                'base_products_store.unit as product_store_unit',
                'base_products_store.product_distributor_price as product_store_distributor_price',
                'base_products_store.product_store_price as product_store_product_price',
                'base_products_store.stock as product_store_product_stock',
                'base_products_store.fk_store_id',
                'categories.id as category_id',
                'categories.category_name_en',
            )
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            // ->whereIn('base_products.fk_sub_category_id', $sub_category_ids)
            ->where('base_products.deleted', '=', 0)
            ->where('base_products_store.deleted', '=', 0);
        
        if (!empty($filter)) {
            $products->where('base_products.product_name_en', 'like', '%' . $filter . '%');
            $products->where('base_products_store.barcode', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('base_products.id', 'desc')
        ->paginate($no_of_items);

        return view('admin.fleet.update_stock_multiple_bp', [
            'filter' => $filter,
            'products' => $products,
            'store' => $store,
            'starting_no' => $starting_no,
            'category' => $category
        ]);
    }

    protected function update_stock_multiple_save(Request $request)
    {
        $id = $request->input('id');
        $base_product_store = BaseProductStore::find($id);

        if ($base_product_store) {

            //calculate product distributor price
            $store = Store::find($request->input('fk_store_id'));
            if($store->back_margin > 0){
                $product_distributor_price = ($request->input('product_store_distributor_price') - (($store->back_margin / 100) * $request->input('product_store_distributor_price')));
            }else{
                $product_distributor_price = $request->input('product_store_distributor_price');
            }

            $update_arr = [
                'product_distributor_price_before_back_margin' => $request->input('product_store_distributor_price'),
                'product_distributor_price' => $product_distributor_price,
                'stock' => $request->input('product_store_product_stock'),
                'product_store_stock' => $request->input('product_store_product_stock'),
            ];
            
            $base_product = BaseProduct::find($base_product_store->fk_product_id);

            if ($base_product) {

                $diff = $base_product_store->product_store_price - $product_distributor_price;
                if ($base_product_store->allow_margin == 1 || ($base_product_store->allow_margin == 0 && $diff < 0)) {
                    $priceArr = calculatePriceFromFormula($product_distributor_price, $base_product->fk_offer_option_id, $base_product->fk_brand_id, $base_product->fk_sub_category_id, $base_product->fk_store_id);
                    $update_arr['product_store_price'] = $priceArr[0];
                    $update_arr['base_price'] = $priceArr[2];
                    $update_arr['base_price_percentage'] = $priceArr[3];
                    $update_arr['discount_percentage'] = $priceArr[4];
                    $update_arr['fk_price_formula_id'] = $priceArr[5];
                } else {
                    $profit = abs($product_distributor_price - $base_product_store->product_store_price);
                    $margin = number_format((($profit / $product_distributor_price) * 100), 2);
                    $update_arr['product_store_price'] = $base_product_store->product_store_price;
                    $update_arr['base_price'] = $base_product_store->base_price;
                    $update_arr['base_price_percentage'] = number_format(((($update_arr['base_price']-$product_distributor_price)/$product_distributor_price) * 100), 2);
                    $update_arr['discount_percentage'] = number_format(((($update_arr['base_price']-$update_arr['product_store_price'])/$update_arr['base_price']) * 100), 2);
                    $update_arr['fk_price_formula_id'] = 0;
                }

                $update = $base_product_store->update($update_arr);
                if ($update) {

                    $this->update_base_product($base_product_store->fk_product_id);
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $base_product_store]);
                } else {
                    return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
                }
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    // Products stock update from store (For veg and fruits, pet care)
    protected function store_update_stock_multiple(Request $request, $id = 0, $category)
    {
        $id = base64_decode($id);
        
        if (Auth::guard('admin')->user()->fk_store_id !== (int)$id) {
            return redirect()->back();
        }
        
        $filter = $request->query('filter');
        $parent_category = $request->query('fk_category_id');
        $subcategory = $request->query('fk_sub_category_id');

        $store = Store::find($id);
        
        $no_of_items = 20;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        $categories = Category::where('parent_id',$category)->get();
        
        $sub_category_ids = [];
        foreach ($categories as $key => $value) {
            array_push($sub_category_ids, $value->id);
            
        }

        $sub_categories = Category::where('parent_id','=',$parent_category)->get();

        $products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->select(
                'base_products.*',
                'base_products_store.id as product_store_id',
                'base_products_store.itemcode',
                'base_products_store.barcode',
                'base_products_store.unit as product_store_unit',
                'base_products_store.product_distributor_price_before_back_margin as product_store_distributor_price',
                'base_products_store.product_store_price as product_store_product_price',
                'base_products_store.stock as product_store_product_stock',
                'base_products_store.fk_store_id',
                'base_products_store.is_active',
                'categories.id as category_id',
                'categories.category_name_en',
            )
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            // ->whereIn('base_products.fk_sub_category_id', $sub_category_ids)
            ->where('base_products.deleted', '=', 0)
            ->where('base_products_store.deleted', '=', 0)
            ->where('base_products_store.fk_store_id', '=', $id);
        
        if (!empty($filter)) {
            $products = $products->where('base_products.product_name_en', 'like', '%' . $filter . '%')
                            ->orWhere('base_products_store.barcode', $filter);
        }
        if (!empty($parent_category)) {
            $products->where('base_products_store.fk_category_id','=',$parent_category);
        }
        if (!empty($subcategory)) {
            $products->where('base_products_store.fk_sub_category_id','=',$subcategory);
        }
        $products = $products->orderBy('base_products.id', 'desc')
        ->paginate($no_of_items);

        return view('admin.vendor_panel.update_stock_multiple', [
            'filter' => $filter,
            'products' => $products,
            'store' => $store,
            'starting_no' => $starting_no,
            'category' => $category,
            'categories' => $categories,
            'parent_category' => $parent_category,
            'subcategory' => $subcategory,
            'sub_categories' => $sub_categories
        ]);
    }

    // Products stock update from store with barcode reader
    protected function store_update(Request $request, $id = 0)
    {
        $id = base64_decode($id);

        if (Auth::guard('admin')->user()->fk_store_id !== (int)$id) {
            return redirect()->back();
        }
        
        $barcode = $request->query('barcode');

        $store = Store::find($id);
        $product = false;

        if ($barcode!="") {
            $product = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                ->join('categories','base_products.fk_sub_category_id','=','categories.id')
                ->select(
                    'base_products.*',
                    'base_products_store.id as product_store_id',
                    'base_products_store.itemcode',
                    'base_products_store.barcode',
                    'base_products_store.unit as product_store_unit',
                    'base_products_store.product_distributor_price_before_back_margin as product_store_distributor_price',
                    'base_products_store.product_store_price as product_store_product_price',
                    'base_products_store.stock as product_store_product_stock',
                    'base_products_store.fk_store_id',
                    'base_products_store.is_active',
                    'categories.id as category_id',
                    'categories.category_name_en',
                )
                ->where('base_products.parent_id', '=', 0)
                ->where('base_products.product_type', '=', 'product')
                ->where('base_products.deleted', '=', 0)
                ->where('base_products_store.fk_store_id', '=', $store->id)
                ->where('base_products_store.barcode', '=', $barcode)
                ->where('base_products_store.deleted', '=', 0)
                ->first(1);
        } 

        return view('admin.vendor_panel.update_stock', [
            'barcode' => $barcode,
            'product' => $product,
            'store' => $store,
            'barcode' => $barcode
        ]);
    }

    protected function update_stock_save(Request $request)
    {
        $id = $request->input('id');
        $product = BaseProductStore::find($id);
        if ($product) {
            $update_arr = [
                'distributor_price' => $request->product_store_distributor_price,
                'stock' => $request->product_store_product_stock,
            ];

            $diff = $product->product_store_price - $request->product_store_distributor_price;
            if ($product->allow_margin == 1 || ($product->allow_margin == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->product_store_distributor_price, $product->fk_store_id);
                $update_arr['product_store_price'] = $priceArr[0];
                $update_arr['base_price'] = $priceArr[2];
                $update_arr['base_price_percentage'] = $priceArr[3];
                $update_arr['discount_percentage'] = $priceArr[4];
                $update_arr['fk_price_formula_id'] = $priceArr[5];
            } else {
                $profit = abs($request->product_store_distributor_price - $product->product_store_price);
                $margin = number_format((($profit / $request->product_store_distributor_price) * 100), 2);
                $update_arr['product_store_price'] = $product->product_store_price;
                $update_arr['base_price'] = (double)trim($product->base_price);
                $update_arr['base_price_percentage'] = number_format(((($update_arr['base_price']-$update_arr['product_distributor_price'])/$update_arr['product_distributor_price']) * 100), 2);
                $update_arr['discount_percentage'] = number_format(((($update_arr['base_price']-$update_arr['product_store_price'])/$update_arr['base_price']) * 100), 2);
                $update_arr['fk_price_formula_id'] = 0;
            }

            $update = $product->update($update_arr);
        
            if ($update) {
                $product = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                ->join('categories','base_products.fk_sub_category_id','=','categories.id')
                ->select(
                    'base_products.*',
                    'base_products_store.id as product_store_id',
                    'base_products_store.unit as product_store_unit',
                    'base_products_store.product_distributor_price as product_store_distributor_price',
                    'base_products_store.product_store_price as product_store_product_price',
                    'base_products_store.stock as product_store_product_stock',
                    'categories.id as fk_sub_category_id',
                    'categories.category_name_en as sub_category_name',
                )
                ->where('base_products_store.id', '=', $id)
                ->first();
                
                $this->update_base_product($product->id);

                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    // Products stock update from store with barcode reader
    protected function sale_update(Request $request, $id = 0)
    {
        if (Auth::guard('admin')->user()->fk_store_id !== (int)$id) {
            return redirect()->back();
        }
        
        $barcode = $request->query('barcode');

        $store = Store::find($id);
        $product = false;

        if ($barcode!="") {
            $product = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                ->join('categories','base_products.fk_sub_category_id','=','categories.id')
                ->select(
                    'base_products.*',
                    'base_products_store.id as product_store_id',
                    'base_products_store.itemcode',
                    'base_products_store.barcode',
                    'base_products_store.unit as product_store_unit',
                    'base_products_store.product_distributor_price as product_store_distributor_price',
                    'base_products_store.product_store_price as product_store_product_price',
                    'base_products_store.stock as product_store_product_stock',
                    'base_products_store.fk_store_id',
                    'categories.id as category_id',
                    'categories.category_name_en',
                )
                ->where('base_products.parent_id', '=', 0)
                ->where('base_products.product_type', '=', 'product')
                ->where('base_products.deleted', '=', 0)
                ->where('base_products_store.fk_store_id', '=', $store->id)
                ->where('base_products_store.barcode', '=', $barcode)
                ->where('base_products_store.deleted', '=', 0)
                ->first(1);
        } 

        return view('admin.vendor_panel.update_sale', [
            'barcode' => $barcode,
            'product' => $product,
            'store' => $store,
            'barcode' => $barcode
        ]);
    }

    protected function update_sale_save(Request $request)
    {
        $id = $request->input('id');
        $product = BaseProductStore::find($id);
        if ($product) {
            $stock = $product->stock - $request->product_store_product_stock;
            $stock = $stock>0 ? $stock : 0;
            $update_arr = [
                'stock' => $stock,
            ];

            $update = $product->update($update_arr);
        
            if ($update) {
                $product = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
                ->join('categories','base_products.fk_sub_category_id','=','categories.id')
                ->select(
                    'base_products.*',
                    'base_products_store.id as product_store_id',
                    'base_products_store.unit as product_store_unit',
                    'base_products_store.product_distributor_price as product_store_distributor_price',
                    'base_products_store.product_store_price as product_store_product_price',
                    'base_products_store.stock as product_store_product_stock',
                    'categories.id as fk_sub_category_id',
                    'categories.category_name_en as sub_category_name',
                )
                ->where('base_products_store.id', '=', $id)
                ->first();
                
                $this->update_base_product($product->id);

                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Not found!']);
        }
    }

    protected function dashboard($id)
    {
        $id = base64_decode($id);
        $store = Store::find($id);

        $all_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products_store.fk_store_id', '=', $id)
            ->count();
        $instock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products_store.fk_store_id', '=', $id)
            ->where('base_products_store.stock', '>', 0)
            ->count();

        $outofstock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products_store.fk_store_id', '=', $id)
            ->where('base_products_store.stock', '=', 0)
            ->count();

        $ative_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7])
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->limit(10)
            ->get();

        $total_active_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7])
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->get()
            ->count();

        $storekeeper_collected_orders = StorekeeperProduct::where('fk_store_id', $id)
            ->groupBy('fk_order_id')
            ->havingRaw('MAX(status) = 1 AND MIN(status) = 1')
            ->get()->pluck('fk_order_id')->toArray();
        
        $preparing_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7,5])
            ->whereNotIn('orders.id', $storekeeper_collected_orders)
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->limit(10)
            ->get();

        $ready_for_pickup_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7,5])
            ->whereIn('orders.id', $storekeeper_collected_orders)
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->limit(10)
            ->get();

        return view('admin.vendor_panel.dashboard',['all_products' => $all_products, 'instock_products' => $instock_products, 'outofstock_products' => $outofstock_products, 'ative_orders' => $ative_orders, 'order_status' => $this->order_status, 'total_active_orders' => $total_active_orders, 'store' => $store, 'preparing_orders' => $preparing_orders, 'ready_for_pickup_orders' => $ready_for_pickup_orders]);
    }

    protected function store_orders(Request $request, $id = 0)
    {

        $id = base64_decode($id);
        $store = Store::find($id);

        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc');

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

            $orders = $orders->paginate(30);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc')
                ->paginate(30);
        }

        return view('admin.vendor_panel.all_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'store' => $store
        ]);
    }

    protected function store_active_orders(Request $request, $id = 0)
    {

        $id = base64_decode($id);
        $store = Store::find($id);

        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4,7])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc');

            $orders = $orders->paginate(30);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4,7])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 2)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc')
                ->paginate(30);
        }

        return view('admin.vendor_panel.active_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'store' => $store
        ]);
    }

    protected function store_order_detail(Request $request, $store_id, $order_id)
    {
        $order_id = base64url_decode($order_id);
        $store_id = base64url_decode($store_id);

        $store = Store::find($store_id);

        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        ->whereNotIn('orders.status',[0,4])
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
        ->where(['order_products.fk_order_id'=>$order_id])
        ->where(['order_products.fk_store_id'=>$store_id])
        ->orderBy('order_products.id','asc')
        ->get();

        $replaced_products = TechnicalSupportProduct::join($this->products_table, 'technical_support_products.fk_product_id', '=', $this->products_table . '.id')
            ->select($this->products_table . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where($this->products_table . '.deleted', '=', 0)
            // ->where($this->products_table . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy($this->products_table . '.product_name_en', 'asc')
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

        return view('admin.vendor_panel.order-detail', [
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
            'all_drivers' => $all_drivers,
            'store_id' => $store_id,
            'store' => $store
        ]);
    }

    protected function store_instant_order_detail(Request $request, $store_id, $order_id)
    {
        $order_id = base64url_decode($order_id);
        $store_id = base64url_decode($store_id);

        $store = Store::find($store_id);

        $order = Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        ->whereNotIn('orders.status',[0,4])
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
        ->where(['order_products.fk_order_id'=>$order_id])
        ->where(['order_products.fk_store_id'=>$store_id])
        ->orderBy('order_products.id','asc')
        ->get();

        $replaced_products = TechnicalSupportProduct::join($this->products_table, 'technical_support_products.fk_product_id', '=', $this->products_table . '.id')
            ->select($this->products_table . '.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $order_id])
            ->where($this->products_table . '.deleted', '=', 0)
            // ->where($this->products_table . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy($this->products_table . '.product_name_en', 'asc')
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

        return view('admin.vendor_panel.instant_order_detail', [
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
            'all_drivers' => $all_drivers,
            'store_id' => $store_id,
            'store' => $store
        ]);
    }

    protected function store_product_add($id)
    {
        $id = base64_decode($id);
        $base_products = BaseProduct::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->get();

        $brands = Brand::where('deleted', '=', 0)->orderBy('id', 'desc')->get();
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();
        $store = Store::find($id);

        return view('admin.vendor_panel.add_product',['brands' => $brands, 'categories' => $categories, 'base_products' => $base_products, 'store' => $store]);
    }

    protected function vendor_product_store(Request $request)
    {
        $_tags = $request->input('_tags');
        
        $insert_arr = [
            'itemcode' => $request->input('itemcode'),
            'barcode' => $request->input('barcode'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('unit'),
            'base_price' => $request->input('base_price') ?? 0,
            '_tags' => $_tags??'',
            'country_code' => $request->input('country_code'),
            'fk_store_id' => base64_decode($request->input('fk_store_id')),
            'fk_product_id' => $request->input('fk_product_id'),
            'stock' => $request->input('stock')
        ];
        
        if ($request->hasFile('image')) {
            $product_images_path = str_replace('\\', '/', storage_path("app/public/images/base_product_images/"));
            $product_images_url_base = "storage/images/base_product_images/"; 

            $path = "/images/base_product_images/";
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['product_image_url'] = env('APP_URL').$product_images_url_base . $fileNameToStore;
            endif;
        }

        if ($request->hasFile('country_icon')) {
            $country_icon_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
            $country_icon_url_base = "storage/images/country_icons/"; 

            $path = "/images/country_icons/";
            $filenameWithExt = $request->file('country_icon')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('country_icon')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('country_icon')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['country_icon'] = $country_icon_path . $fileNameToStore;
            endif;
        }

        $create = VendorRequestedProduct::create($insert_arr);
        if ($create) {
            return redirect()->back()->with('success', 'Product Requested successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    // vendor instock products
    protected function store_instock_products(Request $request, $id = 0, $category)
    {
        $id = base64_decode($id);

        if (Auth::guard('admin')->user()->fk_store_id !== (int)$id) {
            return redirect()->back();
        }
        
        $filter = $request->query('filter');

        $store = Store::find($id);
        
        $no_of_items = 20;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        $categories = Category::where('parent_id',$category)->get();
        
        $sub_category_ids = [];
        foreach ($categories as $key => $value) {
            array_push($sub_category_ids, $value->id);
            
        }

        $products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->select(
                'base_products.*',
                'base_products_store.id as product_store_id',
                'base_products_store.itemcode',
                'base_products_store.barcode',
                'base_products_store.unit as product_store_unit',
                'base_products_store.product_distributor_price_before_back_margin as product_store_distributor_price',
                'base_products_store.product_store_price',
                'base_products_store.stock as product_store_product_stock',
                'base_products_store.product_store_stock',
                'base_products_store.fk_store_id',
                'base_products_store.is_active',
                'categories.id as category_id',
                'categories.category_name_en',
            )
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products_store.stock','>', 0)
            ->where('base_products.deleted', '=', 0)
            ->where('base_products_store.deleted', '=', 0)
            ->where('base_products_store.fk_store_id', '=', $id);
        
        if (!empty($filter)) {
            $products = $products->where('base_products.product_name_en', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('base_products.id', 'desc')
        ->paginate($no_of_items);

        return view('admin.vendor_panel.instock_products', [
            'filter' => $filter,
            'products' => $products,
            'store' => $store,
            'starting_no' => $starting_no,
            'category' => $category
        ]);
    }

    // vendor instock products
    protected function store_out_of_stock_products(Request $request, $id = 0, $category)
    {
        $id = base64_decode($id);

        if (Auth::guard('admin')->user()->fk_store_id !== (int)$id) {
            return redirect()->back();
        }
        
        $filter = $request->query('filter');

        $store = Store::find($id);
        
        $no_of_items = 20;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        $categories = Category::where('parent_id',$category)->get();
        
        $sub_category_ids = [];
        foreach ($categories as $key => $value) {
            array_push($sub_category_ids, $value->id);
            
        }

        $products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
            ->join('categories','base_products.fk_sub_category_id','=','categories.id')
            ->select(
                'base_products.*',
                'base_products_store.id as product_store_id',
                'base_products_store.itemcode',
                'base_products_store.barcode',
                'base_products_store.unit as product_store_unit',
                'base_products_store.product_distributor_price_before_back_margin as product_store_distributor_price',
                'base_products_store.product_store_price as product_store_product_price',
                'base_products_store.stock as product_store_product_stock',
                'base_products_store.fk_store_id',
                'base_products_store.is_active',
                'categories.id as category_id',
                'categories.category_name_en',
            )
            ->where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products_store.stock','=', 0)
            ->where('base_products.deleted', '=', 0)
            ->where('base_products_store.deleted', '=', 0)
            ->where('base_products_store.fk_store_id', '=', $id);
        
        if (!empty($filter)) {
            $products = $products->where('base_products.product_name_en', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('base_products.id', 'desc')
        ->paginate($no_of_items);

        return view('admin.vendor_panel.out_of_stock_products', [
            'filter' => $filter,
            'products' => $products,
            'store' => $store,
            'starting_no' => $starting_no,
            'category' => $category
        ]);
    }

    protected function mark_order_product_collected(Request $request)
    {
        $this->required_input($request->input(), ['id']);

        $storekeeperProduct = StorekeeperProduct::where('fk_order_product_id', $request->input('id'))->first();

        if($storekeeperProduct){
            $update = $storekeeperProduct->update([
                'status' => 1 //collected
            ]);

            $this->mark_order_as_invoiced($request, $storekeeperProduct);

            if ($update) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product marked collected successfully']);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking collected']);
            }
        }else{

            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'A Storekeeper has not been assigned to this product !']);
        }

    }

    protected function mark_order_product_out_of_stock(Request $request)
    {
        $this->required_input($request->input(), ['id']);

        $storekeeperProduct = StorekeeperProduct::where('fk_order_product_id', $request->input('id'))->first();

        if($storekeeperProduct){
            $update = $storekeeperProduct->update([
                'status' => 2 //collected
            ]);

            if ($update) {

                $order_product = OrderProduct::find($request->input('id'));
                if ($order_product->fk_product_store_id!=0) {
                    BaseProductStore::where('id', '=', $order_product->fk_product_store_id)
                        ->where('stock', '>=', 1)
                        ->update(['stock' => 0]);

                    $this->update_base_product($order_product->fk_product_id);
                }

                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product marked out of stock successfully']);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while marking collected']);
            }
        }else{
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'A Storekeeper has not been assigned to this product !']);
        }


    }

    protected function store_instant_active_orders(Request $request, $id = 0)
    {

        $id = base64_decode($id);
        $store = Store::find($id);

        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4,7])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 1)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc');

            $orders = $orders->paginate(30);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4,7])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 1)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->orderBy('order_delivery_slots.later_time', 'desc')
                ->paginate(30);
        }

        return view('admin.vendor_panel.instant_active_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'store' => $store
        ]);
    }

    protected function store_instant_orders(Request $request, $id = 0)
    {

        $id = base64_decode($id);
        $store = Store::find($id);

        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 1)
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc');

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

            $orders = $orders->paginate(30);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('order_products.fk_store_id', '=', $id)
                ->whereNotIn('orders.status',[0,4])
                ->where('orders.parent_id', '=', 0)
                ->where('order_delivery_slots.delivery_time', '=', 1)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->groupBy('orders.id')
                ->orderBy('order_delivery_slots.delivery_date', 'desc')
                ->paginate(30);
        }

        return view('admin.vendor_panel.all_instant_orders', [
            'orders' => $orders,
            'order_status' => $this->order_status,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'store' => $store
        ]);
    }

    protected function update_products_status(Request $request)
    {
        $product = BaseProductStore::find($request->input('fk_product_store_id'));
        if($request->input('status') == "on"){
            $product->update(['is_active' => 1]);
        }else{
            $product->update(['is_active' => 0]);
        }

        $this->update_base_product($product->fk_product_id);
        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
    }

    protected function vendor_invoice_pdf($store,$id = null)
    {
        $store = base64url_decode($store);
        $id = base64url_decode($id);

        $company = Store::find($store);

        $storekeeperNotCollectedProducts = StorekeeperProduct::where(['fk_order_id' => $id, 'fk_store_id' => $store, 'status' => 0])->count();
            if($storekeeperNotCollectedProducts == 0){
                $order = \App\Model\Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select('orders.*')
                ->selectRaw("SUM(order_products.distributor_price) as total_product_price")
                ->where('orders.id', '=', $id)
                ->where('order_products.fk_store_id','=', $store)
                ->first();
            if (!$order) {
                die("Order not found");
            }

            $invoiceId = 'N/A';
            $cardNumber = 'N/A';

            $pdf = \PDF::loadView('admin.vendor_panel.vendor_invoice_pdf', [
                'order' => $order,
                'invoiceId' => $invoiceId,
                'cardNumber' => $cardNumber,
                'company' => $company,
                'store' => $store
            ]);
            return $pdf->download('invoice_#'.$order->orderId.'.pdf')->header('Content-Type','application/pdf');
        }else{
            return redirect()->back()->with('error','Please collect all the ordered products before invoicing');
        }
    }

    protected function vendor_invoice_print($store,$id = null)
    {
        $store = base64url_decode($store);
        $id = base64url_decode($id);

        $company = Store::find($store);

        $storekeeperNotCollectedProducts = StorekeeperProduct::where(['fk_order_id' => $id, 'fk_store_id' => $store, 'status' => 0])->count();
            if($storekeeperNotCollectedProducts == 0){
                $order = \App\Model\Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
                ->select('orders.*')
                ->selectRaw("SUM(order_products.distributor_price) as total_product_price")
                ->where('orders.id', '=', $id)
                ->where('order_products.fk_store_id','=', $store)
                ->first();
            if (!$order) {
                die("Order not found");
            }

            $invoiceId = 'N/A';
            $cardNumber = 'N/A';

            return view('admin.vendor_panel.vendor_invoice_print', [
                'order' => $order,
                'invoiceId' => $invoiceId,
                'cardNumber' => $cardNumber,
                'company' => $company,
                'store' => $store
            ]);
        }else{
            return redirect()->back()->with('error','Please collect all the ordered products before invoicing');
        }
    }

    protected function master_dashboard($id)
    {
        //get all stores for the dashboard
        $company = Company::find(base64url_decode($id));
        $stores = Store::where('company_id',$company->id)->get();
        return view('admin.vendor_panel.master_dashboard',['stores' => $stores, 'company' => $company]);
    }

    protected function preparing_orders(Request $request)
    {
        $search   = $request->search['value'];
        $sort     = $request->order;
        $column   = $sort[0]['column'];
        $order    = $sort[0]['dir'] == 'asc' ? "ASC" : "DESC" ;
        
        $id = Auth::guard('admin')->user()->fk_store_id;

        $storekeeper_collected_orders = StorekeeperProduct::where('fk_store_id', $id)
            ->groupBy('fk_order_id')
            ->havingRaw('MAX(status) = 1 AND MIN(status) = 1')
            ->get()->pluck('fk_order_id')->toArray();

        $preparing_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7,5])
            ->whereNotIn('orders.id', $storekeeper_collected_orders)
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->limit(10);

        if ($search != '')
        {
            $preparing_orders->where(function($query) use ($search) {

                $query->where('orders.orderId', 'LIKE', '%'.$search.'%');
                $query->orWhere('order_delivery_slots.later_time', 'LIKE', '%'.$search.'%');
            });
        }

        $total = $preparing_orders->count();

        $result['data'] = $preparing_orders->take($request->length)->skip($request->start)->get();
        $result['recordsTotal'] = $total;
        $result['recordsFiltered'] =  $total;

        echo json_encode($result);
    }

    protected function ready_for_pickup_orders(Request $request)
    {
        $search   = $request->search['value'];
        $sort     = $request->order;
        $column   = $sort[0]['column'];
        $order    = $sort[0]['dir'] == 'asc' ? "ASC" : "DESC" ;
        
        $id = Auth::guard('admin')->user()->fk_store_id;

        $storekeeper_collected_orders = StorekeeperProduct::where('fk_store_id', $id)
            ->groupBy('fk_order_id')
            ->havingRaw('MAX(status) = 1 AND MIN(status) = 1')
            ->get()->pluck('fk_order_id')->toArray();

        $ready_for_pickup_orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
            ->where('order_products.fk_store_id', '=', $id)
            ->whereNotIn('orders.status',[0,4,7,5])
            ->whereIn('orders.id', $storekeeper_collected_orders)
            ->where('orders.parent_id', '=', 0)
            ->where(function($query) {
                $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
            })
            ->groupBy('orders.id')
            ->orderBy('order_delivery_slots.delivery_date', 'desc')
            ->orderBy('order_delivery_slots.later_time', 'desc')
            ->limit(10);

        if ($search != '')
        {
            $ready_for_pickup_orders->where(function($query) use ($search) {

                $query->where('orders.orderId', 'LIKE', '%'.$search.'%');
                $query->orWhere('order_delivery_slots.later_time', 'LIKE', '%'.$search.'%');
            });
        }

        $total = $ready_for_pickup_orders->count();

        $result['data'] = $ready_for_pickup_orders->take($request->length)->skip($request->start)->get();
        $result['recordsTotal'] = $total;
        $result['recordsFiltered'] =  $total;

        echo json_encode($result);
    }

}
