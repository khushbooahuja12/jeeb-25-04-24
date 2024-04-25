<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use App\Model\Driver;
use Illuminate\Http\Request;
use App\Model\Category;
use App\Model\Store;
use App\Model\Storekeeper;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Model\Company;
use App\Model\Admin;
use App\Model\Role;
use App\Model\Vendor;
use Illuminate\Support\Str;

class StoreController extends CoreApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter');
        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        } else {
            $stores = Store::where('deleted', '=', 0)
                ->sortable(['id' => 'asc'])
                ->paginate(20);
        }
        $stores->appends(['filter' => $filter]);

        return view('admin.stores.index', ['stores' => $stores, 'filter' => $filter]);
    }

    public function categories($id = null)
    {
        $store = Store::find($id);
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.stores.categories', ['store' => $store, 'categories' => $categories]);
    }

    public function create(Request $request)
    {
        $storekeepers = Storekeeper::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $drivers = Driver::where(['is_available' => 1])->orderBy('name', 'asc')->get();
        $companies = Company::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        return view('admin.stores.create', ['storekeepers' => $storekeepers, 'drivers' => $drivers, 'companies' => $companies]);
    }

    public function store(Request $request)
    {
        $store = Store::where('email', $request->input('email'))->get();
        if (count($store) > 0) {
            return back()->withInput()->with('error', 'Email already exist');
        } else {
            
            $insert_arr = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'pin' => $request->input('pin'),
                'password' => bcrypt($request->password),
                'api_url' => $request->api_url,
                'last_api_updated_at' => isset($request->api_url) ? Carbon::now()->format('d-M-y_h_i_s') : null,
                'company_id' => $request->input('company_id'),
                'status' => 1
            ];  
        

            $company_name = Company::find($request->input('company_id'));
            $insert_arr['company_name'] = $company_name->name;
            
            $add = Store::create($insert_arr);
            if ($add) {
                if ($request->input('storekeeper_ids') != null) {
                    foreach ($request->input('storekeeper_ids') as $value) {
                        Storekeeper::find($value)->update(['fk_store_id' => $add->id]);
                    }
                }
                if ($request->input('driver_ids') != null) {
                    foreach ($request->input('driver_ids') as $value) {
                        Driver::find($value)->update(['fk_store_id' => $add->id]);
                    }
                }

                //creating store administrator
                $store_product_stock_update_role = Role::where('slug','store-product-stock-update')->first();
                $insert_arr = [
                    'name' => $add->name,
                    'email' => $add->email,
                    'password' => $add->password,
                    'fk_role_id' => $store_product_stock_update_role->id,
                    'fk_store_id' => $add->id
                ];
    
                $create = Admin::create($insert_arr);
                $randomToken = Str::random(40);

                $vendor = [
                    'name' => $add->name,
                    'email' => $add->email,
                    'password' => $add->password,
                    'fk_store_id' => $add->id,
                    'pass_token' => $randomToken,
                    'mobile' => $request->input('mobile'),
                    'address' => $request->input('address'),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'country' => $request->input('country'),
                    'pin' => $request->input('pin')

                ];
                // print_r(Vendor::create($vendor));die;
                $create_vendor = Vendor::create($vendor);
               

                return redirect('admin/fleet/stores')->with('success', 'Store added successfully');
            }
            return back()->withInput()->with('error', 'Error while adding store');
        }
    }

    public function show($id = null)
    {
        $id = base64url_decode($id);
        $store = Store::find($id);
        return view('admin.stores.show', ['store' => $store]);
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);
        $store = Store::find($id);
        $storekeepers = Storekeeper::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        $added_storekeepers = Storekeeper::where('fk_store_id', '=', $id)->pluck('id')->toArray();
        $drivers = Driver::where(['is_available' => 1])->orderBy('name', 'asc')->get();
        $added_drivers = Driver::where('fk_store_id', '=', $id)->pluck('id')->toArray();
        $companies = Company::where(['deleted' => 0])->orderBy('name', 'asc')->get();
        return view('admin.stores.edit', ['store' => $store, 'storekeepers' => $storekeepers, 'added_storekeepers' => $added_storekeepers, 'drivers' => $drivers, 'added_drivers' => $added_drivers,'companies' => $companies]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $store = Store::where(['email' => $request->input('email'),['id','!=', $id],'deleted' => 0])->get();
        if (count($store) > 0) {
            return back()->withInput()->with('error', 'Email already exist');
        }else{
            
            $update_arr = [
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'address' => $request->input('address'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'pin' => $request->input('pin'),
                'api_url' => $request->input('api_url'),
                'last_api_updated_at' => isset($request->api_url) ? Carbon::now()->format('d-M-y_h_i_s') : null,
            ];
            $store = Store::find($id);
            if($store->password || isset($request->password)){
                $company_name = Company::find($store->company_id);
                $update_arr['company_name'] = $company_name->name;

                if(isset($request->password)){
                    $update_arr['password'] = bcrypt($request->password);
                }

                $update = Store::find($id)->update($update_arr);
                if ($update) {
                    if ($request->input('storekeeper_ids') != null) {
                        Storekeeper::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                        foreach ($request->input('storekeeper_ids') as $value) {
                            Storekeeper::find($value)->update(['fk_store_id' => $id]);
                        }
                    }else{
                        Storekeeper::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                    }

                    if ($request->input('driver_ids') != null) {
                        Driver::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                        foreach ($request->input('driver_ids') as $value) {
                            Driver::find($value)->update(['fk_store_id' => $id]);
                        }
                    }else{
                        Driver::where(['fk_store_id' => $id])->update(['fk_store_id' => null]);
                    }

                    //updating store administrator
                    $store_admin = Admin::where('fk_store_id',$id)->first();
                    if($store_admin){
                        $store_admin->update($update_arr);
                    }else{
                        $store_product_stock_update_role = Role::where('slug','store-product-stock-update')->first();
                        $insert_arr = [
                            'name' => $store->name,
                            'email' => $store->email,
                            'password' => $store->password,
                            'fk_role_id' => $store_product_stock_update_role->id,
                            'fk_store_id' => $id
                        ];

                        Admin::create($insert_arr);
                    }
                    

                    return redirect('admin/stores')->with('success', 'Store updated successfully');
                }
            }else{

                return back()->withInput()->with('error', 'Please enter a password');
            }
            
            return back()->withInput()->with('error', 'Error while updating store');
        }
        
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $update = Store::find($id)->update(['deleted' => 1]);
        if ($update) {
            return redirect('admin/stores')->with('success', 'Store deleted successfully');
        }
        return back()->withInput()->with('error', 'Error while deleting store');
    }

    public function categories_bp($id = null)
    {
        $store = Store::find($id);
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.stores.categories_bp', ['store' => $store, 'categories' => $categories]);
    }

    public function create_company_store_name(Request $request)
    {
        $company_id = $request->company_id;
        $stores_count = Store::select('name')->where('company_id',$company_id)->count();
        $new_store = $stores_count+1;
        $store = 'Store '.$new_store;
        return json_encode($store);
    }
}
