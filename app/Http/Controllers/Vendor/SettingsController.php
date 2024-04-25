<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Store;
use App\Model\Category;
use App\Model\Vendor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SettingsController extends CoreApiController
{


    public function profile_page()
    {
        $user_data = Auth::guard('vendor')->user();
        $store = Store::find($user_data->store_id);

        return view('vendor.settings.profile_page', ['user_data' => $user_data, 'store' => $store]);

    }

    public function pass_token(){

        $user = Auth::guard('vendor')->user();

        return view('vendor.settings.pass_key_page');

    }


    public function stores_data(){

        $user = Auth::guard('vendor')->user();
        $stores_data = Store::find($user->store_id);

        return view('vendor.settings.stores_detail', ['stores_data' => $stores_data]);
    }

    public function payment_card(){

        $user = Auth::guard('vendor')->user();
        $stores_data = Store::find($user->store_id);

        return view('vendor.settings.payment_card', ['user' => $user, 'stores_data' => $stores_data]);
    }


    public function update_profile(Request $request){

        $user = Auth::guard('vendor')->user();
        $stores_data = Store::find($user->store_id);

        $user_data = Vendor::find($user->id);

        $user_data['name'] = $request->input('name');
        $user_data['email'] = $request->input('email');
        $user_data['password'] = $request->input('password');
        $user_data['mobile'] = $request->input('mobile');

        if( $user_data->update()){

            return redirect('vendor/profile')->with('success', 'Profile Updated Successfully');

        }else{

            return redirect('vendor/profile')->with('success', 'Something went wrong');
        }

       
    }


    public function pass_token_generate(Request $request){

       
        $user = Auth::guard('vendor')->user();

        if(isset($user->store_id)){

            // $token = $request->input('_token');
            
            $randomToken = Str::random(40);

            $user['pass_token'] = $randomToken;

            if($user->update()){

                return redirect('vendor/pass_key')->with('success', 'Pass Key Generated Successfully');
            }

        }else{

            return view('vendor.auth.login');
        }

       
    }

    public function update_store_detail(Request $request){

        $user = Auth::guard('vendor')->user();
        $stores_data = Store::find($user->store_id);

        $stores_data['address'] = $request->input('address');
        $stores_data['city'] = $request->input('city');
        $stores_data['state'] = $request->input('state');
        $stores_data['country'] = $request->input('country');
        $stores_data['pin'] = $request->input('pin');

        if( $stores_data->update()){

            return redirect('vendor/store_setting')->with('success', 'Store Detail Updated Successfully');

        }else{

            return redirect('vendor/store_setting')->with('success', 'Something went wrong');
        }


    }

    public function card_store(Request $request){

        return redirect('vendor/payment_card');
    }


}
