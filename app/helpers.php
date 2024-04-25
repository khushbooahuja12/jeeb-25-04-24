<?php

use App\Model\OauthAccessToken;
use App\Model\Store;
use App\Model\User;
use App\Model\Admin;
use App\Model\BaseProductStore;
use App\Model\BaseProduct;
use App\Model\ProductOfferOption;

function get_controller()
{
    $action = app('request')->route()->getAction();
    $controller = class_basename($action['controller']);
    return explode('@', $controller)[0];
}

function get_action()
{
    $action = app('request')->route()->getActionMethod();
    return $action;
    // $action = app('request')->route()->getAction();
    // $controller = class_basename($action['controller']);
    // return explode('@', $controller)[1];
}

function getStoreId()
{
    return explode('/', request()->path())[1];
}

function getStore()
{
    $id = explode('/', request()->path())[1];
    return Store::find($id);
}

function str_random($length = 16)
{
    return Str::random($length);
}

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function createCsv($heading = array(), $exportData = array(), $filename)
{
    $delimiter = ",";
    $filename = $filename . date('d-m-Y') . ".xls";

    $f = fopen('php://memory', 'w');
    fputcsv($f, $heading, $delimiter);

    foreach ($exportData as $key => $row) :
        fputcsv($f, $row, $delimiter);
    endforeach;

    fseek($f, 0);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    fpassthru($f);
}

function get_age($dob)
{
    return \Carbon\Carbon::parse($dob)->age;
}

function get_distance($lat1 = 0, $lon1 = 0, $lat2 = 0, $lon2 = 0, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

function getCityName($lat, $long)
{
    $apiurl = 'https://maps.googleapis.com/maps/api/geocode/json';
    $args = array('latlng' => $lat . ',' . $long);
    //echo $apiurl . '?' . http_build_query($args); die();
    $curlHandle = curl_init();
    curl_setopt($curlHandle, CURLOPT_URL, $apiurl . '?' . http_build_query($args) . '&key=AIzaSyCErOS8XDmqBPa5HtTVcUSNhAadrrhfVa4');
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curlHandle);
    if ($result === false) {
        throw new \RuntimeException(
            'API call failed with cURL error: ' . curl_error($curlHandle)
        );
    }
    curl_close($curlHandle);
    $apiResponse = json_decode($result, true);
    //echo "<pre>";print_r($apiResponse);die;
    $jsonErrorCode = json_last_error();
    if ($jsonErrorCode !== JSON_ERROR_NONE) {
        throw new \RuntimeException(
            'API response not well-formed (json error code: ' . $jsonErrorCode . ')'
        );
    }
    if ($apiResponse['results']) {
        foreach ($apiResponse['results'][0]['address_components'] as $key => $row) {
            if ($row['types'][0] == 'administrative_area_level_2') {
                $city_long = $row['long_name'];
                $city_short = $row['short_name'];
            }
        }
    } else {
        $city_long = '';
        $city_short = '';
    }
    return $city_long ?? '';
}

function get_vendor_count($id)
{
    $vendor_count = \App\Model\OrderProduct::selectRaw("COUNT(DISTINCT(fk_vendor_id)) as vendor_count")
        ->where('fk_order_id', '=', $id)
        ->first();
    if ($vendor_count->vendor_count > 1) {
        return 1;
    } else {
        return 0;
    }
}

function get_vendor($id)
{
    $result = App\Model\Vendor::find($id);
    return $result;
}

function csvToArray($filename = '', $delimiter = ',')
{
    if (!file_exists($filename) || !is_readable($filename))
        return false;

    $header = [];
    $data = array();
    if (($handle = fopen($filename, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if (!$header)
                $header = array_keys($row);
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }

    return $data;
}

function count_slot_orders($delivery_date, $delivery_slot)
{
    $delivery_slot_arr = explode('-', $delivery_slot);
    $new_slot = date('H:i', strtotime($delivery_slot_arr[0])) . '-' . date('H:i', strtotime($delivery_slot_arr[1]));

    $order_count = App\Model\Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
        ->where('order_delivery_slots.delivery_slot', '=', $new_slot)
        ->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($delivery_date)))
        ->where('order_payments.status', '!=', 'rejected')
        ->count();
    return $order_count;
}

function get_slots($date)
{
    $slots = App\Model\DeliverySlot::where(['date' => $date])->orderBy('from', 'asc')->get();
    return $slots;
}

function get_slot_settings($date)
{
    $slots = App\Model\DeliverySlotSetting::where(['start_date' => $date])->orderBy('from', 'asc')->get();
    return $slots;
}

function pp($param)
{
    echo "<pre>";
    print_r($param);
    die;
}

function get_product_dictionary($products, $user_id, $lang, $access_token)
{
    $latLong = OauthAccessToken::where(['id' => $access_token])->first();
    if ($latLong) {
        $latitude = $latLong->latitude;
        $longitude = $latLong->longitude;
    } else {
        $latitude = '';
        $longitude = '';
    }

    if ($user_id != '') {
        $user = User::find($user_id);
        $nearest_store = Store::where(['id' => $user->nearest_store, 'status' => 1, 'deleted' => 0])->first();
        if ($nearest_store) {
            $default_store = get_store_no($nearest_store->name);
        } else {
            $nearestStore = Store::select('id')
                ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $latitude . "')) * COS(
                                        RADIANS(latitude)
                                    ) * COS(
                                        RADIANS(longitude) - RADIANS('" . $longitude . "')
                                    ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                        RADIANS(latitude)
                                    )
                                )
                            ) AS distance")
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->orderBy('distance', 'asc')
                ->first();
            $default_store = get_store_no($nearestStore->name);
        }
    } else {
        $nearestStore = Store::select('id')
            ->selectRaw("(
                            6371 * ACOS(
                                COS(RADIANS('" . $latitude . "')) * COS(
                                    RADIANS(latitude)
                                ) * COS(
                                    RADIANS(longitude) - RADIANS('" . $longitude . "')
                                ) + SIN(RADIANS('" . $latitude . "')) * SIN(
                                    RADIANS(latitude)
                                )
                            )
                        ) AS distance")
            ->where('deleted', '=', 0)
            ->where('status', '=', 1)
            ->orderBy('distance', 'asc')
            ->first();
        $default_store = get_store_no($nearestStore->name);
    }

    // throw new Exception("Store: ".$default_store, 105);

    $product_arr = [];
    if ($products->count()) {
        foreach ($products as $key => $row) {
            $is_fav = App\Model\UserWishlist::where([
                'fk_user_id' => $user_id,
                'fk_product_id' => $row->id
            ])
                ->first();
            if ($is_fav) {
                $is_favorite = 1;
            } else {
                $is_favorite = 0;
            }

            $price_key = 'store' . $default_store . '_price';
            $product_arr[$key] = [
                'product_id' => $row->id,
                'product_category_id' => $row->fk_category_id,
                'product_sub_category_id' => $row->fk_sub_category_id,
                'product_category' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                'product_brand_id' => $row->brand_id ?? 0,
                'product_brand' => $lang == 'ar' ? $row->brand_name_ar ?? "" : $row->brand_name_en ?? "",
                'product_name' => $lang == 'ar' ? $row->product_name_ar : $row->product_name_en,
                'product_image' => $row->product_image_url ?? '',
                'product_price' => number_format($row->$price_key, 2),
                'product_price_before_discount' => number_format($row->$price_key, 2),
                'quantity' => $row->unit,
                'unit' => $row->unit,
                'is_favorite' => $is_favorite,
                'product_discount' => $row->margin,
                'min_scale' => $row->min_scale ?? '',
                'max_scale' => $row->max_scale ?? '',
                'country_code' => $row->country_code ?? '',
                'country_icon' => $row->country_icon ?? '',
                'order_created_at' => $row->order_created_at ?? '',
                'order_id' => $row->order_id ?? ''
            ];

            if ($user_id != '') {
                $cart_product = App\Model\UserCart::where(['fk_user_id' => $user_id, 'fk_product_id' => $row->id])->first();
                if ($cart_product) {
                    $single_product_quantity = $cart_product['quantity'];
                } else {
                    $single_product_quantity = 0;
                }
            } else {
                $single_product_quantity = 0;
            }
            $product_arr[$key]['cart_quantity'] = $single_product_quantity;
        }
    }
    return $product_arr;
}

function get_product_dictionary_bp($products, $user_id, $lang, $access_token)
{
    
    $product_arr = [];
    
    if ($products && $products->count()) {
        foreach ($products as $key => $row) {
            $is_fav = App\Model\UserWishlist::where([
                'fk_user_id' => $user_id,
                'fk_product_id' => $row->id,
            ])->first();
            if ($is_fav) {
                $is_favorite = 1;
            } else {
                $is_favorite = 0;
            }

            $product_arr[$key] = [
                'product_id' => $row->id,
                'product_category_id' => $row->fk_category_id,
                'product_sub_category_id' => $row->fk_sub_category_id,
                'product_category' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                'product_brand_id' => $row->brand_id ?? 0,
                'product_brand' => $lang == 'ar' ? $row->brand_name_ar ?? "" : $row->brand_name_en ?? "",
                'product_name' => $lang == 'ar' ? $row->product_name_ar : $row->product_name_en,
                'product_image' => $row->product_image_url ?? '',
                'product_image_url' => $row->product_image_url ?? '',
                'product_price' => $row->product_store_price,
                'product_price_before_discount' => $row->base_price ?? "0.00",
                'quantity' => $row->unit,
                'unit' => $row->unit,
                'is_favorite' => $is_favorite,
                'product_discount' => $row->margin ?? "0.00",
                'min_scale' => $row->min_scale ?? '',
                'max_scale' => $row->max_scale ?? '',
                'country_code' => $row->country_code ?? '',
                'country_icon' => $row->country_icon ?? '',
                'order_created_at' => $row->order_created_at ?? '',
                'order_id' => $row->order_id ?? '',
                'fk_product_store_id' => $row->fk_product_store_id,
                'fk_store_id' => $row->fk_store_id,
                'product_store_price' => $row->product_store_price,
                'product_store_stock' => $row->product_store_stock
            ];

            if ($user_id != '') {
                $cart_product = App\Model\UserCart::where(['fk_user_id' => $user_id, 'fk_product_store_id' => $row->id])->first();
                if ($cart_product) {
                    $single_product_quantity = $cart_product['quantity'];
                } else {
                    $single_product_quantity = 0;
                }
            } else {
                $single_product_quantity = 0;
            }
            $product_arr[$key]['cart_quantity'] = $single_product_quantity;
        }
    }
    return $product_arr;
}

function getGeoCode($address)
{
    // geocoding api url
    $url = "https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyCErOS8XDmqBPa5HtTVcUSNhAadrrhfVa4";
    // send api request
    $geocode = file_get_contents($url);
    $json = json_decode($geocode);
    $data['lat'] = $json->results[0]->geometry->location->lat;
    $data['lng'] = $json->results[0]->geometry->location->lng;
    return $data;
}

function check_all_orders_completed_in_slot($delivery_date, $delivery_slot)
{
    $delivery_slot_arr = explode('-', $delivery_slot);
    $new_slot = date('H:i', strtotime($delivery_slot_arr[0])) . '-' . date('H:i', strtotime($delivery_slot_arr[1]));

    $total_orders = App\Model\Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->where('order_delivery_slots.delivery_slot', '=', $new_slot)
        ->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($delivery_date)))
        ->count();

    $completed_orders = App\Model\Order::join('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
        ->where('order_delivery_slots.delivery_slot', '=', $new_slot)
        ->where('order_delivery_slots.delivery_date', '=', date('Y-m-d', strtotime($delivery_date)))
        ->whereIn('orders.status', [4, 7])
        ->count();

    if ($total_orders == $completed_orders) {
        return true;
    } else {
        return false;
    }
}

function updateDriverAvailability($driver_id)
{
    $vehicleCapacity = \App\Model\Driver::join('vehicles', 'drivers.fk_vehicle_id', '=', 'vehicles.id')
        ->select('vehicles.vehicle_capacity')
        ->where('drivers.id', '=', $driver_id)
        ->first(); //1,1,3
    $incompleteOrders = \App\Model\OrderDriver::join('orders', 'order_drivers.fk_order_id', '=', 'orders.id')
        ->where('order_drivers.fk_driver_id', '=', $driver_id)
        ->where('orders.status', '!=', 7)
        ->count(); //1,1,(1
    // echo $vehicleCapacity->vehicle_capacity.'<br>';
    // echo $incompleteOrders;die;
    //3-1 == 3 

    if ($incompleteOrders == 0) { //1==0,2==0,3==0
        \App\Model\Driver::find($driver_id)->update(['is_available' => 1]);
    } else {
        //having some orders not completed
        if ($incompleteOrders >= $vehicleCapacity->vehicle_capacity) { //1>=3,2>=3,3>=3
            \App\Model\Driver::find($driver_id)->update(['is_available' => 0]);
        }
    }
}

function getSadadAccessToken()
{
    $wspath = 'https://api.sadadqatar.com/api-v4/';

    $requestData = array();
    $requestData = '{
                        "sadadId": "6571676",
                        "secretKey": "6HQQg5wq3nmwbK3/",
                        "domain": "jeeb.tech"
                    }';

    $ch = curl_init($wspath . 'userbusinesses/getsdktoken');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, @$requestData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(@$requestData)
    ));
    $returndata = curl_exec($ch) . "\n";
    curl_close($ch);

    if ($returndata) {
        return $returndata;
    } else {
        return json_encode(['accessToken' => uniqid()]);
    }
}

function getPaymentStatusFromMyFatoorah($paymentResponseArr)
{
    if (!isset($paymentResponseArr) || empty($paymentResponseArr)) {
        return "rejected";
    }
    if (isset($paymentResponseArr->error_code)) {
        if ($paymentResponseArr->error_code == 0) {
            $status = "success";
        } else {
            if (isset($paymentResponseArr->InvoiceId) && empty($paymentResponseArr->InvoiceId)) {
                $status = "rejected";
            }
        }
    } else {
        if (isset($paymentResponseArr->InvoiceStatus) && $paymentResponseArr->InvoiceStatus == 'Paid') {
            $status = "success";
        }
    }

    if (isset($paymentResponseArr->InvoiceId) && $paymentResponseArr->InvoiceId!="") {
            
        $apiURL = 'https://api.myfatoorah.com/v2/';
        $apiKey = 'XYgwpDLlIL3BVm_dvAFepg55uKVdKlz--1bMhtO4qhyAZYFVeqO51O4IPHTwahBXPI-FfA4W0Q3m5QCm9JZ4Ql-Q3LuIPpK88TomNQL7L4X2jCOHRJVMsETrjUsdlYU65p2ET8SaoBbTQbGupAn611tAGlLFNnHaNVo4wbUZwGwJsjGnNBfpEnNZwIRN-6UTzRbvc3lLjQzC5lhIyA-Ai6NymOuWHFRFRwb9LNd96yHp_Z6QhWhPDVJYzSys4ICfCQuKRZ71o1xzlMtpIHaLMkugiiH9Nuu3H_ODjntxtSroC61kpB93-Mj6uJ_L-eOKDSOF4hu88NpO1MgtTQvcENm1FWGVUynKnIbCLfS26c8ScObJcZseiCEtmI_O514GP6qj0a7JscE5l5aSpGE1HgbFtJpkvUmW31OzpJ7AJuyQTNj-nOnfOoR8hcM1FhTx2IU5i9e6Q4Q7IPbYagPWYgQjyMVvsBD-XdtgaILwxIkGz2uMi3dRnCUSwqMAvw9OWD56Pahi_ezdwIoyyujestMBcA615qVJ7SsQfCac6o3B3QYYlzFY0l2UbDfxggG5CN0gwWqulPDkfImEP2mHuZImfVtvNZaC48kjQ32X4IyOZePnjxCI2TmEjCZgwptstFALFdrqBOtUtEHVtHQUOZa6ZdEI2dG3NpQGIbqNh8gGEt6i';
    
        $endpointURL = $apiURL . 'getPaymentStatus';
    
        $postFields = [
            'Key' => $paymentResponseArr->InvoiceId,
            'KeyType' => "invoiceId"
        ];
    
        //Call endpoint
        $res = callAPI($endpointURL, $apiKey, $postFields);

        if (isset($res) && isset($res->Data) && isset($res->Data->InvoiceStatus)) {
            switch ($res->Data->InvoiceStatus) {
                case "Pending":
                    if ($res->Data->InvoiceTransactions[0]->ErrorCode != '') {
                        $status = "rejected";
                    } else {
                        $status = "rejected";
                    }
                    break;
                case "Paid":
                    $status = "success";
                    break;
                case "Canceled":
                    $status = "rejected";
                    break;
                default:
                    $status = "rejected";
            }
        }
    
    } else {
        $status = "blocked";
    }
    return $status;
}

function getPaymentStatusAndAmountFromMyFatoorah($paymentResponseArr)
{
    $amount = 0;
    if (!isset($paymentResponseArr) || empty($paymentResponseArr)) {
        return ['status'=>"rejected",'amount'=>$amount];;
    }
    
    if (isset($paymentResponseArr->error_code)) {
        if ($paymentResponseArr->error_code == 0) {
            $status = "success";
        } else {
            if (isset($paymentResponseArr->InvoiceId) && empty($paymentResponseArr->InvoiceId)) {
                $status = "rejected";
            }
        }
    } else {
        if (isset($paymentResponseArr->InvoiceStatus) && $paymentResponseArr->InvoiceStatus == 'Paid') {
            $status = "success";
        }
    }

    if (isset($paymentResponseArr->InvoiceId) && $paymentResponseArr->InvoiceId!="") {
        $apiURL = 'https://api.myfatoorah.com/v2/';
        $apiKey = 'XYgwpDLlIL3BVm_dvAFepg55uKVdKlz--1bMhtO4qhyAZYFVeqO51O4IPHTwahBXPI-FfA4W0Q3m5QCm9JZ4Ql-Q3LuIPpK88TomNQL7L4X2jCOHRJVMsETrjUsdlYU65p2ET8SaoBbTQbGupAn611tAGlLFNnHaNVo4wbUZwGwJsjGnNBfpEnNZwIRN-6UTzRbvc3lLjQzC5lhIyA-Ai6NymOuWHFRFRwb9LNd96yHp_Z6QhWhPDVJYzSys4ICfCQuKRZ71o1xzlMtpIHaLMkugiiH9Nuu3H_ODjntxtSroC61kpB93-Mj6uJ_L-eOKDSOF4hu88NpO1MgtTQvcENm1FWGVUynKnIbCLfS26c8ScObJcZseiCEtmI_O514GP6qj0a7JscE5l5aSpGE1HgbFtJpkvUmW31OzpJ7AJuyQTNj-nOnfOoR8hcM1FhTx2IU5i9e6Q4Q7IPbYagPWYgQjyMVvsBD-XdtgaILwxIkGz2uMi3dRnCUSwqMAvw9OWD56Pahi_ezdwIoyyujestMBcA615qVJ7SsQfCac6o3B3QYYlzFY0l2UbDfxggG5CN0gwWqulPDkfImEP2mHuZImfVtvNZaC48kjQ32X4IyOZePnjxCI2TmEjCZgwptstFALFdrqBOtUtEHVtHQUOZa6ZdEI2dG3NpQGIbqNh8gGEt6i';

        $endpointURL = $apiURL . 'getPaymentStatus';

        $postFields = [
            'Key' => $paymentResponseArr->InvoiceId,
            'KeyType' => "invoiceId"
        ];

        //Call endpoint
        $res = callAPI($endpointURL, $apiKey, $postFields);

        if (isset($res) && isset($res->Data) && isset($res->Data->InvoiceStatus)) {
            switch ($res->Data->InvoiceStatus) {
                case "Pending":
                    if ($res->Data->InvoiceTransactions[0]->ErrorCode != '') {
                        $status = "rejected";
                    } else {
                        $status = "rejected";
                    }
                    break;
                case "Paid":
                    $status = "success";
                    break;
                case "Canceled":
                    $status = "rejected";
                    break;
                default:
                    $status = "rejected";
            }
        }
        
        $amount = $res->Data->InvoiceValue;
    } else {
        $status = "blocked";
    }
    return ['status'=>$status,'amount'=>$amount];
}

function callAPI($endpointURL, $apiKey, $postFields = [])
{

    $curl = curl_init($endpointURL);
    curl_setopt_array($curl, array(
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postFields),
        CURLOPT_HTTPHEADER => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
        CURLOPT_RETURNTRANSFER => true,
    ));

    $response = curl_exec($curl);
    $curlErr = curl_error($curl);

    curl_close($curl);

    if ($curlErr) {
        //Curl is not working in your server
        die("Curl Error: $curlErr");
    }

    $error = handleError($response);
    if ($error) {
        die("Error: $error");
    }

    return json_decode($response);
}

function callGetAPI($endpointURL)
{

    $curl = curl_init($endpointURL);
    curl_setopt_array($curl, array(
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_RETURNTRANSFER => true,
    ));

    $response = curl_exec($curl);
    $curlErr = curl_error($curl);

    curl_close($curl);

    if ($curlErr) {
        return null;
    } else {
        return json_decode($response);
    }
}

function handleError($response)
{

    $json = json_decode($response);
    if (isset($json->IsSuccess) && $json->IsSuccess == true) {
        return null;
    }

    //Check for the errors
    if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
        $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
        $blogDatas = array_column($errorsObj, 'Error', 'Name');

        $error = implode(', ', array_map(function ($k, $v) {
            return "$k: $v";
        }, array_keys($blogDatas), array_values($blogDatas)));
    } else if (isset($json->Data->ErrorMessage)) {
        $error = $json->Data->ErrorMessage;
    }

    if (empty($error)) {
        $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
    }

    return $error;
}

function getRoundedOffNumber($number)
{
    $reqNum = $number;
    $floor = floor($number);
    $decPart = $number - $floor;

    // Rounding decimal numbers
    $newDecPart = 0.00;
    if ($floor>=5) {
        if ($decPart>0.75) {
            $newDecPart = 0.99;
        }
        elseif ($decPart>0.50) {
            $newDecPart = 0.75;
        }
        elseif ($decPart>0.25) {
            $newDecPart = 0.50;
        }
        elseif ($decPart>0.00) {
            $newDecPart = 0.25;
        }
        $reqNum = $floor + $newDecPart;
    }

    return number_format($reqNum, 2);
}

function calculatePriceFromFormula($distributor_price, $offer_option_id=0, $brand_id=0, $sub_category_id=0, $store_id=0)
{

    // Returning variables
    $selling_price = 0;
    $base_price = 0;
    $price_percentage = 0;
    $base_price_percentage = 0;
    $discount_percentage = 0;
    $price_formula_id = 0;

    // Price formula objects
    $price_formula = false;
    $offer_formula = false;
    $offer_option = false;

    // If product level offer option sent
    if($offer_option_id !=0){

        $offer_option = ProductOfferOption::find($offer_option_id);

    } else {

        // If brand, subcategory, store level offer option found
        $brand_test = $brand_id ? true : false;
        $sub_category_test = $sub_category_id ? true : false;
        $store_test = $store_id ? true : false;
        $global_test = true;
        $conditions = [
            [$brand_id, $sub_category_id, $store_id, $brand_test], // brand, subcategory, store combo
            [$brand_id, $sub_category_id, 0, $brand_test], // brand, subcategory combo
            [$brand_id, 0, 0, $brand_test], // brand
            [0, $sub_category_id, $store_id, $sub_category_test], // subcategory , store
            [0, $sub_category_id, 0, $sub_category_test], // subcategory
            [0, 0, $store_id, $store_test], // store
            [0, 0, 0, $global_test] // global store
        ];

        foreach ($conditions as $key => $condition) { 

            // Skip checking if no data sent
            if (!$condition[3]) {
                continue;
            }
            // Check for available function
            $brand_id = $condition[0];
            $sub_category_id = $condition[1];
            $store_id = $condition[2];

            $query = \App\Model\PriceFormula::where('fk_offer_option_id','!=', 0);
            
            if ($brand_id !== null || $brand_id !==0) {
                $query->where('fk_brand_id', '=', $brand_id);
            }
            if ($sub_category_id !== null || $sub_category_id !==0) {
                $query->where('fk_subcategory_id', '=', $sub_category_id);
            }
            if ($store_id !== null || $store_id !==0) {
                $query->where('fk_store_id', '=', $store_id);
            }

            $offer_formula = $query->first();

            if ($offer_formula !== null) {
                $price_formula_id = $offer_formula->id;  
                break; // Exit the loop if a match is found
            }
        }
        
        if($offer_formula){

            $offer_option = ProductOfferOption::find($offer_formula->fk_offer_option_id);

        } else {
            
            // If no offer option found, collect pricing formula
            foreach ($conditions as $condition) {

                // Skip checking if no data sent
                if (!$condition[3]) {
                    continue;
                }
                // Check for available function
                $brand_id = $condition[0];
                $sub_category_id = $condition[1];
                $store_id = $condition[2];
    
                $query = \App\Model\PriceFormula::whereRaw("x1 < $distributor_price AND x2 >= $distributor_price");
                
                if ($brand_id !== null || $brand_id !==0) {
                    $query->where('fk_brand_id', '=', $brand_id);
                }
                if ($sub_category_id !== null || $sub_category_id !==0) {
                    $query->where('fk_subcategory_id', '=', $sub_category_id);
                }
                if ($store_id !== null || $store_id !==0) {
                    $query->where('fk_store_id', '=', $store_id);
                }
    
                $price_formula = $query->first();
    
                if ($price_formula !== null) {
                    $price_formula_id = $price_formula->id;        
                    break; // Exit the loop if a match is found
                }
            }
            
        }

    }

    if ($offer_option) {

        // If offer option found, calculate base price and selling price
        $base_price = getRoundedOffNumber($distributor_price + ($distributor_price * ($offer_option->base_price_percentage / 100)));
        $selling_price = floatval(str_replace(',','',$base_price)) - (floatval(str_replace(',','',$base_price)) * ($offer_option->discount_percentage / 100));
        $selling_price  = getRoundedOffNumber($selling_price);

        $price_percentage = $distributor_price>0 ? ((floatval(str_replace(',','',$selling_price)) - $distributor_price) / $distributor_price * 100) : 0;
        
        \Log::info('Price calculation with offer option: '.$offer_option->id.' for distributor_price:'.$distributor_price.' selling_price:'.$selling_price.', base_price:'.$base_price);
        
    } elseif ($price_formula) {

        // Calculate base price and selling price based on selected pricing formula
        $x1 = $price_formula->x1;
        $x2 = $price_formula->x2;
        $x3 = $price_formula->x3;
        $x4 = $price_formula->x4;

        if ($x4 == '0') { //condition in which distributor price is more than 200
            $price_percentage = $x3;
        } else {
            $numerator = ($distributor_price - $x1) * $price_formula->x3x4;

            $price_percentage = $price_formula->x2x1>0 ? ($price_formula->x3x4 - (($numerator) / $price_formula->x2x1)) + $x4 : $x4;
        }

        $selling_price = $distributor_price + ($distributor_price * $price_percentage / 100);
        $selling_price = getRoundedOffNumber($selling_price);
        $base_price = floatval(str_replace(',','',$selling_price));
        
        \Log::info('Price calculation for distributor_price:'.$distributor_price.', x1 '.$x1.' x2 '.$x2.' x3 '.$x3.' x4 '.$x4.' x3x4 '.$price_formula->x3x4.' x2x1 '.$price_formula->x2x1);
        
    } else {
        
        \Log::info('Price calculation formula not found for distributor_price:'.$distributor_price.', offer_option_id:'.$offer_option_id.', brand_id:'.$brand_id.', sub_category_id:'.$sub_category_id.', store_id:'.$store_id);
    
    }

    $selling_price = floatval(str_replace(',','',$selling_price));
    $base_price = floatval(str_replace(',','',$base_price));
    
    $base_price_percentage = $distributor_price>0 ? number_format(((($base_price-$distributor_price)/$distributor_price) * 100), 2) : 0;
    $discount_percentage = $base_price>0 ? number_format(((($base_price-$selling_price)/$base_price) * 100), 2) : 0;
    
    return [$selling_price, $price_percentage, $base_price, $base_price_percentage, $discount_percentage, $price_formula_id];
    
}

function get_all_stores()
{
    $result = App\Model\Store::where(['status' => 1, 'deleted' => 0])->orderBy('name', 'asc')->get();
    return $result;
}

function get_store_no($store_name) {
    $default_store_no = (int)trim(str_replace("Store ","",$store_name));
    return $default_store_no;
}
function get_store_no_string($default_store_no) {
    return 'store'.$default_store_no;
}

function getDeliveryIn($time_elapsed)
{
    $seconds     = $time_elapsed;
    $minutes     = round($time_elapsed / 60);
    $hours         = round($time_elapsed / 3600);
    $days         = round($time_elapsed / 86400);
    $weeks         = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640);
    $years         = round($time_elapsed / 31207680);

    // Seconds
    if ($seconds <= 60) {
        return "Just now";
    }
    //Minutes
    else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "1 minute";
        } else {
            return "$minutes minutes";
        }
    }
    //Hours
    else if ($hours <= 24) {
        if ($hours == 1) {
            return "1 hour";
        } else {
            return "$hours hours";
        }
    }
    //Days
    else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    }
    //Weeks
    else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return "a week";
        } else {
            return "$weeks weeks";
        }
    }
    //Months
    else if ($months <= 12) {
        if ($months == 1) {
            return "a month";
        } else {
            return "$months months";
        }
    }
    //Years
    else {
        if ($years == 1) {
            return "one year";
        } else {
            return "$years years";
        }
    }
}

function getDeliveryInTimeRange($time_elapsed, $time_now, $lang, $later_time=false)
{
    $seconds = $time_elapsed;
    // $time = date("dS M H:i", strtotime($time_now) + $seconds);
    $time = date("Y.m.d\\TH:i", strtotime($time_now) + $seconds);

    // Display today tomorrow etc.
    $today = new DateTime("today"); 
    $match_date = DateTime::createFromFormat( "Y.m.d\\TH:i", $time );
    $day = '';

    if ($match_date) {
        $match_date->setTime( 0, 0, 0 ); 

        $diff = $today->diff( $match_date );
        $diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

        switch( $diffDays ) {
            case 0:
                $day = $lang=='ar' ? "اليوم" : "today,";
                break;
            // case -1:
            //     $day = "yesterday,";
            //     break;
            case +1:
                $day = $lang=='ar' ? "غداً" : "tomorrow,";
                break;
            default:
                $day = date("dS M", strtotime($time_now) + $seconds);
        }
    }

    $time = $later_time ? $later_time : date("h:i A", strtotime($time_now) + $seconds);
    $time_and_day = $day." ".$time;
    return $time_and_day;
}

function encript_api_keys_type1($key) {
    $rand = substr(md5(microtime()),rand(0,26),5);
    $key = substr_replace($key, $rand, 5, 0);
    return base64_encode($key);
}

function get_delivery_cost($ivp='p', $user_mobile = false, $user_id=0) {
    if ($ivp=='i') {
        $delivery_cost = \App\Model\AdminSetting::where(['key' => 'delivery_cost_instant'])->first();
    } else {
        $delivery_cost = \App\Model\AdminSetting::where(['key' => 'delivery_cost'])->first();
    }
    $delivery_cost = $delivery_cost ? $delivery_cost->value : 0;
    // Set no delivery cost always
    if ($user_mobile) {
        if ($user_mobile=='12345671' || $user_mobile=='87654321') {
            $delivery_cost = "0";
        }
    }
    return $delivery_cost;
}

function get_minimum_purchase_amount_for_free_delivery($ivp='p') {
    if ($ivp=='i') {
        $mpa = \App\Model\AdminSetting::where(['key' => 'minimum_purchase_amount_for_free_delivery_instant'])->first();
        $mp_amount = $mpa ? $mpa->value : 100;
    } else {
        $mpa = \App\Model\AdminSetting::where(['key' => 'minimum_purchase_amount_for_free_delivery'])->first();
        $mp_amount = $mpa ? $mpa->value : 10;
    }
    return $mp_amount;
}

function get_minimum_purchase_amount($ivp='p', $user_mobile = false, $user_id=0) {
    if ($ivp=='i') {
        $mpa = \App\Model\AdminSetting::where(['key' => 'minimum_purchase_amount_instant'])->first();
    } else {
        $mpa = \App\Model\AdminSetting::where(['key' => 'minimum_purchase_amount'])->first();
    }
    $mp_amount = $mpa ? $mpa->value : 65;
    //Test numbers array
    // if ($user_mobile) {
    //     $test_numbers = explode(',', env("TEST_NUMBERS"));
    //     if (in_array($user_mobile, $test_numbers) || $user_id==15751) { // Removing minimum amount for test numbers and tester's actual number
    //         $mp_amount = "1";
    //     }
    //     if ($user_mobile=='50122838' || $user_mobile=='70825711') {
    //         $mp_amount = "1";
    //     }
    // }
    return $mp_amount;
}

function get_ivp_feature_mode($user_mobile = false, $user_id=0) {
    //Test numbers array
    $ivp_feature = env("IVP_Feature", false);
    if ($user_mobile) {
        $test_numbers = explode(',', env("TEST_NUMBERS"));
        if ( in_array($user_mobile, $test_numbers) ) { 
            $ivp_feature = env("IVP_feature_for_test_numbers", false);
        }
    } 
    return $ivp_feature;
}

function get_userWallet($user_id) {
    $userWallet = \App\Model\UserWallet::where(['fk_user_id' => $user_id])->first();
    $wallet_balance = $userWallet ? $userWallet->total_points : '0.00';
    return $wallet_balance;
}

function get_userAddressList($user_id) {
    $address_arr = [];
    if ($user_id) {
        $all_addresses = \App\Model\UserAddress::where(['fk_user_id' => $user_id, 'deleted' => 0])->orderBy('id', 'desc')->get();
        if ($all_addresses->count()) {
            foreach ($all_addresses as $key => $row) {
                $address_arr[$key] = [
                    'id' => $row->id,
                    'name' => $row->name ?? '',
                    'mobile' => $row->mobile ?? '',
                    'landmark' => $row->landmark ?? '',
                    'address_line1' => $row->address_line1 ?? '',
                    'address_line2' => $row->address_line2 ?? '',
                    'latitude' => $row->latitude ?? '',
                    'longitude' => $row->longitude ?? '',
                    'address_type' => $row->address_type,
                    'image' => $row->getAddressImage ? asset('/') . $row->getAddressImage->file_path . $row->getAddressImage->file_name : '',
                    'is_default' => $row->is_default,
                    'blocked_timeslots' => $row->blocked_timeslots,
                    'created_at' => date('Y-m-d H:i:s', strtotime($row->created_at)),
                    'updated_at' => date('Y-m-d H:i:s', strtotime($row->updated_at))
                ];
            }
        } 
    }
    return $address_arr;
}

function get_storeTimeslotBlockings() {
    $all_stores = \App\Model\Store::select('id','blocked_timeslots')->where(['status' => 1, 'deleted' => 0])->orderBy('id', 'desc')->get();
    return $all_stores;
}

function get_userSelectedAddress($user_id) {
    $address = [];
    if ($user_id) {
        $row = \App\Model\UserAddress::where(['fk_user_id' => $user_id, 'deleted' => 0, 'is_default' => 1])->orderBy('id', 'desc')->first();
        if ($row) {
            $address = [
                'id' => $row->id,
                'name' => $row->name ?? '',
                'mobile' => $row->mobile ?? '',
                'landmark' => $row->landmark ?? '',
                'address_line1' => $row->address_line1 ?? '',
                'address_line2' => $row->address_line2 ?? '',
                'latitude' => $row->latitude ?? '',
                'longitude' => $row->longitude ?? '',
                'address_type' => $row->address_type,
                'image' => $row->getAddressImage ? asset('/') . $row->getAddressImage->file_path . $row->getAddressImage->file_name : '',
                'is_default' => $row->is_default,
                'blocked_timeslots' => $row->blocked_timeslots,
                'created_at' => date('Y-m-d H:i:s', strtotime($row->created_at)),
                'updated_at' => date('Y-m-d H:i:s', strtotime($row->updated_at))
            ];
        } 
    }
    return $address;
}

// Get delivery time text
function get_delivery_time_in_text($expected_eta, $ivp='p') {
    // Disable delivery time for plus model
    $delivery_time = '';
    if ($ivp=='i') {
        $delivery_time = "" . getDeliveryIn($expected_eta);
        $store_open_time = env("STORE_OPEN_TIME");
        $store_close_time = env("STORE_CLOSE_TIME");
        $store_open_time_a = date("g:i a", strtotime($store_open_time.":00"));
        $store_close_time_a = date("g:i a", strtotime($store_close_time.":00"));
        if(strtotime($store_open_time) > time()) {
            $delivery_time = "delivery at $store_open_time_a today";
        } elseif (strtotime($store_close_time) < time()) {
            $delivery_time = "delivery at $store_open_time_a tomorrow";
        } 
    }
    return $delivery_time;
}

// Add user tracking data
function add_tracking_data($user_id,$key,$request,$response,$tracking_id=0) {
    if ($tracking_id==0) {
        $user_tracking = \App\Model\UserTracking::create([
            'user_id'=>$user_id,
            'key'=>$key,
            'request'=>$request,
            'response'=>$response
        ]);
        $tracking_id = $user_tracking->id;
    } else {
        \App\Model\UserTracking::find($tracking_id)->update([
            'user_id'=>$user_id,
            'key'=>$key,
            'request'=>$request,
            'response'=>$response
        ]);
    }
    return $tracking_id;
}
function update_tracking_response($tracking_id,$response) {
    $user_tracking = \App\Model\UserTracking::find($tracking_id)->update(['response'=>$response]);
    return $user_tracking;
}
function update_tracking_request($tracking_id,$request) {
    $user_tracking = \App\Model\UserTracking::find($tracking_id)->update(['request'=>$request]);
    return $user_tracking;
}
function update_tracking_userid($tracking_id,$user_id) {
    $user_tracking = \App\Model\UserTracking::find($tracking_id)->update(['user_id'=>$user_id]);
    return $user_tracking;
}
function update_tracking_key($tracking_id,$key) {
    $user_tracking = \App\Model\UserTracking::find($tracking_id)->update(['key'=>$key]);
    return $user_tracking;
}
function update_tracking_userid_and_key($tracking_id,$key,$user_id) {
    $user_tracking = \App\Model\UserTracking::find($tracking_id)->update(['key'=>$key,'user_id'=>$user_id]);
    return $user_tracking;
}
function can($perm,$guard = 'admin')
{
    $admin = Auth::guard($guard)->id();
    $admin = Admin::find(auth()->guard($guard)->id());
    $check_list = array();
    foreach ($admin->getRole()->where('status','1')->get() as $key => $value) {
        $check_list = array_merge($check_list,$value->permissions()->pluck('permissions.slug')->toArray());
        $check_list = array_unique($check_list);
    }
    return in_array($perm, $check_list);
}

function update_product_discount_from_csv($product, $base_price = 'not-assigned')
{
    $total_base_broduct_stock = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0])->sum('stock');
    $base_product_store = BaseProductStore::where(['fk_product_id' => $product,['stock','>',0],'deleted'=> 0])->orderby('product_price','asc')->first();

    if($base_price != 'not-assigned'){
        $update_arr = [
            'base_price' => $base_price
        ];
    }

    if($base_product_store){

        $update_arr = [
            'fk_product_store_id' => $base_product_store->id,
            'fk_store_id' => $base_product_store->fk_store_id,
            'product_distributor_price' => $base_product_store->distributor_price,
            'product_store_price' => $base_product_store->product_store_price,
            'product_store_stock' => $total_base_broduct_stock,
            'product_store_updated_at' => date('Y-m-d H:i:s'),
        ];

    }else{

        $update_arr = [
            'product_store_stock' => 0,
            'product_store_updated_at' => date('Y-m-d H:i:s')
        ];
    }

    $base_product = BaseProduct::find($product);
    if($base_product){
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

/* ============Send sms by Twillio api============== */
function mobile_sms_curl($msg, $phone) {
    //echo $phone;die;
    $id = "AC4381447b4e2460bc6aba0c8a27554a96";
    $token = "3a4041c90b36c6396828c0f0ef5db264";
    $url = "https://api.twilio.com/2010-04-01/Accounts/AC4381447b4e2460bc6aba0c8a27554a96/Messages.json";
    $from = "Jeeb";
//        $from = "9389991514";
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
//        print_r($y);exit;
    return true;
}
