<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use App\Model\AdminSetting;
use App\Model\DeliveryArea;
use App\Model\InstantStoreGroup;
use App\Model\InstantStoreGroupStore;
use App\Model\Store;
use App\Model\Company;
use App\Model\User;
use App\Model\UserCart;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\UserAddress;
use App\Model\OauthAccessToken;
use App\Model\UserDeviceDetail;

class AddressController extends CoreApiController
{

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = [
            'add_new_address', 'update_address', 'delete_address',
            'address_list', 'set_default_address', 'get_default_address'
        ];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->user_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }
    }

    protected function address_list(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $home_static = \App\Model\HomeStatic::orderBy('id','desc')->first();
            $home_static_last_id = $home_static ? $home_static->id : 0;
            $home_static_data_feeded = $home_static ? $home_static->home_static_data_feeded : 0;

            $home_static_version = env("HOME_STATIC_VERSION", false).$home_static_last_id.$home_static_data_feeded;

            if ($this->user_id!='') {
                $all_addresses = UserAddress::where(['fk_user_id' => $this->user_id, 'deleted' => 0])->orderBy('id', 'desc')->get();
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
                            'created_at' => date('Y-m-d H:i:s', strtotime($row->created_at)),
                            'updated_at' => date('Y-m-d H:i:s', strtotime($row->updated_at))
                        ];
                    }
    
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                    $this->result = [
                        'address_list' => $address_arr,
                        'home_static_version' => $home_static_version
                    ];
                } else {
                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "نجحت العملية" : "Success";
                    $this->result = [
                        'address_list' => [],
                        'home_static_version' => $home_static_version
                    ];
                }
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'address_list' => [],
                    'home_static_version' => $home_static_version
                ];
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

    protected function add_new_address(Request $request)
    {
        try {
            $lang = $request->header('lang');

            // Validation
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            if (empty($latitude) || empty($longitude)) {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "Lat and long are required";
                throw new Exception($error_message, 105);
            }
            $this->required_input($request->input(), ['latitude', 'longitude']);

            // Check delivery area
            $getdistance = DeliveryArea::select("radius","blocked_timeslots")
                ->selectRaw("(
                                6371 * ACOS(
                                    COS(RADIANS('" . $request->input('latitude') . "')) * COS(
                                        RADIANS(latitude)
                                    ) * COS(
                                        RADIANS(longitude) - RADIANS('" . $request->input('longitude') . "')
                                    ) + SIN(RADIANS('" . $request->input('latitude') . "')) * SIN(
                                        RADIANS(latitude)
                                    )
                                )
                            ) AS distance")
                ->havingRaw("radius >= distance")
                ->get();
            if ($getdistance->count()) {

                $blocked_time = 0;
                foreach ($getdistance as $area) {
                    if ($area->blocked_timeslots > 0 && $area->blocked_timeslots > $blocked_time) {
                        $blocked_time = $area->blocked_timeslots;
                    }
                }
                
                $insert_arr = [
                    'fk_user_id' => $this->user_id,
                    'name' => $request->input('name'),
                    'mobile' => $request->input('mobile'),
                    'landmark' => $request->input('landmark'),
                    'address_line1' => $request->input('address_line1'),
                    'address_line2' => $request->input('address_line2'),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'address_type' => $request->input('address_type'), //1:office,2:home,3:others
                    'is_default' => $request->input('is_default') ?? 0,
                    'blocked_timeslots' => $blocked_time
                ];
                $add = UserAddress::create($insert_arr);
                if ($add) {

                    // Save user address location as image
                    // if ($request->input('latitude') != '' && $request->input('longitude') != '') {
                    //     $map_marker = asset('/') . '/assets/images/map-marker2x.png';
                    //     $url = "https://maps.googleapis.com/maps/api/staticmap?zoom=17&size=152x152&maptype=roadmap&markers=icon:$map_marker|" . $request->input('latitude') . "," . $request->input('longitude') . "&key=AIzaSyDGBWqzz573j5ZKwJlG9eHvhC05vCCI_po";
                    //     $path = "/images/address_images/";

                    //     $data = file_get_contents($url);
                    //     $filePath = public_path($path) . 'address_image' . $add->id . '.png';
                    //     $upload = file_put_contents($filePath, $data);

                    //     if ($upload) :
                    //         $req = [
                    //             'file_path' => $path,
                    //             'file_name' => 'address_image' . $add->id . '.png',
                    //             'file_ext' => 'png'
                    //         ];
                    //         $returnArr = $this->insertFile($req);

                    //         $update_arr['image'] = $returnArr->id;

                    //         UserAddress::find($add->id)->update($update_arr);
                    //     endif;
                    // }

                    $this->error = false;
                    $this->status_code = 200;
                    $this->message = $lang == 'ar' ? "تم إضافة العنوان بنجاح" : "Address added successfully";
                    $this->result = ['address_id' => $add->id];
                } else {
                    $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                    throw new Exception($error_message, 105);
                }

            } else {
                $error_message = $lang == 'ar' ? "هذا العنوان ليس في منطقة التسليم" : "This address is not in the deliverable area";
                throw new Exception($error_message, 105);
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

    protected function update_address(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['id', 'name', 'address_line1', 'address_line2', 'address_type']);

            $address = UserAddress::find($request->input('id'));

            $update_arr = [
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'landmark' => $request->input('landmark'),
                'address_line1' => $request->input('address_line1'),
                'address_line2' => $request->input('address_line2'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'address_type' => $request->input('address_type'), //1:office,2:home,3:others
                'is_default' => $request->input('is_default') ? $request->input('is_default') : $address->is_default
            ];

            // Save user address location as image
            // if ($address->latitude != $request->input('latitude') && $address->longitude != $request->input('longitude')) {
            //     $map_marker = asset('/') . '/assets/images/map-marker2x.png';
            //     $url = "https://maps.googleapis.com/maps/api/staticmap?zoom=17&size=152x152&maptype=roadmap&markers=icon:$map_marker|" . $request->input('latitude') . "," . $request->input('longitude') . "&key=AIzaSyDGBWqzz573j5ZKwJlG9eHvhC05vCCI_po";
            //     $path = "/images/address_images/";

            //     $data = file_get_contents($url);
            //     $filePath = public_path($path) . 'address_image' . $request->input('id') . '.png';
            //     $upload = file_put_contents($filePath, $data);

            //     if ($upload) :
            //         $req = [
            //             'file_path' => $path,
            //             'file_name' => 'address_image' . $request->input('id') . '.png',
            //             'file_ext' => 'png'
            //         ];
            //         if ($address->image != '') {
            //             $destinationPath = public_path("images/address_images/");
            //             if (!empty($address->getAddressImage) && file_exists($destinationPath . $address->getAddressImage->file_name)) {
            //                 unlink($destinationPath . $address->getAddressImage->file_name);
            //             }
            //             $returnArr = $this->updateFile($req, $address->image);
            //         } else {
            //             $returnArr = $this->insertFile($req);
            //         }
            //         $update_arr['image'] = $returnArr->id;
            //     endif;
            // }

            $update = UserAddress::find($request->input('id'))->update($update_arr);
            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم تحديث العنوان بنجاح" : "Address updated successfully";
                $this->result = ['address_id' => $request->input('id')];
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
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

    protected function delete_address(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $this->required_input($request->input(), ['id']);

            $address = UserAddress::find($request->input('id'));
            if (!$address) {
                throw new Exception($lang == 'ar' ? "العنوان ليس موجود" : "Address doesn't exist", 105);
            }
            $flag = false;
            if ($address->is_default == 1) {
                $flag = true;
                throw new Exception($lang == 'ar' ? "لا يمكن حذف العنوان الافتراضي" : "Default address can not be deleted", 105);
            }
            $delete = UserAddress::find($request->input('id'))->update(['deleted' => 1, 'is_default' => 0]);
            if ($delete) {
                $exist = UserAddress::where(['fk_user_id' => $address->fk_user_id, 'deleted' => 0])->get();
                if ($flag && $exist->count()) {
                    UserAddress::find($exist[0]->id)->update(['is_default' => 1]);
                }

                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم حذف العنوان بنجاح" : "Address deleted successfully";
            } else {
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
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

    protected function set_default_address(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $tracking_id = add_tracking_data($this->user_id, 'set_default_address', $request, '');

            $access_token = $request->hasHeader('Authorization') ? $request->header('Authorization') : '';
            $arr = [];

            // Check selected address lat and long
            if ($request->input('id') != '') {
                $user_selected_address = UserAddress::find($request->input('id'));
                $arr['latitude'] = $user_selected_address->latitude;
                $arr['longitude'] = $user_selected_address->longitude;
            } else {
                $user_selected_address = false;
            }

            $arr['is_default'] = 1;
            if ($request->input('latitude') != '' && $request->input('latitude') != '') {
                $arr['latitude'] = $request->input('latitude');
                $arr['longitude'] = $request->input('longitude');
            }
            
            // add/update the device detail 
            if ($request->input('device_token')!= '') {
                $device_detail_arr = [
                    'fk_user_id' => $this->user_id,
                    'device_type' => $request->input('device_type') ?? 0, //1:iOS,2:android
                    'app_version' => $request->input('app_version') ?? 'not_detected',
                    'device_token' => $request->input('device_token') ?? '123456789',
                ];
                if (UserDeviceDetail::where(['fk_user_id' => $this->user_id])->first()) {
                    UserDeviceDetail::where(['fk_user_id' => $this->user_id])->update($device_detail_arr);
                } else {
                    UserDeviceDetail::create($device_detail_arr);
                }
            }

            // Process address
            if ($user_selected_address) {
                UserAddress::where(['fk_user_id' => $this->user_id])->update(['is_default' => 0]);
                $update = UserAddress::find($request->input('id'))->update($arr);
            } else {
                if ($this->user_id != '') {
                    UserAddress::where(['fk_user_id' => $this->user_id])->update(['is_default' => 0]);

                    $arr['address_type'] = 3;
                    $arr['name'] = 'noname';
                    $arr['fk_user_id'] = $this->user_id;

                    $exist = UserAddress::where(['fk_user_id' => $this->user_id, 'latitude' => $request->input('latitude'), 'longitude' => $request->input('longitude')])->first();
                    if (!$exist) {
                        $update = UserAddress::create($arr);
                    } else {
                        $update = UserAddress::find($exist->id)->update(['is_default' => 1]);
                    }
                } else {
                    $update = true;
                }
            }

            if ($update) {
                $ivp = $request->input('ivp');
                if (!($ivp=='i' || $ivp=='p' || $ivp=='m')) {
                    $ivp = 'p';
                }
                $nearest_store_id = 0;
                $expected_eta = 1800 + 1200; // Default ETA

                if ($ivp=='i') {

                    $get_nearest_instant_group = $this->get_nearest_instant_group($arr['latitude'], $arr['longitude']);
                    $nearest_store_id = $get_nearest_instant_group['nearest_store_id'];
                    $expected_eta = $get_nearest_instant_group['expected_eta']; // Default ETA

                }

                //saving lat/long in case user 
                if ($this->user_id != '') {

                    User::find($this->user_id)->update([
                        'nearest_store' => $nearest_store_id, 
                        'ivp' => $ivp, 
                        'expected_eta' => $expected_eta
                    ]);
                    // Check the cart
                    // $cartItems = UserCart::where(['fk_user_id' => $this->user_id])
                    // ->orderBy('id', 'desc')
                    // ->get();
                    // if ($cartItems->count()) {
                    //     foreach ($cartItems as $key => $value) { 
                    //         $product = $value->getProduct;
                    //         // If products are not belongs to the company
                    //         if ($product && $product->fk_company_id != $nearestStore->company_id) {
                    //             UserCart::where(['fk_user_id' => $this->user_id])->delete();     
                    //             $cart_emptied = true;
                    //         }
                    //     }
                    // }

                }

                //saving lat/long in case of guest user login
                if ($access_token!='') {
                    $isGuestUser = OauthAccessToken::where('id', '=', $access_token)->where('user_id', '=', NULL)->first();
                    if ($isGuestUser) {
                        OauthAccessToken::where(['id' => $access_token])
                            ->update([
                                'latitude' => $request->input('latitude'),
                                'longitude' => $request->input('longitude'),
                                'nearest_store' => $nearest_store_id, 
                                'ivp' => $ivp, 
                                'expected_eta' => $expected_eta
                            ]);
                    }
                }
                    
                $result = $this->get_default_address_data($this->user_id, $tracking_id, $access_token);
    
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "تم اختيار العنوان الاساسي" : "Address selected as default";
                $this->result = $result;
    
            } else {
                if (isset($tracking_id) && $tracking_id) {
                    update_tracking_response($tracking_id,'An error has been discovered');
                }
                $error_message = $lang == 'ar' ? "لقينا خلل بسيط" : "An error has been discovered";
                throw new Exception($error_message, 105);
            }
        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_nearest_instant_group($latitude, $longitude) {
        
        $nearest_store = InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
        ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
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
        ->where('instant_store_groups.deleted', '=', 0)
        ->where('instant_store_groups.status', '=', 1)
        ->orderBy('distance', 'asc')
        ->first();
        $nearest_store_id = $nearest_store->id;

        $admin_setting = AdminSetting::where(['key' => 'packing_time_instant'])->first();

        $eta_from_store_to_customer = (new UserAddress())->getExpectedETA($latitude, $longitude, $nearest_store->latitude, $nearest_store->longitude);
        $eta_for_packing = $admin_setting ? $admin_setting->value : 1200; // 1200 seconds default
        $expected_eta = $eta_from_store_to_customer + $eta_for_packing;    

        return array(
            'nearest_store' => $nearest_store,
            'nearest_store_id' => $nearest_store_id,
            'expected_eta' => $expected_eta,
        );

    }

    protected function get_default_address(Request $request)
    {
        try {

            $lang = $request->header('lang');
            $tracking_id = add_tracking_data($this->user_id, 'get_default_address', $request, '');
            $access_token = $request->hasHeader('Authorization') ? $request->header('Authorization') : '';
            
            $result = $this->get_default_address_data($this->user_id, $tracking_id, $access_token);

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang == 'ar' ? "تم اختيار العنوان الاساسي" : "Address selected as default";
            $this->result = $result;

        } catch (Exception $ex) {
            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        if (isset($tracking_id) && $tracking_id) {
            update_tracking_response($tracking_id,$this->makeJson());
        }
        $response = $this->makeJson();
        return $response;
    }
    
    protected function get_default_address_data($user_id, $tracking_id, $access_token='')
    {
        try {

            $cart_emptied = false;
            $user_address = false;
            $latitude = '';
            $longitude = '';
            $nearest_store_id = 0; // Default Store
            $nearest_store = false;
            $expected_eta = 1800 + 1200; // Default ETA
            $ivp = 'p';
            $user_mobile = false;
            $user = false;

            // Get default address for user
            if ($user_id != '') {
                $user = User::find($user_id);
                if ($user) {
                    $user_mobile = $user->mobile;
                }
                $user_address = UserAddress::where(['fk_user_id' => $user_id, 'is_default' => 1])->first();
                if ($user && $user_address) {
                    $latitude = $user_address->latitude ? $user_address->latitude : '';
                    $longitude = $user_address->longitude ? $user_address->longitude : '';
                    $nearest_store_id = $user->nearest_store ? $user->nearest_store : $nearest_store_id;
                    $expected_eta = $user->expected_eta ? $user->expected_eta : $expected_eta;
                    $ivp = $user->ivp;
                }
            } 
            else {
                // Get default address for guest
                if ($access_token && $access_token!='') {
                    $isGuestUser = OauthAccessToken::where('id', '=', $access_token)->whereNull('user_id')->first();
                    if ($isGuestUser) {
                        $latitude = $isGuestUser->latitude ? $isGuestUser->latitude : '';
                        $longitude = $isGuestUser->longitude ? $isGuestUser->longitude : '';
                        $nearest_store_id = $isGuestUser->nearest_store ? $isGuestUser->nearest_store : $nearest_store_id;
                        $expected_eta = $isGuestUser->expected_eta ? $isGuestUser->expected_eta : $expected_eta;
                        $ivp = $isGuestUser->ivp;
                    }
                } 
            }

            // IVP feature activations
            $ivp_feature = get_ivp_feature_mode($user_mobile, $user_id);

            // Get nearest instant group
            $sub_categories_to_hide_instant = [];
            if ($ivp=='i') {
                $nearest_store = InstantStoreGroup::join('stores','stores.id','instant_store_groups.fk_hub_id')
                        ->select('instant_store_groups.id', 'instant_store_groups.fk_hub_id', 'instant_store_groups.name', 'stores.name AS store_name',  'stores.company_name',  'stores.company_id', 'stores.latitude', 'stores.longitude')
                        ->where(['instant_store_groups.id'=>$nearest_store_id,'instant_store_groups.deleted'=>0,'instant_store_groups.status'=>1])
                        ->first();
                if (!$nearest_store) {
                    
                    $get_nearest_instant_group = $this->get_nearest_instant_group($latitude, $longitude);
                    $nearest_store = $get_nearest_instant_group['nearest_store'];
                    $nearest_store_id = $get_nearest_instant_group['nearest_store_id'];
                    $expected_eta = $get_nearest_instant_group['expected_eta']; // Default ETA
                    
                    if ($user_id != '' && isset($user) && $user) {
                        $user->update([
                            'nearest_store' => $nearest_store_id, 
                            'expected_eta' => $expected_eta
                        ]);
                    }
                    elseif (isset($isGuestUser) && $isGuestUser) {
                        $isGuestUser->update([
                            'nearest_store' => $nearest_store_id, 
                            'expected_eta' => $expected_eta
                        ]);
                    }
                }
            }
            
            // ------------------------------------------------------------------------------------------------------
            // Check store closed or not, if closed show the delivery time within the opening time slot
            // ------------------------------------------------------------------------------------------------------
            $delivery_time = get_delivery_time_in_text($expected_eta, $ivp);
            $store_open_time = env("STORE_OPEN_TIME");
            $store_close_time = env("STORE_CLOSE_TIME");

            // Home static version
            $home_static = \App\Model\HomeStatic::orderBy('id','desc')->first();
            $home_static_last_id = $home_static ? $home_static->id : 0;
            $home_static_data_feeded = $home_static ? $home_static->home_static_data_feeded : 0;
            $home_static_version = env("HOME_STATIC_VERSION", false).$home_static_last_id.$home_static_data_feeded;

            // Active stores - JEEB Instant
            $active_stores_arr_jeeb_instant = [];
            if ($nearest_store) {
                $active_stores = InstantStoreGroupStore::join('stores','stores.id','instant_store_group_stores.fk_store_id')
                ->where(['instant_store_group_stores.fk_group_id'=>$nearest_store->id,'stores.deleted'=>0,'stores.status'=>1, 'stores.schedule_active'=>1])->get();
                if ($active_stores->count()) {
                    $active_stores_arr_jeeb_instant = $active_stores->map(function($store) {
                        return $store->id;
                    });
                }
                // Sub categories to hide in instant
                $sub_categories_to_hide_instant = [
                    73
                ];
            }
            
            // Active stores - JEEB Plus
            $active_stores_arr_jeeb_plus = [];
            $active_stores = Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1, 'schedule_active'=>1, 'jeeb_groceries'=>1])->get();
            if ($active_stores->count()) {
                $active_stores_arr_jeeb_plus = $active_stores->map(function($store) {
                    return $store->id;
                });
            }
            
            // Active stores - JEEB Mall
            $active_stores_arr_jeeb_mall = [];
            $active_stores_jeeb_mall = Store::where(['deleted'=>0,'status'=>1, 'schedule_active'=>1])->get();
            if ($active_stores_jeeb_mall->count()) {
                $active_stores_arr_jeeb_mall = $active_stores_jeeb_mall->map(function($store) {
                    return $store->id;
                });
            }

            // Active stores 
            $active_stores_arr = [];
            if ($ivp=='i') {
                $active_stores_arr = $active_stores_arr_jeeb_instant;
            } elseif ($ivp=='m') {
                $active_stores_arr = $active_stores_arr_jeeb_mall;
            } else {
                $active_stores_arr = $active_stores_arr_jeeb_plus;
            }
            
            // Check user level features
            $features = $this->get_my_features($user_id,$user);

            return array(
                'user_id' => $user_id,
                'company_id' => 0, 
                'store_id' => 0, 
                'store' => 0,
                'features' => $features,
                'instant_group_id' => $nearest_store ? $nearest_store->id : 0, 
                'instant_group_name' => $nearest_store ? $nearest_store->name : 0, 
                'instant_group_hub_id' => $nearest_store ? $nearest_store->fk_hub_id : '', 
                'instant_group_hub_name' => $nearest_store ? $nearest_store->store_name.' - '.$nearest_store->company_name : '', 
                'delivery_time' => $delivery_time, 
                'store_open_time' => $store_open_time,
                'store_close_time' => $store_close_time,
                'home_static_version' => $home_static_version,
                'ivp_feature_activation' => $ivp_feature,
                'cart_emptied' => $cart_emptied,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'default_address' => $user_address,
                'active_stores' => $active_stores_arr,
                'active_stores_jeeb_instant' => $active_stores_arr_jeeb_instant,
                'active_stores_jeeb_plus' => $active_stores_arr_jeeb_plus,
                'active_stores_jeeb_mall' => $active_stores_arr_jeeb_mall,
                'sub_categories_to_hide_instant' => $sub_categories_to_hide_instant
            );

        } catch (Exception $ex) {

            if (isset($tracking_id) && $tracking_id) {
                update_tracking_response($tracking_id,$ex->getMessage());
            }
            return $ex;

        }
    }

}
