<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Company;
use App\Model\Product;
use App\Model\Category;
use App\Model\Brand;
use App\Model\Store;
use App\Model\TagBundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use App\Model\ProductTag;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;


require __DIR__ . "../../../../../vendor/autoload.php";




class ProductController extends CoreApiController
{
    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {

        $vendor = Auth::guard('vendor')->user();
        $store = Store::findOrFail($vendor->store_id);

        $filter = $request->query('filter');
        $unit = $request->query('unit');
        $product_id = $request->query('product_id');
        $tags = $request->query('tags');

        $products = BaseProduct::where('parent_id', '=', 0)
            ->where('fk_store_id', '=', $vendor->store_id);

        if (!empty($product_id)) {
            $products = $products->where('id', '=', $product_id);
        }
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        }
        if (!empty($unit)) {
            $products = $products->where('unit', 'like', '%' . $unit . '%');
        }
        if (!empty($tags)) {
            $products = $products->where('_tags', 'like', '%' . $tags . '%');
        }
        $products = $products->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->sortable(['id' => 'desc'])
            ->paginate(50);
        $products->appends(['filter' => $filter]);

        $classification = \App\Model\Classification::where(['parent_id' => 0])
            ->orderBy('name_en', 'asc')
            ->get();

        //stores 
        $stores = Store::all();

        return view('vendor.products.index', [
            'products' => $products,
            'classification' => $classification,
            'filter' => $filter,
            'unit' => $unit,
            'product_id' => $product_id,
            'tags' => $tags,
            'stores' => $stores
        ]);

    }


    protected function stock_update(Request $request)
    {

        $vendor = Auth::guard('vendor')->user();

        $filter = $request->input('filter');

        if (!empty($filter)) {
            $stores = Store::where('name', 'like', '%' . $filter . '%')
                ->where('id', '=', $vendor->store_id)
                ->where('deleted', '=', 0)
                ->where('status', '=', 1)
                ->first();

        } else {
            $stores = Store::find($vendor->store_id)->first();

        }
        // $stores->appends(['filter' => $filter]);
        // print_r($stores);die;

        return view('vendor.products.store_stock_update', ['stores' => $stores, 'filter' => $filter]);

    }


    protected function get_sub_category(Request $request)
    {
        $category_id = $request->input('fk_category_id');
        $sub_category = Category::where('parent_id', '=', $category_id)
            ->where('deleted', '=', 0)
            ->get();
        $sub_category_list = "";
        if (!empty($sub_category)) {
            foreach ($sub_category as $category) {
                $string = '<option value="' . $category->id . '">' . $category->category_name_en . '</option>';
                $sub_category_list = $sub_category_list . $string;
            }
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Sub Category List', 'data' => $sub_category_list]);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'No sub category found']);
        }
    }


    protected function new_product(Request $request)
    {

        $brands = Brand::where('deleted', '=', 0)->orderBy('id', 'desc')->get();
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();
        $product_tags = ProductTag::all();

        return view('vendor.products.create', ['brands' => $brands, 'categories' => $categories, 'product_tags' => $product_tags]);
    }



    protected function add_new_product(Request $request)
    {


        $vendor = Auth::guard('vendor')->user();
        $store = Store::findOrFail($vendor->store_id);

        $insert_arr = [
            'parent_id' => 0,
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_store_id' => $vendor->store_id,
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('quantity'),
            'is_home_screen' => $request->input('is_home_screen') ?? 0,
            'frequently_bought_together' => $request->input('frequently_bought_together') ?? 0,
            'stock' => $request->input('instock'),
            'base_price' => $request->input('price'),
            'desc_en' => $request->input('desc_en'),
           
        ];

        if ($request->hasFile('image')) {
            $product_images_path = str_replace('\\', '/', storage_path("app/public/images/product_images/"));
            $product_images_url_base = "public/images/product_images/";

            $path = "/images/product_images/";
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            // Upload Image
            $check = $request->file('image')->storeAs('public/' . $path, $fileNameToStore);
            if ($check):
                $insert_arr['product_image_url'] = env('APP_URL') . $product_images_url_base . $fileNameToStore;
            endif;
        }


        $create = BaseProduct::create($insert_arr);

        if ($create) {
            BaseProduct::find($create->id)->update(['itemcode' => empty($create->itemcode) ? $create->id . time() : '']);

            return redirect('vendor/all_products')->with('success', 'Product added successfully');

        } else {

            return back()->withInput()->with('error', 'Error while adding Product');
        }



    }


    public function view_product($id)
    {

        $id = base64url_decode($id);
        // $product= BaseProduct::where(['id' => $id])->first();
        $product = BaseProduct::join('categories', 'base_products.fk_category_id', '=', 'categories.id')
            ->join('brands', 'base_products.fk_brand_id', '=', 'brands.id')
            ->where('base_products.id', '=', $id)
            ->select('base_products.*', 'brands.brand_name_en', 'categories.category_name_en')
            ->first();
        
        // print_r($product);die;
        return view('vendor.products.show', ['product' => $product]);
    }

    public function edit_product($id)
    {

        $id = base64url_decode($id);
        $product = BaseProduct::where(['id' => $id])->first();

        $brands = Brand::where('deleted', '=', 0)->orderBy('id', 'desc')->get();
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();
        $sub_categories = Category::where('parent_id', '=', $product->fk_category_id)
            ->where('deleted', '=', 0)
            ->orderBy('category_name_en', 'asc')
            ->get();
        // $product_tags = ProductTag::all();

        return view('vendor.products.edit_product', ['brands' => $brands, 'categories' => $categories, 'sub_categories' => $sub_categories, 'product' => $product]);

    }

    protected function update(Request $request, $id)
    {
        $id = base64url_decode($id);
        $product = BaseProduct::find($id);
        // print_r($product);die;

        if ($product) {
            $_tags = $request->input('_tags');

            // if($_tags){
            //     $_tag_arr = [];
            //     foreach ($_tags as $_tag) {
            //         if ($_tag=='') {
            //             $_tag_arr[] = $_tag;
            //             continue;
            //         }
            //         // Check tag already exist
            //         $product_tag_exist = ProductTag::find($_tag);
            //         if($product_tag_exist){
            //             $_tag_arr[] = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
            //             $_tags = implode(",",$_tag_arr);
            //         }
            //     }
            // }

            // $main_tags = $request->input('main_tags');
            // $fk_main_tag_id = 0;

            // if($main_tags && is_array($main_tags) && !empty($main_tags)){
            //     $main_tag_arr = [];
            //     foreach ($main_tags as $main_tag) {
            //         // Check tag already exist
            //         $product_main_tag_exist = ProductTag::find($main_tag);
            //         if($product_main_tag_exist){
            //             $fk_main_tag_id = $main_tag;
            //             $main_tag_arr[] = $product_main_tag_exist->title_en.','.$product_main_tag_exist->title_ar;
            //         }
            //         $main_tags = implode(",",$main_tag_arr);
            //     }
            // }

            $update_arr = [
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_sub_category_id' => $request->input('fk_sub_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                'stock' => $request->input('instock'),
                'base_price' => $request->input('price'),
                // 'fk_main_tag_id' => $fk_main_tag_id,
                // 'main_tags' => $main_tags??'',
                // '_tags' => $_tags??'',
                // 'search_filters' => $request->input('search_filters'),
                // 'custom_tag_bundle' => $request->input('custom_tag_bundle'),
                // 'min_scale' => $request->input('min_scale'),
                // 'max_scale' => $request->input('max_scale'),
                'desc_en' => $request->input('desc_en'),
                // 'desc_ar' => $request->input('desc_ar'),
                // 'characteristics_en' => $request->input('characteristics_en'),
                // 'characteristics_ar' => $request->input('characteristics_ar'),
                // 'country_code' => $request->input('country_code'),
            ];

            if ($request->hasFile('image')) {
                $product_images_path = str_replace('\\', '/', storage_path("app/public/images/product_images/"));
                $product_images_url_base = "public/images/product_images/";

                $path = "/images/product_images/";
                $filenameWithExt = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                // Upload Image
                $check = $request->file('image')->storeAs('public/' . $path, $fileNameToStore);
                if ($check):
                    $update_arr['product_image_url'] = env('APP_URL') . $product_images_url_base . $fileNameToStore;
                endif;
            }

            // if ($request->hasFile('country_icon')) {
            //     $country_icon_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
            //     $country_icon_url_base = "storage/images/country_icons/"; 

            //     $path = "/images/country_icons/";
            //     $filenameWithExt = $request->file('country_icon')->getClientOriginalName();
            //     $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //     $extension = $request->file('country_icon')->getClientOriginalExtension();
            //     $fileNameToStore = $filename.'_'.time().'.'.$extension;
            //     // Upload Image
            //     $check = $request->file('country_icon')->storeAs('public/'.$path,$fileNameToStore);
            //     if ($check) :
            //         $update_arr['country_icon'] = env('APP_URL').$country_icon_path . $fileNameToStore;
            //     endif;
            // }

            $update = BaseProduct::find($id)->update($update_arr);
            if ($update) {

                $base_product_stores = BaseProductStore::where(['fk_product_id' => $id, 'deleted' => 0])->get();

                if (!empty($base_product_stores)) {
                    $this->update_base_product_store($id);
                }

                return back()->with('success', 'Product updated successfully');
            }

            return back()->withInput()->with('error', 'Error while updating Product');

        } else {
            return redirect('vendor/all_products')->with('error', 'Product not found');
        }
    }


    public function delete_product($id)
    {

        $id = base64url_decode($id);
        $update = BaseProduct::find($id)->update(['deleted' => 1]);

        if ($update) {
            $base_product_stores = BaseProductStore::where(['fk_product_id' => $id, 'deleted' => 0])->get();

            if (!empty($base_product_stores)) {
                foreach ($base_product_stores as $key => $value) {
                    $value->update(['deleted' => 1]);
                }
            }

            return response()->json(['error' => false, 'status_code' => 200, 'message' => 'Product deleted successfully']);
        } else {
            return response()->json(['error' => true, 'status_code' => 404, 'message' => 'Some error found']);
        }
    }

    protected function product_stock($id = null, $batch_id = null)
    {
        

        $batchId = \App\Model\AdminSetting::where('key', '=', 'batchIdBp')->first();
        $batchIdValue = $batchId ? $batchId->value : 0;
        $completed_percent = 0;

        $endpointURL = url('vendor/batch') . '/' . $batchIdValue;

        $getFields = [];

        //Call endpoint
        $response = callGetAPI($endpointURL, $getFields);
        if ($response && $response->totalJobs > 0) {
            $completed_percent = (($response->processedJobs + $response->failedJobs) / $response->totalJobs) *
                100;
        } else {
            $completed_percent = 0;
        }

        return view('vendor.products.update_product_store_stock', [
            'completed_percent' => $completed_percent,
            'batchId' => $batchIdValue,
            'id' => $id,
            'batch_id' => $batch_id
        ]);
    }

    public function batch($id = null)
    {
        return Bus::findBatch($id);
    }

    protected function bulk_stock_update(Request $request)
    {
        $store_id = base64url_decode($request->input('store_id'));
        // print_r($store_id);die;

        $store = Store::find($store_id);
        if ($store) {
            $store_no = $store->id;
            $company_id = $store->company_id;
        } else {
            return redirect('admin/base_products/stock_update/' . $store_id)->with('success', 'Store is not found!');
        }

        $file = file($request->file->getRealPath());

        $data = array_slice($file, 1);

        $parts = (array_chunk($data, 200));

        $batch = Bus::batch([])->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) use ($store_id) {
            // The batch has finished executing...
            \App\Model\AdminSetting::where('key', '=', 'stock_upload_completed_store' . $store_id)->update([
                'value' => 1
            ]);
        })->name('UpdateBaseProductStock_Step1_StoreID:' . $store_id)->dispatch();

        foreach ($parts as $index => $part) {

            $part = array_map('utf8_encode', $part);
            $data = array_map('str_getcsv', $part);

            // Store the csv in the path
            $stock_files_path = str_replace('\\', '/', storage_path("app/public/stock_files/"));
            $stock_files_url_base = "storage/stock_files/";
            $filePath = $stock_files_path . 'myCSVFile-' . $index . '.csv';
            $fp = fopen($filePath, 'w+');
            fputcsv($fp, $part);
            fclose($fp);

            $batch->add(new UpdateBaseProductStock_Step1(json_encode($data), $index, $store_no, $company_id, $batch->id));
            sleep(1);
        }
        // return $batch;
        \App\Model\AdminSetting::where('key', '=', 'batchIdBp')->update([
            'value' => $batch->id
        ]);

        return redirect('vendor/products/stock/' . base64url_encode($store_id) . '/' . $batch->id)->with('success', 'Stock update started');
    }

   


}
