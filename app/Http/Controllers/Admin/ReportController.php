<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Driver;
use App\Model\User;
use App\Model\Order;
use App\Model\BaseProduct;
use App\Model\Store;
use App\Model\Storekeeper;
use App\Model\Category;
use App\Model\UserOtp;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
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

    protected function index(Request $request)
    {
        $storekeeper_count = Storekeeper::where(['deleted' => 0])->count();
        $driver_count = Driver::where(['deleted' => 0])->count();
        $user_count = User::count();
        $user_verified_count = User::where(['status' => 1])->count();
        $user_non_verified_count = User::where(['status' => 0])->count();
        $orders_count = Order::where('status','=',7)
            ->where('parent_id', '=', 0)
            ->where(function ($query) {
                $query->where('test_order', '=', 0)
                    ->orWhereNull('test_order');
            })->count();
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
        $orders_count_test = Order::where(['status'=>7,'test_order'=>1])->count();
        $forgot_orders_count = DB::select("SELECT o.id, COUNT(o.id) as orders_count
                    FROM orders o 
                    INNER JOIN order_products op ON (op.fk_order_id = o.id AND op.is_missed_product=1)
                    WHERE o.status = 7 AND o.parent_id = 0
                    GROUP BY o.id");
        $forgot_orders_count =  $forgot_orders_count ? count($forgot_orders_count) : 0;
        $forgot_products_count = DB::select("SELECT o.id, COUNT(op.id) as products_count
                    FROM orders o 
                    INNER JOIN order_products op ON (op.fk_order_id = o.id AND op.is_missed_product=1)
                    WHERE o.status = 7 AND o.parent_id = 0");
        $forgot_products_count = isset($forgot_products_count) && isset($forgot_products_count[0]) ? $forgot_products_count[0]->products_count : 0;
        // dd($forgot_products_count);
        $products_count = BaseProduct::where('deleted', '=', 0)
            ->count();
        $products_count_active = BaseProduct::where('deleted', '=', 0)
            ->where('product_store_stock', '>', 0)
            ->count();

        // Filter date
        $dates = [];
        for ($i=0; $i < 10; $i++) { 
            $dates[] = date('Y-m-d',strtotime(date('d-m-Y').' -'.$i.' days'))." ";
        }
        $filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $filter_date_next_day = date('Y-m-d', strtotime('+1 day', strtotime($filter_date)));
        if ($filter_date==date('Y-m-d')) {
            $filter_date_text = 'Today';
        } elseif ($filter_date==date('Y-m-d',strtotime(date('d-m-Y').' -1 days'))."") {
            $filter_date_text = 'Yesterday';
        // } elseif ($filter_date==date('Y-m-d',strtotime(date('d-m-Y').' -2 days'))."") {
        //     $filter_date_text = 'Day before yesterday';
        } else {
            $filter_date_text = 'On '.$filter_date;
        }

        $users_count_today = User::where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();
        $users_verified_count_today = User::where(['status' => 1])
            ->where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();
        $users_non_verified_count_today = User::where(['status' => 0])
            ->where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();
        $products_count_today = BaseProduct::where('deleted', '=', 0)
            ->where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();

        // All orders count and amount
        $orders_count_today = DB::select("SELECT COUNT(id) as count
                    FROM orders 
                    WHERE 
                        (status = 1 OR status = 2 OR status = 3 OR status = 5 OR status = 6 OR status = 7) AND 
                        parent_id = 0 AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_count_today = $orders_count_today && isset($orders_count_today[0]) ? $orders_count_today[0]->count : 0;
        $orders_total_today = DB::select("SELECT SUM(sub_total) as sub_total
                    FROM orders 
                    WHERE 
                        (status = 1 OR status = 2 OR status = 3 OR status = 5 OR status = 6 OR status = 7) AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_total_today = $orders_total_today && isset($orders_total_today[0]) ? $orders_total_today[0]->sub_total : 0;
        $orders_count_today = Order::whereIn('status',[1,2,3,5,6,7])
            ->where('parent_id', '=', 0)
            ->where(function ($query) {
                $query->where('test_order', '=', 0)
                    ->orWhereNull('test_order');
            })
            ->where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();

        // Delivered orders count and amount
        $orders_count_today_delivered = DB::select("SELECT COUNT(id) as count
                    FROM orders 
                    WHERE 
                        status = 7 AND 
                        parent_id = 0 AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_count_today_delivered = $orders_count_today_delivered && isset($orders_count_today_delivered[0]) ? $orders_count_today_delivered[0]->count : 0;
        $orders_total_today_delivered = DB::select("SELECT SUM(sub_total) as sub_total
                    FROM orders 
                    WHERE 
                        status = 7 AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_total_today_delivered = $orders_total_today_delivered && isset($orders_total_today_delivered[0]) ? $orders_total_today_delivered[0]->sub_total : 0;
       
        // Inprogress orders count and amount
        $orders_count_today_inprogress = $orders_count_today - $orders_count_today_delivered;
        $orders_total_today_inprogress = $orders_total_today - $orders_total_today_delivered;
        
        // Cancelled orders count and amount
        $orders_count_today_cancelled = DB::select("SELECT COUNT(id) as count
                    FROM orders 
                    WHERE 
                        status = 4 AND 
                        parent_id = 0 AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_count_today_cancelled = $orders_count_today_cancelled && isset($orders_count_today_cancelled[0]) ? $orders_count_today_cancelled[0]->count : 0;
        $orders_total_today_cancelled = DB::select("SELECT SUM(sub_total) as sub_total
                    FROM orders 
                    WHERE 
                        status = 4 AND 
                        (test_order=0 OR test_order IS NULL)  AND 
                        created_at >= '$filter_date 00:00:00'  AND 
                        created_at > '$filter_date_next_day 00:00:00'");
        $orders_total_today_cancelled = $orders_total_today_cancelled && isset($orders_total_today_cancelled[0]) ? $orders_total_today_cancelled[0]->sub_total : 0;
       
        // Test orders
        $orders_count_today_test = Order::where('test_order', '=', 1)
            ->where('parent_id', '=', 0)
            ->where('created_at', '>=', $filter_date.' 00:00:00')
            ->where('created_at', '<', $filter_date_next_day.' 00:00:00')
            ->count();

        $time_now = date('Y-m-d H:i:s');

        return view('admin.report.index', [
            'storekeeper_count' => $storekeeper_count,
            'driver_count' => $driver_count,
            'user_count' => $user_count,
            'user_verified_count' => $user_verified_count,
            'user_non_verified_count' => $user_non_verified_count,
            'orders_count' => $orders_count,
            'orders_count_inprogress' => $orders_count_inprogress,
            'orders_count_cancelled' => $orders_count_cancelled,
            'orders_count_test' => $orders_count_test,
            'forgot_orders_count' => $forgot_orders_count,
            'forgot_products_count' => $forgot_products_count,
            'products_count' => $products_count,
            'products_count_active' => $products_count_active,
            'products_count_today' => $products_count_today,
            'orders_count_today' => $orders_count_today,
            'orders_total_today' => $orders_count_today,
            'orders_count_today_delivered' => $orders_count_today_delivered,
            'orders_total_today_delivered' => $orders_total_today_delivered,
            'orders_count_today_inprogress' => $orders_count_today_inprogress,
            'orders_total_today_inprogress' => $orders_total_today_inprogress,
            'orders_count_today_cancelled' => $orders_count_today_cancelled,
            'orders_total_today_cancelled' => $orders_total_today_cancelled,
            'orders_count_today_test' => $orders_count_today_test,
            'users_count_today' => $users_count_today,
            'users_verified_count_today' => $users_verified_count_today,
            'users_non_verified_count_today' => $users_non_verified_count_today,
            'dates' => $dates,
            'filter_date' => $filter_date,
            'filter_date_text' => $filter_date_text,
            'time_now' => $time_now
        ]);
    }

    public function stores(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
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

        return view('admin.report.stores', ['stores' => $stores, 'filter' => $filter]);
    }

    public function store_categories($id = null)
    {
        $store = Store::find($id);
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.report.store_categories', ['store' => $store, 'categories' => $categories]);
    }

}
