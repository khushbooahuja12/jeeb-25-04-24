<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;
use Illuminate\Support\Facades\Auth;

class AccountsController extends CoreApiController
{
   
    public function subscription(){

        return view('vendor.accounts.subscription');
    }

   public function billing_overview(){

    return view('vendor.accounts.overview');
   }
   public function usage_details(){

    return view('vendor.accounts.usage_details');
   }
   public function payment_details(){

    return view('vendor.accounts.payment_details');
   }
   public function invoice(){

    return view('vendor.accounts.invoices');
   }

}
