<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller {

    use \App\Http\Traits\PayThem;
    use \App\Http\Traits\PushNotification;
    use \App\Http\Traits\OrderProcessing;
    use \App\Http\Traits\TapPayment;

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

    public function index() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('index', ['social_links' => $social_links, 'news' => $news]);
    }

    public function home() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('home_v3', ['social_links' => $social_links, 'news' => $news]);
    }

    public function media() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('media', ['social_links' => $social_links, 'news' => $news]);
    }

    protected function media_single($id) {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }
        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->get();

        if ($id=='start-up-event-in-qatar') {
            return view('posts.post-1', ['social_links' => $social_links, 'news' => $news]);
        // } elseif ($id==2) {
        //     return view('posts.post-2', ['social_links' => $social_links, 'news' => $news]);
        // } elseif ($id==3) {
        //     return view('posts.post-3', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='why-moms-love-jeeb') {
            return view('posts.post-4', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='get-connected-from-home-to-grocery-shop-with-jeeb') {
            return view('posts.post-5', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='what-makes-jeeb-on-top') {
            return view('posts.post-6', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='what-really-differentiates-jeeb') {
            return view('posts.post-7', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='top-5-ready-to-cook-items-on-Jeeb') {
            return view('posts.post-8', ['social_links' => $social_links, 'news' => $news]);
        } elseif ($id=='100-biodegradable-with-jeeb') {
            return view('posts.post-9', ['social_links' => $social_links, 'news' => $news]);
        } else {
            return view('posts.media-single', ['social_links' => $social_links, 'news' => $news]);
        } 
    }

    public function contact() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('contact', ['social_links' => $social_links, 'news' => $news]);
    }

    public function contact_form(Request $request) {
        try {
            // Form validation
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required|email',
                // 'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
                'subject'=>'required',
                'message' => 'required',
                'g-recaptcha-response' => ['required', 'string', function ($attribute, $value, $fail) {
                    $secretKey = env('GOOGLE_RECAPTCHA_SECRET_KEY');
                    $response = $value;
                    $userIP = $_SERVER['REMOTE_ADDR'];
                    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$response&remoteip=$userIP";
                    $response = \file_get_contents($url);
                    $response = json_decode($response);
                }],
             ]);
            //  Store data in database
            // Contact::create($request->all());
            //  Send mail to admin
            \Mail::send('emails.support_email_admin', array(
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                // 'phone' => $request->get('phone'),
                'subject' => $request->get('subject'),
                'description' => $request->get('message'),
            ), function($message) use ($request){
                $message->to('admin@jeeb.tech', 'Admin')->subject($request->get('subject'));
                $message->to('thuwansujan@gmail.com', 'Admin')->subject($request->get('subject'));
            });
            // 
            return 'OK';
            // return response()->json(['data' => 'OK', 'text' => ''We have received your message and would like to thank you for writing to us.'']);
            // return 'We have received your message and would like to thank you for writing to us.';
        
        } catch (Exception $ex) {
            // return 'Error occured while sending the email';
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
    }

    protected function news() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }
        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->get();

        return view('news', ['social_links' => $social_links, 'news' => $news]);
    }

    protected function download_app() {
        return view('download_app');
    }

    public function download_app_submit(Request $request) {
        try {
            // Form validation
            $this->validate($request, [
                'mobile' => 'required'
            ]);
            // Store data in database
            $exits = \App\Model\AffiliateUser::where(['user_mobile'=>$request->input('mobile')])->first();
            if (!$exits) {
                $affiliate_user_last_id = \App\Model\AffiliateUser::orderBy('id', 'DESC')->first();
                $affiliate_user_last_id = $affiliate_user_last_id ? $affiliate_user_last_id->id : 0;
                $user_coupon = bin2hex(random_bytes(5)).($affiliate_user_last_id+1);

                $mobile = trim($request->input('mobile'));
                $mobile = str_replace(' ', '', $mobile);
                $mobile = (substr($mobile, 0, 1 ) == '+') ? substr($mobile, 1) : $mobile;

                // 974 to all numbers
                $mobile = '974'.$mobile;
            
                $insert_arr = [
                    'user_mobile' => $mobile,
                    'affiliate_code' => $request->input('affiliate_code') ? $request->input('affiliate_code') : 'GUEST',
                    'user_ip' => $_SERVER['REMOTE_ADDR'],
                    'user_registered' => false,
                    'user_coupon' => $user_coupon,
                    'user_coupon_used' => false
                ];
                $add = \App\Model\AffiliateUser::create($insert_arr);
            }
            // Redirect to Playstore or Appstore
            $url = 'http://onelink.to/ekg49s';
            // if ($request->has('download_play_store')) {
            //     $url = 'https://play.google.com/store/apps/details?id=com.jeeb.user&hl=en_US&gl=US';
            // } else {
            //     $url = 'https://apps.apple.com/qa/app/jeeb-grocery-service/id1614152924';
            // }
            return redirect()->away($url);
            // return response()->json(['data' => 'OK', 'text' => ''We have received your message and would like to thank you for writing to us.'']);
        } catch (Exception $ex) {
            // return 'Error occured while sending the email';
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
    }

    public function about() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('about', ['social_links' => $social_links, 'news' => $news]);
    }

    public function faq() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        $news = \App\Model\News::where(['deleted' => 0])->orderBy('id', 'desc')->limit(3)->get();

        return view('faq', ['social_links' => $social_links, 'news' => $news]);
    }

    protected function privacy_policy() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        return view('privacy_policy', ['social_links' => $social_links]);
    }

    protected function terms() {
        //get the social media links from db
        $names = ['facebook', 'instagram', 'youtube', 'linkedin', 'snapchat', 'twitter', 'tiktok'];
        $socialAccounts = \App\Model\AdminSetting::whereIn('key', $names)->get();
        $social_links = [];
        if ($socialAccounts->count()) {
            foreach ($socialAccounts as $key => $value) {
                $social_links[$value->key] = $value->value ?? "";
            }
        }

        return view('t&c', ['social_links' => $social_links]);
    }

    protected function privacy_policy_for_app () {
        return view('privacy_policy_for_app');
    }

    protected function terms_for_app() {
        return view('t&c_for_app');
    }

    protected function app() {
        return view('app');
    }

    protected function customer_invoice($id = null)
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

    protected function customer_invoice_pdf($id = null)
    {
        $id = base64url_decode($id);

        $order = \App\Model\Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->select('orders.*')
            ->selectRaw("SUM(order_products.total_product_price) as total_product_price")
            ->where('orders.id', '=', $id)
            ->first();
        if (!$order) {
            die("Order not found");
        }

        $invoiceId = 'N/A';
        $cardNumber = 'N/A';

        // Send data to the view using loadView function of PDF facade
        // $view = view('admin.orders.customer_invoice_pdf', [
        //     'order' => $order,
        //     'invoiceId' => $invoiceId,
        //     'cardNumber' => $cardNumber
        // ])->render();
        // $html = mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
        // $html_decode = html_entity_decode($html);
        // $pdf = \PDF::setOptions(['defaultFont' => 'dejavu serif'])->loadHTML($html_decode)
        //     ->setPaper('a4', 'landscape')
        //     ->setWarnings(false)
        //     ->setOptions(['isFontSubsettingEnabled' => true]);

        // return $pdf->stream('invoice_#'.$order->orderId.'.pdf')->header('Content-Type','application/pdf');

        $pdf = \PDF::loadView('admin.orders.customer_invoice_pdf', [
            'order' => $order,
            'invoiceId' => $invoiceId,
            'cardNumber' => $cardNumber
        ]);
        return $pdf->download('invoice_#'.$order->orderId.'.pdf')->header('Content-Type','application/pdf');
        // return $pdf->stream('invoice_#'.$order->orderId.'.pdf')->header('Content-Type','application/pdf');

        return view('admin.orders.customer_invoice_pdf', [
            'order' => $order,
            'invoiceId' => $invoiceId,
            'cardNumber' => $cardNumber
        ]);
    }

    protected function bifm_order($bifm_id) {
        $bifm_id = base64_decode($bifm_id);
        $lang = 'en';
        
        // Update buy it for me requests
        $buy_it_for_me_request = \App\Model\UserBuyItForMeRequest::find($bifm_id);
        $buy_it_for_me_cart = \App\Model\UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                            ->where('user_buy_it_for_me_cart.fk_request_id',$bifm_id)->get();
        $buy_it_for_me_user = \App\Model\User::find($buy_it_for_me_request->from_user_id);
        
        // Buying user
        $user_mobile = $buy_it_for_me_request->to_user_mobile;
        $country_code = '+974';
        $country_code = (substr( $country_code, 0, 1 ) == '+') ? substr($country_code, 1) : $country_code;
        $phone = $country_code . $user_mobile;
        $user = \App\Model\User::where(DB::raw('CONCAT(country_code,mobile)'), '=', $phone)->first();
        
        foreach ($buy_it_for_me_cart as $key => $value) {
            $base_product = \App\Model\BaseProduct::where('id',$value->fk_product_id)->first();

            // Calculate total price
            $total_price = round($base_product->product_store_price * $value->quantity, 2);
            
            // If sub products for recipe
            if (isset($value->sub_products) && $value->sub_products!='') {
                $sub_products = $value->sub_products;
                if ($sub_products && is_array($sub_products)) {
                    foreach ($sub_products as $key => $value2) {
                        if ($value2->product_id && $value2->product_quantity) {
                            $sub_product = \App\Model\BaseProduct::find($value2->product_id);
                            $sub_product_price = round($sub_product->product_store_price * $value2->product_quantity, 2);
                            $total_price = $total_price + $sub_product_price;
                            $value2->product_name_en = $sub_product->product_name_en;
                            $value2->product_name_ar = $sub_product->product_name_ar;
                            $value2->unit = $sub_product->unit;
                            $value2->price = $sub_product->product_store_price;
                            $value2->product_image_url = $sub_product->product_image_url;
                        }
                    }
                }
            }
            
        }

        $bifm_order = $this->get_buy_it_for_me_data($lang,$bifm_id,$user);

        $addresses = \App\Model\UserAddress::where([
            'fk_user_id'=>$buy_it_for_me_user->id,
            'deleted'=>0
        ])->orderBy('is_default')->get();

        return view('bifm_order',[
            'order' => $bifm_order,
            'addresses' => $addresses
        ]);
    }

    protected function bifm_order_payment($order_id) {
        $order_id = base64_decode($order_id);
        $order = \App\Model\Order::find($order_id);
        $user_id = $order->bought_by ? $order->bought_by : $order->fk_user_id;
        $user = \App\Model\User::find($user_id);
        return view('bifm_order_payment',[
            'order' => $order,
            'user' => $user
        ]);
    }
    
    protected function bifm_order_payment_process(Request $request) {
        try {
            $order_id = $request->input('order_id');
            $token_id = $request->input('token');
                
            $order = \App\Model\Order::find($order_id);
            $user_id = $order->bought_by ? $order->bought_by : $order->fk_user_id;
            $user = \App\Model\User::find($user_id);
            $lang = 'en';

            if ($order && $user) {

                // Get charge
                $cus_ref = $user->tap_id;
                $charge = $this->create_charge_with_token($token_id, $order_id, $cus_ref, $order->amount_from_card);

                // SMS content & required variables
                $total_amount = $order->total_amount;
                $amount_from_wallet = $order->amount_from_wallet;
                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $user->id])->first();
                $status = 'failed';
                
                // Payment successful
                if ($charge && isset($charge['status']) && $charge['status']==true) {
                    
                    // Order payment wallet
                    if ($amount_from_wallet>0) {
                        \App\Model\UserWallet::where(['fk_user_id' => $user->id])->update([
                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                        ]);
                        \App\Model\UserWalletPayment::create([
                            'fk_user_id' => $user->id,
                            'amount' => $amount_from_wallet,
                            'status' => "sucsess",
                            'wallet_type' => 2,
                            'transaction_type' => 'debit',
                            'paymentResponse' => $order_id
                        ]);
                    }

                    // Order payment update
                    $status = 'success';
                    $insert_arr = [
                        'amount' => $total_amount,
                        'status' => $status,
                        'paymentResponse' => $charge['response']
                    ];
                    \App\Model\OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                    // Order update
                    $order_confirmed = $this->order_confirmed($order_id);
                    $order_status = $order_confirmed && isset($order_confirmed['order_status']) ? $order_confirmed['order_status'] : 0;
                    if ($order_status>0) {
                        // $message = $lang == 'ar' ? "تم الدفع بنجاح" : "Paid successfully!";
                        $message = "Paid successfully!";
                        $error_code = 200;
                    } else {
                        if (isset($tracking_id) && $tracking_id) {
                            update_tracking_response($tracking_id,'Something went wrong, please contact customer service!');
                        }
                        $error_message = $lang == 'ar' ? "هناك مشكلة، من فضلك تحدث مع خدمة العملاء" : "Something went wrong, please contact customer service!";
                        throw new Exception($error_message, 105);
                    }

                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->id);

                    $this->error = false;
                    $this->status_code = $error_code;
                    $this->message = $message;
                    $this->result = [
                        'status' => $charge['status'],
                        'response' => $charge['text'],
                        'message' => $charge['text'],
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                } elseif ($charge && isset($charge['text']) && strtolower($charge['text'])=="initiated") {
                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->id);

                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "قيد الانتظار" : "Pending";
                    $this->result = [
                        'status' => isset($charge) && isset($charge['status']) ? $charge['status'] : false,
                        'response' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                        'message' => isset($charge) && isset($charge['text']) ? $charge['text'] : "",
                        'transaction' => isset($charge) && isset($charge['transaction']) ? $charge['transaction'] : [],
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];

                    // if (isset($charge['transaction']) && isset($charge['transaction']->url)) {
                    //     $redirectUrl = $charge['transaction']->url;
                    //     return Redirect::away($redirectUrl);
                    // }

                } else {

                    // Order failed
                    $this->order_failed($order_id, $order, $charge);

                    // Send order details
                    $order_arr = $this->get_order_arr($lang, $order->id);

                    $this->error = true;
                    $this->status_code = 99;
                    $this->message = $lang == 'ar' ? "فشل" : "Failed";
                    $this->result = [
                        'status' => $charge['status'],
                        'response' => $charge['text'],
                        'message' => $charge['text'],
                        'orderId' => isset($order_arr) && isset($order_arr["orderId"]) ? $order_arr["orderId"] : 0, 
                        'order' => isset($order_arr) ? $order_arr : [], 
                        'tap_id' => $cus_ref
                    ];
                }
                
            } else {
                $this->error = true;
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
                $this->result = [];
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

    //TO Create JSON Response
    protected function makeJson() {
        return response()->json([
                    'error' => $this->error,
                    'status_code' => $this->status_code,
                    'message' => $this->message,
                    'result' => $this->result
        ]);
    }

    private function get_buy_it_for_me_data($lang,$id,$user)
    {
        try {
            $cartItems = \App\Model\UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                ->where('user_buy_it_for_me_cart.fk_request_id', $id)
                ->orderBy('user_buy_it_for_me_cart.id', 'desc')
                ->get();
            $cartItemArr = [];
            $cartItemOutofstockArr = [];
            $cartItemQuantityMismatch = [];
            $change_for = [100,200,500,1000]; 

            $wallet_balance = get_userWallet($user->id);

            $cartTotal = \App\Model\UserBuyItForMeCart::join('base_products','user_buy_it_for_me_cart.fk_product_id','=','base_products.id')
                    ->selectRaw("SUM(user_buy_it_for_me_cart.total_price) as total_amount")
                    ->where('user_buy_it_for_me_cart.fk_request_id', $id)
                    ->orderBy('user_buy_it_for_me_cart.id', 'desc')
                    ->first();

            $nearest_store = false;
            $delivery_cost = get_delivery_cost($user->ivp,$user->mobile,$user->id);
            $mp_amount = get_minimum_purchase_amount($user->ivp,$user->mobile,$user->id);
            
            // For Instant model
            if ($user->ivp=='i') {
                $nearest_store = \App\Model\InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
                ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
                ->where(['instant_store_groups.id'=>$user->nearest_store,'instant_store_groups.deleted'=>0,'instant_store_groups.status'=>1])
                ->first();
            }
            $active_stores_arr = [];
            if ($nearest_store) {
                $active_stores = \App\Model\InstantStoreGroupStore::join('stores','stores.id','instant_store_group_stores.fk_store_id')
                ->where(['instant_store_group_stores.fk_group_id'=>$nearest_store->id,'stores.deleted'=>0,'stores.status'=>1, 'stores.schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            } else {
                // For Planned model
                $active_stores = \App\Model\Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
            }

            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($user->expected_eta, $user->ivp);
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");
            // ------------------------------------------------------------------------------------------------------
            // Generate and send time slots
            // ------------------------------------------------------------------------------------------------------
            $time_slots_made = $this->getTimeSlotFromDB();
            $time_slots = $time_slots_made[0];
            $active_time_slot = $time_slots_made[1];
            $active_time_slot_value = $time_slots_made[2];
            $active_time_slot_string = $time_slots_made[3];
            
            // Get my cart data
            $h1_text = $lang=='ar' ? 'هناك تأخير بسيط لطلبك' : 'There is a slight delay for your order';
            $h2_text = $lang=='ar' ? 'قد يتأخر التسليم الخاص بك بسبب منتج معين في سلة التسوق الخاصة بك. انتظر بينما نستعد لتسليم طلبك.' : 'Your delivery may be delayed due to certain product in your cart. Hold tight as we prepare to deliver your order.';
            
            if ($cartItems->count()) {
                $message = $lang == 'ar' ? "عربة التسوق" : "My cart";
                foreach ($cartItems as $key => $value) {
                    $base_product = \App\Model\BaseProduct::leftjoin('categories','categories.id','base_products.fk_sub_category_id')
                                ->select('base_products.*','categories.blocked_timeslots')
                                ->where('base_products.id','=',$value->fk_product_id)
                                ->first();
                    
                    // If sub products for recipe
                    $sub_product_arr = [];
                    $sub_product_names = "";
                    $sub_product_price = 0;
                    if (isset($value->sub_products) && $value->sub_products!='') {
                        $sub_products = json_decode($value->sub_products);
                        if ($sub_products && is_array($sub_products)) {
                            foreach ($sub_products as $key2 => $value2) {
                                if ($value2->product_id && $value2->product_quantity) {
                                    $sub_product = \App\Model\BaseProduct::find($value2->product_id);
                                    $sub_product_arr[] = array (
                                        'product_id' => $sub_product->id,
                                        'product_name' => $lang == 'ar' ? $sub_product->product_name_ar : $sub_product->product_name_en,
                                        'product_image' => $sub_product->product_image_url ?? '',
                                        'product_price' => (string) number_format($sub_product->product_store_price, 2),
                                        'item_unit' => $sub_product->unit
                                    );
                                    $sub_product_names .= $lang == 'ar' ? $sub_product->product_name_ar.',' : $sub_product->product_name_en.',';
                                    $sub_product_price += $sub_product->product_store_price *  $value2->product_quantity;
                                }
                            }
                        }
                    }
                    $sub_product_names = rtrim($sub_product_names,',');
                    $sub_product_price = (string) number_format($sub_product_price,2);
                    // Checking stocks
                    // For retailmart company id = 2
                    if ($base_product->product_store_stock > 0 && $base_product->product_store_stock < $value->quantity) {
                        $cartItemQuantityMismatch[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                    // For other companies
                    elseif ($base_product->product_store_stock > 0 && $base_product->deleted == 0) {
                        $cartItemArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    } else {
                        $cartItemOutofstockArr[$key] = [
                            'id' => $value->id,
                            'product_id' => $base_product->id,
                            'type' => $base_product->product_type,
                            'parent_id' => $base_product->parent_id,
                            'recipe_id' => $base_product->recipe_id,
                            'product_name' => $lang == 'ar' ? $base_product->product_name_ar : $base_product->product_name_en,
                            'product_image' => $base_product->product_image_url ?? '',
                            'product_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_store_price' => (string) number_format($base_product->product_store_price, 2),
                            'product_total_price' => (string) round(($value->total_price), 2),
                            'product_price_before_discount' => (string) round($value->total_price, 2),
                            'cart_quantity' => $value->quantity,
                            'unit' => $base_product->unit,
                            'product_discount' => $base_product->margin,
                            'stock' => (string) $base_product->product_store_stock,
                            'item_weight' => $value->weight,
                            'item_unit' => $value->unit,
                            'product_category_id' => $base_product->fk_category_id ? $base_product->fk_category_id : 0,
                            'product_sub_category_id' => $base_product->fk_sub_category_id ? $base_product->fk_sub_category_id : 0,
                            'min_scale' => $base_product->min_scale ?? '',
                            'max_scale' => $base_product->max_scale ?? '',
                            'sub_products' => $sub_product_arr,
                            'sub_product_names' => $sub_product_names,
                            'sub_product_price' => $sub_product_price
                        ];
                    }
                }
                
                $result = [
                    'cart_items' => array_values($cartItemArr),
                    'cart_items_outofstock' => array_values($cartItemOutofstockArr),
                    'cart_items_quantitymismatch' => array_values($cartItemQuantityMismatch),
                    'sub_total' => (string) round($cartTotal->total_amount, 2),
                    'delivery_charge' => $delivery_cost,
                    'total_amount' => (string) ($cartTotal->total_amount + $delivery_cost),
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'store_open_time' => $store_open_time,
                    'store_close_time' => $store_close_time,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => $time_slots,
                    'active_time_slot_value' => $active_time_slot_value,
                    'active_time_slot_string' => $active_time_slot_string,
                    'bring_change_for' => $change_for
                ];
            } else {
                $message = $lang == 'ar' ? "سلتك فاضية" : "Cart is empty";
                $result = [
                    'cart_items' => [],
                    'cart_items_outofstock' => [],
                    'cart_items_quantitymismatch' => [],
                    'sub_total' => '0',
                    'delivery_charge' => '0',
                    'total_amount' => '0',
                    'wallet_balance' => $wallet_balance,
                    'minimum_purchase_amount' => $mp_amount,
                    'delivery_in' => $delivery_time,
                    'current_time' => date('d-m-y H:i:s'),
                    'h1' => $h1_text,
                    'h2' => $h2_text,
                    'RETAILMART_ID' => env("RETAILMART_ID"),
                    'time_slots' => [],
                    'active_time_slot_value' => '',
                    'active_time_slot_string' => '',
                    'sub_category_ids_of_blocking_timeslots' => [],
                    'bring_change_for' => $change_for
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => []
            ]);
        }
        return ['message'=>$message, 'result'=>$result];
    }

    protected function getTimeSlotFromDB()
    {
        $time = [];

        // Current time
        $current = new \DateTime(date('H:i'));
        $currentTime = $current->format('H:i');
        $currentTime_set = 0;
        
        // Active time
        $activeTime = 'tomorrow';
        $activeTimeString = 'tomorrow';
        $activeTimeValue = false;
        
        // Get address selected
        // $address_selected = get_userSelectedAddress($this->user_id);
        $address_selected = get_userSelectedAddress(false);
        $interval = $address_selected && isset($address_selected['blocked_timeslots']) ? $address_selected['blocked_timeslots']*60 : 0;

        // Get Time Slots
        $delivery_slots = \App\Model\DeliverySlotSetting::orderBy('from','asc')->get();
        if ($delivery_slots) {
            foreach ($delivery_slots as $key => $delivery_slot) {
                $start = date('H:i',strtotime($delivery_slot->from));
                $end = date('H:i',strtotime($delivery_slot->to));
                $start_str = date('h:i a',strtotime($delivery_slot->from));
                $end_str = date('h:i a',strtotime($delivery_slot->to));
                // $blockTime = date('H:i',strtotime($delivery_slot->block_time));
                $blockTime = date('H:i',strtotime('-'.$interval.' minutes',strtotime($delivery_slot->block_time)));
                // Set Active Time and Active Time Value
                if ($activeTimeString=='tomorrow') {
                    $activeTimeString = $start_str.' - '.$end_str.' (tomorrow)';
                }
                if (!$activeTimeValue) {
                    $activeTimeValue = $start.'-'.$end;
                }
                if(strtotime($delivery_slot->from) <= strtotime($delivery_slot->to)){
                    if (strtotime($currentTime) <= strtotime($blockTime) && $currentTime_set==0) {
                        $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,'active'];
                        $activeTime = $start_str.' - '.$end_str;
                        $activeTimeValue = $start.'-'.$end;
                        $activeTimeString = $start_str.' - '.$end_str.' (today)';
                        $currentTime_set=1;
                    } else {
                        $time[] = [$start_str.' - '.$end_str,$blockTime,$start.'-'.$end,''];
                    }
                }
            }
        }

        return [$time,$activeTime,$activeTimeValue,$activeTimeString];
    }

    public function order_tracker($id = null) {

        $lang = isset($_GET) && isset($_GET['lang']) ? $_GET['lang'] : 'en';

        $order = $id ? \App\Model\Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_address', 'orders.id', '=', 'order_address.fk_order_id')
        ->select("orders.*", "order_delivery_slots.delivery_time",  "order_delivery_slots.delivery_date", "order_delivery_slots.later_time", "order_delivery_slots.delivery_preference", "order_address.latitude", "order_address.longitude", "order_address.longitude", "order_address.name", "order_address.mobile", "order_address.landmark", "order_address.address_line1", "order_address.address_line2", "order_address.address_type")
        // ->where(['orders.id'=>base64url_decode($id)])
        ->where(['orders.id'=>$id])
        ->first() : false;
        if (!$order) {
            if ($lang=='ar') {
                die("لم يتم العثور على الطلب!");
            } {
                die("Order is not found!");
            }
        }
        $order_out_for_delivery = $order->status==6 ? true : false;
        $driver = false;
        $driver_location = false;
        if ($order && $order->getOrderDriver && $order->getOrderDriver->fk_driver_id != 0) {
            $driver = $order->getOrderDriver;
            if ($driver) {
                $driver_location = \App\Model\DriverLocation::where(['fk_driver_id'=>$order->getOrderDriver->fk_driver_id])->orderBy('id', 'desc')->first();
            }
        }

        // dd($driver);

        $driver_assigned = (isset($driver) && $driver && isset($driver_location) && $driver_location && $order_out_for_delivery) ? true : false;
        if ($driver_assigned) {
            $title = ($lang=='ar') ? 'طلبك (ارقم: #'.$order->orderId.') في الطريق!' : 'Your Order (ID: #'.$order->orderId.') is on the way!';
        } else {
            $title = ($lang=='ar') ? 'طلبك (رقم: #'.$order->orderId.') في التحضير' : 'Your Order (ID: #'.$order->orderId.') is being prepared';
        }
        $driver_title = $lang=='ar' ? 'عنوان طلبك الحالي' : 'Your order is here';
        $center_location = [];
        $center_location['lat'] = 25.286106;
        $center_location['lng'] = 51.534817;
        if ($driver_assigned) {
            $center_location['lat'] = $driver_location->latitude;
            $center_location['lng'] = $driver_location->longitude;
        } else {
            $center_location['lat'] = $order->latitude;
            $center_location['lng'] = $order->longitude;
        }

        return view('order_tracker', [
            'order' => $order,
            'order_status' => $this->order_status, 
            'driver_assigned' => $driver_assigned,
            'driver' => $driver, 
            'driver_location' => $driver_location,
            'center_location' => $center_location,
            'title' => $title,
            'driver_title' => $driver_title,
        ]);
    }

    protected function shared_link(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
        $order = \App\Model\Order::join('order_products', 'orders.id', '=', 'order_products.fk_order_id')
            ->join($products_table, 'order_products.fk_product_id', '=', $products_table . '.id')
            ->select('orders.*')
            ->selectRaw("SUM(order_products.product_quantity*$products_table.distributor_price) as total_distributor_price")
            ->where('orders.id', '=', $id)
            ->where('orders.parent_id', '=', 0)
            ->first();

        $order_products = \App\Model\OrderProduct::where(['fk_order_id' => $id, 'deleted' => 0])
            ->orderBy('id', 'desc')
            ->get();

        return view('vendor.orders.shared_link', [
            'order' => $order,
            'order_status' => $this->order_status,
            'order_products' => $order_products
        ]);
    }

    protected function ai_service()
    {
        return view('ai_service');
    }

    public function order_payment() {

        $charge_id = isset($_GET) && isset($_GET['tap_id']) ? $_GET['tap_id'] : false;
        $success = false;
        $title = "Payment failed!";
        $text = "Your payment was not success due to an unexpected error. Please try again..";
        $order_id = false;
        $buy_it_for_me_request_id = false;

        if ($charge_id) {
            
            $curl = curl_init();

            $postfields = json_encode(
                []
            );

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tap.company/v2/charges/".$charge_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
                "content-type: application/json"
            ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
            
            if ($err) {
                \Log::error('Tap get_charge cURL Error #:' . $err);
            } else {
                \Log::info('Tap get_charge response:' . $response);
                $response_json = json_decode($response);
                if ($response_json && isset($response_json->status)) {
                    if ($response_json->status=="CAPTURED") {
                        $success = true;
                        $title = "Payment success!";
                        $text = "Your payment was successfully made and order is confirmed..";
                    } else {
                        if (isset($response_json->response) && isset($response_json->response->message)) {
                            $success = false;
                            $title = "Payment failed!";
                            if ($response_json->response->message=="Declined, Tap") {
                                $text = "Your card is blocked temporarily, please use another card..";
                            } else {
                                $text = $response_json->response->message;
                            }
                        }
                    }

                    // Get order ID
                    if (isset($response_json) && isset($response_json->metadata->order_id)) {
                        $order_id = $response_json->metadata->order_id;
                        // Process order here
                        if ($order_id) {
                            
                            // Get order 
                            $order = \App\Model\Order::find($order_id);
                            if ($order && $order->status<1) {

                                $user = \App\Model\User::find($order->fk_user_id);
                                $user_id = $user ? $user->id : 0;

                                // SMS content & required variables
                                $msg_to_admin = '';
                                $payment_amount_text = '';
                                $total_amount = $order->total_amount;
                                $amount_from_wallet = $order->amount_from_wallet;
                                $amount_from_card = $order->amount_from_card;
                                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $user_id])->first();
                                $status = 'failed';
                                if ($amount_from_wallet>0) {
                                    $payment_amount_text .= "Card (QAR. $amount_from_card) / Wallet (QAR. $amount_from_wallet)";
                                } else {    
                                    $payment_amount_text .= "Card (QAR. $amount_from_card)";
                                }
                                
                                // Payment successful
                                if ($success) {
                                    
                                    // Order payment wallet
                                    if ($amount_from_wallet>0) {
                                        \App\Model\UserWallet::where(['fk_user_id' => $user_id])->update([
                                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                                        ]);
                                        \App\Model\UserWalletPayment::create([
                                            'fk_user_id' => $user_id,
                                            'amount' => $amount_from_wallet,
                                            'status' => "sucsess",
                                            'wallet_type' => 2,
                                            'transaction_type' => 'debit',
                                            'paymentResponse' => $order_id
                                        ]);
                                    }

                                    // Order payment update
                                    $status = 'success';
                                    $insert_arr = [
                                        'amount' => $total_amount,
                                        'status' => $status,
                                        'paymentResponse' => $response
                                    ];
                                    \App\Model\OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                                    // Order update
                                    $order_status_update = \App\Model\Order::find($order_id)->update(['status'=>1]);
                                    if ($order_status_update) {

                                        // Delete cart items 
                                        \App\Model\UserCart::where(['fk_user_id' => $user_id])->delete();

                                        // Update order status time
                                        \App\Model\OrderStatus::create([
                                            'fk_order_id' => $order_id,
                                            'status' => 1
                                        ]);

                                        // Assigning storekeepers
                                        // $order_products = \App\Model\OrderProduct::where('fk_order_id',$order_id)->get();
                                        // if (count($order_products) > 0) {
                                        //     foreach ($order_products as $key => $order_product) {
                                        //         $this->assignStoreKeeper($order,$order_product);
                                        //     }
                                        // }
                                        
                                    }
                                    
                                } else {
                                    
                                    // Order payment update
                                    $insert_arr = [
                                        'amount' => $total_amount,
                                        'status' => $status,
                                        'paymentResponse' => $response
                                    ];
                                    \App\Model\OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                                }
                                
                                // ---------------------------------------------------------------
                                // Send notification SMS to admins
                                // ---------------------------------------------------------------
                                $msg_to_admin .= "Order #$order->orderId - Payment Method: $payment_amount_text.\n\n";
                                $msg_to_admin .= "Payment Status: ".$title;

                                $test_order = $order->test_order;
                                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                                // SMS to Fleet Manager
                                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                                if ($send_notification && $send_notification_number) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                                }
                                // SMS to CEO
                                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                                if ($send_notification && $send_notification_number_2) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                                }
                                // SMS to CTO
                                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                                if ($send_notification && $send_notification_number_3) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                                }
                                // SMS to Backend Developer
                                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                                if ($send_notification && $send_notification_number_4) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                                }
                                // SMS to Driver
                                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                                if ($send_notification && $send_notification_number_5) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                                }
                                
                            }

                        }
                    }
                }
            }
            
        }

        return view('order_payment', [
            'charge_id' => $charge_id,
            'success' => $success,
            'title' => $title, 
            'text' => $text, 
            'order_id' => $order_id
        ]);
    }

    public function forgot_something_order_payment() {

        $charge_id = isset($_GET) && isset($_GET['tap_id']) ? $_GET['tap_id'] : false;
        $success = false;
        $title = "Payment failed!";
        $text = "Your payment was not success due to an unexpected error. Please try again..";
        $order_id = false;

        if ($charge_id) {
            
            $curl = curl_init();

            $postfields = json_encode(
                []
            );

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.tap.company/v2/charges/".$charge_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".env("TAP_PAYMENT_KEY"),
                "content-type: application/json"
            ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
            
            if ($err) {
                \Log::error('Tap get_charge cURL Error #:' . $err);
            } else {
                \Log::info('Tap get_charge response:' . $response);
                $response_json = json_decode($response);
                if ($response_json && isset($response_json->status)) {
                    if ($response_json->status=="CAPTURED") {
                        $success = true;
                        $title = "Payment success!";
                        $text = "Your payment was successfully made and order is confirmed..";
                    } else {
                        if (isset($response_json->response) && isset($response_json->response->message)) {
                            $success = false;
                            $title = "Payment failed!";
                            if ($response_json->response->message=="Declined, Tap") {
                                $text = "Your card is blocked temporarily, please use another card..";
                            } else {
                                $text = $response_json->response->message;
                            }
                        }
                    }

                    // Get order ID
                    if (isset($response_json->metadata) && isset($response_json->metadata->order_id)) {
                        $order_id = $response_json->metadata->order_id;
                        // Process order here
                        if ($order_id) {
                            
                            // Get order 
                            $order = \App\Model\Order::find($order_id);
                            if ($order && $order->status<1) {

                                $user = \App\Model\User::find($order->fk_user_id);
                                $user_id = $user ? $user->id : 0;

                                // SMS content & required variables
                                $msg_to_admin = '';
                                $payment_amount_text = '';
                                $total_amount = $order->total_amount;
                                $amount_from_wallet = $order->amount_from_wallet;
                                $amount_from_card = $order->amount_from_card;
                                $user_wallet = \App\Model\UserWallet::where(['fk_user_id' => $user_id])->first();
                                $status = 'failed';
                                if ($amount_from_wallet>0) {
                                    $payment_amount_text .= "Card (QAR. $amount_from_card) / Wallet (QAR. $amount_from_wallet)";
                                } else {    
                                    $payment_amount_text .= "Card (QAR. $amount_from_card)";
                                }
                                
                                // Payment successful
                                if ($success) {
                                    
                                    // Order payment wallet
                                    if ($amount_from_wallet>0) {
                                        \App\Model\UserWallet::where(['fk_user_id' => $this->user_id])->update([
                                            'total_points' => ($user_wallet ? $user_wallet->total_points : 0) - $amount_from_wallet
                                        ]);
                                        \App\Model\UserWalletPayment::create([
                                            'fk_user_id' => $this->user_id,
                                            'amount' => $amount_from_wallet,
                                            'status' => "sucsess",
                                            'wallet_type' => 2,
                                            'transaction_type' => 'debit',
                                            'paymentResponse' => $order_id
                                        ]);
                                    }

                                    // Order payment update
                                    $status = 'success';
                                    $insert_arr = [
                                        'amount' => $total_amount,
                                        'status' => $status,
                                        'paymentResponse' => $response
                                    ];
                                    \App\Model\OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                                    // Order update
                                    \App\Model\Order::find($order_id)->update(['status'=>1]);

                                    // Update order products
                                    \App\Model\OrderProduct::where(["fk_order_id"=>$order->id])->update(["fk_order_id"=>$order->parent_id]);

                                    // Assigning storekeepers
                                    // $order_products = \App\Model\OrderProduct::where(["fk_order_id"=>$order->id, "is_missed_product"=>1])->get();
                                    // if(count($order_products) > 0){
                                    //     foreach ($order_products as $key => $order_product) {
                                    //         $this->assignStoreKeeper($order,$order_product);
                                    //     }
                                    // }

                                } else {
                                    
                                    // Order payment update
                                    $insert_arr = [
                                        'amount' => $total_amount,
                                        'status' => $status,
                                        'paymentResponse' => $response
                                    ];
                                    \App\Model\OrderPayment::where(["fk_order_id"=>$order_id])->first()->update($insert_arr);

                                }
                                
                                // ---------------------------------------------------------------
                                // Send notification SMS to admins
                                // ---------------------------------------------------------------
                                $parent_orderId = $order->parent_id+1000;
                                $msg_to_admin .= "Forgot Something Order #$parent_orderId - Payment Method: $payment_amount_text.\n\n";
                                $msg_to_admin .= "Payment Status: ".$title;

                                $test_order = $order->test_order;
                                $send_notification = $test_order==1 ? 0 : env("SEND_NOTIFICATION_FOR_NEW_ORDER",0);
                                // SMS to Fleet Manager
                                $send_notification_number = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER",false);
                                if ($send_notification && $send_notification_number) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number);
                                }
                                // SMS to CEO
                                $send_notification_number_2 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_2", false);
                                if ($send_notification && $send_notification_number_2) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_2);
                                }
                                // SMS to CTO
                                $send_notification_number_3 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_3", false);
                                if ($send_notification && $send_notification_number_3) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_3);
                                }
                                // SMS to Backend Developer
                                $send_notification_number_4 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_4", false);
                                if ($send_notification && $send_notification_number_4) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_4);
                                }
                                // SMS to Driver
                                $send_notification_number_5 = env("SEND_NOTIFICATION_FOR_NEW_ORDER_NUMBER_5", false);
                                if ($send_notification && $send_notification_number_5) {
                                    $msg = $msg_to_admin;
                                    $sent = $this->mobile_sms_curl($msg, $send_notification_number_5);
                                }
                                
                            }

                        }
                    }
                }
            }
            
        }

        return view('order_payment', [
            'charge_id' => $charge_id,
            'success' => $success,
            'title' => $title, 
            'text' => $text, 
            'order_id' => $order_id
        ]);
    }

    /* ============Send sms by Twillio api============== */

    protected function mobile_sms_curl($msg, $phone) {
        //echo $phone;die;
        $id = "AC4381447b4e2460bc6aba0c8a27554a96";
        $token = "3a4041c90b36c6396828c0f0ef5db264";
        $url = "https://api.twilio.com/2010-04-01/Accounts/AC4381447b4e2460bc6aba0c8a27554a96/Messages.json";
        $from = "Jeeb";
        
        $to = "+$phone";
        $body = $msg;
        $data = array(
            'From' => $from,
            'To' => $to,
            'Body' => $body,
        );
        $post = http_build_query($data);
        $x = curl_init($url);
        curl_setopt($x, CURLOPT_POST, true);
        curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
        curl_setopt($x, CURLOPT_POSTFIELDS, $post);
        $y = curl_exec($x);
        curl_close($x);
        
        return true;
    }

}
