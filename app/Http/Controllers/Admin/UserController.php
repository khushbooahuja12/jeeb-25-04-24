<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\DeletedUser;
use Illuminate\Support\Facades\DB;
use App\Model\UserAddress;
use App\Model\UserCart;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\InstantStoreGroup;
use App\Model\UserWallet;
use App\Model\UserWalletPayment;
use App\Model\ScratchCardUser;
use App\Model\ScratchCard;

class UserController extends CoreApiController {

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $filter = $request->query('filter');
        $user_id = $request->query('user_id');
        $minimum_no_of_orders = $request->query('minimum_no_of_orders');
        $maximum_no_of_orders = $request->query('maximum_no_of_orders');
        $minimum_amount_of_orders = $request->query('minimum_amount_of_orders');
        $maximum_amount_of_orders = $request->query('maximum_amount_of_orders');
        $test_numbers = explode(',', env("TEST_NUMBERS"));
        $order_delivered_status = 7;

        $users = User::leftJoin(DB::raw("(SELECT id, user_id, COUNT(id) as tracking_items_num FROM user_tracking WHERE `key` IN ('update_cart','update_cart_bp','make_order','make_order_bp') GROUP BY user_id) as user_tracking"), 'users.id', 'user_tracking.user_id')
                ->leftJoin(DB::raw('(SELECT orders.id, orders.fk_user_id, orders.status, COUNT(orders.id) as orders_num, SUM(COALESCE(orders.total_amount,0)) as orders_sum, SUM(COALESCE(sub_orders.total_amount,0)) as sub_orders_sum, SUM(COALESCE(orders.total_amount,0)) + SUM(COALESCE(sub_orders.total_amount,0)) as all_orders_sum FROM orders LEFT JOIN orders AS sub_orders ON (orders.id = sub_orders.parent_id AND sub_orders.status=1) WHERE orders.status='.$order_delivered_status.' GROUP BY orders.fk_user_id) as parent_orders'), 'users.id', 'parent_orders.fk_user_id')
                ->select('users.*', 
                    DB::raw('user_tracking.tracking_items_num as no_of_tracking'), 
                    DB::raw('parent_orders.orders_num as no_of_orders'), 
                    DB::raw('parent_orders.orders_sum AS sum_of_orders'), 
                    DB::raw('parent_orders.sub_orders_sum AS sum_of_sub_orders'), 
                    DB::raw('parent_orders.all_orders_sum AS sum_of_all_orders'), 
                )
                ->groupBy('users.id')
                ->sortable(['id' => 'desc']);
        if (!empty($user_id)) {
            $users = $users->where('users.id', '=', $user_id);
        } elseif (!empty($filter)) {
            $users = $users->where('name', 'like', '%' . $filter . '%')
                    ->orWhere('email', 'like', '%' . $filter . '%')
                    ->orWhere('mobile', 'like', '%' . $filter . '%');
        } else {
            $users = $users->whereNotIn('users.mobile', $test_numbers);
        }
        if (!empty($minimum_no_of_orders)) {
            $users = $users->having('no_of_orders', '>=', $minimum_no_of_orders);
        } 
        if (!empty($maximum_no_of_orders)) {
            $users = $users->having('no_of_orders', '<=', $maximum_no_of_orders);
        } 
        if (!empty($minimum_amount_of_orders)) {
            $users = $users->having('sum_of_all_orders', '>=', $minimum_amount_of_orders);
            // $users = $users->havingRaw('SUM(sum_of_orders + sum_of_sub_orders) >= '.$minimum_amount_of_orders);
        } 
        if (!empty($maximum_amount_of_orders)) {
            $users = $users->having('sum_of_all_orders', '<=', $maximum_amount_of_orders);
            // $users = $users->havingRaw('SUM(sum_of_orders + sum_of_sub_orders) <= '.$maximum_amount_of_orders);
        } 
        $users = $users->paginate(10);

        $users_count = User::selectRaw("SUM(IF(lang_preference = 'en', 1, 0)) AS english_users, SUM(IF(lang_preference = 'ar', 1, 0)) AS arabic_users, SUM(IF(lang_preference <> '', 1, 0)) AS all_users")
                ->first();

        return view('admin.users.index', [
            'users' => $users, 
            'filter' => $filter,
            'minimum_no_of_orders' => $minimum_no_of_orders, 
            'maximum_no_of_orders' => $maximum_no_of_orders, 
            'minimum_amount_of_orders' => $minimum_amount_of_orders, 
            'maximum_amount_of_orders' => $maximum_amount_of_orders, 
            'user_id' => $user_id,
            'users_count' => $users_count, 
            'order_status' => $this->order_status
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_abondoned_cart(Request $request) {
        $filter = $request->query('filter');
        $user_id = $request->query('user_id');
        $test_numbers = explode(',', env("TEST_NUMBERS"));
        $minimum_no_of_orders = $request->query('minimum_no_of_orders');
        $maximum_no_of_orders = $request->query('maximum_no_of_orders');
        $minimum_amount_of_orders = $request->query('minimum_amount_of_orders');
        $maximum_amount_of_orders = $request->query('maximum_amount_of_orders');
        $order_delivered_status = 7;

        $users = User::leftJoin(DB::raw('(SELECT id, fk_user_id, COUNT(id) as cart_items_num FROM user_cart GROUP BY fk_user_id) as user_cart'), 'users.id', 'user_cart.fk_user_id')
                ->leftJoin(DB::raw('(SELECT orders.id, orders.fk_user_id, orders.status, COUNT(orders.id) as orders_num, SUM(COALESCE(orders.total_amount,0)) as orders_sum, SUM(COALESCE(sub_orders.total_amount,0)) as sub_orders_sum, SUM(COALESCE(orders.total_amount,0)) + SUM(COALESCE(sub_orders.total_amount,0)) as all_orders_sum FROM orders LEFT JOIN orders AS sub_orders ON (orders.id = sub_orders.parent_id AND sub_orders.status=1) WHERE orders.status='.$order_delivered_status.' GROUP BY orders.fk_user_id) as parent_orders'), 'users.id', 'parent_orders.fk_user_id')
                ->select('users.*', 
                    DB::raw('user_cart.cart_items_num as cart_items'), 
                    DB::raw('parent_orders.orders_num as no_of_orders'), 
                    DB::raw('parent_orders.orders_sum AS sum_of_orders'), 
                    DB::raw('parent_orders.sub_orders_sum AS sum_of_sub_orders')
                )
                ->groupBy('users.id')
                ->sortable(['id' => 'desc']);
        if (!empty($user_id)) {
            $users = $users->where('users.id', '=', $user_id);
        } elseif (!empty($filter)) {
            $users = $users->where('name', 'like', '%' . $filter . '%')
                    ->orWhere('email', 'like', '%' . $filter . '%')
                    ->orWhere('mobile', 'like', '%' . $filter . '%');
        } else {
            $users = $users->whereNotIn('users.mobile', $test_numbers);
            $users = $users->having('cart_items', '>', 0);
        }
        if (!empty($minimum_no_of_orders)) {
            $users = $users->having('no_of_orders', '>=', $minimum_no_of_orders);
        } 
        if (!empty($maximum_no_of_orders)) {
            $users = $users->having('no_of_orders', '<=', $maximum_no_of_orders);
        } 
        if (!empty($minimum_amount_of_orders)) {
            $users = $users->having('sum_of_all_orders', '>=', $minimum_amount_of_orders);
            // $users = $users->havingRaw('SUM(sum_of_orders + sum_of_sub_orders) >= '.$minimum_amount_of_orders);
        } 
        if (!empty($maximum_amount_of_orders)) {
            $users = $users->having('sum_of_all_orders', '<=', $maximum_amount_of_orders);
            // $users = $users->havingRaw('SUM(sum_of_orders + sum_of_sub_orders) <= '.$maximum_amount_of_orders);
        } 
        $users = $users->paginate(10);

        return view('admin.users.index_abondoned_cart', [
            'users' => $users, 
            'filter' => $filter,
            'minimum_no_of_orders' => $minimum_no_of_orders, 
            'maximum_no_of_orders' => $maximum_no_of_orders, 
            'minimum_amount_of_orders' => $minimum_amount_of_orders, 
            'maximum_amount_of_orders' => $maximum_amount_of_orders, 
            'user_id' => $user_id,
            'order_status' => $this->order_status
        ]);
    }

    public function show($id = null) {
        $user = User::find($id);
        if (!$user) {
            die('User not found!');
        }
        $user_address = UserAddress::where(['fk_user_id'=>$user->id,'deleted'=>0])->orderBy('id','desc')->get();
        $store_group_hubs = InstantStoreGroup::join('stores','instant_store_groups.fk_hub_id','=','stores.id')->where('instant_store_groups.deleted','=',0)->get();
        $orders = Order::where('fk_user_id',$user->id)->where('status',7)->orderBy('id','desc')->get();
        $tracking_data = \App\Model\UserTracking::where('user_id',$user->id)->orderBy('id','desc')->paginate(30);
        $wallet = UserWallet::where('fk_user_id',$user->id)->first();
        $wallet_payments = UserWalletPayment::where('fk_user_id',$user->id)->orderBy('id','desc')->get();
        $scratch_card_users = ScratchCardUser::where(['fk_user_id'=>$user->id,'deleted'=>0])->orderBy('id','desc')->get();
        $scratch_cards = ScratchCard::where(['deleted'=>0,'status'=>1])->orderBy('id', 'desc')->get();
        $cart_items = UserCart::join('base_products_store','base_products_store.id','=','user_cart.fk_product_store_id')
                            ->selectRaw('user_cart.*, base_products_store.product_name_en, base_products_store.unit, base_products_store.product_store_price')
                            ->where('user_cart.fk_user_id','=',$user->id)
                            ->orderBy('id','desc')
                            ->get();
        $ordered_items = OrderProduct::join('orders','orders.id','=','order_products.fk_order_id')
                            ->selectRaw('order_products.*, orders.orderId, orders.status')
                            ->where('orders.fk_user_id','=',$user->id)
                            // ->where('orders.status','=',7)
                            ->orderBy('id','desc')
                            ->get();
        return view('admin.users.show', [
            'user' => $user, 
            'user_address' => $user_address, 
            'store_group_hubs' => $store_group_hubs, 
            'tracking_data' => $tracking_data, 
            'orders' => $orders, 
            'order_status' => $this->order_status, 
            'full_view' => true, 
            'wallet' => $wallet, 
            'wallet_payments' => $wallet_payments, 
            'scratch_card_users' => $scratch_card_users, 
            'scratch_cards' => $scratch_cards, 
            'cart_items' => $cart_items, 
            'ordered_items' => $ordered_items,
            'order_status' => $this->order_status
        ]);
    }

    public function show_by_mobile($mobile) {
        $user = User::where(['mobile'=>$mobile])->first();
        if (!$user) {
            die('User not found!');
        }
        $user_address = UserAddress::where(['fk_user_id'=>$user->id,'deleted'=>0])->orderBy('id','desc')->get();
        $store_group_hubs = InstantStoreGroup::join('stores','instant_store_groups.fk_hub_id','=','stores.id')->where('instant_store_groups.deleted','=',0)->get();
        $orders = Order::where('fk_user_id',$user->id)->where('status',7)->orderBy('id','desc')->get();
        $tracking_data = \App\Model\UserTracking::where('user_id',$user->id)->orderBy('id','desc')->paginate(30);
        $wallet = UserWallet::where('fk_user_id',$user->id)->first();
        $wallet_payments = UserWalletPayment::where('fk_user_id',$user->id)->orderBy('id','desc')->get();
        $scratch_card_users = ScratchCardUser::where(['fk_user_id'=>$user->id,'deleted'=>0])->orderBy('id','desc')->get();
        $scratch_cards = ScratchCard::where(['deleted'=>0,'status'=>1])->orderBy('id', 'desc')->get();
        $cart_items = UserCart::join('base_products_store','base_products_store.id','=','user_cart.fk_product_store_id')
                            ->selectRaw('user_cart.*, base_products_store.product_name_en, base_products_store.unit, base_products_store.product_store_price')
                            ->where('user_cart.fk_user_id','=',$user->id)
                            ->orderBy('id','desc')
                            ->get();
        $ordered_items = OrderProduct::join('orders','orders.id','=','order_products.fk_order_id')
                            ->selectRaw('order_products.*, orders.orderId, orders.status')
                            ->where('orders.fk_user_id','=',$user->id)
                            // ->where('orders.status','=',7)
                            ->orderBy('id','desc')
                            ->get();
        return view('admin.users.show', [
            'user' => $user, 
            'user_address' => $user_address, 
            'store_group_hubs' => $store_group_hubs, 
            'tracking_data' => $tracking_data, 
            'orders' => $orders, 
            'order_status' => $this->order_status, 
            'full_view' => true, 
            'wallet' => $wallet, 
            'wallet_payments' => $wallet_payments, 
            'scratch_card_users' => $scratch_card_users, 
            'scratch_cards' => $scratch_cards, 
            'cart_items' => $cart_items, 
            'ordered_items' => $ordered_items,
            'order_status' => $this->order_status
        ]);
    }

    public function enable_user_features(Request $request) {
        $id = $request->input('id');
        $features_enabled = $request->input('features');
        $features_enabled_str = '';
        if (is_array($features_enabled)) {
            foreach ($features_enabled as $value) {
                $features_enabled_str .= trim($value).',';
            }
        } else {
            $features_enabled_str .= $features_enabled.',';
        }
        $update = User::find($id)->update(['features_enabled' => $features_enabled_str]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Features updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating features']);
        }
    }

    public function change_user_status(Request $request) {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = User::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    protected function delete(Request $request, $id = null) {
        if ($id != '') {
            $id = base64url_decode($id);

            \App\Model\UserPreference::where(['fk_user_id' => $id])->delete();
            \App\Model\UserOtp::where(['user_id' => $id])->delete();
            \App\Model\UserNotification::where(['fk_user_id' => $id])->delete();
            \App\Model\UserFeedback::where(['fk_user_id' => $id])->delete();
            \App\Model\UserDeviceDetail::where(['fk_user_id' => $id])->delete();
            \App\Model\UserCart::where(['fk_user_id' => $id])->delete();
            \App\Model\UserAddress::where(['fk_user_id' => $id])->delete();

            DB::table('oauth_access_tokens')
                    ->where('user_id', $id)
                    ->delete();

            $user = User::find($id);
            $delete = $user ? $user->delete() : false;
            if ($delete) {
                $user_arr = $user->toArray();
                $user_arr['reason'] = "Deleted by Admin";
                DeletedUser::insert($user_arr);
                $user->delete();
                return redirect('admin/users')->with('success', 'User deleted!');
            }
            return back()->withInput()->with('error', 'Error while deleting user');
        } else {
            return back()->withInput()->with('error', 'Error while deleting user');
        }
    }
    
    // Send Push Notification via AJAX
    public function send_push_notification(Request $request) {
        $title = $request->input('title');
        $body = $request->input('body');
        $user_id = $request->input('user_id');

        $deviceToken = \App\Model\UserDeviceDetail::select("fk_user_id","device_token")
        ->where('fk_user_id', $user_id)
        ->first();

        $data = [];
        $type = '1';
        $res = $deviceToken && $deviceToken->device_token ? $this->send_push_notification_to_user($deviceToken->device_token, $type, $title, $body, $data) : false;

        if ($res) {
            $notification = \App\Model\Notification::create([
                'title' => $title,
                'body' => $body,
                'userIds' => $user_id
            ]);
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Sent', 'data' => $notification]);
        }
        return response()->json(['status' => false, 'error_code' => 201, 'message' => $res]);
    }

    // Add Wallet via AJAX
    public function add_wallet(Request $request) {
        $wallet_to_refund = $request->input('wallet_amount');
        $secret_key = $request->input('secret_key');
        $user_id = $request->input('user_id');
        if ($wallet_to_refund=='' || $secret_key=='' || $user_id=='') {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please enter amount and secret key..']);
        }

        $wallet_password = \App\Model\AdminSetting::where('key', '=', 'wallet_password')->first();
        if ($wallet_password) {
            $wallet_password = $wallet_password->value;
            $secret_key = md5($secret_key.'123');
            if ($wallet_password!='' && $wallet_password==$secret_key) {
                $refunded = $this->makeRefund('wallet', $user_id, 0, $wallet_to_refund, 0, 6);
                if ($refunded) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Wallet added successfully', 'data' => $refunded]);
                } else {
                    return response()->json(['status' => true, 'error_code' => 201, 'message' => 'Wallet is not added successfully', 'data' => $refunded]);
                }
            }
        }

        return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Wrong secret key!']);
    }

    // Add Wallet via AJAX
    public function add_scratch_card(Request $request) {
        $scratch_card_id = $request->input('scratch_card_id');
        $secret_key = $request->input('secret_key');
        $user_id = $request->input('user_id');
        if ($scratch_card_id=='' || $secret_key=='' || $user_id=='') {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please select scratch card and enter secret key..']);
        }

        $wallet_password = \App\Model\AdminSetting::where('key', '=', 'wallet_password')->first();
        if ($wallet_password) {
            $wallet_password = $wallet_password->value;
            $secret_key = md5($secret_key.'123');
            if ($wallet_password!='' && $wallet_password==$secret_key) {
                $scratch_card = ScratchCard::where(['id'=>$scratch_card_id,'deleted'=>0])->first();
                if ($scratch_card) {
                    if ($scratch_card->scratch_card_type==0 && $scratch_card->type==1) {
                        return response()->json(['status' => true, 'error_code' => 201, 'message' => 'This Scratch Card is OnSpot percentage, only applicaple for orders', 'data' => $scratch_card]);
                    } else {
                        // Add Scratch Card to user as active and unscractched
                        $scratch_card_arr = $scratch_card->toArray();
                        $scratch_card_arr['id'] = NULL;
                        $scratch_card_arr['fk_user_id'] = $user_id;
                        $scratch_card_arr['fk_scratch_card_id'] = $scratch_card->id;
                        $scratch_card_arr['fk_order_id'] = 0;
                        $scratch_card_arr['status'] = 1;
                        if ($scratch_card->scratch_card_type==1) {
                            $scratch_card_arr['coupon_code'] = 'SC'.$user_id.time();
                        }
                        // Set expiry date
                        $expiry_date=date('Y-m-d');
                        $expiry_in = intval($scratch_card->expiry_in);
                        if ($expiry_in>0) {
                            $expiry_date = strtotime("+$expiry_in day", strtotime($expiry_date));
                            $expiry_date=date('Y-m-d',$expiry_date);
                        }
                        $scratch_card_arr['expiry_date'] = $expiry_date;
                        $scratch_card_added = ScratchCardUser::create($scratch_card_arr);
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'The Scratch Card is added successfully', 'data' => $scratch_card_added]);
                    }
                } else {
                    return response()->json(['status' => true, 'error_code' => 201, 'message' => 'The Scratch Card is not added successfully', 'data' => $scratch_card]);
                }
            }
        }

        return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Wrong secret key!']);
    }


}
