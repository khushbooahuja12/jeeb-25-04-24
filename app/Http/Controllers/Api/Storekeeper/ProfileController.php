<?php

namespace App\Http\Controllers\Api\Storekeeper;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Storekeeper;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\Rule;

class ProfileController extends CoreApiController
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

        $methods_arr = ['get_profile'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 3)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->storekeeper_id = $auth->user_id;
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

    protected function get_profile(Request $request)
    {
        try {
            $storekeeper = Storekeeper::find($this->storekeeper_id);
            if ($storekeeper) {
                $profile_arr = [
                    'id' => $storekeeper->id,
                    'name' => $storekeeper->name ?? '',
                    'email' => $storekeeper->email ?? '',
                    'country_code' => $storekeeper->country_code,
                    'mobile' => $storekeeper->mobile,
                    'address' => $storekeeper->address,
                    'image' => !empty($storekeeper->getStorekeeperImage) ? asset('images/storekeeper_images') . '/' . $storekeeper->getStorekeeperImage->file_name : '',
                    'status' => $storekeeper->status,
                ];
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Success";
                $this->result = [
                    'profile' => $profile_arr
                ];
            } else {
                throw new Exception("storekeeper doesn't exist", 105);
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

    protected function update_profile(Request $request)
    {
        try {
            $this->required_input($request->input(), ['name', 'email']);

            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->where('user_type', 3)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $storekeeper_id = $auth->user_id;
            } else {
                $storekeeper = Storekeeper::where(DB::raw('CONCAT(country_code,mobile)'), '=', $request->input('country_code') . $request->input('mobile'))->first();
                if ($storekeeper->status == 1) {
                    if ($access_token=='') {
                        throw new Exception("Access token empty", 105);
                    }
                }
                $storekeeper_id = $storekeeper->id;
            }

            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', Rule::unique('storekeepers', 'email')->ignore($storekeeper_id, "id")]
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 105);
            }

            $storekeeper = Storekeeper::find($storekeeper_id);

            $update_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'address' => $request->input('address')
            ];
            if ($request->hasFile('image')) {
                $path = "/images/storekeeper_images/";
                $check = $this->uploadFile($request, 'image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    if ($storekeeper->image != '') {
                        $destinationPath = public_path("images/storekeeper_images/");
                        if (!empty($storekeeper->getStorekeeperImage) && file_exists($destinationPath . $storekeeper->getStorekeeperImage->file_name)) {
                            unlink($destinationPath . $storekeeper->getStorekeeperImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $storekeeper->image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }
                    $update_arr['image'] = $returnArr->id;
                endif;
            }
            $update = Storekeeper::find($storekeeper_id)->update($update_arr);

            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Profile updated successfully";
            } else {
                throw new Exception('Something went wrong', 105);
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
}
