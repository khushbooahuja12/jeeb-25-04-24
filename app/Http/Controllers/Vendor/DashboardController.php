<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Admin;
use App\Model\Vendor;
use App\Model\User;
use App\Model\Order;
use App\Model\UserOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Model\VendorOrder;
use App\Model\Store;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

    protected function index()
    {

        $store_id = Auth::guard('vendor')->user()->store_id;
        // $vendor = Auth::guard('vendor')->user()

        $store = Store::find(Auth::guard('vendor')->user()->store_id);

        // $all_products = BaseProduct::join('base_products_store','base_products_store.fk_product_id','=','base_products.id')
        //     ->join('categories','base_products.fk_sub_category_id','=','categories.id')
        //     ->where('base_products.parent_id', '=', 0)
        //     ->where('base_products.product_type', '=', 'product')
        //     ->where('base_products_store.fk_store_id', '=', $store_id)
        //     ->where('base_products.fk_store_id', '=', $store_id)
        //     ->count();
        $all_products = BaseProduct::where('base_products.parent_id', '=', 0)
            ->where('base_products.product_type', '=', 'product')
            ->where('base_products.fk_store_id', '=', $store_id)
            ->count();

        $vendors = Vendor::all()->count();

        // $instock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
        //     ->join('categories','base_products.fk_sub_category_id','=','categories.id')
        //     ->where('base_products.parent_id', '=', 0)
        //     ->where('base_products.product_type', '=', 'product')
        //     ->where('base_products_store.fk_store_id', '=', $id)
        //     ->where('base_products_store.stock', '>', 0)
        //     ->count();

        // $outofstock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
        //     ->join('categories','base_products.fk_sub_category_id','=','categories.id')
        //     ->where('base_products.parent_id', '=', 0)
        //     ->where('base_products.product_type', '=', 'product')
        //     ->where('base_products_store.fk_store_id', '=', $id)
        //     ->where('base_products_store.stock', '=', 0)
        //     ->count();

        $order_count = Order::where('orders.fk_store_id', '=', $store_id)->count();

        $total_orders = Order::where('orders.fk_store_id', '=', $store_id)
            ->orderBy('created_at', 'desc')->take(10)->get();


        return view(
            'vendor.dashboard.index',
            [
                'all_products' => $all_products,
                'order_status' => $this->order_status,
                'store' => $store,
                'total_orders' => $total_orders,
                'order_count' => $order_count,
                'vendors' => $vendors
            ]
        );
    }

    protected function change_password(Request $request)
    {
        return view('vendor.dashboard.change_password');
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

    public function analytics(Request $request)
    {

        $store_id = Auth::guard('vendor')->user()->store_id;


        $topProductsinCart = BaseProduct::join('order_products', 'base_products.id', '=', 'order_products.fk_product_id')
            ->where('base_products.fk_store_id', '=', $store_id)
            ->select('base_products.*', DB::raw('COUNT(order_products.fk_product_id) as total_orders'))
            ->groupBy('base_products.id')
            ->orderByDesc('total_orders')
            ->limit(10)
            ->get();

        $topSoldProducts = BaseProduct::leftJoin('order_products', 'base_products.id', '=', 'order_products.fk_product_id')
            ->leftJoin('orders', 'order_products.fk_order_id', '=', 'orders.id')
            ->where('orders.fk_store_id', '=', $store_id)
            ->select('base_products.id', 'base_products.product_name_en','base_products.unit', 'base_products.base_price' , DB::raw('SUM(IF(orders.status = "7", 1, 0)) as total_sold'))
            ->groupBy('base_products.id', 'base_products.product_name_en')
            ->limit(10)
            ->get();

        //, DB::raw('SUM(IF(orders.status = "4", 1, 0)) as total_in_cart')
        // foreach($topSoldProducts as $val){

        //     echo '<pre>'; print_r($val);die;
        // }
            
        $order_counts = DB::table('orders')
        ->selectRaw('SUM(CASE WHEN payment_type = "online" THEN 1 ELSE 0 END) as online_count, 
        SUM(CASE WHEN payment_type = "offline" THEN 1 ELSE 0 END) as offline_count')
        ->first();
        
        // print_r($order_counts);die;
    
        return view('vendor.analytics.index', 
        ['topSoldProducts' => $topSoldProducts, 'topProductsinCart' => $topProductsinCart, 'order_counts' => $order_counts]);

    }

    
}
