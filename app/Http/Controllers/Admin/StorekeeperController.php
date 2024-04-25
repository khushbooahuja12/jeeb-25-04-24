<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use App\Model\Store;
use Illuminate\Http\Request;
use App\Model\Storekeeper;
use App\Model\Category;
use App\Model\StorekeeperSubcategory;
use App\Model\StorekeeperProduct;

class StorekeeperController extends CoreApiController
{

    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost()=='localhost' || $request->getHttpHost()=='127.0.0.1:8000' ? 'dev_products' : 'products';
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter');
        if (!empty($filter)) {
            $storekeepers = Storekeeper::where('deleted', 0)
                ->where('name', 'like', '%' . $filter . '%')
                ->orWhere('email', 'like', '%' . $filter . '%')
                ->orWhere('mobile', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $storekeepers = Storekeeper::where('deleted', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $storekeepers->appends(['filter' => $filter]);

        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('id', 'asc')->get();

        return view('admin.storekeepers.index', ['storekeepers' => $storekeepers, 'stores' => $stores, 'filter' => $filter]);
    }

    public function create()
    {
        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('id', 'asc')->get();
        return view('admin.storekeepers.create', ['stores' => $stores]);
    }

    public function store(Request $request)
    {
        $insert_arr = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'country_code' => $request->input('country_code'),
            'mobile' => $request->input('mobile'),
            'address' => $request->input('address'),
            'fk_store_id' => $request->input('fk_store_id'),
            'status' => 1
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

                $returnArr = $this->insertFile($req);
                $insert_arr['image'] = $returnArr->id;
            endif;
        }

        //default store keeper exist in this store
        $storekeeper = Storekeeper::where(['fk_store_id' => $request->input('fk_store_id'), 'default' => 1, 'is_test_user' => 0, 'deleted' => 0 ])->first();
        if(!$storekeeper){
            $insert_arr['default'] = 1;
        }

        $add = Storekeeper::create($insert_arr);

        if ($add) {
            return redirect('admin/storekeepers')->with('success', 'Storekeeper added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    public function edit($id)
    {
        $id = base64url_decode($id);
        $storekeeper = Storekeeper::find($id);

        $stores = Store::where(['deleted' => 0,'status'=>1])->orderBy('id', 'asc')->get();

        return view('admin.storekeepers.edit', [
            'storekeeper' => $storekeeper,
            'stores' => $stores
        ]);
    }
    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);

        $storekeeper = Storekeeper::find($id);

        $update_arr = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'country_code' => $request->input('country_code'),
            'mobile' => $request->input('mobile'),
            'address' => $request->input('address'),
            'fk_store_id' => $request->input('fk_store_id')
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
                if ($storekeeper->storekeeper_image != '') {
                    $destinationPath = public_path("images/storekeeper_images/");
                    if (!empty($storekeeper->getStorekeeperImage) && file_exists($destinationPath . $storekeeper->getStorekeeperImage->file_name)) {
                        unlink($destinationPath . $storekeeper->getStorekeeperImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $storekeeper->storekeeper_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['image'] = $returnArr->id;
            endif;
        }
        $update = Storekeeper::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/storekeepers')->with('success', 'Storekeeper updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating storekeeper');
    }

    public function show($id = null)
    {
        $id = base64url_decode($id);
        $storekeeper = Storekeeper::find($id);

        // pp($storekeeper->getSubCategories);

        return view('admin.storekeepers.show', [
            'storekeeper' => $storekeeper
        ]);
    }

    public function destroy($id)
    {
        $id = base64url_decode($id);
        $update = Storekeeper::find($id)->update(['deleted' => 1]);
        if ($update) {
            return redirect('admin/storekeepers')->with('success', 'Storekeeper deleted successfully');
        }
        return back()->withInput()->with('error', 'Error while deleting storekeeper');
    }

    public function storekeepers(Request $request, $id = null)
    {
        $store = Store::find($id);
        $categories = Category::where('parent_id', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();

        if (isset($_GET['test_user'])) {
            $storekeepers = Storekeeper::where('fk_store_id', $id)
                ->where('deleted', 0)
                ->where('is_test_user', 1)
                ->sortable(['id' => 'desc'])
                ->get();
           
        } else {
            $storekeepers = Storekeeper::where('fk_store_id', $id)
                ->where('deleted', 0)
                ->where('is_test_user',0)
                ->sortable(['id' => 'desc'])
                ->get();
        }

        return view('admin.storekeepers.storekeepers', [
            'storekeepers' => $storekeepers, 
            'store' => $store,
            'categories' => $categories,
            // 'sub_categories' => $sub_categories
        ]);
    }

    public function storekeeper_detail($id = null, $storekeeper_id = null)
    {
        $storekeeper_id = base64url_decode($storekeeper_id);
        $storekeeper = Storekeeper::find($storekeeper_id);

        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $added_subcategories = StorekeeperSubcategory::where('fk_storekeeper_id', '=', $storekeeper_id)->pluck('fk_sub_category_id')->toArray();

        $storekeeper_products = StorekeeperProduct::join($this->products_table, 'storekeeper_products.fk_product_id', '=', $this->products_table.'.id')
            ->select($this->products_table.'.*', 'storekeeper_products.fk_order_id', 'storekeeper_products.status')
            ->where('storekeeper_products.fk_storekeeper_id', '=', $storekeeper_id)
            ->where('storekeeper_products.status', '=', 0)
            ->orderBy($this->products_table.'.product_name_en', 'asc')
            ->get();

        return view('admin.storekeepers.storekeeper_detail', [
            'storekeeper' => $storekeeper,
            'storeId' => $id,
            'sub_categories' => $sub_categories,
            'added_subcategories' => $added_subcategories,
            'storekeeper_products' => $storekeeper_products
        ]);
    }

    //Ajax
    protected function update_subcategories_storekeeper(Request $request)
    {
        StorekeeperSubcategory::where(['fk_sub_category_id' => $request->input('fk_sub_category_id')])->delete();
        if($request->input('storekeepers')){
            foreach ($request->input('storekeepers') as $key => $value) {
                StorekeeperSubcategory::create([
                    'fk_storekeeper_id' => $value,
                    'fk_sub_category_id' => $request->input('fk_sub_category_id')
                ]);
            }
        }
        
        return response()->json(['error' => false, 'status_code' => 200, 'message' => 'Subcategory Storekeepers updated']);
        
    }
}
