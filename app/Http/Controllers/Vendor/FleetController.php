<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Company;
use App\Model\Store;
use App\Model\StoreSchedule;
use App\Model\Order;
use App\Model\OrderProduct;
use App\Model\OrderDriver;
use App\Model\OrderStatus;
use App\Model\Driver;
use App\Model\DriverLocation;
use App\Model\StorekeeperProduct;
use App\Model\Invoice;
use App\Model\VendorOrder;
use App\Model\OrderPayment;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;

use DB;
use Illuminate\Support\Facades\Auth;

class FleetController extends CoreApiController
{

    
    public function __construct(Request $request)
    {
        $this->loadApi = new \App\Model\LoadApi();

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {
        $filter = $request->query('filter');
        $vendor = Auth::guard('vendor')->user();


        // if (!empty($filter)) {
        //     $stores = Store::where(['deleted'=>0, 'status'=>1])
        //         ->where('company_name', 'like', '%' . $filter . '%')
        //         ->where('id', '=', $vendor->store_id)
        //         ->sortable(['id' => 'asc'])
        //         ->paginate(20);
        // } else {
        //     $stores = Store::where(['deleted'=>0, 'status'=>1])
        //         ->where('id', '=', $vendor->store_id)
        //         ->sortable(['id' => 'asc'])
        //         ->paginate(20);
        // }
        // $stores->appends(['filter' => $filter]);

        // if ($stores && !empty($stores)) {
        //     foreach ($stores as $store) {
        //         $store->no_available_products = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
        //                                             ->where('base_products.fk_store_id',$store->id)
        //                                             ->where('base_products.deleted',0)
        //                                             ->where('base_products_store.deleted',0)
        //                                             ->where('base_products.product_type','product')
        //                                             ->count();
        //         $store->no_instock_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
        //                                             ->where('base_products.product_store_stock','>',0)
        //                                             ->where('base_products.product_type','product')
        //                                             ->where('base_products_store.fk_store_id',$store->id)
        //                                             ->count();
        //         $store->no_products = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
        //                                             ->where('base_products.product_type','product')
        //                                             ->where('base_products_store.fk_store_id',$store->id)
        //                                             ->count();
        //         $store->no_available_recipes = BaseProduct::join('base_products_store','base_products.id','=','base_products_store.fk_product_id')
        //                                             ->where('base_products.deleted',0)
        //                                             ->where('base_products_store.deleted',0)
        //                                             ->whereIn('base_products.product_type',['recipe','pantry_item'])
        //                                             ->where('base_products.fk_store_id',$store->id)
        //                                             ->count();
        //         $store->no_recipes = BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
        //                                             ->whereIn('base_products.product_type',['recipe','pantry_item'])
        //                                             ->where('base_products_store.fk_store_id',$store->id)
        //                                             ->count();
        //     }
        // }


        return view('vendor.fleet.index', [
            'vendor' => $vendor,
        ]);

    }

   


}
