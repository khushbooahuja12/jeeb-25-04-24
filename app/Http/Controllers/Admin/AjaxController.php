<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Store;
use App\Model\Vendor;
use App\Model\User;
use App\Model\Driver;
use App\Model\Storekeeper;
use App\Model\Role;
use App\Model\Admin;
use App\Model\ProductOfferOption;
use App\Model\ProductTag;

class AjaxController extends CoreApiController
{

    protected $error = true;
    protected $error_code = 101;
    protected $message = "Invalid request format";
    protected $result;

    public function change_status(Request $request)
    {
        $method = $request->input('method');
        $status = $request->input('status');
        $id = $request->input('id');

        if ($method == 'changeVendorStatus') {
            $updateArr = Vendor::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $vendor = Vendor::find($id);

                switch ($vendor->status) {
                    case 0:
                        $finalstatus = 'Account not verified';
                        break;
                    case 1:
                        $finalstatus = 'Not approved';
                        break;
                    case 2:
                        $finalstatus = 'Active';
                        break;
                    case 3:
                        $finalstatus = 'Blocked';
                        break;
                    case 4:
                        $finalstatus = 'Rejected';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }
        if ($method == 'changeUserStatus') {
            $updateArr = Vendor::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $vendor = Vendor::find($id);

                switch ($vendor->status) {
                    case 0:
                        $finalstatus = 'Account not verified';
                        break;
                    case 1:
                        $finalstatus = 'Not approved';
                        break;
                    case 2:
                        $finalstatus = 'Active';
                        break;
                    case 3:
                        $finalstatus = 'Blocked';
                        break;
                    case 4:
                        $finalstatus = 'Rejected';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }
        if ($method == 'changeDriverStatus') {
            $updateArr = Driver::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $vendor = Driver::find($id);

                switch ($vendor->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'checkTestStorekeeper') {
            $updateArr = Storekeeper::find($id)->update([
                'is_test_user' => $status
            ]);
            if ($updateArr) {
                $vendor = Storekeeper::find($id);

                switch ($vendor->is_test_user) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'changeStorekeeperStatus') {
            $updateArr = Storekeeper::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $vendor = Storekeeper::find($id);

                switch ($vendor->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'changeTicketStatus') {
            $updateArr = \App\Model\CustomerSupport::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $support = \App\Model\CustomerSupport::find($id);

                switch ($support->status) {
                    case 0:
                        $finalstatus = 'Close';
                        break;
                    case 1:
                        $finalstatus = 'Open';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }
        if ($method == 'changeStoreStatus') {
            $updateArr = Store::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $store = Store::find($id);

                switch ($store->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }
        if ($method == 'changeRoleStatus') {
            $updateArr = Role::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $role = Role::find($id);

                switch ($role->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }
        
        if ($method == 'changeAdministratorStatus') {
            $updateArr = Admin::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $administrator = Admin::find($id);

                switch ($administrator->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Oops! Something went wrong. Please try again.";
            }
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'makeStorekeeperDefault') {

            $storekeeper = Storekeeper::find($id);
            if($storekeeper->is_test_user == 1){
                $default_storekeeper = Storekeeper::where('fk_store_id', $storekeeper->fk_store_id)
                    ->where('id','!=', $id)
                    ->where('is_test_user','=', 1)
                    ->where('default','=', 1)
                    ->first();
            }else{
                $default_storekeeper = Storekeeper::where('fk_store_id', $storekeeper->fk_store_id)
                    ->where('id','!=', $id)
                    ->where('is_test_user','=', 0)
                    ->where('default','=', 1)
                    ->first();
            }
            
            if($default_storekeeper){
                
                $default_storekeeper = $default_storekeeper->update(['default' => 0]);

                $updateArr = Storekeeper::find($id)->update([
                    'default' => $status
                ]);

                if ($updateArr) {
                    $vendor = Storekeeper::find($id);
    
                    switch ($vendor->default) {
                        case 0:
                            $finalstatus = 'Non Default';
                            break;
                        case 1:
                            $finalstatus = 'Default';
                            break;
                        default:
                            $finalstatus = "NA";
                    }
    
                    $this->status_code = 200;
                    $this->message = "Success";
                    $this->result = ['status' => $finalstatus];
                } else {
                    $this->status_code = 99;
                    $this->message = "Oops! Something went wrong. Please try again.";
                }
            }else{

                $this->status_code = 201;
                $this->message = "Store must have a default storekeeper";
            }
            
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'changeOfferOptionStatus') {
            $updateArr = ProductOfferOption::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $offer_option = ProductOfferOption::find($id);

                switch ($offer_option->status) {
                    case 0:
                        $finalstatus = 'Blocked';
                        break;
                    case 1:
                        $finalstatus = 'Active';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }

        if ($method == 'changeProductTagStatus') {
            $updateArr = ProductTag::find($id)->update([
                'is_main_tag' => $status
            ]);
            if ($updateArr) {
                $product_tag = ProductTag::find($id);

                switch ($product_tag->is_main_tag) {
                    case 0:
                        $finalstatus = 'Not main tag';
                        break;
                    case 1:
                        $finalstatus = 'Main tag';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }

    }

    protected function approve_reject_entity(Request $request)
    {
        $type = $request->input('type');
        $status = $request->input('status');
        $id = $request->input('id');

        if ($type == 'vendor') {
            $updateArr = Vendor::find($id)->update([
                'status' => $status
            ]);
            if ($updateArr) {
                $vendor = Vendor::find($id);

                switch ($vendor->status) {
                    case 0:
                        $finalstatus = 'Account not verified';
                        break;
                    case 1:
                        $finalstatus = 'Not approved';
                        break;
                    case 2:
                        $finalstatus = 'Active';
                        break;
                    case 3:
                        $finalstatus = 'Blocked';
                        break;
                    case 4:
                        $finalstatus = 'Rejected';
                        break;
                    default:
                        $finalstatus = "NA";
                }

                $this->status_code = 200;
                $this->message = "Success";
                $this->result = ['status' => $finalstatus];
            } else {
                $this->status_code = 99;
                $this->message = "Some error found, please try again";
            }
            $response = $this->makeJson();
            return $response;
        }
    }
}
