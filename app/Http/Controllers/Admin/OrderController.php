<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderDriver;
use App\Model\OrderStatus;
use App\Model\Driver;
use App\Model\StorekeeperProduct;
use App\Model\Invoice;
use App\Model\VendorOrder;
use App\Model\OrderPayment;
use App\Model\Product;
use App\Model\TechnicalSupport;
use App\Model\TechnicalSupportProduct;
use App\Jobs\UpdateOrderJson;
use App\Jobs\ProcessOrderJson;
use App\Model\ScratchCardUser;
use DB;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends CoreApiController
{

    use \App\Http\Traits\OrderProcessing;
    use \App\Http\Traits\PushNotification;

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

    protected function active_orders(Request $request)
    {
        $order_type = $request->query('order_type') ? $request->query('order_type') : 0;
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.expected_eta', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc');

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
                $orders = $orders->whereIn('orders.status',[1,2,3,5,6]);
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
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->whereIn('orders.status',[1,2,3,5,6])
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('admin.orders.all_orders', [
            'orders' => $orders,
            'order_type' => $order_type,
            'order_status' => $this->order_status,
            // 'delivery_slots' => $delivery_slots ?? '',
            // 'delivery_date' => $slot ? date('d-m-Y', strtotime($date_and_slot[0])) : "",
            // 'from' => $slot ? date('h:i A', strtotime($delivery_slot_arr[0])) : "",
            // 'to' => $slot ? date('h:i A', strtotime($delivery_slot_arr[1])) : "",
            // 'slot' => $slot,
            'delivery_date' => $request->query('delivery_date') ? $request->query('delivery_date') : '',
            'orderId' => $request->query('orderId') ? $request->query('orderId') : '',
            'status' => $request->query('status') ? $request->query('status') : '',
            'active_orders' => true
        ]);
    }

    protected function later_orders(Request $request)
    {
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc');

            if (isset($_GET['delivery_from']) && $request->query('delivery_from') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '>=', date('Y-m-d', strtotime($request->input('delivery_from'))));
            }
            if (isset($_GET['delivery_to']) && $request->query('delivery_to') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '<=', date('Y-m-d', strtotime($request->input('delivery_to'))));
            }
            if (isset($_GET['delivery_date']) && $request->query('delivery_date') != '') {
                $orders = $orders->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($request->input('delivery_date'))));
            }
            if (isset($_GET['status']) && $request->query('status') == 'all') {
            } elseif (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } else {
                $orders = $orders->where('orders.status', '=', 7);
            }
            if (isset($_GET['orderId']) && $request->query('orderId') != '') {
                $orders = $orders->where('orders.orderId', 'like', '%' . $request->query('orderId') . '%');
            }
            if (!isset($_GET['test_order'])) {
                $orders = $orders->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                });
            }
            
            $orders = $orders->where('order_delivery_slots.delivery_time', '=', 2);
            $orders = $orders->paginate(50);

            $orders->appends(['delivery_date' => $request->query('delivery_date')]);
            $orders->appends(['orderId' => $request->query('orderId')]);
            $orders->appends(['status' => $request->query('status')]);
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where('orders.status', '=', 7)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
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

    protected function export_orders(Request $request)
    {
        
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc');

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
            if (!isset($_GET['test_order'])) {
                $orders = $orders->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                });
            }

            $orders = $orders->get();
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->get();
        }
        
        return Excel::download(new OrdersExport($orders), 'orders.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    protected function all_orders(Request $request)
    {
        $order_type = $request->query('order_type') ? $request->query('order_type') : 0;
        $od_status = $request->query('status') ? $request->query('status') : 7;
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.expected_eta', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->orderBy('orders.id', 'desc');

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
            if (isset($_GET['status']) && $request->query('status') == 'all') {
            } elseif (isset($_GET['status']) && $request->query('status') == 'placed') {
                $orders = $orders->where('orders.status', '=', 0);
            } elseif (isset($_GET['status']) && $request->query('status') != '') {
                $orders = $orders->where('orders.status', '=', $_GET['status']);
            } else {
                $orders = $orders->where('orders.status', '=', 7);
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
        } else {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->where('orders.status', '=', 7)
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('admin.orders.all_orders', [
            'orders' => $orders,
            'order_type' => $order_type,
            'od_status' => $od_status,
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

    protected function forgot_something_orders(Request $request, $slot = null)
    {
        if ($_GET) {
            $orders = Order::join('order_products', function($join)
                {
                    $join->on('orders.id', '=', 'order_products.fk_order_id');
                    $join->on('order_products.is_missed_product','=',1);
                })
                ->leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->selectRaw('orders.*, order_payments.status as payment_status, COUNT(order_products.id) as forgot_products_count')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->groupBy('orders.id')
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
            $orders = Order::join('order_products', function($join)
                {
                    $join->on('orders.id', '=', 'order_products.fk_order_id');
                    $join->on('order_products.is_missed_product','=',DB::raw("'1'"));
                })
                ->leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->selectRaw('orders.*, order_payments.status as payment_status, COUNT(order_products.id) as forgot_products_count')
                // ->where('order_payments.status', '!=', 'rejected')
                ->where('orders.parent_id', '=', 0)
                ->groupBy('orders.id')
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('admin.orders.forgot_something_orders', [
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

        $drivers = Driver::where(['deleted' => 0])->orderBy('id', 'desc')->get();

        $scratch_cards = ScratchCardUser::where(['deleted' => 0, 'fk_order_id' => $id])->orderBy('id', 'desc')->get();

        $totalOutOfStockProduct = (new OrderProduct())->getTotalOfOutOfStockProduct($order->id);

        $replaced_products = TechnicalSupportProduct::join('base_products', 'technical_support_products.fk_product_id', '=', 'base_products.id')
            ->select('base_products.*', 'technical_support_products.fk_order_id')
            ->where(['technical_support_products.fk_order_id' => $id])
            ->where('base_products.deleted', '=', 0)
            // ->where($this->products_table . '.store3', '=', 1)
            // ->where('technical_support_products.in_stock', '=', 1)
            ->orderBy('base_products.product_name_en', 'asc')
            ->get();
        // pp($replaced_products);

        return view('admin.orders.detail', [
            'order' => $order,
            'order_status' => $this->order_status,
            'drivers' => $drivers,
            'totalOutOfStockProduct' => $totalOutOfStockProduct,
            'sub_order' => $sub_order,
            'scratch_cards' => $scratch_cards,
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

    protected function assign_driver(Request $request, $id)
    {
        $id = base64url_decode($id);

        $already_assigned = OrderDriver::where(['fk_order_id' => $id])->first();
        if ($already_assigned) {
            return back()->withInput()->with('error', "Driver already assigned");
        }

        /* checking condition that driver already working on some other order */
        $is_driver_busy = OrderDriver::leftJoin('orders', 'order_drivers.fk_order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 4)
            ->where('order_drivers.fk_driver_id', '=', $request->input('driver_id'))
            ->first();
        if ($is_driver_busy) {
            return back()->withInput()->with('error', "Can't assign because driver is busy with other order");
        }

        // $vendororder = VendorOrder::where(['fk_order_id' => $id])->first();
        // if (!$vendororder) {
        //     return back()->withInput()->with('error', "Please allocate the vendor first");
        // }

        $insert_arr = [
            'fk_order_id' => $id,
            'fk_driver_id' => $request->input('driver_id'),
            'status' => 1
        ];
        $create = OrderDriver::create($insert_arr);
        if ($create) {
            Order::find($id)->update(['status' => 1]);
            OrderStatus::create([
                'fk_order_id' => $id,
                'status' => 1
            ]);

            return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
        }
        return back()->withInput()->with('error', 'Error while assigning driver');
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

        if ($order->status!=$order_status) {
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
        $fk_order_id = $id;
        $fk_driver_id = $request->input('fk_driver_id');

        $driver = Driver::find($fk_driver_id);

        // $distance = get_distance($driver->getLocation->latitude, $driver->getLocation->longitude, $request->input('longitude'), $driver->getStore->latitude, $driver->getStore->longitude, 'K');

        if (!$driver) {
            return back()->withInput()->with('error', "This driver is not found");
        // } elseif ($driver->is_available == 0) {
        //     return back()->withInput()->with('error', "This driver is already occupied");
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
                return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
            } else {
                return back()->withInput()->with('error', 'Error while assigning status');
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
                return redirect('admin/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
            } else {
                return back()->withInput()->with('error', 'Error while assigning status');
            }
        }

        return back()->withInput()->with('error', 'Error while assigning status');
    }

    //new cancel order function
    //Ajax
    protected function cancel_order(Request $request)
    {
        $id = $request->input('order_id');
        $order = Order::find($id);

        if ($order->status == 4) {
            return response()->json([
                'error' => false,
                'status_code' => 105,
                'message' => 'Order already cancelled'
            ]);
        }
        
        // Refund to wallet if pay by cart or wallet
        $order_cancelled = $this->order_cancelled($order->id);
        $order_status = $order_cancelled && isset($order_cancelled['order_status']) ? $order_cancelled['order_status'] : 0;
        if ($order_status==4) {
            $wallet_to_refund = $order_cancelled && isset($order_cancelled['wallet_to_refund']) ? $order_cancelled['wallet_to_refund'] : 0;
            if ($wallet_to_refund>0) {
                $error_message = 'Order cancelled and refund processed';
            } else {
                $error_message = 'Order cancelled';
            }
        } else {
            $error_message = 'Something went wrong, please contact customer service!';
        }

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => $error_message
        ]);
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

        $invoiceId = 'N/A';
        $cardNumber = 'N/A';

        return view('admin.orders.customer_invoice', [
            'order' => $order,
            'invoiceId' => $invoiceId,
            'cardNumber' => $cardNumber
        ]);
    }

    
    protected function apply_all_order_generate_jsons(Request $request)
    {

        // Select all base products
        $perPage = 1000; // Number of items per page
        $query = \App\Model\Order::whereIn('status',[4,7]); // Your query

        $paginator = $query->paginate($perPage);
        $lastPage = $paginator->lastPage();

        for ($i=1; $i <= $lastPage; $i++) { 
            
            $orders = $query->paginate($perPage, ['*'], 'page', $i);
            $orders_arr = $orders->map(function ($order) {
                if ($order !== null && is_object($order)) {
                    return [
                        'id' => $order->id
                    ];
                }
            })->toArray();

            // Dispatch the batch job with the array
            UpdateOrderJson::dispatch($i,$orders_arr);
        }
        return redirect('admin/all-orders')->with('success', 'JSON for all delivered and cancelled orders are being generated in the server!');
        
    }

    protected function order_generate_json(Request $request)
    {
        
        // Dispatch the batch job with the array
        $order_id = $request->input('order_id');
        $order = Order::find($order_id);
        if ($order) {
            ProcessOrderJson::dispatch($order_id);
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success"
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Not found"
            ]);
        }
        
    }

    //Ajax
    protected function cancel_order_detail(Request $request){
        
        $data = $this->get_wallet_to_refund($request->input('order_id'));

        return response()->json([
            'error' => false,
            'data' => $data,
            'status_code' => 200,
            'message' => "Success"
        ]);
    }
}
