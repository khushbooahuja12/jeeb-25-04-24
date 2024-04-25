<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Model\User;
use App\Model\File;
use Illuminate\Support\Facades\DB;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\UserWhatsAppTracking;
use App\Model\UserCart;
use App\Model\UserTracking;
use App\Model\OrderPayment;
use App\Model\Order;
use App\Model\UserAddress;
use App\Model\OrderAddress;
use App\Model\OrderDeliverySlot;
use App\Model\OrderStatus;
use App\Model\OrderProduct;
use App\Model\UserBuyItForMeCart;
use Exception;

class CoreApiController extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected function auth_check() {
        if (!empty(apache_request_headers()['Authorization'])) {
            $access_token = apache_request_headers()['Authorization'];
        } else {
            throw new Exception('Access token empty', 301);
        }
        $api_token_exist = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->exists();
        if ($api_token_exist != 1) {
            return response()->json([
                        'error' => true,
                        'status_code' => 301,
                        'message' => "Invalid access token",
                        'result' => (object) []
            ]);
        } else {
            $currdate = \Carbon\Carbon::now();
            $access_token_data = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->first();
            if ($currdate > $access_token_data->expires_at) {
                return response()->json([
                            'error' => true,
                            'status_code' => 301,
                            'message' => "Access token expired",
                            'result' => (object) []
                ]);
            }
        }
        $auth = DB::table('oauth_access_tokens')->where('id', "$access_token")->orderBy('created_at', 'desc')->first();
        return $auth;
    }

    //Check Required Input
    protected function required_input($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if (isset($data[$c]) && !empty($data[$c]) || isset($data[$c]) && $data[$c] == '0'):
                // return true;
                else:
                    echo json_encode(array("error" => true, "status_code" => 99,
                        "message" => "$c is required field", "result" => array("required_keys" => $check)));
                    die();
                endif;
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    protected function check_email_exist($email, $type) {
        if (User::where('email', '=', $email)->where('user_type', '=', $type)->exists()) {
            return true;
        } else {
            return false;
        }
    }

    //Check Numeric Input
    protected function check_numeric($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if (!is_numeric($data[$c])) {
                    echo json_encode(array("error" => true, "status_code" => 201,
                        "message" => "Please enter only digit in $c",
                        "result" => new \stdClass()
                    ));
                    http_response_code(400);
                    die();
                }
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    //Check Invalid Input
    protected function check_invalid($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if ($data[$c] == '0') {
                    echo json_encode(array(
                        "error" => true,
                        "status_code" => 203,
                        "message" => "invalid $c",
                        "result" => new \stdClass(),));
                    http_response_code(400);
                    die();
                }
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    //Check Alpha Numeric Input
    protected function check_alphanumeric($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if (!preg_match('/[^A-Za-z0-9]/', $data[$c])) {
                    echo json_encode(array("error" => true,
                        "status_code" => 202,
                        "message" => "Please enter only alpha numeric in $c",
                        "result" => new \stdClass(),
                    ));
                    http_response_code(400);
                    die();
                }
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    //Check Character Input
    protected function check_character($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if (!preg_match('/^[a-zA-Z\s,.?!:-]+$/', $data[$c])) {
                    echo json_encode(array(
                        "error" => true,
                        "status_code" => 202,
                        "message" => "Please enter only character in $c",
                        "result" => new \stdClass()));
                    http_response_code(400);
                    die();
                }
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    protected function check_space($data, $check) {
        if (!empty($data)):
            foreach ($check as $c):
                if (preg_match("/\\s/", $data[$c])) {
                    echo json_encode(array(
                        "error" => true,
                        "status_code" => 202,
                        "message" => "Please enter $c without space",
                        "result" => new \stdClass()));
                    http_response_code(400);
                    die();
                }
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    protected function is_json($string, $return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
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

    protected function uploadFile($request, $fileName, $path) {
        $return = false;
        if ($request->hasFile($fileName)) :
            $file = $request->file($fileName);
            $fullName = $file->getClientOriginalName();
            $stringName = $this->my_random_string(explode('.', str_replace(' ', '', $fullName))[0]);
            $fileName = $stringName . time() . '.' . (($file->getClientOriginalExtension() == 'jpg' || $file->getClientOriginalExtension() == 'jpeg') ? 'png' : $file->getClientOriginalExtension());
            $destinationPath = public_path($path);
            $check = $file->move($destinationPath, $fileName);
            $return = $check ? $fileName : false;
        endif;
        return $return;
    }

    protected function uploadMultipleFile($request, $fileName, $path) {
        $return = false;
        $init = $fileName;
        $uploadedFiles = [];
        if ($request->hasFile($fileName)) :
            $files = $request->file($fileName);
            foreach ($files as $file) {
                $fullName = $file->getClientOriginalName();

                // $stringName = $this->my_random_string($init . explode('.', str_replace(' ', '', $fullName))[0]);
                // $fileName = $stringName . time() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path($path);
                $check = $file->move($destinationPath, $fullName);
                if ($check) {
                    // array_push($uploadedFiles, $fileName);
                    array_push($uploadedFiles, $fullName);
                }
            }
            $return = ($uploadedFiles ? $uploadedFiles : false);
        endif;
        return $return;
    }

    protected function insertFile($req) {
        $response = File::create($req);
        return $response;
    }

    protected function updateFile($req, $id) {
        $response = File::where(['id' => $id])->update($req);
        if ($response) {
            return File::find($id);
        } else {
            return FALSE;
        }
    }

    protected function my_random_string($char) {
        return uniqid($char);
    }

    function randomPassword($len = 8) {
        //enforce min length 8
        if ($len < 8)
            $len = 8;

        //define character libraries - remove ambiguous characters like iIl|1 0oO
        $sets = array();
        $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = '23456789';
        $sets[] = '~!@#$%^&*(){}[],./?';

        $password = '';

        //append a character from each set - gets first 4 characters
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
        }

        //use all characters to fill up to $len
        while (strlen($password) < $len) {
            //get a random set
            $randomSet = $sets[array_rand($sets)];

            //add a random char from the random set
            $password .= $randomSet[array_rand(str_split($randomSet))];
        }

        //shuffle the password string before returning!
        return str_shuffle($password);
    }

    protected function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    protected function createCsv($heading = array(), $exportData = array(), $filename, $id) {
        $delimiter = ",";
        $filename = $filename . ".csv";

        if (!\Illuminate\Support\Facades\File::isDirectory(storage_path("exports/exported_files" . $id))) {
            mkdir(storage_path("exports/exported_files" . $id), 0755);
        }

        $f = fopen(storage_path("exports/exported_files$id" . '/' . $filename), 'w');
        fputcsv($f, $heading, $delimiter);

        foreach ($exportData as $key => $row):
            fputcsv($f, $row, $delimiter);
        endforeach;

        fseek($f, 0);
        fpassthru($f);
    }

    /* ============Send sms by Twillio api============== */

    protected function mobile_sms_curl($msg, $phone, $country_code='974') {
        $tracking_id = add_tracking_data(0, 'twillio', $phone.'-'.$msg, '');
        //echo $phone;die;
        $id = "AC4381447b4e2460bc6aba0c8a27554a96";
        $token = "3a4041c90b36c6396828c0f0ef5db264";
        $url = "https://api.twilio.com/2010-04-01/Accounts/AC4381447b4e2460bc6aba0c8a27554a96/Messages.json";
        $from = $country_code=="974" ? "Jeeb" : "19389991514";
        $to = "$phone";
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
//        print_r($y);exit;
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$y);
        }
        return true;
    }

    protected function uploadImageFromUrl($url, $path) {
        $data = file_get_contents($url);

        $fileNameWithExt = basename($url);
        $exploded = $fileNameWithExt ? explode('.', $fileNameWithExt) : ['dummy', 'jpg'];

        $filePath = public_path($path) . $fileNameWithExt;
        $upload = file_put_contents($filePath, $data);
        if ($upload) {
            return $fileNameWithExt;
        } else {
            return false;
        }
    }

    //update base product
    protected function update_base_product($product)
    {
        $total_base_broduct_stock = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->sum('stock');
        $base_product_store = BaseProductStore::where(['fk_product_id' => $product,['stock','>',0],'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->orderby('product_store_price','asc')->orderBy('margin', 'desc')->first();
        $base_product = false;
        $update_arr = [];

        if(!$base_product_store){
            $base_product_store = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->orderby('product_store_price','asc')->orderBy('margin', 'desc')->first();
        }

        if($base_product_store){

            $update_arr = [
                'fk_product_store_id' => $base_product_store->id,
                'fk_store_id' => $base_product_store->fk_store_id,
                'itemcode' => $base_product_store->itemcode,
                'barcode' => $base_product_store->barcode,
                'allow_margin' => $base_product_store->allow_margin,
                'base_price' => $base_product_store->base_price,
                'product_store_price' => $base_product_store->product_store_price,
                'product_distributor_price' => $base_product_store->product_distributor_price,
                'product_distributor_price_before_back_margin' => $base_product_store->product_distributor_price_before_back_margin,
                'stock' => $total_base_broduct_stock,
                'product_store_stock' => $total_base_broduct_stock,
                'product_store_updated_at' => $base_product_store->product_store_updated_at,
                'fk_price_formula_id' => $base_product_store->fk_price_formula_id,
                'margin' => $base_product_store->margin,
                'back_margin' => $base_product_store->back_margin,
                'base_price_percentage' => $base_product_store->base_price_percentage,
                'discount_percentage' => $base_product_store->discount_percentage,
            ];
        } else {
             
            $update_arr = [
                'product_store_stock' => 0,
                'product_store_updated_at' => date('Y-m-d H:i:s')
            ];
        
        }

        $base_product = BaseProduct::find($product);
        if($base_product && !empty($update_arr)){
            $update = $base_product->update($update_arr);
            if($update){
                $base_product_store = BaseProductStore::with('getStore')->where('id',$base_product->fk_product_store_id)->first();
                return response()->json(['status' => true, 'error_code' => 200, 'data' => $base_product_store, 'base_product' => $base_product->refresh()]);
            }else{
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }

        }else{
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }

    }

    //update base product store
    protected function update_base_product_store($product)
    {
        $base_product = BaseProduct::find($product);

        $base_product_stores = BaseProductStore::where(['fk_product_id' => $product, 'deleted' => 0])->get();

        foreach ($base_product_stores as $key => $base_product_store) {

            $update_arr = [
                'product_type' => $base_product->product_type,
                'recipe_id' => $base_product->recipe_id,
                'recipe_variant_id' => $base_product->recipe_variant_id,
                'recipe_ingredient_id' => $base_product->recipe_ingredient_id,
                'parent_id' => $base_product->parent_id,
                'fk_category_id' => $base_product->fk_category_id,
                'fk_sub_category_id' => $base_product->fk_sub_category_id,
                'fk_brand_id' => $base_product->fk_brand_id,
                'product_name_en' => $base_product->product_name_en,
                'product_name_ar' => $base_product->product_name_ar,
                'product_image_url' => $base_product->product_image_url,
                'unit' => $base_product->unit,
                'min_scale' => $base_product->min_scale,
                'max_scale' => $base_product->max_scale,
                'fk_main_tag_id' => $base_product->fk_main_tag_id,
                'main_tags' => $base_product->main_tags,
                '_tags' => $base_product->_tags,
                'search_filters' => $base_product->search_filters,
                'custom_tag_bundle' => $base_product->custom_tag_bundle,
                'desc_en' => $base_product->desc_en,
                'desc_ar' => $base_product->desc_ar,
                'characteristics_en' => $base_product->characteristics_en,
                'characteristics_ar' => $base_product->characteristics_ar,
                'offers' => $base_product->offers,
                'fk_offer_option_id' => $base_product->fk_offer_option_id,
                'country_icon' => $base_product->country_icon,
                'country_code' => $base_product->country_code,
                'paythem_product_id' => $base_product->paythem_product_id
            ];

            $update = BaseProductStore::find($base_product_store->id);
            $update->update($update_arr);
        }
        
        return true;
    }

    function makeRefund($refund_source, $user_id, $invoice_id, $amount, $order_id, $wallet_type=3)
    {
        if ($refund_source == 'wallet') {
            (new \App\Model\UserWallet)->updateOrCreate($user_id, $amount, $order_id, $wallet_type);
            return true;
        }
        if ($refund_source == 'card' && $invoice_id != '') {
            // Not ready yet
            return false;
        } else {
            return false;
        }
    }
    
    function makeFundDeduction($refund_source, $user_id, $invoice_id, $amount, $order_id)
    {
        if ($refund_source == 'wallet') {
            (new \App\Model\UserWallet)->updateOrCreate($user_id, $amount, $order_id);
            return true;
        }
    }

    protected function validateOrderTimeslot($later_time, $delivery_date, $later_time_blocked_slots) {

        // Current time
        $current = new \DateTime(date('H:i'));
        $current_time = strtotime($current->format('H:i'));

        // Today
        $delivery_date_of_time = new \DateTime($delivery_date);
        $current_date_of_time = new \DateTime(date('Y-m-d'));

        // Default returning values
        $valid = ($delivery_date_of_time > $current_date_of_time) ? true : false;
        $today_date = ($delivery_date_of_time == $current_date_of_time) ? true : false;
        $exists = false;
        $interval = $later_time_blocked_slots > 0 ? $later_time_blocked_slots*60 : 0;

        // Later time
        $later_time_arr = explode('-', $later_time);

        // Formate the timeslot
        if ($later_time && empty($later_time_arr) || !isset($later_time_arr[0])) {
            $start_time = $later_time_arr[0]<5 ? '0'.$later_time_arr[0] : $later_time_arr[0];
            $end_time = $later_time_arr[1]<5 ? '0'.$later_time_arr[1] : $later_time_arr[1];
            $later_time = $start_time.'-'.$end_time;
        }

        // Check Validity
        if ($later_time && !empty($later_time_arr) && isset($later_time_arr[0])) {
            if ($later_time_arr[1]=="00:00") {
                $later_time_arr[1]="24:00";
            }
            $existTimeslot = \App\Model\DeliverySlotSetting::where(['from'=>$later_time_arr[0].':00', 'to'=>$later_time_arr[1].':00'])->first();
            if ($existTimeslot) {
                $exists = true;
                // Check time validity if not future date
                if (!$valid && $today_date) {
                    $block = new \DateTime(date($existTimeslot->block_time));
                    $block_time = $block->format('H:i');
                    $block_time = $interval>0 ? strtotime('-'.$interval.' minutes',strtotime($block_time)) : strtotime($block_time);
                    if ($current_time <= $block_time) {
                        $valid = true;
                    }
                }
            } 
        }

        return [$exists, $valid, $later_time];

    }

    function Get_Address_From_Google_Maps($lat, $lon) 
    {

        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false&key=AIzaSyCErOS8XDmqBPa5HtTVcUSNhAadrrhfVa4";
        
        // Make the HTTP request
        $data = @file_get_contents($url);
        // Parse the json response
        $apiResponse = json_decode($data,true);
        
        return $apiResponse['results'][0]['formatted_address'];
    }


    // Add whatsapp user tracking data
    function add_whatsapp_tracking_data($user_id,$mobile,$key,$request,$response,$tracking_id=0) {
        if ($tracking_id==0) {
            $user_tracking = \App\Model\UserWhatsAppTracking::create([
                'user_id'=>$user_id,
                'mobile' =>$mobile,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
            $tracking_id = $user_tracking->id;
        } else {
            \App\Model\UserWhatsAppTracking::find($tracking_id)->update([
                'user_id'=>$user_id,
                'mobile' =>$mobile,
                'key'=>$key,
                'request'=>$request,
                'response'=>$response
            ]);
        }
        return $tracking_id;
    }

    // Update whatsapp user tracking data
    function update_whatsapp_tracking_userid_and_key($tracking_id,$key,$user_id) {
        $user_tracking = \App\Model\UserWhatsAppTracking::find($tracking_id)->update(['key'=>$key,'user_id'=>$user_id]);
        return $user_tracking;
    }

    function update_whatsapp_tracking_response($tracking_id,$response) {
        $user_tracking = \App\Model\UserWhatsAppTracking::find($tracking_id)->update(['response'=>$response]);
        return $user_tracking;
    }

    protected function update_instant_alternate_product ($cart_item, $active_stores) {
        $product = BaseProductStore::leftJoin('categories AS A', 'A.id','=', 'base_products_store.fk_category_id')
            ->leftJoin('categories AS B', 'B.id','=', 'base_products_store.fk_sub_category_id')
            ->leftJoin('brands','base_products_store.fk_brand_id', '=', 'brands.id')
            ->select(
                'base_products_store.*',
                'base_products_store.fk_product_id AS fk_product_id',
                'base_products_store.id AS fk_product_store_id',
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
            ->where('base_products_store.fk_product_id', '=', $cart_item->fk_product_id)
            ->whereIn('base_products_store.fk_store_id', $active_stores)
            ->orderBy('base_products_store.product_store_price','asc')
            ->first(); 
        if ($product) {

            // Calculate total price
            $total_price = round($product->product_store_price * $cart_item->product_quantity, 2);
            // If sub products for recipe
            if (isset($cart_item->sub_products) && $cart_item->sub_products!='') {
                $sub_products = $cart_item->sub_products;
                if ($sub_products && is_array($sub_products)) {
                    foreach ($sub_products as $key => $value2) {
                        if ($value2->product_id && $value2->product_quantity) {
                            $sub_product = BaseProduct::find($value2->product_id);
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

            $update_arr = [
                'fk_user_id' => $this->user_id,
                'fk_product_id' => $product->fk_product_id,
                'fk_product_store_id' => $product->fk_product_store_id,
                'quantity' => $cart_item->product_quantity,
                'total_price' => $total_price,
                'total_discount' => '',
                'weight' => $cart_item->weight ?? '',
                'unit' => $cart_item->unit ?? '',
                'sub_products' => isset($cart_item->sub_products) && $cart_item->sub_products ? json_encode($cart_item->sub_products) : ''
            ];
            if (UserCart::find($cart_item->id)->update($update_arr)) {
                return $product;
            }
        } 
        return false;
    }

    // Get my features
    protected function get_my_features ($user_id=0, $user=false) {
        // Assign logged in users features
        $features = [
            'ivp'=>env("ivp", false),
            'scratch_card'=>env("scratch_card", false)
        ];
        // Get user
        if (!$user) {
            $user = User::find($user_id);
        }
        // Check user level features
        if ($user && $user->features_enabled) {
            $user_features = explode(',',$user->features_enabled);
            if (is_array($user_features) && !empty($user_features)) {
                // User Specific IVP feature
                if (!$features['ivp'] && in_array('ivp',$user_features)) {
                    $features['ivp'] = true;
                }
                // User Specific Scratch Card feature
                if (!$features['scratch_card'] && in_array('scratch_card',$user_features)) {
                    $features['scratch_card'] = true;
                }
            }
        }
        return $features;
    }
    
    protected function is_scratch_card_enabled ($user_id=0, $user=false) {
        // Assign logged in users features
        $scratch_card_feature=env("scratch_card", false);
        // Get user
        if (!$user) {
            $user = User::find($user_id);
        }
        // Check user level features
        if ($user && !$scratch_card_feature) {
            $user_features = explode(',',$user->features_enabled);
            if (is_array($user_features) && !empty($user_features) && in_array('scratch_card',$user_features)) {
                // User Specific Scratch Card feature
                $scratch_card_feature = true;
            }
        }
        return $scratch_card_feature;
    }

    function apply_custom_list_styles($html)
    {
        // Define the CSS styles for the list marker
        $customListMarkerStyle = 'style="list-style: none; padding-left: 1.2em;"';

        // Find and replace <ul> and <li> elements with inline styles
        $html = preg_replace('/<ul>(.*?)<\/ul>/s', "<ul $customListMarkerStyle>$1</ul>", $html);
        $html = preg_replace('/<li>(.*?)<\/li>/', '<li style="list-style: none; margin: 0 0 0 -1.2em;">✦&nbsp; $1</li>', $html);

        return $html;
    }
    
}
