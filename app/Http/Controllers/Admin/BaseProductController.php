<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\BaseProduct;
use App\Model\Category;
use App\Model\Brand;
use App\Model\Store;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;
use App\Model\ProductTag;
use App\Model\TagBundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

use App\Exports\BaseProductsExport;
use App\Exports\BaseProductsStoreExport;
use App\Exports\BaseProductsExportHeading;
use App\Exports\BaseProductsStoreExportHeadings;
use Maatwebsite\Excel\Facades\Excel;

require __DIR__ . "../../../../../vendor/autoload.php";

use Algolia\AlgoliaSearch\SearchClient;
use App\Jobs\UpdateBaseProductStock_Step1;
use App\Jobs\UpdateBaseProductStock_Step2;
use App\Jobs\UpdateBaseProductSingleColumn;
use App\Jobs\UpdateBaseProductStoreSingleColumn;
use App\Jobs\UpdateBaseProductStoreIsActive;
use App\Model\VendorRequestedProduct;
use App\Model\ProductOfferOption;
use Illuminate\Bus\Batch;
use Artisan;
use Throwable;


class BaseProductController extends CoreApiController
{
    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == '3.130.60.46' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {
        $filter = $request->query('filter');
        $unit = $request->query('unit');
        $product_id = $request->query('product_id');
        $tags = $request->query('tags');

        $products = BaseProduct::with('stocks')->where('parent_id', '=', 0);
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

        return view('admin.base_products.index', [
            'products' => $products,
            'classification' => $classification,
            'filter' => $filter,
            'unit' => $unit,
            'product_id' => $product_id,
            'tags' => $tags,
            'stores' => $stores
        ]);
    }

    protected function show($id = null)
    {
        $product = BaseProduct::find($id);

        if ($product) {
            $product['company_name'] = !empty($product->getCompany) ? $product->getCompany->name : 'N/A';
            $product['category_name'] = !empty($product->getProductCategory) ? $product->getProductCategory->category_name_en : 'N/A';
            $product['sub_category_name'] = !empty($product->getProductSubCategory) ? $product->getProductSubCategory->category_name_en : 'N/A';
            $product['brand_name'] = (!empty($product->getProductBrand) ? $product->getProductBrand->brand_name_en : 'N/A');
            $group_products = BaseProduct::where('parent_id', $id)->orderBy('id', 'desc')->get();
            $product['group_products'] = $group_products;
            return view('admin.base_products.show', ['product' => $product]);
        } else {
            return redirect('admin/base_products')->with('error', 'Product not found');
        }
    }

    protected function create(Request $request)
    {
        $brands = Brand::where('deleted', '=', 0)->orderBy('id', 'desc')->get();
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();
        $product_tags = ProductTag::all();

        return view('admin.base_products.create', ['brands' => $brands, 'categories' => $categories, 'product_tags' => $product_tags]);
    }

    protected function store(Request $request)
    {
        $_tags = $request->input('_tags');
        if($_tags){
            $_tag_arr = [];
            foreach ($_tags as $_tag) {
                if ($_tag=='') {
                    $_tag_arr[] = $_tag;
                    continue;
                }
                // Check tag already exist
                $product_tag_exist = ProductTag::find($_tag);
                if($product_tag_exist){
                    $_tag_arr[] = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                    $_tags = implode(",",$_tag_arr);
                }
            }
        }

        $main_tags = $request->input('main_tags');
        $fk_main_tag_id = 0;
        if($main_tags && is_array($main_tags) && !empty($main_tags)){
            $main_tag_arr = [];
            foreach ($main_tags as $main_tag) {
                // Check tag already exist
                $product_main_tag_exist = ProductTag::find($main_tag);
                if($product_main_tag_exist){
                    $fk_main_tag_id = $main_tag;
                    $main_tag_arr[] = $product_main_tag_exist->title_en.','.$product_main_tag_exist->title_ar;
                }
                $main_tags = implode(",",$main_tag_arr);
            }
        }

        $insert_arr = [
            'parent_id' => 0,
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('unit'),
            'is_home_screen' => $request->input('is_home_screen') ?? 0,
            'frequently_bought_together' => $request->input('frequently_bought_together') ?? 0,
            'fk_main_tag_id' => $fk_main_tag_id,
            'main_tags' => $main_tags??'',
            '_tags' => $_tags??'',
            'search_filters' => $request->input('search_filters'),
            'custom_tag_bundle' => $request->input('custom_tag_bundle'),
            'desc_en' => $request->input('desc_en'),
            'desc_ar' => $request->input('desc_ar'),
            'characteristics_en' => $request->input('characteristics_en'),
            'characteristics_ar' => $request->input('characteristics_ar'),
            'country_code' => $request->input('country_code'),
        ];
        
        if ($request->hasFile('image')) {
            $product_images_path = str_replace('\\', '/', storage_path("app/public/images/base_product_images/"));
            $product_images_url_base = "storage/images/base_product_images/"; 

            $path = "/images/base_product_images/";
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['product_image_url'] = env('APP_URL').$product_images_url_base . $fileNameToStore;
            endif;
        }

        if ($request->hasFile('country_icon')) {
            $country_icon_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
            $country_icon_url_base = "storage/images/country_icons/"; 

            $path = "/images/country_icons/";
            $filenameWithExt = $request->file('country_icon')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('country_icon')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('country_icon')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['country_icon'] = env('APP_URL').$country_icon_path . $fileNameToStore;
            endif;
        }

        $create = BaseProduct::create($insert_arr);
        if ($create) {
            BaseProduct::find($create->id)->update(['itemcode' => empty($create->itemcode) ? $create->id . time() : '']);

            return redirect('admin/base_products')->with('success', 'Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    protected function edit($id = null)
    {
        $product = BaseProduct::find($id);

        if ($product) {
            $brands = Brand::where('deleted', '=', 0)
                ->orderBy('brand_name_en', 'asc')
                ->get();
            $sub_categories = Category::where('parent_id', '=', $product->fk_category_id)
                ->where('deleted', '=', 0)
                ->orderBy('category_name_en', 'asc')
                ->get();
            $categories = Category::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')
                ->get();
            $product_tags = ProductTag::all();

            return view('admin.base_products.edit', ['product' => $product, 'brands' => $brands, 'sub_categories' => $sub_categories, 'categories' => $categories, 'product_tags' => $product_tags]);
        } else {
            return back()->withInput()->with('error', 'Product not found');
        }
    }

    protected function update(Request $request, $id = null)
    {
        $product = BaseProduct::find($id);
        if ($product) {
            $_tags = $request->input('_tags');

            if($_tags){
                $_tag_arr = [];
                foreach ($_tags as $_tag) {
                    if ($_tag=='') {
                        $_tag_arr[] = $_tag;
                        continue;
                    }
                    // Check tag already exist
                    $product_tag_exist = ProductTag::find($_tag);
                    if($product_tag_exist){
                        $_tag_arr[] = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                        $_tags = implode(",",$_tag_arr);
                    }
                }
            }

            $main_tags = $request->input('main_tags');
            $fk_main_tag_id = 0;

            if($main_tags && is_array($main_tags) && !empty($main_tags)){
                $main_tag_arr = [];
                foreach ($main_tags as $main_tag) {
                    // Check tag already exist
                    $product_main_tag_exist = ProductTag::find($main_tag);
                    if($product_main_tag_exist){
                        $fk_main_tag_id = $main_tag;
                        $main_tag_arr[] = $product_main_tag_exist->title_en.','.$product_main_tag_exist->title_ar;
                    }
                    $main_tags = implode(",",$main_tag_arr);
                }
            }

            $update_arr = [
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_sub_category_id' => $request->input('fk_sub_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                'fk_main_tag_id' => $fk_main_tag_id,
                'main_tags' => $main_tags??'',
                '_tags' => $_tags??'',
                'search_filters' => $request->input('search_filters'),
                'custom_tag_bundle' => $request->input('custom_tag_bundle'),
                'min_scale' => $request->input('min_scale'),
                'max_scale' => $request->input('max_scale'),
                'desc_en' => $request->input('desc_en'),
                'desc_ar' => $request->input('desc_ar'),
                'characteristics_en' => $request->input('characteristics_en'),
                'characteristics_ar' => $request->input('characteristics_ar'),
                'country_code' => $request->input('country_code'),
            ];

            if ($request->hasFile('image')) {
                $product_images_path = str_replace('\\', '/', storage_path("app/public/images/base_product_images/"));
                $product_images_url_base = "storage/images/base_product_images/"; 

                $path = "/images/base_product_images/";
                $filenameWithExt = $request->file('image')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $update_arr['product_image_url'] = env('APP_URL').$product_images_url_base . $fileNameToStore;
                endif;
            }

            if ($request->hasFile('country_icon')) {
                $country_icon_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
                $country_icon_url_base = "storage/images/country_icons/"; 

                $path = "/images/country_icons/";
                $filenameWithExt = $request->file('country_icon')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('country_icon')->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $request->file('country_icon')->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $update_arr['country_icon'] = env('APP_URL').$country_icon_path . $fileNameToStore;
                endif;
            }

            $update = BaseProduct::find($id)->update($update_arr);
            if ($update) {

                $base_product_stores = BaseProductStore::where(['fk_product_id' => $id, 'deleted' => 0])->get();
                
                if(!empty($base_product_stores)){
                    $this->update_base_product_store($id);
                }
                
                return back()->with('success', 'Product updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating Product');
        } else {
            return redirect('admin/base_products')->with('error', 'Product not found');
        }
    }

    //Ajax
    protected function delete_base_product(Request $request)
    {
        $update = BaseProduct::find($request->input('product_id'))->update(['deleted' => 1]);

        if ($update) {
            $base_product_stores = BaseProductStore::where(['fk_product_id' => $request->input('product_id'), 'deleted' => 0])->get();

            if(!empty($base_product_stores)){
                foreach ($base_product_stores as $key => $value) {
                    $value->update(['deleted' => 1]);
                }
            }

            return response()->json(['error' => false, 'status_code' => 200, 'message' => 'Product deleted successfully']);
        } else {
            return response()->json(['error' => true, 'status_code' => 404, 'message' => 'Some error found']);
        }
    }

    //Ajax
    protected function create_base_product_ajax(Request $request)
    {
        $storeProduct = BaseProductStore::where(['fk_product_id' => $request->input('fk_product_id'),'fk_store_id' => $request->input('fk_store_id'),
            'itemcode' => $request->input('itemcode'),'barcode' => $request->input('barcode'),'deleted' => 0])->first();
        
        if($storeProduct == null){
            
            $storeExist = BaseProductStore::where(['fk_product_id' => $request->input('fk_product_id'),'fk_store_id' => $request->input('fk_store_id'),'deleted' => 0])->first();
            
            if($storeExist){

                $storeProductItemcode = BaseProductStore::where(['fk_product_id' => $request->input('fk_product_id'),'fk_store_id' => $request->input('fk_store_id'),
                'itemcode' => $request->input('itemcode'),'deleted' => 0])->first();
               
                if($storeProductItemcode != null){
                    
                    $storeProductBarcode = BaseProductStore::where(['fk_product_id' => $request->input('fk_product_id'),'fk_store_id' => $request->input('fk_store_id'),
                        'itemcode' => $request->input('itemcode'),'barcode' => $request->input('barcode'),'deleted' => 0])->first();

                        if($storeProductBarcode){

                            return response()->json([
                                'status' => false,
                                'error_code' => 201,
                                'message' => 'The product already exists in this store'
                            ]);
                        }
                }else{

                    return response()->json([
                        'status' => false,
                        'error_code' => 201,
                        'message' => 'The product store item code should be unique'
                    ]);
                }
            }

        }else{
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'The product already exists in this store'
            ]);
        }

        if($request->input('allow_margin') == null && ($request->input('product_distributor_price') >= $request->input('product_store_price'))){
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'The product price should not be less than the distributor price'
            ]);
        }
        
        $base_product = BaseProduct::find($request->input('fk_product_id'));
        $base_product_brand_id = $base_product->fk_brand_id;
        $base_product_sub_category_id = $base_product->fk_sub_category_id;
        $base_product_store_id = $request->input('fk_store_id');

        //calculate product distributor price
        $product_store = Store::find($request->input('fk_store_id'));
        if($product_store->back_margin > 0){
            $product_distributor_price = ($request->input('product_distributor_price_before_back_margin') - (($product_store->back_margin / 100) * $request->input('product_distributor_price_before_back_margin')));
        }else{
            $product_distributor_price = $request->input('product_distributor_price_before_back_margin');
        }
        
        $insert_arr = [
            'fk_product_id' => $request->input('fk_product_id'),
            'fk_store_id' => $request->input('fk_store_id'),
            'itemcode' => $request->input('itemcode'),
            'barcode' => $request->input('barcode'),
            'allow_margin' => $request->input('allow_margin') ?? 0,
            'other_names' => $request->input('other_names'),
            'product_store_price' => 0,
            'product_distributor_price_before_back_margin' => $request->input('product_distributor_price_before_back_margin'),
            'product_distributor_price' => $product_distributor_price,
            'stock' => $request->input('stock'),
            'product_store_stock' => $base_product->product_store_stock,
            'product_store_updated_at' => date('Y-m-d H:i:s'),
            'fk_offer_option_id' => $base_product->fk_offer_option_id,
            'fk_price_formula_id' => $base_product->fk_price_formula_id,
            'margin' => 0,
            'back_margin' => $base_product->back_margin,
            'is_active' => $request->input('is_active') ?? 0,
        ];

        $diff = $request->input('product_store_price') - $product_distributor_price;
        if ($request->input('allow_margin') == 1 || ($request->input('allow_margin') == 0 && $diff < 0)) {
            $priceArr = calculatePriceFromFormula($product_distributor_price, $base_product->fk_offer_option_id, $base_product_brand_id, $base_product_sub_category_id, $base_product_store_id);
            $insert_arr['margin'] = $priceArr[1];
            $insert_arr['product_store_price'] = $priceArr[0];
            $insert_arr['base_price'] = $priceArr[2];
            $insert_arr['base_price_percentage'] = $priceArr[3];
            $insert_arr['discount_percentage'] = $priceArr[4];
            $insert_arr['fk_price_formula_id'] = $priceArr[5];
        } else {
            $profit = abs($product_distributor_price - $request->input('product_store_price'));
            $margin = number_format((($profit / $product_distributor_price) * 100), 2);
            $insert_arr['margin'] = $margin;
            $insert_arr['product_store_price'] = $request->input('product_store_price');
            $insert_arr['base_price'] = $request->input('base_price');
            $insert_arr['base_price_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_distributor_price'])/$insert_arr['product_distributor_price']) * 100), 2);
            $insert_arr['discount_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_store_price'])/$insert_arr['base_price']) * 100), 2);
            $insert_arr['fk_price_formula_id'] = 0;
        }

        $create = BaseProductStore::create($insert_arr);
        $base_product_store = BaseProductStore::with('getStore')->find($create->id);

        if($create){
            if($this->update_base_product($request->input('fk_product_id'))){
                $this->update_base_product_store($request->input('fk_product_id'));
            }
            
            return response()->json(['status' => true, 'error_code' => 200, 'data' => $base_product_store->refresh()]);
        }else{
            return response()->json([
                'status' => false,
                'error_code' => 201,
            ]);
        }
    }

    //Ajax
    protected function update_base_product_ajax(Request $request)
    {
        $storeProduct = BaseProductStore::where('fk_product_id', $request->input('base_product_store_product_id'))
            ->where('fk_store_id', $request->input('fk_store_id'))
            ->where('itemcode', $request->input('itemcode'))
            ->where('barcode', $request->input('barcode'))
            ->where('id','!=', $request->input('base_product_store_id'))
            ->where('deleted','=', 0) 
            ->first();
        
        if($storeProduct == null){
            
            $storeExist = BaseProductStore::where(['fk_product_id' => $request->input('base_product_store_product_id'),'fk_store_id' => $request->input('fk_store_id'),'deleted' => 0])->first();
            
            if($storeExist){

                $storeProductItemcode = BaseProductStore::where(['fk_product_id' => $request->input('base_product_store_product_id'),'fk_store_id' => $request->input('fk_store_id'),
                'itemcode' => $request->input('itemcode'),'deleted' => 0])->first();
               
                if($storeProductItemcode != null){
                    
                    $storeProductBarcode = BaseProductStore::where('fk_product_id', $request->input('base_product_store_product_id'))
                        ->where('fk_store_id', $request->input('fk_store_id'))
                        ->where('itemcode', $request->input('itemcode'))
                        ->where('barcode', $request->input('barcode'))
                        ->where('id','!=', $request->input('base_product_store_id'))
                        ->where('deleted','=', 0) 
                        ->first();;

                        if($storeProductBarcode){

                            return response()->json([
                                'status' => false,
                                'error_code' => 201,
                                'message' => 'The product already exists in this store'
                            ]);
                        }
                }else{

                    return response()->json([
                        'status' => false,
                        'error_code' => 201,
                        'message' => 'The product store item code should be unique'
                    ]);
                }
            }
        }else{
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'The product already exists in this store'
            ]);
        }

        if($request->input('allow_margin') == null && ($request->input('product_distributor_price') >= $request->input('product_store_price'))){
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'The product price should not be less than the distributor price'
            ]);
        }

        $base_product = BaseProduct::find($request->input('base_product_store_product_id'));
        $base_product_brand_id = $base_product->fk_brand_id;
        $base_product_sub_category_id = $base_product->fk_sub_category_id;
        $base_product_store_id = $request->input('fk_store_id');

        //calculate product distributor price
        $product_store = Store::find($request->input('fk_store_id'));
        if($product_store->back_margin > 0){
            $product_distributor_price = ($request->input('product_distributor_price_before_back_margin') - (($product_store->back_margin / 100) * $request->input('product_distributor_price_before_back_margin')));
        }else{
            $product_distributor_price = $request->input('product_distributor_price_before_back_margin');
        }
        
        $update_arr = [
            'fk_product_id' => $request->input('base_product_store_product_id'),
            'fk_store_id' => $request->input('fk_store_id'),
            'itemcode' => $request->input('itemcode'),
            'barcode' => $request->input('barcode'),
            'allow_margin' => $request->input('allow_margin') ?? 0,
            'other_names' => $request->input('other_names'),
            'product_store_price' => 0,
            'product_distributor_price_before_back_margin' => $request->input('product_distributor_price_before_back_margin'),
            'product_distributor_price' => $product_distributor_price,
            'stock' => $request->input('stock'),
            'product_store_stock' => $request->input('stock'),
            'product_store_updated_at' => date('Y-m-d H:i:s'),
            'fk_offer_option_id' => $base_product->fk_offer_option_id,
            'fk_price_formula_id' => $base_product->fk_price_formula_id,
            'margin' => 0,
            'back_margin' => $base_product->back_margin,
            'is_active' => $request->input('is_active') ?? 0,
        ];

        $diff = $request->input('product_store_price') - $product_distributor_price;
        if ($request->input('allow_margin') == 1 || ($request->input('allow_margin') == 0 && $diff < 0)) {
            $priceArr = calculatePriceFromFormula($product_distributor_price, $base_product->fk_offer_option_id, $base_product_brand_id, $base_product_sub_category_id, $base_product_store_id);
            $update_arr['margin'] = $priceArr[1];
            $update_arr['product_store_price'] = $priceArr[0];
            $update_arr['base_price'] = $priceArr[2];
            $update_arr['base_price_percentage'] = $priceArr[3];
            $update_arr['discount_percentage'] = $priceArr[4];
            $update_arr['fk_price_formula_id'] = $priceArr[5];
        } else {
            $profit = abs($product_distributor_price - $request->input('product_store_price'));
            $margin = number_format((($profit / $product_distributor_price) * 100), 2);
            $update_arr['margin'] = $margin;
            $update_arr['product_store_price'] = floatval(str_replace(',','',$request->input('product_store_price')));
            $update_arr['base_price'] = floatval(str_replace(',','',$request->input('base_price')));
            $update_arr['base_price_percentage'] = number_format(((($update_arr['base_price']-$update_arr['product_distributor_price'])/$update_arr['product_distributor_price']) * 100), 2);
            $update_arr['discount_percentage'] = number_format(((($update_arr['base_price']-$update_arr['product_store_price'])/$update_arr['base_price']) * 100), 2);
            $update_arr['fk_price_formula_id'] = 0;
        }
        
        $base_product_store = BaseProductStore::find($request->input('base_product_store_id'));
        $update = $base_product_store->update($update_arr);

        if($update){
            $this->update_base_product($request->input('base_product_store_product_id'));
            return response()->json(['status' => true, 'error_code' => 200, 'data' => $base_product_store->refresh()]);
        }else{
            return response()->json([
                'status' => false,
                'error_code' => 201,
            ]);
        }
    }

    //Ajax
    protected function delete_base_product_store(Request $request)
    {
        $delete = BaseProductStore::find($request->input('base_product_store_id'))->update(['deleted' => 1]);
        if ($delete) {
            $produt = BaseProductStore::find($request->input('base_product_store_id'));
            $this->update_base_product($produt->fk_product_id);
        }
    }

    protected function create_multiple()
    {
        return view('admin.base_products.create_multiple');
    }
    
    // Edit Multiple products
    protected function edit_multiple(Request $request, $id = 0)
    {
        $filter = $request->query('filter');
        $filter_category = $request->query('filter_category');
        $tags = $request->query('tags');

        $categories = Category::where('deleted','=',0)->where('parent_id','<>',0)->get();
        $brands = Brand::where('deleted','=',0)->get();
        $product_tags = ProductTag::all();
        // $product_tags = ProductTag::take(10)->get();

        $products = BaseProduct::with('stocks')->where('parent_id', '=', 0);
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        } 
        if (!empty($tags)) {
            $products = $products->where('_tags', 'like', '%' . $tags . '%');
        }
        if (!empty($filter_category)) {
            $products = $products->where('fk_sub_category_id', 'like', $filter_category);
        }
        $products = $products->where('deleted', '=', 0)
        ->orderBy('id', 'desc')
        ->sortable(['id' => 'desc'])
        ->paginate(10);
        $products->appends([
            'filter' => $filter
        ]);

        $classification = \App\Model\Classification::where(['parent_id' => 0])
            ->orderBy('name_en', 'asc')
            ->get();

        //stores
        $stores = Store::all();

        return view('admin.base_products.edit_multiple', [
            'products' => $products,
            'classification' => $classification,
            'filter_category' => $filter_category,
            'filter' => $filter,
            'tags' => $tags,
            'stores' => $stores,
            'categories'=> $categories,
            'brands' => $brands,
            'product_tags' => $product_tags
        ]);
    }
    
    protected function product_edit_multiple_save(Request $request)
    {
        $id = $request->input('id');
        $product = BaseProduct::find($id);
        if ($product) {
            $sub_category = Category::find($request->input('fk_sub_category_id'));
            $brand = Brand::find($request->input('fk_brand_id'));
            if (!$sub_category) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error in the sub category selection']);
            }
            $category = Category::find($sub_category->parent_id);
            if (!$category) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error in the category selection']);
            }

            // Main Tags
            $main_tags = $request->input('main_tags');
            $fk_main_tag_id = 0;
            if($main_tags && is_array($main_tags) && !empty($main_tags)){
                $main_tag_arr = [];
                foreach ($main_tags as $main_tag) {
                    // Check tag already exist
                    $product_main_tag_exist = ProductTag::where(['title_en'=>$main_tag])->first();
                    if($product_main_tag_exist){
                        $fk_main_tag_id = $main_tag;
                        $main_tag_arr[] = $product_main_tag_exist->title_en.','.$product_main_tag_exist->title_ar;
                    }
                    $main_tags = implode(",",$main_tag_arr);
                }
            }
            
            // _Tags
            // $_tags = $request->input('_tags');
            // if($_tags){
            //     $_tag_arr = [];
            //     foreach ($_tags as $_tag) {
            //         if ($_tag=='') {
            //             $_tag_arr[] = $_tag;
            //             continue;
            //         }
            //         // Check tag already exist
            //         $product_tag_exist = ProductTag::where(['title_en'=>$_tag])->first();
            //         if($product_tag_exist){
            //             $_tag_arr[] = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
            //             $_tags = implode(",",$_tag_arr);
            //         }
            //     }
            // }

            $fk_sub_category_id = $sub_category->id;
            $sub_category_name_en = $sub_category->category_name_en;
            $sub_category_name_ar = $sub_category->category_name_ar;
            $fk_category_id = $category->id;
            $category_name_en = $category->category_name_en;
            $category_name_ar = $category->category_name_ar;
            $fk_brand_id = $brand ? $brand->id : '';
            $brand_name_en = $brand ? $brand->brand_name_en : '';
            $brand_name_ar = $brand ? $brand->brand_name_ar : '';
            $update_arr = [
                'fk_category_id' => $fk_category_id,
                'category_name' => $category_name_en,
                'category_name_ar' => $category_name_ar,
                'fk_sub_category_id' => $fk_sub_category_id,
                'sub_category_name' => $sub_category_name_en,
                'sub_category_name_ar' => $sub_category_name_ar,
                'fk_brand_id' => $fk_brand_id,
                'brand_name' => $brand_name_en,
                'brand_name_ar' => $brand_name_ar,
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                // '_tags' => $_tags??'',
                'search_filters' => $request->input('search_filters'),
                'country_code' => $request->input('country_code'),
                'fk_main_tag_id' => $fk_main_tag_id,
                'main_tags' => $main_tags,
                'fk_tag_bundle_id' => $request->input('tag_bundle_id'),
            ];

            // Upload product image
            if ($request->hasFile('image')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'image', $path);
                
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $duplicate = BaseProduct::selectRaw("COUNT(*) > 1")
                        ->where('product_image_url', '=', $product->product_image)
                        ->first();

                    if ($product->product_image != '' && !$duplicate) {
                        $destinationPath = public_path("images/product_images/");
                        if (!empty($product->getProductImage) && file_exists($destinationPath . $product->getProductImage->file_name)) {
                            unlink($destinationPath . $product->getProductImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $product->product_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }

                    $update_arr['product_image'] = $returnArr->id;
                    $update_arr['product_image_url'] = asset('images/product_images') . '/' . $check;
                endif;
            }

            //Upload country flag
            if ($request->hasFile('country_icon')) {
                $path = "/images/country_icons/";
                $check = $this->uploadFile($request, 'country_icon', $path);

                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);
    
                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];
    
                    $returnArr = $this->insertFile($req);
                    $update_arr['country_icon'] = asset('images/country_icons') . '/' . $check;
                endif;
                
            }
            
            $update = $product->update($update_arr);
        
            if ($update) {

                $base_product_stores = BaseProductStore::where(['fk_product_id' => $id, 'deleted' => 0])->get();

                if(!empty($base_product_stores)){
                    $this->update_base_product_store($id);
                }

                $product = BaseProduct::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $update_arr]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    //Base products bulk upload
    protected function bulk_upload_product(Request $request)
    {
        $path = "/product_files/";
        $file = $this->uploadFile($request, 'base_products_csv', $path);

        $products = csvToArray(public_path('/product_files/') . $file);

        if ($products) {

            foreach ($products as $key => $value) {

                // Get category
                $fk_category_id = (int)trim($value[2]);
                $fk_sub_category_id = (int)trim($value[3]);
                $fk_brand_id = (int)trim($value[4]);
                // $category = Category::find($fk_category_id );
                // $subcategory = Category::where(['id' => $fk_sub_category_id, 'parent_id' => $fk_category_id ])->first();
                // $brand = Brand::find($fk_brand_id);
                
                // Min scale and max scale
                $min_scale = (double)trim($value[11]);
                $max_scale = (double)trim($value[12]);

                $_tags = $value[10];

                // Check product tag exist
                $_tags = $value[10];
                $split_tags = explode(',',$_tags);
                if($split_tags){
                    $_tags_arr = [];
                    foreach ($split_tags as $key => $tag) {
                        $product_tag_exist = ProductTag::where('title_en',$tag)->first();
                        if($product_tag_exist){
                            $_tags_arr[]= $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                            $_tags = implode(",",$_tags_arr);
                        }else{
                            $insert_arr = [
                                'title_en' => $tag
                            ];
                            //Creating product tag if not exiting
                            $create = ProductTag::create($insert_arr);

                            if($create){
                                $_tags_arr[]= $create->title_en.','.$create->title_ar;
                                $_tags = implode(",",$_tags_arr);
                            }
                        }
                    }
                }

                $insert_arr = [
                    'product_type' => trim($value[0]),
                    'parent_id' => (int)trim($value[1]),
                    'fk_category_id' => $fk_category_id,
                    'fk_sub_category_id' => $fk_sub_category_id ,
                    'fk_brand_id' => $fk_brand_id,
                    'product_name_en' => trim($value[5]),
                    'product_name_ar' => trim($value[6]),
                    'product_image_url' => trim($value[7]),
                    'base_price' => (double)trim($value[8]),
                    'unit' => $value[9],
                    '_tags' => $_tags??'',
                    'min_scale' => $min_scale,
                    'max_scale' => $max_scale,
                    'country_code' => trim($value[13]),
                    'country_icon' => trim($value[14])

                ];
                
                // Copy product image from the url
                if ($value[7] && $value[7]!='') {
                    // $check = $this->uploadFile($request, 'image', $path);
                    // Storage::putFile('spares', $file);

                    if (strpos($value[7], 'http:') === 0 || strpos($value[7], 'https:') === 0) {
                        $product_images_path = str_replace('\\', '/', storage_path("app/public/images/product_images/"));
                        $product_images_url_base = "storage/images/product_images/"; 
                        // Copy from internet
                        $product_images_ext = pathinfo($value[7], PATHINFO_EXTENSION);
                        $product_images_name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $value[0]);
                        $product_images_name = mb_ereg_replace("([\.]{2,})", '', $product_images_name);
                        $product_images_name = time().'_'.$product_images_name.'.'.$product_images_ext;
                        $check = copy($value[7], $product_images_path.$product_images_name);
                        if ($check) :
                            $nameArray = explode('.', $check);
                            $ext = end($nameArray);
            
                            $req = [
                                'file_path' => $product_images_url_base,
                                'file_name' => $product_images_name,
                                'file_ext' => $ext
                            ];
            
                            $returnArr = $this->insertFile($req);
                            $insert_arr['product_image'] = $returnArr->id;
                            $insert_arr['product_image_url'] = env("APP_URL", "https://jeeb.tech/").$product_images_url_base.$product_images_name;
                        endif;
                    } else {
                        $product_images_path = str_replace('\\', '/', storage_path("app/public/images/product_images_3/"));
                        $product_images_url_base = "storage/images/product_images_3/"; 
                        // Insert manually
                        $nameArray = explode('.', $value[7]);
                        $ext = end($nameArray);
        
                        $req = [
                            'file_path' => $product_images_url_base,
                            'file_name' => $value[7],
                            'file_ext' => $ext
                        ];
        
                        $returnArr = $this->insertFile($req);
                        $insert_arr['product_image'] = $returnArr->id;
                        $insert_arr['product_image_url'] = env("APP_URL", "https://jeeb.tech/").$product_images_url_base.$value[7];
                    }

                }

                 // Copy Country Icons from the url
                 if ($value[14] && $value[14]!='') {
                    // $check = $this->uploadFile($request, 'image', $path);
                    // Storage::putFile('spares', $file);

                    if (strpos($value[14], 'http:') === 0 || strpos($value[14], 'https:') === 0) {
                        $country_icons_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
                        $country_icons_url_base = "storage/images/country_icons/"; 
                        // Copy from internet
                        $country_icons_ext = pathinfo($value[14], PATHINFO_EXTENSION);
                        $country_icons_name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $value[0]);
                        $country_icons_name = mb_ereg_replace("([\.]{2,})", '', $country_icons_name);
                        $country_icons_name = time().'_'.$country_icons_name.'.'.$country_icons_ext;
                        $check = copy($value[14], $country_icons_path.$country_icons_name);
                        if ($check) :
                            $nameArray = explode('.', $check);
                            $ext = end($nameArray);
            
                            $req = [
                                'file_path' => $country_icons_url_base,
                                'file_name' => $country_icons_name,
                                'file_ext' => $ext
                            ];
                            $insert_arr['country_icon'] = env("APP_URL", "https://jeeb.tech/").$country_icons_url_base.$country_icons_name;
                        endif;
                    } else {
                        $country_icons_path = str_replace('\\', '/', storage_path("app/public/images/country_icons_2/"));
                        $country_icons_url_base = "storage/images/country_icons_2/"; 
                        // Insert manually
                        $nameArray = explode('.', $value[14]);
                        $ext = end($nameArray);
        
                        $req = [
                            'file_path' => $country_icons_url_base,
                            'file_name' => $value[14],
                            'file_ext' => $ext
                        ];
                        $insert_arr['country_icon'] = env("APP_URL", "https://jeeb.tech/").$country_icons_url_base.$value[7];
                    }

                }

                
                $create = BaseProduct::create($insert_arr);
                if ($create) {
                    \Log::info('Product created (product ID: '.$create->id.') itemcode: '.$value[0].' barcode: '.$value[1]);
                } else {
                    \Log::info('Product not created itemcode: '.$value[0].' barcode: '.$value[1]);
                }
                
                
            }

            return redirect('admin/base_products')->with('success', 'Base Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Base Products');
    }

    //Base product stores bulk upload
    protected function bulk_upload_product_store(Request $request)
    {
        $path = "/product_files/";
        $file = $this->uploadFile($request, 'base_products_store_csv', $path);

        $products = csvToArray(public_path('/product_files/') . $file);

        if ($products) {

            foreach ($products as $key => $value) {

                $base_product = BaseProduct::find((int)trim($value[9]));

                if (!$base_product) {
                    \Log::info('Base product not found ID: '.$value[9].' itemcode: '.$value[0].' barcode: '.$value[1]);
                    continue;
                } elseif ((int)trim($value[2]) == 0 && ((double)trim($value[3]) >= (double)trim($value[5]))) {
                    \Log::info('Base product price should not be less than the distributor price ID: '.$value[9].' itemcode: '.$value[0].' barcode: '.$value[1]);
                    continue;
                }

                //calculate product distributor price
                $product_store = Store::find((int)trim($value[10]));
                if( isset($product_store) && $product_store->back_margin > 0){
                    $product_distributor_price = ((double)trim($value[3]) - (($product_store->back_margin / 100) * (double)trim($value[3])));
                }else{
                    $product_distributor_price = (double)trim($value[3]);
                }

                $insert_arr = [
                    'fk_product_id' => (int)trim($value[9]),
                    'fk_store_id' => (int)trim($value[10]),
                    'itemcode' => trim($value[0]),
                    'barcode' => trim($value[1]),
                    'allow_margin' => (int)trim($value[2]) ?? 0,
                    'other_names' => trim($value[7]) == "" ? null : trim($value[7]),
                    'product_store_price' => 0,
                    'product_distributor_price_before_back_margin' => (double)trim($value[3]),
                    'product_distributor_price' => $product_distributor_price,
                    'stock' => (int)trim($value[6]),
                    'product_store_stock' => (int)trim($value[6]),
                    'product_store_updated_at' => date('Y-m-d H:i:s'),
                    'fk_offer_option_id' => $base_product->fk_offer_option_id,
                    'fk_price_formula_id' => $base_product->fk_price_formula_id,
                    'margin' => 0,
                    'back_margin' => $base_product->back_margin,
                    'is_active' => (int)trim($value[8]) ?? 0,
                ];

                $diff = (double)trim($value[5]) - $insert_arr['product_distributor_price'];
                if ((int)trim($value[2]) == 1 || ((int)trim($value[2]) == 0 && $diff < 0)) {
                    $priceArr = calculatePriceFromFormula($insert_arr['product_distributor_price'], $base_product->fk_offer_option_id, $base_product->fk_brand_id, $base_product->fk_sub_category_id, (int)trim($value[10]));
                    $insert_arr['margin'] = $priceArr[1];
                    $insert_arr['product_store_price'] = $priceArr[0];
                    $insert_arr['base_price'] = $priceArr[2];
                    $insert_arr['base_price_percentage'] = $priceArr[3];
                    $insert_arr['discount_percentage'] = $priceArr[4];
                    $insert_arr['fk_price_formula_id'] = $priceArr[5];
                } else {
                    $profit = abs($insert_arr['product_distributor_price'] - (double)trim($value[5]));
                    $margin = number_format((($profit / $insert_arr['product_distributor_price']) * 100), 2);
                    $insert_arr['margin'] = $margin;
                    $insert_arr['product_store_price'] = (double)trim($value[5]);
                    $insert_arr['base_price'] = (double)trim($value[6]);
                    $insert_arr['base_price_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_distributor_price'])/$insert_arr['product_distributor_price']) * 100), 2);
                    $insert_arr['discount_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_store_price'])/$insert_arr['base_price']) * 100), 2);
                    $insert_arr['fk_price_formula_id'] = 0;
                }
                
                $exist = BaseProductStore::where(['itemcode'=>trim($value[0]), 'barcode'=>trim($value[1]),'fk_store_id'=> (int)trim($value[10])])->first();
                if ($exist) {
                    $update = BaseProductStore::find($exist->id)->update($insert_arr);
                    if($update){
                        $this->update_base_product((int)trim($value[9]));
                    }
                    \Log::info('Product updated itemcode: '.$value[0].' barcode: '.$value[1]);
                } else {
                    $create = BaseProductStore::create($insert_arr);
                    if ($create) {
                        if($this->update_base_product((int)trim($value[9]))){
                            $this->update_base_product_store((int)trim($value[9]));
                        }
                        \Log::info('Product Stores created (product ID: '.$create->id.') itemcode: '.$value[0].' barcode: '.$value[1]);
                    } else {
                        \Log::info('Product Stores not created itemcode: '.$value[0].' barcode: '.$value[1]);
                    }
                }
            }

            return redirect('admin/base_products')->with('success', 'Base Product Stores added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Base Product Stores');
    }

    //Base product stores - Stock update
    protected function stock_update_stores(Request $request)
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

        return view('admin.base_products.store_stock_update', ['stores' => $stores, 'filter' => $filter]);
    }
    
    protected function stock_update_new($id = null, $batch_id= null)
    {
        $batchId = \App\Model\AdminSetting::where('key', '=', 'batchIdBp')->first();
        $batchIdValue = $batchId ? $batchId->value : 0;
        $completed_percent = 0;

        $endpointURL = url('admin/batch') . '/' . $batchIdValue;

        $getFields = [];

        //Call endpoint
        $response = callGetAPI($endpointURL, $getFields);
        if ($response && $response->totalJobs > 0) {
            $completed_percent = (($response->processedJobs + $response->failedJobs) / $response->totalJobs) *
                100;
        } else {
            $completed_percent = 0;
        }

        return view('admin.base_products.update_product_store_stock', [
            'completed_percent' => $completed_percent,
            'batchId' => $batchIdValue,
            'id' => $id,
            'batch_id' => $batch_id
        ]);
    }

    protected function stock_update_stores_new_products(Request $request)
    {
        // $filter = $request->input('filter');
        $store_id = $request->input('store_id');
        $stores = Store::where(['deleted'=>0])->get();
        if (!empty($store_id)) {
            $new_products = BaseProductStock::where('status', '=', 1)
                ->where('fk_store_id', '=', $store_id)
                ->orderBy('updated_at','desc')
                ->paginate(20);
        } else {
            $new_products = BaseProductStock::where('status', '=', 1)
                ->orderBy('updated_at','desc')
                ->paginate(20);
        }

        return view('admin.base_products.stock_update_stores_new_products', [
            'new_products' => $new_products, 
            'stores' => $stores, 
            'store_id' => $store_id
        ]);
    }
    
    protected function bulk_stock_update(Request $request)
    {
        $store_id = $request->input('store_id');
        
        $store = Store::find($store_id);
        if ($store) {
            $store_no = $store->id;
            $company_id = $store->company_id;
        } else {
            return redirect('admin/base_products/stock_update/'.$store_id)->with('success', 'Store is not found!');
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
            \App\Model\AdminSetting::where('key', '=', 'stock_upload_completed_store'.$store_id)->update([
                'value' => 1
            ]);
        })->name('UpdateBaseProductStock_Step1_StoreID:'.$store_id)->dispatch();

        foreach ($parts as $index => $part) {

            $part = array_map('utf8_encode', $part);
            $data = array_map('str_getcsv', $part);

            // Store the csv in the path
            $stock_files_path = str_replace('\\', '/', storage_path("app/public/stock_files/"));
            $stock_files_url_base = "storage/stock_files/"; 
            $filePath = $stock_files_path.'myCSVFile-'.$index.'.csv';
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

        return redirect('admin/base_products/stock_update/'.$store_id.'/'.$batch->id)->with('success', 'Stock update started');
    }

    function add_new_products_store(Request $request){
        
        $product_stock = BaseProductStock::find($request->id);
        if (!$product_stock) {
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'Stock record not found!'
            ]);
        }

        // Required variables
        $fk_product_id = $request->fk_product_id;
        $base_product_store_id = $product_stock->fk_store_id;
        $product_distributor_price_before_back_margin = $product_stock->distributor_price;
        $itemcode = $product_stock->itemcode;
        $barcode = $product_stock->barcode;
        $stock = $product_stock->stock;
        $allow_margin = 1;
        $other_names = '';

        // Add base product store
        $base_product = BaseProduct::find($fk_product_id);
        $base_product_brand_id = $base_product->fk_brand_id;
        $base_product_sub_category_id = $base_product->fk_sub_category_id;
        if (!$base_product) {
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'Base product not found!'
            ]);
        }

        // Calculate product distributor price
        $store = Store::find($base_product_store_id);
        if($store->back_margin > 0){
            $product_distributor_price = ($product_distributor_price_before_back_margin - (($store->back_margin / 100) * $product_distributor_price_before_back_margin));
        }else{
            $product_distributor_price = $product_distributor_price_before_back_margin;
        }
        
        $insert_arr = [
            'fk_product_id' => $fk_product_id,
            'fk_store_id' => $base_product_store_id,
            'itemcode' => $itemcode,
            'barcode' => $barcode,
            'allow_margin' => $allow_margin,
            'other_names' => $other_names,
            'product_store_price' => 0,
            'product_distributor_price_before_back_margin' => $product_distributor_price_before_back_margin,
            'product_distributor_price' => $product_distributor_price,
            'stock' => $stock,
            'product_store_stock' => $base_product->product_store_stock,
            'product_store_updated_at' => date('Y-m-d H:i:s'),
            'fk_offer_option_id' => $base_product->fk_offer_option_id,
            'fk_price_formula_id' => $base_product->fk_price_formula_id,
            'margin' => 0,
            'back_margin' => $base_product->back_margin,
            'is_active' => 1,
        ];

        $priceArr = calculatePriceFromFormula($product_distributor_price, $base_product->fk_offer_option_id, $base_product_brand_id, $base_product_sub_category_id, $base_product_store_id);
        $insert_arr['margin'] = $priceArr[1];
        $insert_arr['product_store_price'] = $priceArr[0];
        $insert_arr['base_price'] = $priceArr[2];
        $insert_arr['base_price_percentage'] = $priceArr[3];
        $insert_arr['discount_percentage'] = $priceArr[4];
        $insert_arr['fk_price_formula_id'] = $priceArr[5];

        $create = BaseProductStore::create($insert_arr);
        $base_product_store = BaseProductStore::with('getStore')->find($create->id);

        if($create){
            if($this->update_base_product($fk_product_id)){
                $this->update_base_product_store($fk_product_id);
            }
            
            $product_stock->update([
                'fk_product_store_id'=>$create->id,
                'status'=>2
            ]);
            return response()->json(['status' => true, 'error_code' => 200, 'data' => $base_product_store->refresh()]);
        }else{
            return response()->json([
                'status' => false,
                'error_code' => 201,
            ]);
        }
        
    }

    protected function stock_update_one_by_one($id = null, $batch_id = null)
    {
        $batchId = \App\Model\AdminSetting::where('key', '=', 'batchIdBp')->first();

        $all_stocks = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id])->paginate(500);
        $all_stocks_count = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id])->count();
        $all_stocks_matched = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'matched'=>1])->count();
        $all_stocks_checked = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'checked'=>1])->count();
        $all_stocks_updated = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'updated'=>1])->count();
        $all_stocks_added_new_product = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'added_new_product'=>1])->count();

        return view('admin.base_products.stock_update_all_stocks', [
            'batchId' => $batchId->value,
            'all_stocks' => $all_stocks,
            'counts' => array(
                'all_stocks_count' => $all_stocks_count,
                'all_stocks_matched' => $all_stocks_matched,
                'all_stocks_checked' => $all_stocks_checked,
                'all_stocks_updated' => $all_stocks_updated,
                'all_stocks_added_new_product' => $all_stocks_added_new_product
            )
        ]);
    }

    protected function post_stock_update_one_by_one_bulk_v3(Request $request)
    {
        
        \Log::info('Calling stock_bp '.$request->id.' ');

        $store_id = $request->store_id;
        $step1_batch_id = $request->id;

        // Initiate the stock update
        Artisan::call('command:BaseProductsStockUpdateInitiate', [
            'store_id' => $store_id
        ]);
        
        $perPage = 10000; // Number of items per page
        $query = \App\Model\ProductStockFromCsv::select('id')->where([
            'batch_id'=>$step1_batch_id,
            // 'checked'=>0
            ]); 
        $paginator = $query->paginate($perPage);
        $lastPage = $paginator->lastPage();

        // Create the jobs array
        $jobs = [];
        for ($i=1; $i <= $lastPage; $i++) { 
            
            $stocks = $query->paginate($perPage, ['*'], 'page', $i);
            $stocks_arr = $stocks->map(function ($stock) {
                if ($stock !== null && is_object($stock)) {
                    return [
                        'id' => $stock->id
                    ];
                }
            })->toArray();

            // Dispatch a sub-job for each product ID
            $jobs[] = new UpdateBaseProductStock_Step2($i,$stocks_arr,$store_id,$step1_batch_id);
            
            // sleep(1);
        }

        // Dispatch the job queue
        Bus::batch($jobs)->name('UpdateBaseProductStock_Step2_StoreID:'.$store_id.'_batch_id:'.$step1_batch_id)->dispatch();

        return response()->json([
            'message'=>'The stock update started in the server',
            'valid'=>true,
            'error'=>false,
        ]);

    }
    
    protected function post_stock_update_one_by_one_not_updated(Request $request)
    {
        
        \Log::info('Updating stock '.$request->id.' ');

        $stock = \App\Model\ProductStockFromCsv::find($request->id);

        $valid = true;
        $error = false;
        $checked = 1;
        $matched = 0;
        $base_product_store_id = 0;
        $base_product_id = 0;
        $updated = 0;
        $added_new_product = 0;

        if (!$stock) {
            return response()->json([
                'message'=>'failed-not found the stock in csv',
                'updated' => $updated,
                'added_new_product' => $added_new_product,
                'valid'=>$valid,
                'error'=>$error,
            ]);
        }
        
        $itemcode = $stock->itemcode;
        $itemcode_without_0 = ltrim($stock->itemcode, '0');
        $barcode = $stock->barcode;
        $barcode_without_0 = ltrim($stock->barcode, '0');
        $fk_store_id = $stock->store_no;

        $selected_record = false;
        $itemCodeExist_without_0 = BaseProductStore::where(['itemcode'=>$itemcode_without_0, 'fk_store_id'=> $fk_store_id, 'deleted' => 0])->first();
        if ($itemCodeExist_without_0) {
            $selected_record = $itemCodeExist_without_0;
        } else {
            $itemCodeExist = BaseProductStore::where(['itemcode'=>$itemcode, 'fk_store_id'=> $fk_store_id, 'deleted' => 0])->first();
            if ($itemCodeExist) {
                $selected_record = $itemCodeExist;
                $selected_record->update(['itemcode'=>$itemcode_without_0]);
            }
        }
                
        if($selected_record){

            $matched=1;
            $base_product_store_id = $selected_record->id;

            \Log::info('Single stock update, Checking stock id: '.$selected_record->id.', with the itemcode '.$selected_record->itemcode.', with the barcode '.$selected_record->barcode);

            $selected_record->update(['itemcode'=>$itemcode_without_0]);
            $barcode_exists = false;

            if ($selected_record->barcode==$barcode || $selected_record->barcode==$barcode_without_0) {
                $barcode_exists = true;
            }

            if ($barcode_exists) {

                $diff = $selected_record->product_price - (double)trim($stock->rsp);
                if ($selected_record->allow_margin == 1 || ($selected_record->allow_margin == 0 && $diff < 0)) {
                    $priceArr = calculatePriceFromFormula((double)trim($stock->rsp), $selected_record->fk_store_id);
                    $insert_arr['margin'] = $priceArr[1];
                    $insert_arr['product_price'] = $priceArr[0];
                } else { 
                    $profit = abs((double)trim($stock->rsp) - $selected_record->product_price);
                    $margin = number_format((($profit / (double)trim($stock->rsp)) * 100), 2);
                    $insert_arr['margin'] = $margin;
                    $insert_arr['product_price'] = $selected_record->product_price;
                }
                
                $insert_arr['distributor_price'] = $stock->rsp;
                $insert_arr['stock'] = $stock->stock;
                $update_row = BaseProductStore::find($selected_record->id)->update($insert_arr);
                
                if ($update_row) {
                    $updated = 1;
                    $update_base_product_res = $this->update_base_product($selected_record->fk_product_id);
                    if ($update_base_product_res) {
                        $update_base_product = $update_base_product_res->getData()->base_product;
                        if ($update_base_product && isset($update_base_product->id)) {
                            $base_product_id = $update_base_product->id;
                        }
                    }
                } else {
                    \Log::error('Bulk Stock Update From Server: Updating stock failed for the product store ID: '.$selected_record->id);
                }

            }else{

                $latestStore = $selected_record;

                $diff = $latestStore->product_price - (double)trim($stock->rsp);
                if ($latestStore->allow_margin == 1 || ($latestStore->allow_margin == 0 && $diff < 0)) {
                    $priceArr = calculatePriceFromFormula((double)trim($stock->rsp), $latestStore->fk_store_id);
                    $insert_arr['margin'] = $priceArr[1];
                    $insert_arr['product_price'] = $priceArr[0];
                } else { 
                    $profit = abs((double)trim($stock->rsp) - $latestStore->product_price);
                    $margin = number_format((($profit / (double)trim($stock->rsp)) * 100), 2);
                    $insert_arr['margin'] = $margin;
                    $insert_arr['product_price'] = $latestStore->product_price;
                }
                $insert_arr['itemcode'] = $latestStore->itemcode;
                $insert_arr['barcode'] = $latestStore->barcode;
                $insert_arr['unit'] = $latestStore->unit;
                $insert_arr['other_names'] = $latestStore->other_names;
                $insert_arr['distributor_price'] = $stock->rsp;
                $insert_arr['stock'] = $stock->stock;
                $insert_arr['allow_margin'] = $latestStore->allow_margin;
                $insert_arr['fk_product_id'] = $latestStore->fk_product_id;
                $insert_arr['fk_store_id'] = $latestStore->fk_store_id;
                $insert_arr['is_active'] = 0;
                
                $added_new_product_row = BaseProductStock::create($insert_arr);
                if ($added_new_product_row) {
                    $added_new_product = 1;
                } else {
                    \Log::error('Bulk Stock Update From Server: Adding new product failed for the product barcode: '.$barcode);
                }
                
            }
        }
        
        \App\Model\ProductStockFromCsv::find($stock->id)->update([
            'checked' => $checked, 
            'matched' => $matched, 
            'base_product_store_id' => $base_product_store_id, 
            'base_product_id' => $base_product_id,
            'updated' => $updated, 
            'added_new_product' => $added_new_product 
        ]);

        return response()->json([
            'message'=>'completed',
            'updated' => $updated,
            'matched' => $matched, 
            'added_new_product' => $added_new_product,
            'base_product_store_id' => $base_product_store_id, 
            'base_product_id' => $base_product_id,
            'update_base_product_res' => $update_base_product_res ?? false,
            'valid'=>$valid,
            'error'=>$error,
        ]);
    }
    
    protected function requested_products_store(Request $request)
    {
        $page = $request->query('page') !== null ? $request->query('page') : 1;

        $categories = Category::where('deleted','=',0)->where('parent_id','<>',0)->get();
        $brands = Brand::where('deleted','=',0)->get();
        $product_tags = ProductTag::all();
        $product_tag_bundles = TagBundle::all();
        $parent_categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();

        $filter = $request->query('filter');

        $base_products = BaseProduct::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->orderBy('product_name_en', 'asc')
                ->get();

        if (!empty($filter)) {
            $products = VendorRequestedProduct::join('stores','vendor_requested_products.fk_store_id', '=', 'stores.id')
                ->select('vendor_requested_products.*','vendor_requested_products.product_name_en as product_name','stores.name as store_name','stores.company_name as store_company_name')
                ->where(function ($query) use ($filter) {
                        $query->where('vendor_requested_products.product_name_en', 'like', '%%' . $filter . '%%')
                        ->orWhere('stores.name', 'like', '%%' . $filter . '%%')
                        ->orWhere('vendor_requested_products.itemcode', 'like', '%%' . $filter . '%%')
                        ->orWhere('vendor_requested_products.barcode', 'like', '%%' . $filter . '%%');
                    })
                ->orderBy('vendor_requested_products.id','desc')
                ->paginate(20);
        } else {
            $products = VendorRequestedProduct::join('stores','vendor_requested_products.fk_store_id', '=', 'stores.id')
                ->select('vendor_requested_products.*','vendor_requested_products.product_name_en as product_name','stores.name as store_name','stores.company_name as store_company_name')
                ->orderBy('vendor_requested_products.id','desc')
                ->paginate(20);
        }
        $products->appends(['filter' => $filter]);

        return view('admin.base_products.requested_products_store', [
            'products' => $products,
            'filter' => $filter,
            'page' => $page,
            'base_products' => $base_products,
            'categories' => $categories,
            'brands' => $brands,
            'parent_categories' => $parent_categories,
            'product_tags' => $product_tags
        ]);
    }

    function add_requested_products_store(Request $request){
        
        $requested_product = VendorRequestedProduct::find($request->product_id);

        $base_product = BaseProduct::find($request->fk_product_id);
        $base_product_brand_id = $base_product->fk_brand_id;
        $base_product_sub_category_id = $base_product->fk_sub_category_id;
        $base_product_store_id = $requested_product->fk_store_id;
        
        //calculate product distributor price
        $product_store = Store::find($requested_product->fk_store_id);
        if($product_store->back_margin > 0){
            $product_distributor_price = ($requested_product->base_price - (($product_store->back_margin / 100) * $requested_product->base_price));
        }else{
            $product_distributor_price = $requested_product->base_price;
        }
    
        $insert_arr = [
            'fk_product_id' => $request->fk_product_id,
            'fk_store_id' => $requested_product->fk_store_id,
            'itemcode' => $requested_product->itemcode,
            'barcode' => $requested_product->barcode,
            'allow_margin' => 1,
            'other_names' => null,
            'product_store_price' => 0,
            'base_price' => 0,
            'margin' => 0,
            'product_distributor_price_before_back_margin' => $requested_product->base_price,
            'product_distributor_price' => $product_distributor_price,
            'stock' => $requested_product->stock,
            'product_store_stock' => $requested_product->stock,
            'product_store_updated_at' => date('Y-m-d H:i:s'),
            'fk_offer_option_id' => $base_product->fk_offer_option_id,
            'fk_price_formula_id' => $base_product->fk_price_formula_id,
            'margin' => 0,
            'back_margin' => $base_product->back_margin,
            'base_price_percentage' => $base_product->base_price_pebase_productrcentage,
            'discount_percentage' => $base_product->discount_percentage,
            'is_active' => 1,
        ];

        $diff = 0 - $product_distributor_price;
        if ((1 && $diff < 0)) {
            $priceArr = calculatePriceFromFormula($product_distributor_price, $base_product->fk_offer_option_id, $base_product_brand_id, $base_product_sub_category_id, $base_product_store_id);
            $insert_arr['margin'] = $priceArr[1];
            $insert_arr['product_store_price'] = $priceArr[0];
            $insert_arr['base_price'] = $priceArr[2];
            $insert_arr['base_price_percentage'] = $priceArr[3];
            $insert_arr['discount_percentage'] = $priceArr[4];
            $insert_arr['fk_price_formula_id'] = $priceArr[5];
        } else {
            $profit = abs($product_distributor_price - 0);
            $margin = number_format((($profit / $product_distributor_price) * 100), 2);
            $insert_arr['margin'] = $margin;
            $insert_arr['product_store_price'] = 0;
            $insert_arr['base_price'] = $product_distributor_price;
            $insert_arr['base_price_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_distributor_price'])/$insert_arr['product_distributor_price']) * 100), 2);
            $insert_arr['discount_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_store_price'])/$insert_arr['base_price']) * 100), 2);
            $insert_arr['fk_price_formula_id'] = 0;
        }
        
        $create = BaseProductStore::create($insert_arr);
        
        if($create){

            $requested_product->delete($request->product_id);

            if($this->update_base_product($request->fk_product_id)){
                $this->update_base_product_store($request->fk_product_id);
            }
        }
        
        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => "Success"
        ]);
    }

    // remove store requested products
    function remove_store_requested_product(Request $request){

        $requested_product = VendorRequestedProduct::find($request->product_id);
        $requested_product->delete();

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => "Success"
        ]);
    }

    // Offers
    protected function offers(Request $request, $id = 0)
    {
        $filter = $request->query('filter');
        $offer_type = $request->query('offer_type');
        $offer_date = $request->query('offer_date');
        $offer_term = '';
        $products = false;
        $filter_products = false;

        $client = SearchClient::create(env('ALGOLIA_APP_ID'), env('ALGOLIA_SECRET'));
        $index = $client->initIndex(env('ALGOLIA_PRODUCT_INDEX'));

        // Offer products
        if ($offer_type!='' && $offer_date!='') {
            $offer_term = $offer_type.'_'.$offer_date;
            $products = $index->search('', [
                'filters' => 'offers:'.$offer_term
              ]);
            // dd($products);
            $products = $products && isset($products['hits']) ? $products['hits'] : [];


            // Search products
            $filter_products = $index->search($filter);
            $filter_products = $filter_products && isset($filter_products['hits']) ? $filter_products['hits'] : [];    
        } 

        return view('admin.base_products.offers', [
            'offer_term' => $offer_term,
            'products' => $products,
            'filter' => $filter,
            'filter_products' => $filter_products
        ]);
    }
    
    protected function product_edit_offer_save(Request $request)
    {
        $id = $request->input('id');
        $product = BaseProduct::find($id);
        if ($product) {
            $offers = $product->offers;
            $offer_term = $request->input('offer_term');
            $offers_arr = explode(",",$offers);
            $already_exist = false;
            if($offers_arr && $offer_term){
                foreach ($offers_arr as $offer) {
                    // Check tag already exist
                    if ($offer==$offer_term) {
                        $already_exist = true;
                    }
                }
            }
            if(!$already_exist) {

                $offers = ($offers!="" AND $offers!=NULL) ? $offers.",".$offer_term : $offer_term;
                $update_arr = [
                    'offers' => $offers??''
                ];
                $update = $product->update($update_arr);
                
                if ($update) {
                    $product = BaseProduct::find($id);
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product offer updated successfully', 'data' => $product]);
                } else {
                    return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product offer']);
                }

            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is already in the offer list']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not found!']);
        }
    }

    protected function product_edit_offer_remove(Request $request)
    {
        $id = $request->input('id');
        $product = BaseProduct::find($id);
        if ($product) {
            $offers = $product->offers;
            $offer_term = $request->input('offer_term');
            $offers_arr = explode(",",$offers);
            $already_exist = false;
            if($offers_arr && $offer_term){
                foreach ($offers_arr as $key => $offer) {
                    // Check tag already exist
                    if ($offer==$offer_term) {
                        unset($offers_arr[$key]);
                        $already_exist = true;
                    }
                }
            }
            if($already_exist) {

                $offers = ($offers_arr AND !empty($offers_arr)) ? implode(",",$offers_arr) : "";
                $update_arr = [
                    'offers' => $offers??''
                ];
                $update = $product->update($update_arr);
                
                if ($update) {
                    $product = BaseProduct::find($id);
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product offer removed successfully', 'data' => $product]);
                } else {
                    return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while removing product offer']);
                }

            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not in the offer list']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'The product is not found!']);
        }
    }

    //Base products discounts upload
    protected function products_discount()
    {
        return view('admin.base_products.product_discount_update');
    }

    //Base products discounts updates
    protected function update_products_discount(Request $request)
    {
        $path = "/product_files/";
        $file = $this->uploadFile($request, 'base_products_discounts_csv', $path);

        $products = csvToArray(public_path('/product_files/') . $file);
        
        if ($products) {

            foreach ($products as $key => $value) {
                
                $base_product = BaseProduct::find(trim($value[0]));
                if($base_product && ($base_product->fk_store_id !=0 && $base_product->fk_product_store_id !=0)){
                    $base_product_store = BaseProductStore::where(['id' => (int)trim($value[1]), 'fk_product_id' => (int)trim($value[0]), 'fk_store_id' => (int)trim($value[2])])->first();
                    if($base_product_store){

                        $update_base_product_store_arr = [
                            'distributor_price' => (int)trim($value[3]),
                            'product_price' => (int)trim($value[4]),
                        ];
    
                        $update_base_product_store = $base_product_store->update($update_base_product_store_arr);
                        
                        if ($update_base_product_store) {
                            \Log::info('Product Store updated (product ID: '.$base_product_store->id.') ');
                        } else {
                            \Log::info('Product Store not updated product ID: '.$value[1]);
                        }

                        update_product_discount_from_csv((int)trim($value[0]), (int)trim($value[5]));
                    }
                   
                }else{
                    $base_product->update(['base_price' => (int)trim($value[5])]);
                }
               
            }

            return redirect('admin/base_products/products_discount')->with('success', 'Products uploaded successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    //offer options
    protected function offer_options(Request $request)
    { 
        $filter = $request->query('filter');

        if (!empty($filter)) {

            $offer_options = ProductOfferOption::where('name', 'like', '%' . $filter . '%')
                ->where('deleted','=',0)
                ->sortable(['id' => 'asc'])
                ->paginate(50);
        }else{

            $offer_options = ProductOfferOption::where('deleted','=',0)
                ->sortable(['id' => 'asc'])
                ->paginate(50);
        }
        
        
        return view('admin.base_products.offer_options', [
            'offer_options' => $offer_options,
            'filter' => $filter
        ]);
    }

    //add offer option form
    protected function create_offer_option(Request $request)
    { 
        $total_offer_options = ProductOfferOption::count();
        return view('admin.base_products.create_offer_option', [
            'total_offer_options' => $total_offer_options
        ]);
    }

    //store offer option
    protected function store_offer_option(Request $request)
    { 
        $insert_arr = [
            'name' => $request->input('name'),
            'base_price_percentage' => $request->input('base_price_percentage'),
            'discount_percentage' => $request->input('discount_percentage')
        ];

        ProductOfferOption::create($insert_arr);
        
        return redirect('admin/base_products/offer_options')->with('success', 'Offer Option created successfully');
    }

    //edit offer option form
    protected function edit_offer_option(Request $request, $id)
    { 
        $id = base64_decode($id);
        $offer_option = ProductOfferOption::find($id);
        return view('admin.base_products.edit_offer_option',[
            'offer_option' => $offer_option
        ]);
    }

    //update offer option
    protected function update_offer_option(Request $request, $id)
    { 
        $update_arr = [
            'name' => $request->input('name'),
            'base_price_percentage' => $request->input('base_price_percentage'),
            'discount_percentage' => $request->input('discount_percentage')
        ];

        $offer_option = ProductOfferOption::find($id);
        $offer_option->update($update_arr);
        
        return redirect('admin/base_products/offer_options')->with('success', 'Offer option updated successfully');
    }

    //delete offer option
    protected function delete_offer_option(Request $request, $id)
    { 
        $id = base64_decode($id);

        $offer_option = ProductOfferOption::find($id);
        $offer_option->update(['deleted' => 1]);
        
        return redirect('admin/base_products/offer_options')->with('success', 'Offer option deleted successfully');
    }

    //product offers
    protected function product_offers(Request $request)
    { 
        $filter = $request->query('filter');
        $fk_offer_option_id = $request->query('fk_offer_option_id');
        
        $products = BaseProduct::with('stocks')->where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->where('deleted', '=', 0);
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        } 
        if (!empty($fk_offer_option_id)) {
            $products = $products->where('fk_offer_option_id', '=', $fk_offer_option_id);
        } 
        $products = $products->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->paginate(50);

        $products->appends(['filter' => $filter]);
        
        //offer options
        $offer_options = ProductOfferOption::where(['status' => 1, 'deleted' => 0])->get();
        
        return view('admin.base_products.product_offers', [
            'products' => $products,
            'offer_options' => $offer_options,
            'filter' => $filter,
            'fk_offer_option_id' => $fk_offer_option_id
        ]);
    }

    //update product offer
    protected function update_product_offer(Request $request, $id)
    {
        $offer_option = ProductOfferOption::find($request->input('fk_offer_option_id'));
        if($offer_option){
            $update_arr = [
                'fk_offer_option_id' => $request->input('fk_offer_option_id'),
                'base_price_percentage' => $offer_option->base_price_percentage,
                'discount_percentage' => $offer_option->discount_percentage,
            ];
        }else{

            $update_arr = [
                'fk_offer_option_id' => 0,
                'base_price_offer_percentage' => 0,
                'discount_offer_percentage' => 0,
            ];
        }
        
        $base_product = BaseProduct::find($id);
        $update_base_product = $base_product->update($update_arr);
        
        if($update_base_product && $base_product){

            $this->update_base_product_store($id);

            $base_product_stores = BaseProductStore::where(['fk_product_id' => $id, 'deleted' => 0])->get();
        
            foreach ($base_product_stores as $key => $value) {

                $diff = $value->product_store_price - $value->product_distributor_price;
                if ($value->allow_margin == 1 || ($value->allow_margin == 0 && $diff < 0)) {
                    $priceArr = calculatePriceFromFormula($value->product_distributor_price, $base_product->fk_offer_option_id, $base_product->fk_brand_id, $base_product->fk_sub_category_id, $value->fk_store_id);
                    $insert_arr['margin'] = $priceArr[1];
                    $insert_arr['product_store_price'] = $priceArr[0];
                    $insert_arr['base_price'] = $priceArr[2];
                    $insert_arr['base_price_percentage'] = $priceArr[3];
                    $insert_arr['discount_percentage'] = $priceArr[4];
                    $insert_arr['fk_price_formula_id'] = $priceArr[5];

                    $value->update($insert_arr);
                }else{

                    $profit = abs($value->product_distributor_price - 0);
                    $margin = number_format((($profit / $value->product_distributor_price) * 100), 2);
                    $insert_arr['margin'] = $margin;
                    $insert_arr['product_store_price'] = $value->product_store_price;
                    $insert_arr['base_price'] =  $value->base_price;
                    $insert_arr['base_price_percentage'] = number_format(((($insert_arr['base_price']-$value->distributor_price)/$value->distributor_price) * 100), 2);
                    $insert_arr['discount_percentage'] = number_format(((($insert_arr['base_price']-$insert_arr['product_store_price'])/$insert_arr['base_price']) * 100), 2);
                    $insert_arr['fk_price_formula_id'] = 0;

                    $value->update($insert_arr);
                }
            }

            $this->update_base_product($id);
        }
        
        return redirect()->back()->with('success', 'Product offer updated successfully');
    }


    // Base products single column update
    protected function bulk_upload_single_column(Request $request)
    {
        return view('admin.base_products.bulk_upload_single_column');
    }

    protected function bulk_upload_single_column_post(Request $request)
    {
        $file = file($request->file->getRealPath());
        $data = array_slice($file, 1);
        $parts = (array_chunk($data, 1000));
        $key = $request->input('key');

        if ($key && count($parts)) {
            foreach ($parts as $part) {
                $data = array_map('str_getcsv', $part);
                // Itemcode upload
                UpdateBaseProductSingleColumn::dispatch($key, $data);
            }
        }

        return redirect('admin/base_products/bulk_upload_single_column')->with('success', 'Process started');
    }

    protected function bulk_upload_single_column_store_post(Request $request)
    {
        $file = file($request->file->getRealPath());
        $data = array_slice($file, 1);
        $parts = (array_chunk($data, 1000));
        $key = $request->input('key');

        if ($key && count($parts)) {
            foreach ($parts as $part) {
                $data = array_map('str_getcsv', $part);
                // Itemcode upload
                UpdateBaseProductStoreSingleColumn::dispatch($key, $data);
            }
        }

        return redirect('admin/base_products/bulk_upload_single_column')->with('success', 'Process started');
    }

    
    protected function activate_all_store_products(Request $request)
    {
        // Filters
        $store_id = $request->input('store_id');
        $base_products_store_activate = $request->input('base_products_store_activate');
        $base_products_store_deactivate = $request->input('base_products_store_deactivate');

        if ($base_products_store_activate=='base_products_store_activate') {
            $status = 1;
            $message = 'Store products are being activated in the server!';
        } elseif ($base_products_store_deactivate=='base_products_store_deactivate') {
            $status = 0;
            $message = 'Store products are being deactivated in the server!';
        } else {
            return redirect('admin/stores/?store_id='.$store_id)->with('error', 'Status is not valid!');
        }

        // Select all base products
        $perPage = 1000; // Number of items per page
        $query = \App\Model\BaseProductStore::where(['deleted'=>0,'fk_store_id'=>$store_id]); 

        $paginator = $query->paginate($perPage);
        $lastPage = $paginator->lastPage();

        for ($i=1; $i <= $lastPage; $i++) { 
            
            $base_products = $query->paginate($perPage, ['*'], 'page', $i);
            $base_products_arr = $base_products->map(function ($base_product) {
                if ($base_product !== null && is_object($base_product)) {
                    return [
                        'id' => $base_product->id
                    ];
                }
            })->toArray();

            // Dispatch the batch job with the array
            UpdateBaseProductStoreIsActive::dispatch($i,$base_products_arr,$store_id,$status);
            
        }
        return redirect('admin/stores/?store_id='.$store_id.'&status='.$status)->with('success', $message);
        
    }
    
    protected function get_base_products_store_status(Request $request){

        if ($request->ajax()) {

            $store_id = trim($request->store_id);
            $products_active = BaseProductStore::where(['deleted'=>0,'fk_store_id'=>$store_id,'is_store_active'=>1])->count();
            $products_inactive = BaseProductStore::where(['deleted'=>0,'fk_store_id'=>$store_id,'is_store_active'=>0])->count();

            $results = array(
                "products_active" => $products_active,
                "products_inactive" => $products_inactive,
            );

            return \Response::json($results);
        }
    }

    protected function export_base_products(Request $request)
    {
        return Excel::download(new BaseProductsExport, 'base_products.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    protected function export_base_products_store(Request $request)
    {
        return Excel::download(new BaseProductsStoreExport, 'base_products_store.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    protected function export_base_products_heading(Request $request)
    {
        return Excel::download(new BaseProductsExportHeading, 'base_products_csv.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    protected function export_base_products_store_heading(Request $request)
    {
        return Excel::download(new BaseProductsStoreExportHeadings, 'base_products_store_csv.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    // Select2 Ajax (remote data) pagination
    protected function get_base_products_ajax(Request $request){

        if ($request->ajax()) {

            $term = trim($request->term);
            $with_price = $request->with_price;
            if ($with_price==1) {
                $posts = BaseProduct::select('id',DB::raw("CONCAT(product_name_en,' ( ',id,' ) - ',unit,' - ',product_store_price) AS text"))
                    ->where('product_name_en', 'LIKE',  '%' . $term. '%')
                    ->where('deleted','=',0)
                    ->orderBy('product_name_en', 'asc')->simplePaginate(5);
            } else {
                $posts = BaseProduct::select('id',DB::raw("CONCAT(product_name_en,' ( ',id,' ) - ',unit) AS text"))
                    ->where('product_name_en', 'LIKE',  '%' . $term. '%')
                    ->where('deleted','=',0)
                    ->orderBy('product_name_en', 'asc')->simplePaginate(5);
            }

            $morePages=true;
            $pagination_obj = json_encode($posts);
            if (empty($posts->nextPageUrl())){
                $morePages=false;
            }
            $results = array(
                "results" => $posts->items(),
                "pagination" => array(
                    "more" => $morePages
                )
            );

            return \Response::json($results);
        }
    }

    protected function create_requested_base_product(Request $request)
    {
        $_tags = $request->input('_tags');
        if($_tags){
            $_tag_arr = [];
            foreach ($_tags as $_tag) {
                if ($_tag=='') {
                    $_tag_arr[] = $_tag;
                    continue;
                }
                // Check tag already exist
                $product_tag_exist = ProductTag::find($_tag);
                if($product_tag_exist){
                    $_tag_arr[] = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                    $_tags = implode(",",$_tag_arr);
                }
            }
        }

        $main_tags = $request->input('main_tags');
        $fk_main_tag_id = 0;
        if($main_tags && is_array($main_tags) && !empty($main_tags)){
            $main_tag_arr = [];
            foreach ($main_tags as $main_tag) {
                // Check tag already exist
                $product_main_tag_exist = ProductTag::find($main_tag);
                if($product_main_tag_exist){
                    $fk_main_tag_id = $main_tag;
                    $main_tag_arr[] = $product_main_tag_exist->title_en.','.$product_main_tag_exist->title_ar;
                }
                $main_tags = implode(",",$main_tag_arr);
            }
        }

        $insert_arr = [
            'parent_id' => 0,
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('unit'),
            'is_home_screen' => $request->input('is_home_screen') ?? 0,
            'frequently_bought_together' => $request->input('frequently_bought_together') ?? 0,
            'fk_main_tag_id' => $fk_main_tag_id,
            'main_tags' => $main_tags??'',
            '_tags' => $_tags??'',
            'search_filters' => $request->input('search_filters'),
            'custom_tag_bundle' => $request->input('custom_tag_bundle'),
            'desc_en' => $request->input('desc_en'),
            'desc_ar' => $request->input('desc_ar'),
            'characteristics_en' => $request->input('characteristics_en'),
            'characteristics_ar' => $request->input('characteristics_ar'),
            'country_code' => $request->input('country_code'),
        ];
        
        if ($request->hasFile('image')) {
            $product_images_path = str_replace('\\', '/', storage_path("app/public/images/base_product_images/"));
            $product_images_url_base = "storage/images/base_product_images/"; 

            $path = "/images/base_product_images/";
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('image')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['product_image_url'] = env('APP_URL').$product_images_url_base . $fileNameToStore;
            endif;
        }else{
            $insert_arr['product_image_url'] = $request->image_default_file;
        }

        if ($request->hasFile('country_icon')) {
            $country_icon_path = str_replace('\\', '/', storage_path("app/public/images/country_icons/"));
            $country_icon_url_base = "storage/images/country_icons/"; 

            $path = "/images/country_icons/";
            $filenameWithExt = $request->file('country_icon')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('country_icon')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $request->file('country_icon')->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['country_icon'] = env('APP_URL').$country_icon_path . $fileNameToStore;
            endif;
        }else{
            $insert_arr['country_icon'] = $request->country_icon_default_file;
        }

        $create = BaseProduct::create($insert_arr);
        if ($create) {
            BaseProduct::find($create->id)->update(['itemcode' => empty($create->itemcode) ? $create->id . time() : '']);

            return redirect('admin/base_products/requested_products_store')->with('success', 'Product Created successfully');
        }
        return back()->withInput()->with('error', 'Error while creating Product');
    }
}
