<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Admin;
use App\Model\PasswordReset;
use Illuminate\Support\Facades\Hash;
use App\Model\Role;
use App\Model\Store;
use App\Model\Vendor;
use App\Model\Company;

class AuthController extends Controller {


    

    protected function login_get(Request $request) {
        return view('vendor.auth.login');
    }

    protected function login_post(Request $request) {
        
        // print_r(hash::make(12345678));die;
        // die('gfhg');
        $vendor_data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $vendor = Vendor::where('email', $request->input('email'))->first();
      
        if (Auth::guard('vendor')->attempt($vendor_data, $request->input('remember'))) {
        
            if ($vendor && Hash::check($request->input('password'), $vendor->password)) {
               
                $store_id = base64_encode($vendor->store_id);
               
                return redirect('vendor/dashboard');

            } else {
                return back()->withInput()->with('error', 'email or password is incorrect');
            }


        }else {
            return back()->withInput()->with('error', 'email or password is incorrect');
        }

    }

    protected function logout(Request $request) {
        Auth::guard('vendor')->logout();
        return redirect('vendor');
    }

    protected function forgot_password(Request $request) {
        return view('vendor.auth.forgot_password');
    }

    protected function send_password_reset_link(Request $request) {

       
        $emailexist = Vendor::where('email', '=', $request->input('email'))->exists();
        if (!$emailexist) {

            return back()->withInput()->with('error', "Email doesn't exist");
        } else {
           
            $existinreset = PasswordReset::where('email', '=', $request->input('email'))->where('type', '=', 1)->exists();
            if ($existinreset) {
                return back()->withInput()->with('error', "Already requested for reset password, please check your mail or try again after 10 minutes");
            } else {
               
                $response = PasswordReset::create([
                            'email' => $request->input('email'),
                            'token' => str_random(60),
                            'type' => 1,
                            'created_at' => now()
                ]);

                /* forget password email */
                $token = $response->token;
                $email = $response->email;
              
                \Mail::send('emails.vendorpasswordreset', ['token' => $token], function ($message) use ($email) {
                    $message->from('itruk.admin@iqios.co.id', 'Jeeb');
                    $message->to($email);
                    $message->subject('Reset Your Password');
                });

                return redirect('vendor/forgot_password')->with('success', 'Please check your email to reset password');
            }
        }

    }

    protected function reset_password($token = null) {
        $exist = PasswordReset::where('token', '=', $token)->where('type', '=', 1)->exists();
        if ($exist) {
            return view('vendor.auth.reset_password', ['token' => $token]);
        } else {
            return redirect('vendor/login')->with('error', 'Reset Link Expired , Please try again');
        }
    }

    function set_new_password(Request $request) {
        $password = $request->input('password');
        $token = $request->input('token');

        $tokenData = PasswordReset::where(['token' => $token, 'type' => 1])->first();

        $exist = Vendor::where('email', '=', $tokenData->email)->exists();
        if ($exist) {
            $update = Vendor::where(['email' => $tokenData->email])->update(['password' => Hash::make($password)]);
            if ($update) {
                PasswordReset::where(['email' => $tokenData->email, 'type' => 1])->delete();

                return redirect('vendor/login')->with('success', 'Password reset successful, please login with the new password');
            } else {
                return back()->withInput()->with('error', "Some error found while resetting password");
            }
        } else {
            return redirect('vendor/login');
        }
    }


    // function register(Request $request){

    //     $stores = Store::all();
       
    //     $company = Company::all();
    //     // $companies = Company::leftJoin('stores', 'stores.company_id', '=', 'companies.id')
    //     // ->select('stores.*', 'stores.name as store_name', 'companies.name as company_name')
    //     // ->get();

    //     // print_r($companies);die;
    //     return view('vendor.auth.register', 
    //     ['stores' => $stores ,'company' => $company]);
    // }

    // function register_post(Request $request){

    //     $exists = Vendor::where(['email' => $request->input('email')])->exists();

    //     if ($exists) {
    //         return back()->withInput()->with('error', 'A Vendor alreday exist with this email');
    //     } else {

    //         $vendor_data = [
    //             'email' => $request->input('email'),
    //             'password' => Hash::make($request->input('password')),
    //             'name' => $request->input('name'),
    //             'store_id' => $request->input('store_name'),
    //             'address' => $request->input('address'),
    //             'city' => $request->input('city'),
    //             'state' => $request->input('state'),
    //             'country' => $request->input('country'),
    //             'pin' => $request->input('pin'),
    //             'mobile' => $request->input('mobile'),
    //             'latitude' => $request->input('latitude'),
    //             'longitude' => $request->input('longitude'),
    //         ];

    //         Vendor::create($vendor_data);

    //         return redirect('vendor/login')->with('success', 'Vendor created successfully');
            
    //     }

       


    // }

    
}
