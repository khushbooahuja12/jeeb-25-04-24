<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Driver;
//use App\Model\DriverReview;
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
                ->where('user_type', 2)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->driver_id = $auth->user_id;
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
            $driver = Driver::find($this->driver_id);
            if ($driver) {
                $profile_arr = [
                    'id' => $driver->id,
                    'name' => $driver->name ?? '',
                    'email' => $driver->email ?? '',
                    'country_code' => $driver->country_code,
                    'mobile' => $driver->mobile,
                    'driving_licence_no' => $driver->driving_licence_no,
                    'driver_image' => !empty($driver->getDriverImage) ? asset('images/driver_images') . '/' . $driver->getDriverImage->file_name : '',
                    'driving_licence' => !empty($driver->getDrivingLicence) ? asset('images/driver_images') . '/' . $driver->getDrivingLicence->file_name : '',
                    'national_id' => !empty($driver->getNationalId) ? asset('images/driver_images') . '/' . $driver->getNationalId->file_name : '',
                    'status' => $driver->status,
                ];
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Success";
                $this->result = [
                    'profile' => $profile_arr
                ];
            } else {
                throw new Exception("Driver doesn't exist", 105);
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
            $this->required_input($request->input(), ['name', 'email', 'driving_licence_no']);

            if ($request->hasHeader('Authorization')) {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->where('user_type', 2)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $driver_id = $auth->user_id;
            } else {
                $driver = Driver::where(DB::raw('CONCAT(country_code,mobile)'), '=', $request->input('country_code') . $request->input('mobile'))->first();
                if ($driver->status == 1) {
                    if (!$request->hasHeader('Authorization')) {
                        throw new Exception("Access token empty", 105);
                    }
                }
                $driver_id = $driver->id;
            }

            $driver = Driver::find($driver_id);

            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email', Rule::unique('drivers', 'email')->ignore($driver_id, "id")]
            ]);
            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 105);
            }

            $update_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'address' => $request->input('address'),
                'driving_licence_no' => $request->input('driving_licence_no'),
                'driver_image' => $request->input('driver_image'),
                'driving_licence' => $request->input('driving_licence'),
                'national_id' => $request->input('national_id'),
                'status' => 1,
            ];
            if ($request->hasFile('driver_image')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'driver_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    if ($driver->driver_image != '') {
                        $destinationPath = public_path("images/driver_images/");
                        if (!empty($driver->getDriverImage) && file_exists($destinationPath . $driver->getDriverImage->file_name)) {
                            unlink($destinationPath . $driver->getDriverImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $driver->driver_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }
                    $update_arr['driver_image'] = $returnArr->id;
                endif;
            }
            if ($request->hasFile('driving_licence')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'driving_licence', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    if ($driver->driving_licence != '') {
                        $destinationPath = public_path("images/driver_images/");
                        if (!empty($driver->getLicenceImage) && file_exists($destinationPath . $driver->getLicenceImage->file_name)) {
                            unlink($destinationPath . $driver->getLicenceImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $driver->driving_licence);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }
                    $update_arr['driving_licence'] = $returnArr->id;
                endif;
            }
            if ($request->hasFile('national_id')) {
                $path = "/images/driver_images/";
                $check = $this->uploadFile($request, 'national_id', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
                    if ($driver->national_id != '') {
                        $destinationPath = public_path("images/driver_images/");
                        if (!empty($driver->getNationalIdImage) && file_exists($destinationPath . $driver->getNationalIdImage->file_name)) {
                            unlink($destinationPath . $driver->getNationalIdImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $driver->national_id);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }
                    $update_arr['national_id'] = $returnArr->id;
                endif;
            }

            $update = Driver::find($driver_id)->update($update_arr);

            if ($update) {
                $this->error = false;
                $this->status_code = 200;
                $this->message = "Profile updated successfully";
            } else {
                throw new Exception("Oops! Something went wrong. Please try again.", 105);
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
