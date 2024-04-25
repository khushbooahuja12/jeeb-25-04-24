<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin;
use App\Model\Driver;
use App\Model\Vendor;
use App\Model\User;
use App\Model\Order;
use App\Model\BaseProduct;
use App\Model\Storekeeper;
use App\Model\UserOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Model\VendorOrder;
use Carbon\Carbon;

class DashboardController extends Controller
{

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

    protected function otps(Request $request)
    {
        $otps = UserOtp::where(['user_type' => 1, 'type' => 1])->orderBy('updated_at', 'desc')->limit(15)->get();
        $otps_arr = [];
        if ($otps->count()) {
            foreach ($otps as $key => $value) {
                $otps_arr[$key] = [
                    'mobile' => '+' . $value->getUser->country_code . ' ' . $value->getUser->mobile,
                    'otp' => $value->otp_number,
                    'is_used' => $value->is_used,
                    'expiry_date' => $value->expiry_date,
                ];
            }
        }
        pp(json_encode($otps_arr, JSON_PRETTY_PRINT));
    }

    protected function index(Request $request)
    {
        $storekeeper_count = Storekeeper::where(['deleted' => 0])->count();
        $driver_count = Driver::where(['deleted' => 0])->count();
        $user_count = User::count();
        $user_verified_count = User::where(['status' => 1])->count();
        $user_non_verified_count = User::where(['status' => 0])->count();
        $orders_count = Order::where('status','=',7)
            ->where(function ($query) {
                $query->where('test_order', '=', 0)
                    ->orWhereNull('test_order');
            })
            ->where('parent_id', '=', 0)
            ->count();
        $orders_count_inprogress = Order::whereIn('status',[1,2,3,5,6])
            ->where(function ($query) {
                $query->where('test_order', '=', 0)
                    ->orWhereNull('test_order');
            })
            ->where('parent_id', '=', 0)
            ->count();
        $orders_count_cancelled = Order::where('status','=','4')
            ->where(function ($query) {
                $query->where('test_order', '=', 0)
                    ->orWhereNull('test_order');
            })
            ->where('parent_id', '=', 0)
            ->count();
        $products_count = BaseProduct::where('deleted', '=', 0)
            ->count();
        $products_count_active = BaseProduct::where('deleted', '=', 0)
            ->where('product_store_stock', '>', 0)
            ->count();

        return view('admin.dashboard.index', [
            'storekeeper_count' => $storekeeper_count,
            'driver_count' => $driver_count,
            'user_count' => $user_count,
            'user_verified_count' => $user_verified_count,
            'user_non_verified_count' => $user_non_verified_count,
            'orders_count' => $orders_count,
            'orders_count_inprogress' => $orders_count_inprogress,
            'orders_count_cancelled' => $orders_count_cancelled,
            'products_count' => $products_count,
            'products_count_active' => $products_count_active
        ]);
    }

    protected function index_old(Request $request)
    {
        $slots = \App\Model\DeliverySlot::orderBy('date', 'desc')
            ->get();

        if ($slots->count()) {
            $slot_arr = [];
            foreach ($slots as $key => $value) {
                $isOrderAvailable = Order::join('order_delivery_slots', 'orders.id', '=', 'order_delivery_slots.fk_order_id')
                    ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
                    ->leftJoin('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
                    ->select('orders.id', 'orders.status', 'order_delivery_slots.delivery_date', 'order_delivery_slots.delivery_slot')
                    ->where('order_delivery_slots.delivery_date', '=', $value->date)
                    ->where('order_delivery_slots.delivery_slot', '=', date('H:i', strtotime($value->from)) . '-' . date('H:i', strtotime($value->to)))
                    ->where('order_payments.status', '!=', 'rejected')
                    ->get();

                if ($isOrderAvailable->count()) {
                    $slot_arr[$key] = [
                        'date' => $value->date,
                        'from' => $value->from,
                        'to' => $value->to
                    ];
                }
            }
        }
        // pp($slot_arr);

        return view('admin.dashboard.index', [
            'slots' => $slot_arr,
            'current_time' => \Carbon\Carbon::now()->format('H:i:s'),
            'current_date' => \Carbon\Carbon::now()->format('Y-m-d')
        ]);
    }

    protected function recent_orders(Request $request, $slot = null)
    {
        $slot = base64url_decode($slot);

        $drivers = Driver::orderBy('name', 'asc')->get();

        $orders = \App\Model\Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
            ->leftJoin('order_drivers', 'orders.id', '=', 'order_drivers.fk_order_id')
            ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
            ->select("orders.*", "order_drivers.fk_driver_id", "order_drivers.status as assign_status")
            ->where('order_payments.status', '!=', 'rejected')
            ->where('orders.parent_id', '=', 0);

        if ($slot != '') {
            $date_and_slot = explode('|', $slot);

            $delivery_slot_arr = explode('-', $date_and_slot[1]);
            $new_slot = date('H:i', strtotime($delivery_slot_arr[0])) . '-' . date('H:i', strtotime($delivery_slot_arr[1]));

            $orders = $orders->where('order_delivery_slots.delivery_slot', '=', $new_slot)
                ->where('order_delivery_slots.delivery_date', '=', $date_and_slot[0]);
        }

        $orders = $orders
            ->orderBy('order_drivers.created_at', 'desc')
            ->get();

        return view('admin.dashboard.recent_orders', [
            'orders' => $orders,
            'drivers' => $drivers,
            'slot' => base64url_encode($slot),
            'slot_date_time' => $slot ? date('d-m-Y', strtotime($date_and_slot[0])) . ' ' . date('H:i A', strtotime($delivery_slot_arr[0])) . ' to ' . date('H:i A', strtotime($delivery_slot_arr[1])) : 'N/A',
            'order_status' => $this->order_status
        ]);
    }

    protected function update_assigned_driver(Request $request)
    {
        $errors = [];
        $counts = array_count_values($request->input('driverIds'));

        $drivers = Driver::leftJoin('vehicles', 'drivers.fk_vehicle_id', '=', 'vehicles.id')
            ->select('drivers.id', 'drivers.name', 'drivers.country_code', 'drivers.mobile', 'vehicles.vehicle_capacity')
            ->get();
        foreach ($drivers as $key => $value) {
            if (isset($counts[$value->id]) && ($counts[$value->id] > $value->vehicle_capacity)) {
                $driver_name = $value->name ? $value->name . ', +' . $value->country_code . '-' . $value->mobile : '+' . $value->country_code . '-' . $value->mobile;
                array_push($errors, "$driver_name has vehicle capacity $value->vehicle_capacity but you are trying to assign " . $counts[$value->id] . " orders");
            }
        }
        if ($errors) {
            return back()->withInput()->with('errors', $errors);
        }

        if ($request->input('driverIds')) {
            foreach ($request->input('driverIds') as $key => $value) {
                // $isExistInAssignedTable = \App\Model\OrderDriver::where(['fk_order_id' => $request->input('orderIds')[$key]])->first();

                $updateArr = [
                    'fk_driver_id' => $value,
                    'status' => $value != 0 ? 1 : 0
                ];
                if (!empty($request->input('orderIds')[$key])) {
                    \App\Model\OrderDriver::where(['fk_order_id' => $request->input('orderIds')[$key]])
                        ->update($updateArr);
                }

                if ($value != 0) {
                    if (!empty($request->input('orderIds')[$key])) {
                        \App\Model\Order::find($request->input('orderIds')[$key])->update(['status' => 2]);
                        \App\Model\OrderStatus::create([
                            'fk_order_id' => $request->input('orderIds')[$key],
                            'status' => 2
                        ]);

                        $vendor_order = VendorOrder::where(['fk_order_id' => $request->input('orderIds')[$key]])->first();
                        if (!$vendor_order) {
                            VendorOrder::create([
                                'fk_order_id' => $request->input('orderIds')[$key],
                                'fk_vendor_id' => 1,
                                'order_link' => '/vendor/' . base64url_encode($request->input('orderIds')[$key])
                            ]);
                        }
                    }

                    updateDriverAvailability($value);
                }
            }
            event(new \App\Events\MyEvent('New order assigned'));

            return redirect('admin/recent-orders/' . $request->input('slot'))->with('success', 'Updated successfully!');
        }
    }

    protected function change_password(Request $request)
    {
        return view('admin.dashboard.change_password');
    }

    public function update(Request $request)
    {
        $id = 1;
        $admin = Admin::find($id);
        if ($admin) {
            if (!Hash::check($request->input('current_password'), $admin->password)) {
                return back()->withInput()->with('error', 'Invalid Current Password');
            } else {
                $update_arr = [
                    'password' => Hash::make($request->input('set_password'))
                ];
                $update = Admin::find($id)->update($update_arr);
                if ($update) {
                    return redirect('admin/changepassword')->with('success', 'Password updated successfully');
                }
            }
        }
        return back()->withInput()->with('error', 'Error while updating Password');
    }
}
