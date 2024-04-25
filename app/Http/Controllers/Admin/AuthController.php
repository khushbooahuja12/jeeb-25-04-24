<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Admin;
use App\Model\PasswordReset;
use Illuminate\Support\Facades\Hash;
use App\Model\Role;

class AuthController extends Controller {

    protected function login_get(Request $request) {
        return view('admin.auth.login');
    }

    protected function login_post(Request $request) {
        $admin_data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (Auth::guard('admin')->attempt($admin_data, $request->input('remember'))) {
            $role = Role::find(Auth::guard('admin')->user()->fk_role_id);
            $store = Auth::guard('admin')->user()->fk_store_id;
            $company = Auth::guard('admin')->user()->fk_company_id;

            if ($role->slug == 'report-admin') {
                return redirect('admin/report');
            } elseif ($role->slug == 'affiliate-admin') {
                return redirect('admin/affiliates');
            } elseif ($role->slug == 'recipe-admin') {
                return redirect('admin/recipes');
            } elseif ($role->slug == 'product-admin') {
                return redirect('admin/base_products/edit_multiple/0');
            } elseif ($role->slug == 'fleet-admin') {
                return redirect('admin/fleet');
            } elseif ($role->slug == 'executive-admin') {
                return redirect('admin/custom_notifications');
            } elseif ($role->slug == 'hr-admin') {
                return redirect('admin/storekeepers');
            } elseif ($role->slug == 'store-product-stock-update') {
                return redirect('admin/store/'.base64url_encode($store).'');
                // return redirect('admin/stock_update/store/'.$store.'/base_products/update_stock_multiple/0');
            } elseif ($role->slug == 'fleet-store-admin') {
                return redirect('admin/fleet/store-orders');
            } elseif($role->slug == 'storekeeper-fleet-admin'){ 
                return redirect('admin/fleet/storekeeper/orders');
            } elseif($role->slug == 'fleet-store-admin-catalog'){ 
                return redirect('admin/fleet/stores/catalog-with-filter');
            } elseif ($role->slug == 'bot-admin') {
                return redirect('admin/bot_rotator');
            } elseif ($role->slug == 'stores-master-panel-admin') {
                return redirect('admin/store/master/'.base64url_encode($company).'');
            } else {
                return redirect('admin/dashboard');
            }
        } else {
            return back()->withInput()->with('error', 'email or password is incorrect');
        }
    }

    protected function logout(Request $request) {
        Auth::guard('admin')->logout();
        return redirect('admin');
    }

    protected function forgot_password(Request $request) {
        return view('admin.auth.forgot_password');
    }

    protected function send_password_reset_link(Request $request) {
        $emailexist = Admin::where('email', '=', $request->input('email'))->exists();
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

                \Mail::send('emails.adminpasswordreset', ['token' => $token], function ($message) use ($email) {
                    $message->from('itruk.admin@iqios.co.id', 'Jeeb');
                    $message->to($email);
                    $message->subject('Reset Your Password');
                });

                return redirect('admin/forgot_password')->with('success', 'Please check your email to reset password');
            }
        }
    }

    protected function reset_password($token = null) {
        $exist = PasswordReset::where('token', '=', $token)->where('type', '=', 1)->exists();
        if ($exist) {
            return view('admin.auth.reset_password', ['token' => $token]);
        } else {
            return redirect('admin/login')->with('error', 'Reset Link Expired , Please try again');
        }
    }

    function set_new_password(Request $request) {
        $password = $request->input('password');
        $token = $request->input('token');

        $tokenData = PasswordReset::where(['token' => $token, 'type' => 1])->first();

        $exist = Admin::where('email', '=', $tokenData->email)->exists();
        if ($exist) {
            $update = Admin::where(['email' => $tokenData->email])->update(['password' => Hash::make($password)]);
            if ($update) {
                PasswordReset::where(['email' => $tokenData->email, 'type' => 1])->delete();

                return redirect('admin/login')->with('success', 'Password reset successful, please login with the new password');
            } else {
                return back()->withInput()->with('error', "Some error found while resetting password");
            }
        } else {
            return redirect('admin/login');
        }
    }

}
