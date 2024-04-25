<?php

namespace App\Http\Controllers\Vendor;

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
use Illuminate\Support\Facades\Auth;

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
        
        $vendor = Auth::guard('vendor')->user();

        $order_type = $request->query('order_type') ? $request->query('order_type') : 0;
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.expected_eta', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('orders.fk_store_id','=', $vendor->store_id)
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
                ->where('orders.fk_store_id','=', $vendor->store_id)
                ->where('orders.parent_id', '=', 0)
                ->whereIn('orders.status',[1,2,3,5,6])
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('vendor.orders.index', [
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

    protected function completed_orders(Request $request){

        $vendor = Auth::guard('vendor')->user();

        $order_type = $request->query('order_type') ? $request->query('order_type') : 0;
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.expected_eta', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('orders.fk_store_id','=', $vendor->store_id)->Andwhere('orders.status','=', 7)
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
                $orders = $orders->where('orders.status','=', 7);
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
                ->where('orders.fk_store_id','=', $vendor->store_id)->where('orders.status','=',7)
                ->where('orders.parent_id', '=', 0)
                
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        // print_r($orders);die;
        return view('vendor.orders.index', [
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

    protected function canceled_orders(Request $request){

        $vendor = Auth::guard('vendor')->user();

        $order_type = $request->query('order_type') ? $request->query('order_type') : 0;
        if ($_GET) {
            $orders = Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
                ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.expected_eta', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
                ->where('orders.fk_store_id','=', $vendor->store_id)->where('orders.status','=', 4)
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
                $orders = $orders->where('orders.status','=', 4);
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
                ->where('orders.fk_store_id','=', $vendor->store_id)->where('orders.status','=',4)
                ->where('orders.parent_id', '=', 0)
                
                ->where(function($query) {
                    $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(50);
        }

        return view('vendor.orders.index', [
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
            
            ->orderBy('base_products.product_name_en', 'asc')
            ->get();
        

        return view('vendor.orders.order_detail', [
            'order' => $order,
            'order_status' => $this->order_status,
            'drivers' => $drivers,
            'totalOutOfStockProduct' => $totalOutOfStockProduct,
            'sub_order' => $sub_order,
            'scratch_cards' => $scratch_cards,
            'replaced_products' => $replaced_products
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

        if ($order->status!=$order_status) {
            return back()->withInput()->with('error', 'Error while updating status');
        } else {
            return redirect('vendor/orders/detail/' . base64url_encode($id))->with('success', 'Status updated successfully');
        }
    }

    protected function ordered_product_detail($id = null)
    {
        $id = base64url_decode($id);
        $op = OrderProduct::find($id);

        return view('vendor.orders.ordered_product_detail', [
            'op' => $op,
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
        // print_r($driver);die;
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
                return redirect('vendor/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
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
                return redirect('vendor/orders/detail/' . base64url_encode($id))->with('success', 'Driver assigned successfully');
            } else {
                return back()->withInput()->with('error', 'Error while assigning status');
            }
        }

        return back()->withInput()->with('error', 'Error while assigning status');
    }


}
