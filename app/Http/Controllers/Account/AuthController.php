<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\Account;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    protected function login_get(Request $request)
    {
        return view('account.auth.login');
    }

    protected function login_post(Request $request)
    {
        $account = Account::where(['email' => $request->input('email')])->first();
        if ($account) {
            if (Hash::check($request->input('password'), $account->password)) {
                // echo Auth::guard('account')->user()->name;die;
                return redirect('account/invoice');
            } else {
                $error_msg = "email or password is incorrect";
                return back()->withInput()->with('error', $error_msg);
            }
        } else {
            return back()->withInput()->with('error', 'email or password is incorrect');
        }
    }

    protected function logout(Request $request)
    {
        Auth::guard('account')->logout();
        return redirect('account');
    }
}
