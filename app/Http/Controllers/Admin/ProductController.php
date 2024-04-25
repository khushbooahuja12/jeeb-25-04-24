<?php

namespace App\Http\Controllers\Admin;

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

require __DIR__ . "../../../../../vendor/autoload.php";

use Algolia\AlgoliaSearch\SearchClient;
// use App\Jobs\ProcessStockUpdateFromCsv;
use App\Jobs\ProcessStockUpdateFromCsvToDb;
use App\Jobs\ProcessStockUpdateFromCsvToDb_Step2;
use App\Jobs\ProcessProductUpload;
use App\Jobs\RetaimartProductBarcodeUpload;
use App\Jobs\ProcessProductTagsUpload;
use App\Jobs\ProcessProductArabicNamesUpload;
use App\Model\Homepage;
use App\Model\HomepageBannerProduct;
use App\Model\Homepagedata;
use App\Model\HomeStatic;
use App\Model\ProductSuggestion;
use App\Model\ProductTag;
use App\Model\ProductOfferOption;
use Illuminate\Bus\Batch;
use Throwable;

use App\Jobs\UpdateBaseProductPrice;
use App\Jobs\UpdateBaseProductStorePrice;

class ProductController extends CoreApiController
{
    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->where('deleted', '=', 0)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $products->appends(['filter' => $filter]);

        $classification = \App\Model\Classification::where(['parent_id' => 0])
            ->orderBy('name_en', 'asc')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'classification' => $classification,
            'filter' => $filter
        ]);
    }

    protected function index_products() {
        // $products = Product::where('parent_id', '=', 0)
        //     ->where('fk_category_id', '=', 16)
        //     ->where('deleted', '=', 0)
        //     ->orderBy('id', 'desc')
        //     ->sortable(['id' => 'desc'])
        //     ->paginate(50);

        $products = Product::where('parent_id', '=', 0)
            ->where('product_type', '=', 'product')
            ->where('fk_category_id', '=', 16)
            ->where('fk_company_id', '=', 1)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        $products_count = $products->count();

        return view('admin.products.index_products', [
            'products' => $products,
            'products_count' =>$products_count 
        ]);
    }
 
    protected function update_stock_multiple(Request $request, $id = 0)
    {
        $filter = $request->query('filter');

        $company_id = $id;
        $companies = Company::where('deleted','=',0)->get();
        $categories = Category::where('deleted','=',0)->where('parent_id','<>',0)->get();

        $no_of_items = 50;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        if (!empty($company_id) && $company_id!=0) {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->whereIn('fk_sub_category_id', [74,75])
                ->where('deleted', '=', 0)
                ->where('fk_company_id', '=', $company_id);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->whereIn('fk_sub_category_id', [74,75])
                ->where('deleted', '=', 0);
        }
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('id', 'desc')
        ->sortable(['id' => 'desc'])
        ->paginate($no_of_items);

        return view('admin.products.update_stock_multiple', [
            'filter' => $filter,
            'products' => $products,
            'company_id' => $company_id,
            'companies' => $companies,
            'categories' => $categories,
            'starting_no' => $starting_no
        ]);
    }

    protected function update_stock_multiple_save(Request $request)
    {

        $id = $request->input('id');
        $product = Product::find($id);
        if ($product) {

            $update_arr = [
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                'store1_distributor_price' => $request->store1_distributor_price,
                'store1' => $request->store1,
            ];

            if ($request->store1_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($request->store1_distributor_price);
                $update_arr['store1_price'] = $priceArr[0];
            }

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

                    $duplicate = Product::selectRaw("COUNT(*) > 1")
                        ->where('product_image', '=', $product->product_image)
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

            $update = $product->update($update_arr);
        
            if ($update) {
                $product = Product::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    protected function edit_multiple(Request $request, $id = 0)
    {
        $filter = $request->query('filter');
        $filter_category = $request->query('filter_category');

        $company_id = $id;
        $companies = Company::where('deleted','=',0)->get();
        $categories = Category::where('deleted','=',0)->where('parent_id','<>',0)->get();
        $brands = Brand::where('deleted','=',0)->get();

        $no_of_items = 50;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $starting_no = $page ? ($page-1)*$no_of_items : 0;

        if (!empty($company_id) && $company_id!=0) {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->where('deleted', '=', 0)
                ->where('fk_company_id', '=', $company_id);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('product_type', '=', 'product')
                ->where('deleted', '=', 0);
        }
        if (!empty($filter_category)) {
            $products = $products->where('fk_sub_category_id', '=', $filter_category);
        }
        if (!empty($filter)) {
            $products = $products->where('product_name_en', 'like', '%' . $filter . '%');
        }
        $products = $products->orderBy('id', 'desc')
        ->sortable(['id' => 'desc'])
        ->paginate($no_of_items);
        // $products->appends(['company_id' => $company_id]);

        $classification = \App\Model\Classification::where(['parent_id' => 0])
            ->orderBy('name_en', 'asc')
            ->get();

        return view('admin.products.edit_multiple', [
            'filter' => $filter,
            'products' => $products,
            'classification' => $classification,
            'company_id' => $company_id,
            'filter_category' => $filter_category,
            'companies' => $companies,
            'categories' => $categories,
            'brands' => $brands,
            'starting_no' => $starting_no
        ]);
    }

    protected function product_edit_multiple_save(Request $request)
    {

        $id = $request->input('id');
        $product = Product::find($id);
        if ($product) {
            $_tags = $request->input('_tags');
            $sub_category = Category::find($request->input('fk_sub_category_id'));
            $brand = Brand::find($request->input('fk_brand_id'));
            if (!$sub_category) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error in the sub category selection']);
            }
            $category = Category::find($sub_category->parent_id);
            if (!$category) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error in the category selection']);
            }

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
                '_tags' => $_tags??'',
                'tags_ar' => $request->input('tags_ar'),
                'country_code' => $request->input('country_code')
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

                    $duplicate = Product::selectRaw("COUNT(*) > 1")
                        ->where('product_image', '=', $product->product_image)
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

            // Upload country flag
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

                    $update_arr['country_icon_id'] = $returnArr->id;
                    $update_arr['country_icon'] = asset('images/country_icons') . '/' . $check;
                endif;
                
            }

            $update = $product->update($update_arr);
        
            if ($update) {
                $product = Product::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Product updated successfully', 'data' => $product]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating product']);
        }
    }

    protected function update_price(Request $request)
    {
        $id = $request->id;

        $product = Product::find($id);
        $valid = 1;
        $error = 0;

        if ($product) {
            $update_arr = [
                'distributor_price' => $product->distributor_price,
                'store1_distributor_price' => $product->store1_distributor_price,
                'store2_distributor_price' => $product->store2_distributor_price,
                'store3_distributor_price' => $product->store3_distributor_price,
                'store4_distributor_price' => $product->store4_distributor_price,
                'store5_distributor_price' => $product->store5_distributor_price,
                'store6_distributor_price' => $product->store6_distributor_price,
                'store7_distributor_price' => $product->store7_distributor_price,
                'store8_distributor_price' => $product->store8_distributor_price,
                'store9_distributor_price' => $product->store9_distributor_price,
                'store10_distributor_price' => $product->store10_distributor_price,
            ];

            if ($product->distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->distributor_price);
                $update_arr['margin'] = $priceArr[1];
                $update_arr['product_price'] = $priceArr[0];
            }
            if ($product->store1_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store1_distributor_price);
                $update_arr['store1_price'] = $priceArr[0];
            }
            if ($product->store2_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store2_distributor_price);
                $update_arr['store2_price'] = $priceArr[0];
            }
            if ($product->store3_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store3_distributor_price);
                $update_arr['store3_price'] = $priceArr[0];
            }
            if ($product->store4_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store4_distributor_price);
                $update_arr['store4_price'] = $priceArr[0];
            }
            if ($product->store5_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store5_distributor_price);
                $update_arr['store5_price'] = $priceArr[0];
            }
            if ($product->store6_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store6_distributor_price);
                $update_arr['store6_price'] = $priceArr[0];
            }
            if ($product->store7_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store7_distributor_price);
                $update_arr['store7_price'] = $priceArr[0];
            }
            if ($product->store8_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store8_distributor_price);
                $update_arr['store8_price'] = $priceArr[0];
            }
            if ($product->store9_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store9_distributor_price);
                $update_arr['store9_price'] = $priceArr[0];
            }
            if ($product->store10_distributor_price > 0) {
                $priceArr = calculatePriceFromFormula($product->store10_distributor_price);
                $update_arr['store10_price'] = $priceArr[0];
            }
            
            $update = Product::find($id)->update($update_arr);
            
            return response()->json([
                'message'=>'completed',
                'updated' => $update,
                'valid'=>$valid,
                'error'=>$error,
            ]);

        } else {

            return response()->json([
                'message'=>'failed',
                'updated' => false,
                'valid'=>$valid,
                'error'=>$error,
            ]);

        }
    }

    protected function create(Request $request)
    {
        $companies = Company::where('deleted', '=', 0)->orderBy('id', 'asc')->get();
        $brands = Brand::where('deleted', '=', 0)->orderBy('id', 'desc')->get();
        $categories = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')->get();
        $product_tags = ProductTag::all();
        $tag_bundles = TagBundle::all();

        return view('admin.products.create', ['brands' => $brands, 'categories' => $categories, 'companies' => $companies, 'product_tags' => $product_tags, 'tag_bundles'=>$tag_bundles]);
    }

    protected function store(Request $request)
    {
        // $max_count_popular = Product::where(['is_home_screen' => 1, 'popular' => 1])->count();
        // $max_count_essential = Product::where(['is_home_screen' => 1, 'essential' => 1])->count();

        // if ($request->input('product_type') == "essential" && $request->input('is_home_screen') == 1 && $max_count_essential > 10) {
        //     return back()->withInput()->with('error', 'Max 10 product allowed for home screen');
        // }

        // if ($request->input('product_type') == "popular" && $request->input('is_home_screen') == 1 && $max_count_popular > 10) {
        //     return back()->withInput()->with('error', 'Max 10 product allowed for home screen');
        // }

        // if ($request->input('_tags') != '') {
        //     $res = explode(',', $request->input('_tags'));
        //     $_tags = json_encode($res, JSON_UNESCAPED_UNICODE);
        // }
        
        $_tags = $request->input('_tags');
        $_tags_arr = [];
        if($request->input('_tags')){
            foreach($_tags as $tag){
                $_tags = ProductTag::find($tag);
                $_tags_arr[]= 'tag_'.$_tags->id.','.$_tags->title_en.','.$_tags->title_ar;
            }
        }
        
        $_tags = implode(",",$_tags_arr);

        $insert_arr = [
            'parent_id' => 0,
            'fk_company_id' => $request->input('fk_company_id'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('unit'),
            'product_price' => $request->input('price'),
            'distributor_price' => $request->input('distributor_price') ?? 0,
            'is_home_screen' => $request->input('is_home_screen') ?? 0,
            'distributor_id' => $request->input('distributor_id'),
            'stock' => 1,
            'tags_id' => $tags_id??'',
            '_tags' => $_tags??'',
            'tags_ar' => $tags_ar??'',
            'fk_tag_bundle_id' => $request->input('fk_tag_bundle_id')??0,
            'itemcode' => $request->input('itemcode'),
            'barcode' => $request->input('barcode'),
            'allow_margin' => $request->input('allow_margin'),
            'country_code' => $request->input('country_code'),
            'store1' => $request->input('store1') ?? 0,
            'store2' => $request->input('store2') ?? 0,
            'store3' => $request->input('store3') ?? 0,
            'store4' => $request->input('store4') ?? 0,
            'store5' => $request->input('store5') ?? 0,
            'store6' => $request->input('store6') ?? 0,
            'store7' => $request->input('store7') ?? 0,
            'store8' => $request->input('store8') ?? 0,
            'store9' => $request->input('store9') ?? 0,
            'store10' => $request->input('store10') ?? 0,
            'store1_distributor_price' => $request->input('store1') ? $request->input('distributor_price') : 0,
            'store2_distributor_price' => $request->input('store2') ? $request->input('distributor_price') : 0,
            'store3_distributor_price' => $request->input('store3') ? $request->input('distributor_price') : 0,
            'store4_distributor_price' => $request->input('store4') ? $request->input('distributor_price') : 0,
            'store5_distributor_price' => $request->input('store5') ? $request->input('distributor_price') : 0,
            'store6_distributor_price' => $request->input('store6') ? $request->input('distributor_price') : 0,
            'store7_distributor_price' => $request->input('store7') ? $request->input('distributor_price') : 0,
            'store8_distributor_price' => $request->input('store8') ? $request->input('distributor_price') : 0,
            'store9_distributor_price' => $request->input('store9') ? $request->input('distributor_price') : 0,
            'store10_distributor_price' => $request->input('store10') ? $request->input('distributor_price') : 0,
        ];
        if ($request->input('allow_margin') == 1) {
            $priceArr = calculatePriceFromFormula($request->input('distributor_price'));
            $insert_arr['margin'] = $priceArr[1];
            $insert_arr['product_price'] = $priceArr[0];

            $insert_arr['store1_price'] = $request->input('store1') ? $priceArr[0] : 0;
            $insert_arr['store2_price'] = $request->input('store2') ? $priceArr[0] : 0;
            $insert_arr['store3_price'] = $request->input('store3') ? $priceArr[0] : 0;
            $insert_arr['store4_price'] = $request->input('store4') ? $priceArr[0] : 0;
            $insert_arr['store5_price'] = $request->input('store5') ? $priceArr[0] : 0;
            $insert_arr['store6_price'] = $request->input('store6') ? $priceArr[0] : 0;
            $insert_arr['store7_price'] = $request->input('store7') ? $priceArr[0] : 0;
            $insert_arr['store8_price'] = $request->input('store8') ? $priceArr[0] : 0;
            $insert_arr['store9_price'] = $request->input('store9') ? $priceArr[0] : 0;
            $insert_arr['store10_price'] = $request->input('store10') ? $priceArr[0] : 0;
        } else {
            // $profit = abs($request->input('distributor_price') - $request->input('product_price'));
            // $margin = number_format((($profit / $request->input('distributor_price')) * 100), 2);
            $margin = 0;

            $insert_arr['margin'] = $margin;
            $insert_arr['product_price'] = $request->input('product_price');

            $insert_arr['store1_price'] = $request->input('store1') ? $request->input('product_price') : 0;
            $insert_arr['store2_price'] = $request->input('store2') ? $request->input('product_price') : 0;
            $insert_arr['store3_price'] = $request->input('store3') ? $request->input('product_price') : 0;
            $insert_arr['store4_price'] = $request->input('store4') ? $request->input('product_price') : 0;
            $insert_arr['store5_price'] = $request->input('store5') ? $request->input('product_price') : 0;
            $insert_arr['store6_price'] = $request->input('store6') ? $request->input('product_price') : 0;
            $insert_arr['store7_price'] = $request->input('store7') ? $request->input('product_price') : 0;
            $insert_arr['store8_price'] = $request->input('store8') ? $request->input('product_price') : 0;
            $insert_arr['store9_price'] = $request->input('store9') ? $request->input('product_price') : 0;
            $insert_arr['store10_price'] = $request->input('store10') ? $request->input('product_price') : 0;
        }

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

                $returnArr = $this->insertFile($req);
                $insert_arr['product_image'] = $returnArr->id;
                $insert_arr['product_image_url'] = asset('images/product_images') . '/' . $check;
            endif;
        }

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
                $insert_arr['country_icon_id'] = $returnArr->id;
                $insert_arr['country_icon'] = asset('images/country_icons') . '/' . $check;
            endif;
        }

        if ($request->input('fk_category_id') != '') {
            $category = \App\Model\Category::find($request->input('fk_category_id'));

            $insert_arr['category_name'] = $category ? $category->category_name_en : '';
            $insert_arr['category_name_ar'] = $category ? $category->category_name_ar : '';
        }
        if ($request->input('fk_sub_category_id') != '') {
            $category = \App\Model\Category::find($request->input('fk_sub_category_id'));

            $insert_arr['sub_category_name'] = $category ? $category->category_name_en : '';
            $insert_arr['sub_category_name_ar'] = $category ? $category->category_name_ar : '';
        }
        if ($request->input('fk_brand_id') != '') {
            $brand = \App\Model\Brand::find($request->input('fk_brand_id'));

            $insert_arr['brand_name'] = $brand ? $brand->brand_name_en : '';
            $insert_arr['brand_name_ar'] = $brand ? $brand->brand_name_ar : '';
        }

        if ($request->input('fk_category_id') == 16) {
            $insert_arr['min_scale'] = $request->input('min_scale');
            $insert_arr['max_scale'] = $request->input('max_scale');
        }

        $create = Product::create($insert_arr);
        if ($create) {
            Product::find($create->id)->update(['itemcode' => empty($create->itemcode) ? $create->id . '_' . time() : $create->itemcode]);

            return redirect('admin/products')->with('success', 'Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    protected function edit($id = null)
    {
        $id = base64url_decode($id);
        $product = Product::find($id);

        if ($product) {
            $companies = Company::where('deleted', '=', 0)->orderBy('id', 'asc')->get();
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
            $tags = ProductTag::all();
            $tag_bundles = TagBundle::all();
            return view('admin.products.edit', ['product' => $product, 'brands' => $brands, 'sub_categories' => $sub_categories, 'categories' => $categories, 'companies' => $companies, 'tags' => $tags, 'tag_bundles'=>$tag_bundles]);
        } else {
            return back()->withInput()->with('error', 'Product not found');
        }
    }

    protected function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $product = Product::find($id);
        if ($product) {
            // if ($request->input('_tags') != '') {
            //     $res = explode(',', $request->input('_tags'));
            //     $_tags = json_encode($res, JSON_UNESCAPED_UNICODE);
            // }
            $_tags = $request->input('_tags');
            
            $_tags_arr = [];
            if($request->input('_tags')){
                foreach($_tags as $tag){
                    $_tags = ProductTag::find($tag);
                    $_tags_arr[]= 'tag_'.$_tags->id.','.$_tags->title_en.','.$_tags->title_ar;
                }
            }
            
            $_tags = implode(",",$_tags_arr);

            $update_arr = [
                'fk_company_id' => $request->input('fk_company_id'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_sub_category_id' => $request->input('fk_sub_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'product_name_en' => $request->input('product_name_en'),
                'product_name_ar' => $request->input('product_name_ar'),
                'unit' => $request->input('unit'),
                'distributor_price' => $request->input('distributor_price') ?? 0,
                'distributor_id' => $request->input('distributor_id'),
                'stock' => 1,
                'tags_id' => $tags_id??'',
                '_tags' => $_tags??'',
                'tags_ar' => $tags_ar??'',
                'fk_tag_bundle_id' => $request->input('fk_tag_bundle_id')??0,
                'deleted' => 0,
                'allow_margin' => $request->input('allow_margin'),
                'min_scale' => $request->input('min_scale'),
                'max_scale' => $request->input('max_scale'),
                'country_code' => $request->input('country_code'),
                'store1' => $request->input('store1') ?? 0,
                'store2' => $request->input('store2') ?? 0,
                'store3' => $request->input('store3') ?? 0,
                'store4' => $request->input('store4') ?? 0,
                'store5' => $request->input('store5') ?? 0,
                'store6' => $request->input('store6') ?? 0,
                'store7' => $request->input('store7') ?? 0,
                'store8' => $request->input('store8') ?? 0,
                'store9' => $request->input('store9') ?? 0,
                'store10' => $request->input('store10') ?? 0,
                'store1_distributor_price' => $request->input('store1_distributor_price') ? $request->input('store1_distributor_price') : 0,
                'store2_distributor_price' => $request->input('store2_distributor_price') ? $request->input('store2_distributor_price') : 0,
                'store3_distributor_price' => $request->input('store3_distributor_price') ? $request->input('store3_distributor_price') : 0,
                'store4_distributor_price' => $request->input('store4_distributor_price') ? $request->input('store4_distributor_price') : 0,
                'store5_distributor_price' => $request->input('store5_distributor_price') ? $request->input('store5_distributor_price') : 0,
                'store6_distributor_price' => $request->input('store6_distributor_price') ? $request->input('store6_distributor_price') : 0,
                'store7_distributor_price' => $request->input('store7_distributor_price') ? $request->input('store7_distributor_price') : 0,
                'store8_distributor_price' => $request->input('store8_distributor_price') ? $request->input('store8_distributor_price') : 0,
                'store9_distributor_price' => $request->input('store9_distributor_price') ? $request->input('store9_distributor_price') : 0,
                'store10_distributor_price' => $request->input('store10_distributor_price') ? $request->input('store10_distributor_price') : 0,

                'product_price' => $request->input('product_price') ? $request->input('product_price') : 0,
                'store1_price' => $request->input('store1_price') ? $request->input('store1_price') : 0,
                'store2_price' => $request->input('store2_price') ? $request->input('store2_price') : 0,
                'store3_price' => $request->input('store3_price') ? $request->input('store3_price') : 0,
                'store4_price' => $request->input('store4_price') ? $request->input('store4_price') : 0,
                'store5_price' => $request->input('store5_price') ? $request->input('store5_price') : 0,
                'store6_price' => $request->input('store6_price') ? $request->input('store6_price') : 0,
                'store7_price' => $request->input('store7_price') ? $request->input('store7_price') : 0,
                'store8_price' => $request->input('store8_price') ? $request->input('store8_price') : 0,
                'store9_price' => $request->input('store9_price') ? $request->input('store9_price') : 0,
                'store10_price' => $request->input('store10_price') ? $request->input('store10_price') : 0,
            ];

            $diff = $request->input('product_price') - $request->input('distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('allow_margin') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['product_price'] = $priceArr[0];
            } else {
                $profit = abs($request->input('distributor_price') - $request->input('product_price'));
                $margin = number_format((($profit / $request->input('distributor_price')) * 100), 2);
                $update_arr['margin'] = $margin;
            }
            $diff = $request->input('store1_price') - $request->input('store1_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store1_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store1_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store1_price'] = $priceArr[0];
            }
            $diff = $request->input('store2_price') - $request->input('store2_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store2_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store2_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store2_price'] = $priceArr[0];
            }
            $diff = $request->input('store3_price') - $request->input('store3_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store3_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store3_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store3_price'] = $priceArr[0];
            }
            $diff = $request->input('store4_price') - $request->input('store4_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store4_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store4_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store4_price'] = $priceArr[0];
            }
            $diff = $request->input('store5_price') - $request->input('store5_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store5_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store5_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store5_price'] = $priceArr[0];
            }
            $diff = $request->input('store6_price') - $request->input('store6_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store6_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store6_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store6_price'] = $priceArr[0];
            }
            $diff = $request->input('store7_price') - $request->input('store7_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store7_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store7_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store7_price'] = $priceArr[0];
            }
            $diff = $request->input('store8_price') - $request->input('store8_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store8_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store8_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store8_price'] = $priceArr[0];
            }
            $diff = $request->input('store9_price') - $request->input('store9_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store9_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store9_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store9_price'] = $priceArr[0];
            }
            $diff = $request->input('store10_price') - $request->input('store10_distributor_price');
            if ($request->input('allow_margin') == 1 || ($request->input('store10_distributor_price') == 0 && $diff < 0)) {
                $priceArr = calculatePriceFromFormula($request->input('store10_distributor_price'));
                $update_arr['margin'] = $priceArr[1];
                $update_arr['store10_price'] = $priceArr[0];
            } 

            if ($request->input('itemcode') != $product->itemcode) {
                $update_arr['itemcode'] = $request->input('itemcode');
            }
            if ($request->input('barcode') != $product->barcode) {
                $update_arr['barcode'] = $request->input('barcode');
            }

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

                    $duplicate = Product::selectRaw("COUNT(*) > 1")
                        ->where('product_image', '=', $product->product_image)
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

                    if ($product->country_icon_id != '') {
                        $destinationPath = public_path("images/country_icons/");
                        if (!empty($product->getCountryIcon) && file_exists($destinationPath . $product->getCountryIcon->file_name)) {
                            unlink($destinationPath . $product->getCountryIcon->file_name);
                        }
                        $returnArr = $this->updateFile($req, $product->country_icon_id);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }

                    $update_arr['country_icon_id'] = $returnArr->id;
                    $update_arr['country_icon'] = asset('images/country_icons') . '/' . $check;
                endif;
            }

            if ($request->input('fk_category_id') != '' && $product->fk_category_id != $request->input('fk_category_id')) {
                $category = \App\Model\Category::find($request->input('fk_category_id'));

                $update_arr['category_name'] = $category ? $category->category_name_en : '';
                $update_arr['category_name_ar'] = $category ? $category->category_name_ar : '';
            }
            if ($request->input('fk_sub_category_id') != '' && $product->fk_sub_category_id != $request->input('fk_sub_category_id')) {
                $category = \App\Model\Category::find($request->input('fk_sub_category_id'));

                $update_arr['sub_category_name'] = $category ? $category->category_name_en : '';
                $update_arr['sub_category_name_ar'] = $category ? $category->category_name_ar : '';
            }
            if ($request->input('fk_brand_id') != '' && $product->fk_brand_id != $request->input('fk_brand_id')) {
                $brand = \App\Model\Brand::find($request->input('fk_brand_id'));

                $update_arr['brand_name'] = $brand ? $brand->brand_name_en : '';
                $update_arr['brand_name_ar'] = $brand ? $brand->brand_name_ar : '';
            }

            $update = Product::find($id)->update($update_arr);
            if ($update) {
                return redirect('admin/products/edit/'.base64url_encode($id))->with('success', 'Product updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating Product');
        } else {
            return back()->withInput()->with('error', 'Product not found');
        }
    }

    protected function app_homepage(Request $request)
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $home_static_ens = HomeStatic::where('lang','=','en')->orderBy('id', 'desc')->limit(10)->get();
        $home_static_ars = HomeStatic::where('lang','=','ar')->orderBy('id', 'desc')->limit(10)->get();
        return view('admin.products.app_homepage', [
            'homepage' => $homepage,
            'home_static_ens' => $home_static_ens,
            'home_static_ars' => $home_static_ars
        ]);
    }

    protected function home_static_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_1.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function app_homepage_remove(Request $request)
    {
        Homepagedata::where(['fk_homepage_id' => $request->input('id')])->delete();

        $homepage = Homepage::find($request->input('id'));

        $delete = $homepage ? Homepage::destroy($request->input('id')) : false;
        if ($delete) {
            return response()->json([
                'status_code' => 200,
                'message' => 'Removed successfully'
            ]);
        } else {
            return response()->json([
                'status_code' => 105,
                'message' => 'Something went wrong'
            ]);
        }
    }

    protected function app_homepage_detail(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage = Homepage::find($id);

        $homepage_data = Homepagedata::where('fk_homepage_id', $id)->get();

        $redirection_type_arr = ['Search', 'Brands', 'Offers', 'Products'];

        return view('admin.products.app_homepage_detail', [
            'ui_type' => $homepage->ui_type,
            'homepage_data' => $homepage_data,
            'redirection_type_arr' => $redirection_type_arr,
            'homepage' => $homepage
        ]);
    }

    protected function app_homepage_create(Request $request)
    {
        return view('admin.products.app_homepage_create');
    }
    protected function app_homepage_store(Request $request)
    {
        $insert_arr = [
            'title' => $request->input('title'),
            'ui_type' => $request->input('ui_type'),
            'index' => $request->input('index'),
            'background_color' => $request->input('background_color')
        ];
        if ($request->input('ui_type') == 1) {
            $insert_arr['banner_type'] = $request->input('banner_type');
        }
        if ($request->hasFile('background_image')) {
            $path = "/images/background_images/";
            $check = $this->uploadFile($request, 'background_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['background_image'] = $returnArr->id;
            endif;
        }
        $create = Homepage::create($insert_arr);
        if ($create) {
            return redirect('admin/app_homepage')->with('success', 'Added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding');
    }

    protected function app_homepage_edit(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage = Homepage::find($id);
        return view('admin.products.app_homepage_edit', ['homepage' => $homepage]);
    }

    protected function app_homepage_update(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage = Homepage::find($id);

        if (($homepage->ui_type != $request->input('ui_type')) && $request->input('ui_type') == 2) {
            Homepagedata::where(['fk_homepage_id' => $homepage->id])->delete();
        }

        $update_arr = [
            'title' => $request->input('title'),
            'ui_type' => $request->input('ui_type'),
            'index' => $request->input('index'),
            'background_color' => $request->input('background_color')
        ];
        if ($request->input('ui_type') == 1) {
            $update_arr['banner_type'] = $request->input('banner_type');
        }
        if ($request->hasFile('background_image')) {
            $path = "/images/banner_images/";
            $check = $this->uploadFile($request, 'background_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($homepage->background_image != '') {
                    $destinationPath = public_path("images/banner_images/");
                    if (!empty($homepage->getBackgroundImage) && file_exists($destinationPath . $homepage->getBackgroundImage->file_name)) {
                        unlink($destinationPath . $homepage->getBackgroundImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $homepage->background_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['background_image'] = $returnArr->id;
            endif;
        }
        $update = Homepage::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/app_homepage')->with('success', 'Updated successfully');
        }
        return back()->withInput()->with('error', 'Something went wrong');
    }

    protected function app_homepage_add_data(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage = Homepage::find($id);

        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->simplePaginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->simplePaginate(50);
        }
        $products->appends(['filter' => $filter]);

        $brands = Brand::where('deleted', '=', 0)->orderBy('brand_name_en', 'asc')->get();

        return view('admin.products.app_homepage_add_data', [
            'products' => $products,
            'filter' => $filter,
            'id' => $id,
            'homepage' => $homepage,
            'brands' => $brands
        ]);
    }

    protected function app_homepage_edit_data(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage_data = Homepagedata::find($id);

        if ($homepage_data->redirection_type == 2) {
            $brand_id = explode('=', $homepage_data->keyword)[1];
        } else {
            $brand_id = '';
        }

        $brands = Brand::where('deleted', '=', 0)->orderBy('brand_name_en', 'asc')->get();

        return view('admin.products.app_homepage_edit_data', [
            'homepage_data' => $homepage_data,
            'brands' => $brands,
            'brand_id' => $brand_id
        ]);
    }

    protected function app_homepage_update_data(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepage_data = Homepagedata::find($id);

        if ($request->input('redirection_type') == 1 || $request->input('redirection_type') == 4) {
            $keyword = $request->input('keyword');
        } elseif ($request->input('redirection_type') == 2) {
            $keyword = "fk_brand_id=" . $request->input('brand_id');
        } else {
            $keyword = '';
        }

        $update_arr = [
            'title' => $request->input('title'),
            'fk_homepage_id' => $homepage_data->fk_homepage_id,
            'redirection_type' => $request->input('redirection_type'),
            'keyword' => $keyword
        ];
        if ($request->hasFile('image')) {
            $path = "/images/banner_images/";
            $check = $this->uploadFile($request, 'image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($homepage_data->image != '') {
                    $destinationPath = public_path("images/banner_images/");
                    if (!empty($homepage_data->getImage) && file_exists($destinationPath . $homepage_data->getImage->file_name)) {
                        unlink($destinationPath . $homepage_data->getImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $homepage_data->image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('image2')) {
            $path = "/images/banner_images/";
            $check = $this->uploadFile($request, 'image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($homepage_data->image2 != '') {
                    $destinationPath = public_path("images/banner_images/");
                    if (!empty($homepage_data->getImage2) && file_exists($destinationPath . $homepage_data->getImage2->file_name)) {
                        unlink($destinationPath . $homepage_data->getImage2->file_name);
                    }
                    $returnArr = $this->updateFile($req, $homepage_data->image2);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['image2'] = $returnArr->id;
            endif;
        }

        $create = Homepagedata::find($id)->update($update_arr);
        if ($create) {
            return redirect('admin/app_homepage_detail/' . base64url_encode($homepage_data->fk_homepage_id))->with('success', 'Updated successfully');
        }
        return back()->withInput()->with('error', 'Something went wrong');
    }

    protected function app_homepage_add_remove_item(Request $request)
    {
        $exist = Homepagedata::where(['fk_homepage_id' => $request->input('homepage_id'), 'fk_product_id' => $request->input('product_id')])->first();
        if ($exist) {
            Homepagedata::find($exist->id)->delete();
            $message = "Product removed from this section";
        } else {
            Homepagedata::create([
                'fk_homepage_id' => $request->input('homepage_id'),
                'fk_product_id' => $request->input('product_id')
            ]);
            $message = "Product added to this section";
        }
        return response()->json([
            'status_code' => 200,
            'message' => $message
        ]);
    }

    protected function app_homepage_remove_data(Request $request)
    {
        $homepage_data = Homepagedata::find($request->input('id'));

        $delete = $homepage_data ? Homepagedata::destroy($request->input('id')) : false;
        if ($delete) {
            return response()->json([
                'status_code' => 200,
                'message' => 'Product removed from this section'
            ]);
        }
        return response()->json([
            'status_code' => 105,
            'message' => 'Something went wrong'
        ]);
    }

    protected function app_homepage_store_data(Request $request)
    {
        $homepage = Homepage::find($request->input('homepage_id'));

        $totalItem = Homepagedata::where(['fk_homepage_id' => $request->input('homepage_id')])->count();

        if ($homepage->ui_type == 1 && $homepage->banner_type == 3 && $totalItem >= 2) {
            return back()->withInput()->with('error', "You can't add more than 2 banner for this section");
        }
        if ($homepage->ui_type == 1 && $homepage->banner_type == 4 && $totalItem >= 1) {
            return back()->withInput()->with('error', "You can't add more than 1 banner for this section");
        }

        if ($request->input('redirection_type') == 1) {
            $keyword = $request->input('keyword');
        } elseif ($request->input('redirection_type') == 2) {
            $keyword = "fk_brand_id=" . $request->input('brand_id');
        } else {
            $keyword = '';
        }

        $insert_arr = [
            'title' => $request->input('title'),
            'fk_homepage_id' => $request->input('homepage_id'),
            'redirection_type' => $request->input('redirection_type'),
            'keyword' => $keyword
        ];
        if ($request->hasFile('image')) {
            $path = "/images/banner_images/";
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
        if ($request->hasFile('image2')) {
            $path = "/images/banner_images/";
            $check = $this->uploadFile($request, 'image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['image2'] = $returnArr->id;
            endif;
        }


        $create = Homepagedata::create($insert_arr);
        if ($create) {
            return redirect('admin/app_homepage')->with('success', 'Added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding');
    }

    protected function app_homepage_add_banner_products(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $homepagedata = Homepagedata::find($id);

        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('stock', '=', 1)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $products->appends(['filter' => $filter]);

        $brands = Brand::where('deleted', '=', 0)->orderBy('brand_name_en', 'asc')->get();

        return view('admin.products.app_homepage_add_banner_products', [
            'products' => $products,
            'filter' => $filter,
            'id' => $id,
            'homepagedata' => $homepagedata,
            'brands' => $brands
        ]);
    }

    protected function app_homepage_banner_add_remove_item(Request $request)
    {
        $exist = HomepageBannerProduct::where(['fk_homepage_data_id' => $request->input('homepage_id'), 'fk_product_id' => $request->input('product_id')])->first();
        if ($exist) {
            HomepageBannerProduct::find($exist->id)->delete();
            $message = "Product removed from this section";
        } else {
            HomepageBannerProduct::create([
                'fk_homepage_data_id' => $request->input('homepage_id'),
                'fk_product_id' => $request->input('product_id')
            ]);
            $message = "Product added to this section";
        }
        return response()->json([
            'status_code' => 200,
            'message' => $message
        ]);
    }

    protected function images(Request $request)
    {
        $images = \App\Model\File::where('file_path', 'like', '%product_images%')
            ->orderBy('id', 'desc')
            ->paginate(100);

        return view('admin.products.images', ['images' => $images]);
    }

    protected function upload_images(Request $request)
    {
        return view('admin.products.upload_images');
    }

    protected function store_multiple_images(Request $request)
    {
        if ($request->hasFile('product_images')) {
            $path = "/images/product_images/";
            $check = $this->uploadMultipleFile($request, 'product_images', $path);

            foreach ($check as $key => $value) {
                $nameArray = explode('.', $value);
                $ext = end($nameArray);
                $req = [
                    'file_path' => $path,
                    'file_name' => $value,
                    'file_ext' => $ext
                ];
                $returnArr = $this->insertFile($req);
            }
            return redirect('admin/products/images')->with('success', 'Product images uploaded!');
        }
        return back()->withInput()->with('error', 'Error while uploading images');
    }

    protected function create_multiple()
    {
        return view('admin.products.create_multiple');
    }

    protected function bulk_upload(Request $request)
    {
        $path = "/product_files/";
        $file = $this->uploadFile($request, 'product_csv', $path);

        $products = csvToArray(public_path('/product_files/') . $file);

        if ($products) {

            foreach ($products as $key => $value) {

                // Get category
                $fk_category_id = (int)trim($value[2]);
                $fk_sub_category_id = (int)trim($value[3]);
                $fk_brand_id = (int)trim($value[4]);
                $category = Category::find($fk_category_id );
                $subcategory = Category::where(['id' => $fk_sub_category_id, 'parent_id' => $fk_category_id ])->first();
                $brand = Brand::find($fk_brand_id);

                // Company ID
                $fk_company_id = (int)trim($value[11]);

                // Get prices
                $store1_distributor_price = (double)trim($value[12]);
                $store2_distributor_price = (double)trim($value[13]);
                $store3_distributor_price = (double)trim($value[14]);
                $store4_distributor_price = (double)trim($value[15]);
                $store5_distributor_price = (double)trim($value[16]);
                $store6_distributor_price = (double)trim($value[17]);
                $store7_distributor_price = (double)trim($value[18]);
                $store8_distributor_price = (double)trim($value[19]);
                $store9_distributor_price = (double)trim($value[20]);
                $store10_distributor_price = (double)trim($value[21]);

                // Get stocks
                $store1 = (int)trim($value[22]);
                $store2 = (int)trim($value[23]);
                $store3 = (int)trim($value[24]);
                $store4 = (int)trim($value[25]);
                $store5 = (int)trim($value[26]);
                $store6 = (int)trim($value[27]);
                $store7 = (int)trim($value[28]);
                $store8 = (int)trim($value[29]);
                $store9 = (int)trim($value[30]);
                $store10 = (int)trim($value[31]);
                
                // Min scale and max scale
                $min_scale = (double)trim($value[32]);
                $max_scale = (double)trim($value[33]);

                // Tags 
                $_tags = $value[9];
                // if ($value[9] != '') {
                //     $_tags = explode(',', $value[9]);
                //     $_tags = json_encode($_tags, JSON_UNESCAPED_UNICODE);
                // }

                $insert_arr = [
                    'parent_id' => 0,
                    'itemcode' => trim($value[0]),
                    'barcode' => trim($value[1]),
                    'fk_category_id' => $fk_category_id ,
                    'fk_sub_category_id' => $fk_sub_category_id,
                    'fk_brand_id' => $fk_brand_id,
                    'distributor_id' => '',
                    'product_name_en' => $value[5],
                    'product_name_ar' => $value[6],
                    'product_image' => NULL,
                    'product_image_url' => NULL,
                    'allow_margin' => 1,
                    'margin' => 0,
                    'price' => 0.00,
                    'distributor_price' => 0,
                    'store1_distributor_price' => $store1_distributor_price,
                    'store2_distributor_price' => $store2_distributor_price,
                    'store3_distributor_price' => $store3_distributor_price,
                    'store4_distributor_price' => $store4_distributor_price,
                    'store5_distributor_price' => $store5_distributor_price,
                    'store6_distributor_price' => $store6_distributor_price,
                    'store7_distributor_price' => $store7_distributor_price,
                    'store8_distributor_price' => $store8_distributor_price,
                    'store9_distributor_price' => $store9_distributor_price,
                    'store10_distributor_price' => $store10_distributor_price,
                    'store1' => $store1,
                    'store2' => $store2,
                    'store3' => $store3,
                    'store4' => $store4,
                    'store5' => $store5,
                    'store6' => $store6,
                    'store7' => $store7,
                    'store8' => $store8,
                    'store9' => $store9,
                    'store10' => $store10,
                    'unit' => $value[8],
                    'min_scale' => $min_scale,
                    'max_scale' => $max_scale,
                    'brand_name' => $brand ? $brand->brand_name_en : '',
                    'brand_name_ar' => $brand ? $brand->brand_name_ar : '',
                    'category_name' => $category ? $category->category_name_en : '',
                    'category_name_ar' => $category ? $category->category_name_ar : '',
                    'sub_category_name' => $subcategory ? $subcategory->category_name_en : '',
                    'sub_category_name_ar' => $subcategory ? $subcategory->category_name_ar : '',
                    '_tags' => $_tags??'',
                    'tags_ar' => $value[10],
                    'fk_company_id' => $fk_company_id,
                    'deleted' => 0,
                    'stock' => 1
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
                        $product_images_path = str_replace('\\', '/', storage_path("app/public/images/product_images_2/"));
                        $product_images_url_base = "storage/images/product_images_2/"; 
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

                // Calculate the store prices
                if ($value[12] && $value[12] != 0) {
                    $price = calculatePriceFromFormula(trim($value[12]));
                    $insert_arr['store1_price'] = $price[0];
                }
                if ($value[13] && $value[13] != 0) {
                    $price = calculatePriceFromFormula(trim($value[13]));
                    $insert_arr['store2_price'] = $price[0];
                }
                if ($value[14] && $value[14] != 0) {
                    $price = calculatePriceFromFormula(trim($value[14]));
                    $insert_arr['store3_price'] = $price[0];
                }
                if ($value[15] && $value[15] != 0) {
                    $price = calculatePriceFromFormula(trim($value[15]));
                    $insert_arr['store4_price'] = $price[0];
                }
                if ($value[16] && $value[16] != 0) {
                    $price = calculatePriceFromFormula(trim($value[16]));
                    $insert_arr['store5_price'] = $price[0];
                }
                if ($value[17] && $value[17] != 0) {
                    $price = calculatePriceFromFormula(trim($value[17]));
                    $insert_arr['store6_price'] = $price[0];
                }
                if ($value[18] && $value[18] != 0) {
                    $price = calculatePriceFromFormula(trim($value[18]));
                    $insert_arr['store7_price'] = $price[0];
                }
                if ($value[19] && $value[19] != 0) {
                    $price = calculatePriceFromFormula(trim($value[19]));
                    $insert_arr['store8_price'] = $price[0];
                }
                if ($value[20] && $value[20] != 0) {
                    $price = calculatePriceFromFormula(trim($value[20]));
                    $insert_arr['store9_price'] = $price[0];
                }
                if ($value[21] && $value[21] != 0) {
                    $price = calculatePriceFromFormula(trim($value[21]));
                    $insert_arr['store10_price'] = $price[0];
                }

                $exist = Product::where(['itemcode'=>$value[0], 'barcode'=>$value[1], 'fk_company_id'=>$value[11]])->first();
                if ($exist) {
                    Product::find($exist->id)->update($insert_arr);
                    \Log::info('Product updated itemcode: '.$value[0].' barcode: '.$value[1]);
                } else {
                    $create = Product::create($insert_arr);
                    if ($create) {
                        \Log::info('Product created (product ID: '.$create->id.') itemcode: '.$value[0].' barcode: '.$value[1]);
                    } else {
                        \Log::info('Product not created itemcode: '.$value[0].' barcode: '.$value[1]);
                    }
                }
                
            }

            return redirect('admin/products')->with('success', 'Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    protected function stock_update_new($id = null, $batch_id= null)
    {
        $batchId = \App\Model\AdminSetting::where('key', '=', 'batchId')->first();
        $completed_percent = 0;

        $endpointURL = url('admin/batch') . '/' . $batchId->value;

        $getFields = [];

        //Call endpoint
        $response = callGetAPI($endpointURL, $getFields);
        if ($response && $response->totalJobs > 0) {
            $completed_percent = (($response->processedJobs + $response->failedJobs) / $response->totalJobs) *
                100;
        } else {
            $completed_percent = 0;
        }

        return view('admin.products.stock_update_new', [
            'completed_percent' => $completed_percent,
            'batchId' => $batchId ? $batchId->value : 0,
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

        $request->validate([        
            'store_id' => 'required',
        ]);
        
        $store_id = $request->input('store_id');

        $store = Store::find($store_id);
        if ($store) {
            $store_no = get_store_no($store->name);
            $company_id = $store->company_id;
        } else {
            return redirect('admin/products/stock-update/'.$store_id)->with('success', 'Store is not found!');
        }

        $file = file($request->file->getRealPath());

        // // Read csv
        // if (($handle = fopen($request->file->getRealPath(), "r")) === FALSE)
        //     throw new Exception("Couldn't open csv");
        // $data_str = "";

        // // get file all strin in data
        // while (!feof($handle)) {
        //     $data_str .= fgets($handle, 5000);
        // }

        // // convert encoding
        // $data_str = mb_convert_encoding($data_str, 'UTF-8', 'ISO-8859-1');

        // // str_getcsv
        // $array_data = str_getcsv($data_str);

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
        })->name('Stock_update')->dispatch();

        foreach ($parts as $index => $part) {
            
            // $part = mb_convert_encoding($part, 'UTF8', 'UTF-16LE');   // Convert the file to UTF8
            // $part = mb_convert_encoding($part, "UTF-8", "auto");

            $part = array_map('utf8_encode', $part);
            $data = array_map('str_getcsv', $part);

            // Store the csv in the path
            $stock_files_path = str_replace('\\', '/', storage_path("app/public/stock_files/"));
            $stock_files_url_base = "storage/stock_files/"; 
            $filePath = $stock_files_path.'myCSVFile-'.$index.'.csv';
            $fp = fopen($filePath, 'w+');
            fputcsv($fp, $part);
            fclose($fp);
            
            // $filePath = $stock_files_path.'myCSVFile-txt-'.$index.'.csv';
            // $fp = fopen($filePath, 'w+');
            // fputcsv($fp, $data);
            // fclose($fp);

            // $myfile = $stock_files_path.'myCSVFile-'.$index.'.txt';
            // $txt = implode(',', $data);
            // fwrite($myfile, "\n". $txt);
            // fclose($myfile);
            
            // $batch->add(new ProcessStockUpdateFromCsv(json_encode($data), $index, $store_no, $company_id));
            $batch->add(new ProcessStockUpdateFromCsvToDb(json_encode($data), $index, $store_no, $company_id, $batch->id));
        }
        // return $batch;
        \App\Model\AdminSetting::where('key', '=', 'batchId')->update([
            'value' => $batch->id
        ]);

        return redirect('admin/products/stock-update/'.$store_id.'/'.$batch->id)->with('success', 'Stock update started');

    }

    protected function stock_update_one_by_one($id = null, $batch_id = null)
    {
        $batchId = \App\Model\AdminSetting::where('key', '=', 'batchId')->first();

        $all_stocks = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id])->get();
        $all_stocks_count = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id])->count();
        $all_stocks_checked = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'checked'=>1])->count();
        $all_stocks_updated = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'updated'=>1])->count();
        $all_stocks_added_new_product = \App\Model\ProductStockFromCsv::where(['batch_id'=>$batch_id, 'added_new_product'=>1])->count();

        return view('admin.products.stock_update_all_stocks', [
            'batchId' => $batchId->value,
            'all_stocks' => $all_stocks,
            'counts' => array(
                'all_stocks_count' => $all_stocks_count,
                'all_stocks_checked' => $all_stocks_checked,
                'all_stocks_updated' => $all_stocks_updated,
                'all_stocks_added_new_product' => $all_stocks_added_new_product
            )
        ]);
    }

    protected function post_stock_update_one_by_one_bulk(Request $request)
    {
        
        \Log::info('Calling stock '.$request->id.' ');

        $stpe1_batch_id = $request->id;
        $stock_per_set = 20;
        $stocks = \App\Model\ProductStockFromCsv::where([
            'batch_id'=>$request->id
            // 'checked'=>0
            ])->paginate($stock_per_set);
        // dd($stocks->lastPage());

        $batch = Bus::batch([])->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) use ($stpe1_batch_id) {
            // The batch has finished executing...
        })->name('Stock_update_step2')->dispatch();

        if ($stocks) {
            for ($i=1; $i <= $stocks->lastPage(); $i++) { 
                $batch->add(new ProcessStockUpdateFromCsvToDb_Step2($i, $stpe1_batch_id, $stock_per_set));
            }
        }
        \Log::info('ProcessStockUpdateFromCsvToDb_Step2 '.$batch->id.' ');

        return response()->json([
            'message'=>'The stock update started in the server',
            'valid'=>true,
            'error'=>false,
        ]);

    }
        
    protected function post_stock_update_one_by_one_bulk_truncated(Request $request)
    {

        \Log::info('Calling stock '.$request->id.' ');
        dispatch(new ProcessStockUpdateFromCsvToDb_Step2_Truncated($request->id));
        return response()->json([
            'message'=>'The stock update started in the server',
            'valid'=>true,
            'error'=>false,
        ]);

    }
        
    protected function post_stock_update_one_by_one(Request $request)
    {
        
        \Log::info('Updating stock '.$request->id.' ');

        $stock = \App\Model\ProductStockFromCsv::find($request->id);

        $valid = true;
        $error = false;
        $checked = 1;
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

        $itemCodeExist = \App\Model\Product::whereIn('itemcode', [$itemcode, $itemcode_without_0])
        ->where(['fk_company_id' => $stock->company_id, 'deleted' => 0])
        ->first();

        \Log::info('Updated store '.$stock->store_no.' with the itemcode '.$stock->itemcode.' with the fk_company_id '.$stock->company_id);

        if ($itemCodeExist) {
            $itemCodeExist->update(['itemcode'=>$itemcode_without_0]);
            \Log::info('itemCodeExist found '.$stock->itemcode);

            $barCodeExist = \App\Model\Product::where('itemcode','=', $itemcode_without_0)
            ->whereIn('barcode', [$barcode, $barcode_without_0])
            ->where(['fk_company_id' => $stock->company_id])
            ->first();
            
            if ($barCodeExist) {
                \Log::info('barCodeExist found '.$stock->itemcode.' - '.$stock->barcode);
                $distributor_price_key = 'store' . $stock->store_no . '_distributor_price';
                $arr = [
                    'store' . $stock->store_no => !empty(trim($stock->stock)) ? (int) trim($stock->stock) : 0,
                    'store' . $stock->store_no . '_distributor_price' => (!empty($stock->rsp)) ? trim($stock->rsp) : $barCodeExist->$distributor_price_key,
                ];
                if (!empty($stock->rsp)) {
                    $price = calculatePriceFromFormula(trim($stock->rsp));
                    $arr['store' . $stock->store_no . '_price'] = $price[0];
                }
                $update_row = \App\Model\Product::find($barCodeExist->id)->update($arr);
                if ($update_row) {
                    $updated = 1;
                } else {
                    \Log::info('Bulk Stock Update From Local: Updating stock failed for the product ID: '.$barCodeExist->id);
                }
                \Log::info('barCodeExist found '.$stock->itemcode.' - '.$stock->barcode.' - Updated '.'store' . $stock->store_no.' = '.trim($stock->stock).', store' . $stock->store_no.'_distributor_price = '.trim($stock->rsp).', store' . $stock->store_no.'_price = '.$price[0]);
            
            } else {
            
                \Log::info('barCodeExist not found '.$stock->itemcode.' - '.$stock->barcode);
                if (!empty($itemCodeExist->barcode)) {
                    \App\Model\Product::find($itemCodeExist->id)->update(['is_stock_update' => 1]);
                }

                $file = \App\Model\File::find($itemCodeExist->product_image);
                if ($file) {
                    $create = \App\Model\File::create([
                        'file_path' => $file->file_path,
                        'file_name' => $file->file_name,
                        'file_ext' => $file->file_ext
                    ]);
                    $product_image = $create->id;
                } else {
                    $product_image = $itemCodeExist->product_image;
                }

                $distributor_price_key = 'store' . $stock->store_no . '_distributor_price';
                $insertArr = [
                    'deleted' => 1,
                    'stock' => 0,
                    'parent_id' => 0,
                    'itemcode' => $itemCodeExist->itemcode,
                    'barcode' => $stock->barcode,
                    'product_name_en' => $itemCodeExist->product_name_en,
                    'product_name_ar' => $itemCodeExist->product_name_ar,
                    'product_image' => $product_image,
                    'product_image_url' => $itemCodeExist->product_image_url,
                    'unit' => $stock->packing,
                    'store' . $stock->store_no . '_distributor_price' => !empty($stock->rsp) ? trim($stock->rsp) : $itemCodeExist->$distributor_price_key,
                    'distributor_id' => $itemCodeExist->distributor_id,
                    'store' . $stock->store_no => !empty($stock->stock) ? trim($stock->stock) : 0,
                    'fk_category_id' => $itemCodeExist->fk_category_id,
                    'category_name' => $itemCodeExist->category_name,
                    'category_name_ar' => $itemCodeExist->category_name_ar,
                    'fk_sub_category_id' => $itemCodeExist->fk_sub_category_id,
                    'sub_category_name' => $itemCodeExist->sub_category_name,
                    'sub_category_name_ar' => $itemCodeExist->sub_category_name_ar,
                    'fk_brand_id' => $itemCodeExist->fk_brand_id,
                    'brand_name' => $itemCodeExist->brand_name,
                    'brand_name_ar' => $itemCodeExist->brand_name_ar,
                    '_tags' => $itemCodeExist->_tags,
                    'tags_ar' => $itemCodeExist->tags_ar,
                    'is_stock_update' => 1,
                    'fk_company_id' => $stock->company_id
                ];

                if (!empty($stock->rsp) && is_numeric(trim($stock->rsp))) {
                    $price = calculatePriceFromFormula(trim($stock->rsp));
                    $insertArr['store' . $stock->store_no . '_price'] = $price[0];
                    $insertArr['margin'] = $price[1];
                } else {
                    $insertArr['store' . $stock->store_no . '_price'] = $itemCodeExist->product_price;
                    $insertArr['margin'] = $itemCodeExist->margin;
                }

                $added_new_product_row = \App\Model\Product::create($insertArr);
                if ($added_new_product_row) {
                    $added_new_product = 1;
                } else {
                    \Log::info('Bulk Stock Update From Local: Adding new product failed for the product ID: '.$barCodeExist->id);
                }

            }
        }

        \App\Model\ProductStockFromCsv::find($request->id)->update([
            'checked' => $checked, 
            'updated' => $updated, 
            'added_new_product' => $added_new_product 
        ]);

        return response()->json([
            'message'=>'completed',
            'updated' => $updated,
            'added_new_product' => $added_new_product,
            'valid'=>$valid,
            'error'=>$error,
        ]);
    }
    
    protected function show($id = null)
    {
        $id = base64url_decode($id);

        $product = Product::find($id);

        if ($product) {
            $product['company_name'] = !empty($product->getCompany) ? $product->getCompany->name : 'N/A';
            $product['category_name'] = !empty($product->getProductCategory) ? $product->getProductCategory->category_name_en : 'N/A';
            $product['sub_category_name'] = !empty($product->getProductSubCategory) ? $product->getProductSubCategory->category_name_en : 'N/A';
            $product['brand_name'] = (!empty($product->getProductBrand) ? $product->getProductBrand->brand_name_en : 'N/A');
            $product['product_image'] = !empty($product->getProductImage) ? env("APP_URL", "https://jeeb.tech/").$product->getProductImage->file_path . $product->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg');
            $group_products = Product::where('parent_id', $id)->orderBy('id', 'desc')->get();
            $product['group_products'] = $group_products;
            return view('admin.products.show', ['product' => $product]);
        } else {
            return redirect('admin/products')->with('error', 'Product not found');
        }
    }

    protected function create_subproduct(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $product = Product::find($id);

        if ($product) {
            $categories = Category::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')->get();

            $sub_categories = Category::where(['parent_id' => $product->fk_category_id, 'deleted' => 0])->orderBy('category_name_en', 'asc')->get();
            $brands = Brand::where(['deleted' => 0])->orderBy('brand_name_en', 'asc')->get();

            return view('admin.products.create_subproduct', ['product' => $product, 'categories' => $categories, 'sub_categories' => $sub_categories, 'brands' => $brands]);
        } else {
            return redirect('admin/products')->with('error', 'Product not found');
        }
    }

    protected function store_subproduct(Request $request)
    {
        $id = $request->input('parent_id');
        $product = Product::find($id);
        $insert_arr = [
            'parent_id' => $request->input('parent_id'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_sub_category_id' => $request->input('fk_sub_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'product_name_en' => $request->input('product_name_en'),
            'product_name_ar' => $request->input('product_name_ar'),
            'unit' => $request->input('quantity') . ' ' . $request->input('unit'),
            'product_price' => $request->input('price'),
            'margin' => $request->input('margin'),
            'status' => 1,
            'distributor_id' => $request->input('distributor_id'),
            'stock' => $request->input('stock'),
            'description' => $request->input('description'),
            'distributor_price' => $request->input('distributor_price') ?? 0
        ];
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

                $returnArr = $this->insertFile($req);
                $insert_arr['product_image'] = $returnArr->id;
            endif;
        } else {
            $insert_arr['product_image'] = $product->product_image;
        }
        $create = Product::create($insert_arr);
        if ($create) {
            $updateArr = ['barcode' => 'SKU' . $create->id];
            $update = Product::find($create->id)->update($updateArr);

            $product_img_arr = [];
            if ($request->hasFile('product_image1')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'product_image1', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $product_img_arr['product_image1'] = $returnArr->id;
                endif;
            } else {
                if ($product->getProductMoreImages) {
                    if ($product->getProductMoreImages->getImage1) {
                        $product_img_arr['product_image1'] = $product->getProductMoreImages->getImage1->id;
                    }
                }
            }
            if ($request->hasFile('product_image2')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'product_image2', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $product_img_arr['product_image2'] = $returnArr->id;
                endif;
            } else {
                if ($product->getProductMoreImages) {
                    if ($product->getProductMoreImages->getImage2) {
                        $product_img_arr['product_image2'] = $product->getProductMoreImages->getImage2->id;
                    }
                }
            }
            if ($request->hasFile('product_image3')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'product_image3', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $product_img_arr['product_image3'] = $returnArr->id;
                endif;
            } else {
                if ($product->getProductMoreImages) {
                    if ($product->getProductMoreImages->getImage3) {
                        $product_img_arr['product_image3'] = $product->getProductMoreImages->getImage3->id;
                    }
                }
            }
            if ($request->hasFile('product_image4')) {
                $path = "/images/product_images/";
                $check = $this->uploadFile($request, 'product_image4', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $product_img_arr['product_image4'] = $returnArr->id;
                endif;
            } else {
                if ($product->getProductMoreImages) {
                    if ($product->getProductMoreImages->getImage4) {
                        $product_img_arr['product_image4'] = $product->getProductMoreImages->getImage4->id;
                    }
                }
            }
            if ($product_img_arr) {
                $product_img_arr['fk_product_id'] = $create->id;
                ProductImage::create($product_img_arr);
            }
            return redirect('admin/products/show/' . base64url_encode($id))->with('success', 'Product added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    protected function change_product_status(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = Product::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    protected function get_category_brand(Request $request)
    {
        $category_id = $request->input('fk_category_id');
        $category_brand = Brand::where('fk_category_id', '=', $category_id)
            ->where('deleted', '=', 0)
            ->get();

        $brand_list = "";
        if (!empty($category_brand)) {
            foreach ($category_brand as $brand) {
                $string = '<option value="' . $brand->id . '">' . $brand->brand_name_en . '</option>';
                $brand_list = $brand_list . $string;
            }
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Brand List', 'data' => $brand_list]);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'No brands found']);
        }
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

    //Ajax
    protected function delete_product(Request $request)
    {
        $update = Product::find($request->input('product_id'))->update(['deleted' => 1]);
        if ($update) {
            return response()->json(['error' => false, 'status_code' => 200, 'message' => 'Product deleted successfully']);
        } else {
            return response()->json(['error' => true, 'status_code' => 404, 'message' => 'Some error found']);
        }
    }

    //Ajax
    protected function set_home_product(Request $request)
    {
        $value = $request->input('value');
        $is_switched_on = $request->input('is_switched_on');
        $id = $request->input('id');

        if ($value == 'offered') {
            $updateArr['offered'] = $is_switched_on ? 1 : 0;
            $updateArr['is_home_screen'] = $is_switched_on ? 1 : 0;
        } elseif ($value == 'frequently_bought') {
            $updateArr['frequently_bought_together'] = $is_switched_on ? 1 : 0;
        }
        // elseif ($value == 'essential') {
        //     $updateArr['essential'] = $is_switched_on ? 1 : 0;
        //     $updateArr['is_home_screen'] = $is_switched_on ? 1 : 0;
        // } elseif ($value == 'handpicked') {
        //     $updateArr['handpicked'] = $is_switched_on ? 1 : 0;
        //     $updateArr['is_home_screen'] = $is_switched_on ? 1 : 0;
        // }

        $update = Product::find($id)->update($updateArr);

        if ($update) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success"
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 105,
                'message' => "Some error found"
            ]);
        }
    }

    protected function remove_from_home_popular($id = null)
    {
        $id = base64url_decode($id);
        $popular = Product::find($id);
        if ($popular) {
            Product::find($id)->update(['is_home_screen' => 0, 'popular' => 0]);
            return redirect('admin/popular_products')->with('success', 'Product removed from home successfully');
        } else {
            return back()->withInput()->with('error', 'Error while removing product');
        }
    }

    protected function remove_from_home_essential($id = null)
    {
        $id = base64url_decode($id);
        $essential = Product::find($id);
        if ($essential) {
            Product::find($id)->update(['is_home_screen' => 0, 'essential' => 0]);
            return redirect('admin/essential_products')->with('success', 'Product removed from home successfully');
        } else {
            return back()->withInput()->with('error', 'Error while removing product');
        }
    }

    protected function my_all_products(Request $request)
    {
        $products = Product::where(['deleted' => 0])
            ->orderBy('product_name_en', 'asc')
            ->get();

        $priceFormula = \App\Model\PriceFormula::get();

        $productArr = [];
        foreach ($products as $key => $value) {
            //            pp($value->distributor_price);
            $res = \App\Model\PriceFormula::whereRaw("x1 < $value->distributor_price AND x2 >= $value->distributor_price")
                ->first();

            if ($res) {
                $x1 = $res->x1;
                $x2 = $res->x2;
                $x3 = $res->x3;
                $x4 = $res->x4;

                if ($x4 == '0') { //condition in which distributor price is more than 200
                    $pricePercentage = $x3;
                } else {
                    $numerator = ($value->distributor_price - $x1) * $res->x3x4;

                    $pricePercentage = ($res->x3x4 - (($numerator) / $res->x2x1)) + $x4;
                }
            } else {
                $pricePercentage = 0;
            }

            $addingPrice = ($pricePercentage * $value->distributor_price) / 100;

            $sellingPrice = $value->distributor_price + $addingPrice;

            $productArr[$key] = [
                'id' => $value->id,
                'product_image' => $value->getProductImage ? asset($value->getProductImage->file_path) . '/' . $value->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg'),
                'product_name' => $value->product_name_en,
                'quantity' => $value->unit,
                'distributor_price' => number_format($value->distributor_price, 2, '.', ''),
                'category_name' => $value->getProductCategory ? $value->getProductCategory->category_name_en : 'N/A',
                'sub_category_name' => $value->getProductSubCategory ? $value->getProductSubCategory->category_name_en : 'N/A',
                'brand_name' => $value->getProductBrand ? $value->getProductBrand->brand_name_en : 'N/A',
                'selling_price' => number_format($sellingPrice, 2, '.', '')
            ];
        }

        return view('admin.my_all_products', [
            'products' => $productArr
        ]);
    }

    protected function all_products(Request $request)
    {
        $products = Product::where(['deleted' => 0])
            ->orderBy('product_name_en', 'asc')
            ->get();

        $productArr = [];
        foreach ($products as $key => $value) {

            $productArr[$key] = [
                'id' => $value->id,
                'product_image' => $value->getProductImage ? asset($value->getProductImage->file_path) . '/' . $value->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg'),
                'product_name' => $value->product_name_en,
                'quantity' => $value->unit,
                'distributor_price' => $value->distributor_price,
                'category_name' => $value->getProductCategory ? $value->getProductCategory->category_name_en : 'N/A',
                'sub_category_name' => $value->getProductSubCategory ? $value->getProductSubCategory->category_name_en : 'N/A',
                'brand_name' => $value->getProductBrand ? $value->getProductBrand->brand_name_en : 'N/A',
            ];
        }

        return view('admin.all_products', [
            'products' => $productArr
        ]);
    }

    protected function get_sub_classification(Request $request)
    {
        $id = $request->input('id');
        $sub_classifications = \App\Model\Classification::where('parent_id', '=', $id)
            ->where('deleted', '=', 0)
            ->get();

        if ($sub_classifications->count()) {
            $resultArr = [];
            foreach ($sub_classifications as $key => $value) {
                $resultArr[$key] = $value;
            }
            return response()->json([
                'status' => true,
                'error_code' => 200,
                'data' => $resultArr
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error_code' => 201,
                'message' => 'No sub category found'
            ]);
        }
    }

    protected function add_classified_product(Request $request)
    {
        $classifiedPro = \App\Model\ClassifiedProduct::where([
            'fk_classification_id' => $request->input('fk_classification_id'),
            'fk_sub_classification_id' => $request->input('fk_sub_classification_id'),
            'fk_product_id' => $request->input('fk_product_id'),
        ])->first();
        if ($classifiedPro) {
            $update = \App\Model\ClassifiedProduct::find($classifiedPro->id)->update([
                'fk_classification_id' => $request->input('fk_classification_id'),
                'fk_sub_classification_id' => $request->input('fk_sub_classification_id'),
                'fk_product_id' => $request->input('fk_product_id'),
            ]);
        } else {
            $update = \App\Model\ClassifiedProduct::create([
                'fk_classification_id' => $request->input('fk_classification_id'),
                'fk_sub_classification_id' => $request->input('fk_sub_classification_id'),
                'fk_product_id' => $request->input('fk_product_id'),
            ]);
        }

        if ($update) {
            return response()->json([
                'status' => true,
                'error_code' => 200,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error_code' => 201,
            ]);
        }
    }

    protected function price_formula(Request $request)
    {
        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();

        $price_formula_stores = \App\Model\PriceFormula::leftJoin('stores','stores.id','=','product_price_formula.fk_store_id')
            ->select('product_price_formula.*','stores.name as store_name', 'stores.company_name as store_company_name')
            ->where('fk_store_id','!=', 0)
            ->where('fk_offer_option_id','=', 0)
            ->groupBy('fk_store_id')
            ->orderBy('x1', 'asc')
            ->get();

        $price_formula_subcategories = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
            ->select('product_price_formula.*','categories.category_name_en')
            ->where('fk_subcategory_id','!=', 0)
            ->groupBy('fk_subcategory_id')
            ->orderBy('x1', 'asc')
            ->get();
            
        $price_formula_brands = \App\Model\PriceFormula::leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
            ->select('product_price_formula.*','brands.brand_name_en')
            ->where('fk_brand_id','!=', 0)
            ->groupBy('fk_brand_id')
            ->orderBy('x1', 'asc')
            ->get();
        
        return view('admin.products.price_formula', [
            'price_formula_stores' => $price_formula_stores,
            'price_formula_subcategories' => $price_formula_subcategories,
            'price_formula_brands' => $price_formula_brands,
        ]);
    }

    protected function filter_price_formula(Request $request)
    {
        $store_id = $request->input('store_id') == 'default' ? 0 : $request->input('store_id');
        $result = \App\Model\PriceFormula::where(['fk_store_id' => $store_id, 'fk_subcategory_id' => $request->input('subcategory_id') , 'fk_brand_id' => $request->input('brand_id'),['fk_offer_option_id','=',0] ])->orderBy('x1', 'asc')->get();
        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();

        return view('admin.products.filter_price_formula', [
            'result' => $result,
            'stores' => $stores,
            'sub_categories' => $sub_categories,
            'brands' => $brands,
            'store_id' => $store_id,
            'subcategory_id' => $request->input('subcategory_id'),
            'brand_id' => $request->input('brand_id'),
        ]);
    }

    protected function edit_formula(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $result = \App\Model\PriceFormula::find($id);
        return view('admin.products.edit_formula', ['formula' => $result]);
    }

    protected function update_price_formula(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $exist = \App\Model\PriceFormula::find($id);
        if ($exist) {
            $update = \App\Model\PriceFormula::find($id)->update([
                'x1' => $request->input('x1'),
                'x2' => $request->input('x2'),
                'x3' => $request->input('x3'),
                'x4' => $request->input('x4'),
                'x3x4' => $request->input('x3') - $request->input('x4'),
                'x2x1' => $request->input('x2') - $request->input('x1')
            ]);
            if ($update) {
                return redirect('admin/products/filter_price_formula/?store_id='.$exist->fk_store_id.'&subcategory_id='.$exist->fk_subcategory_id.'&brand_id='.$exist->fk_brand_id)->with('success', 'Add Success !');
            }
        } else {
            return redirect('admin/products')->with('error', 'Product not found');
        }
    }

    protected function create_formula(Request $request, $id, $subcategory, $brand)
    {
        $store = Store::where(['id'=>$id, 'deleted'=>0])->first();
        if (!$store && $id!=0) {
            die("Store is not found!");
        }
        
        return view('admin.products.create_formula', ['store' => $store, 'id' => $id, 'subcategory' => $subcategory, 'brand' => $brand]);
    }

    protected function store_price_formula(Request $request, $id, $subcategory, $brand)
    {
        $update = \App\Model\PriceFormula::create([
            'fk_store_id' => $id,
            'fk_subcategory_id' => $subcategory,
            'fk_brand_id' => $brand,
            'x1' => $request->input('x1'),
            'x2' => $request->input('x2'),
            'x3' => $request->input('x3'),
            'x4' => $request->input('x4'),
            'x3x4' => $request->input('x3') - $request->input('x4'),
            'x2x1' => $request->input('x2') - $request->input('x1')
            
        ]);
        if ($update) {
            return redirect('admin/products/filter_price_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('success', 'Add Success !');
        }
    }

    protected function copy_default_formula(Request $request, $id, $subcategory, $brand)
    {
        $defualt_price_formula = \App\Model\PriceFormula::where(['fk_store_id' => 0, 'fk_subcategory_id' => $subcategory, 'fk_brand_id' => $brand])->get();
        $store_price_formula = \App\Model\PriceFormula::where(['fk_store_id' => $id, 'fk_subcategory_id' => $subcategory, 'fk_brand_id' => $brand])->delete();
        if(count($defualt_price_formula) > 0){
            foreach ($defualt_price_formula as $key => $value) {
                $create = \App\Model\PriceFormula::create([
                    'fk_store_id' => $id,
                    'fk_subcategory_id' => $subcategory,
                    'fk_brand_id' => $brand,
                    'x1' => $value->x1,
                    'x2' => $value->x2,
                    'x3' => $value->x3,
                    'x4' => $value->x4,
                    'x3x4' => $value->x3 - $value->x4,
                    'x2x1' => $value->x2 - $value->x1
                ]);
            }
        }else{
            return redirect('admin/products/filter_price_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('error', 'Default store price formula not found');
        }
        
        if ($create) {
            return redirect('admin/products/filter_price_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('success', 'Add Success !');
        }
    }
    
    protected function delete_store_price_formula(Request $request, $id, $subcategory, $brand)
    {
        $store_price_formula = \App\Model\PriceFormula::where(['fk_store_id' => $id, 'fk_subcategory_id' => $subcategory, 'fk_brand_id' => $brand])->get();
        if(count($store_price_formula) > 0){
            $delete = \App\Model\PriceFormula::where(['fk_store_id' => $id, 'fk_subcategory_id' => $subcategory, 'fk_brand_id' => $brand])->delete();
        }else{
            return redirect('admin/products/filter_price_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('error', 'Store price formula not found');
        }
        
        if ($delete) {
            return redirect('admin/products/filter_price_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('success', 'Delete Success !');
        }
    }

    protected function delete_price_formula($id)
    {   $id = base64url_decode($id);
        $store_price_formula = \App\Model\PriceFormula::find($id);
        if($store_price_formula){
            $delete = $store_price_formula->delete();
        }else{
            return redirect()->back()->with('error', 'Store price formula not found');
        }
        
        if ($delete) {
            return redirect()->back()->with('success', 'Delete Success !');
        }
    }

    protected function apply_formula(Request $request)
    {
        $store_id = $request->input('store_id') == 'default' ? 0 : $request->input('store_id');
        $result = \App\Model\PriceFormula::where('fk_store_id', $store_id) 
                        ->where('fk_subcategory_id', $request->input('subcategory_id'))
                        ->where('fk_brand_id', $request->input('brand_id'))
                        ->where('fk_offer_option_id', '!=',0)
                        ->first();

        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();
        $offer_options = ProductOfferOption::where('deleted','=',0)->get();

        return view('admin.products.apply_formula', [
            'result' => $result,
            'stores' => $stores,
            'sub_categories' => $sub_categories,
            'brands' => $brands,
            'store_id' => $store_id,
            'offer_options' => $offer_options,
            'subcategory_id' => $request->input('subcategory_id'),
            'brand_id' => $request->input('brand_id'),
            'offer_id' => $result->fk_offer_option_id ?? 0
        ]);
    }

    protected function apply_formula_to_all_products(Request $request)
    {
        // Filters
        $store_id = $request->input('store_id');
        $subcategory_id = $request->input('subcategory_id');
        $brand_id = $request->input('brand_id');
        $offer_id = $request->input('offer_id');
        $base_products_update = $request->input('base_products_update');
        $base_products_store_update = $request->input('base_products_store_update');

        // Select all base products
        $perPage = 1000; // Number of items per page
        if ($base_products_store_update=='base_products_store_update') {
            $query = \App\Model\BaseProductStore::where(['deleted'=>0,'product_type'=>'product']); // Your query
        } else {
            $query = \App\Model\BaseProduct::where(['deleted'=>0,'product_type'=>'product']); // Your query
        }

        if ($store_id!=0) {
            $query = $query->where('fk_store_id', $store_id);
        }
        if ($subcategory_id!=0) {
            $query = $query->where('fk_sub_category_id', $subcategory_id);
        }
        if ($brand_id!=0) {
            $query = $query->where('fk_brand_id', $brand_id);
        }
        if ($offer_id!=0) {
            $query = $query->where('fk_offer_option_id', $offer_id);
        }
        
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

            if ($base_products_store_update=='base_products_store_update') {
                // Dispatch the batch job with the array
                UpdateBaseProductStorePrice::dispatch($i,$base_products_arr,$store_id,$subcategory_id,$brand_id,$offer_id);
            } elseif ($base_products_update=='base_products_update') {
                // Dispatch the batch job with the array
                UpdateBaseProductPrice::dispatch($i,$base_products_arr,$store_id,$subcategory_id,$brand_id,$offer_id);
            }
        }
        return redirect('admin/products/apply_formula/?store_id='.$store_id.'&subcategory_id='.$subcategory_id.'&brand_id='.$brand_id.'&offer_id='.$offer_id)->with('success', 'Price update started in the server!');
        
    }

    protected function offer_formula(Request $request)
    {
        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();

        $price_formula_stores = \App\Model\PriceFormula::leftJoin('stores','stores.id','=','product_price_formula.fk_store_id')
            ->select('product_price_formula.*','stores.name as store_name', 'stores.company_name as store_company_name')
            ->where('fk_store_id','!=', 0)
            ->groupBy('fk_store_id')
            ->orderBy('x1', 'asc')
            ->get();

        $price_formula_subcategories = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
            ->select('product_price_formula.*','categories.category_name_en')
            ->where('fk_subcategory_id','!=', 0)
            ->groupBy('fk_subcategory_id')
            ->orderBy('x1', 'asc')
            ->get();
            
        $price_formula_brands = \App\Model\PriceFormula::leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
            ->select('product_price_formula.*','brands.brand_name_en')
            ->where('fk_brand_id','!=', 0)
            ->groupBy('fk_brand_id')
            ->orderBy('x1', 'asc')
            ->get();
        
        return view('admin.products.offer_formula', [
            'price_formula_stores' => $price_formula_stores,
            'price_formula_subcategories' => $price_formula_subcategories,
            'price_formula_brands' => $price_formula_brands,
        ]);
    }

    protected function filter_offer_formula(Request $request)
    {
        $store_id = $request->input('store_id') == 'default' ? 0 : $request->input('store_id');
        $result = \App\Model\PriceFormula::where('fk_store_id', $store_id) 
                        ->where('fk_subcategory_id', $request->input('subcategory_id'))
                        ->where('fk_brand_id', $request->input('brand_id'))
                        ->where('fk_offer_option_id', '!=',0)
                        ->first();

        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();
        $offer_options = ProductOfferOption::where('deleted','=',0)->get();

        return view('admin.products.filter_offer_formula', [
            'result' => $result,
            'stores' => $stores,
            'sub_categories' => $sub_categories,
            'brands' => $brands,
            'store_id' => $store_id,
            'offer_options' => $offer_options,
            'subcategory_id' => $request->input('subcategory_id'),
            'brand_id' => $request->input('brand_id'),
            'offer_id' => $result->fk_offer_option_id ?? 0
        ]);
    }

    protected function store_offer_formula(Request $request, $id, $subcategory, $brand)
    {
        $offer_formula = \App\Model\PriceFormula::where('fk_store_id', $id) 
            ->where('fk_subcategory_id', $subcategory) 
            ->where('fk_brand_id', $brand)
            ->where('fk_offer_option_id','!=',0) 
            ->delete();

        if($request->offer_id){
            $create = \App\Model\PriceFormula::create([
                'fk_store_id' => $id,
                'fk_subcategory_id' => $subcategory,
                'fk_brand_id' => $brand,
                'fk_offer_option_id' => $request->offer_id
                
            ]);
            if ($create) {
                return redirect('admin/products/filter_offer_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('success', 'Add Success !');
            }
        }else{
            return redirect('admin/products/filter_offer_formula/?store_id='.$id.'&subcategory_id='.$subcategory.'&brand_id='.$brand)->with('error', 'Please select an offer');
        }
        
    }

    protected function delete_offer_formula($id)
    {   $id = base64url_decode($id);
        $store_price_formula = \App\Model\PriceFormula::find($id);
        if($store_price_formula){
            $delete = $store_price_formula->delete();
        }else{
            return redirect()->back()->with('error', 'Offer formula not found');
        }
        
        if ($delete) {
            return redirect()->back()->with('success', 'Delete Success !');
        }
    }


    protected function edit_offer_formula(Request $request, $id = null)
    {
        $id = base64url_decode($id);
        $result = \App\Model\PriceFormula::find($id);
        $stores = Store::where('deleted', 0)->get();
        $sub_categories = Category::where('parent_id', '!=', 0)->where('deleted', '=', 0)->orderBy('category_name_en', 'asc')->get();
        $brands = Brand::where('deleted','=',0)->get();
        $offer_options = ProductOfferOption::where('deleted','=',0)->get();

        return view('admin.products.filter_offer_formula', [
            'result' => $result,
            'stores' => $stores,
            'sub_categories' => $sub_categories,
            'brands' => $brands,
            'store_id' => $result->fk_store_id,
            'offer_options' => $offer_options,
            'subcategory_id' => $result->fk_subcategory_id,
            'brand_id' => $result->fk_brand_id,
            'offer_id' => $result->fk_offer_option_id
        ]);
    }

    protected function top_selling_products(Request $request)
    {
        $top_selling_products = \App\Model\OrderProduct::join($this->products_table, 'order_products.fk_product_id', '=', $this->products_table . '.id')
            ->select(
                $this->products_table . '.id AS product_id',
                $this->products_table . '.fk_category_id',
                $this->products_table . '.fk_brand_id',
                $this->products_table . '.product_name_en',
                $this->products_table . '.product_name_ar',
                $this->products_table . '.unit',
                $this->products_table . '.product_image',
                $this->products_table . '.product_price',
                $this->products_table . '.margin'
            )
            ->where($this->products_table . '.parent_id', '=', 0)
            ->where($this->products_table . '.deleted', '=', 0)
            ->where('order_products.deleted', '=', 0)
            ->orderBy('order_products.id', 'desc')
            ->groupBy('order_products.fk_product_id')
            ->get();

        return view('admin.products.top_selling_products', [
            'top_selling_products' => $top_selling_products
        ]);
    }

    protected function null_itemcode_products(Request $request)
    {
        $page = $request->query('page') !== null ? $request->query('page') : 1;

        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('itemcode', '=', NULL)
                ->where('barcode', '=', NULL)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('itemcode', '=', NULL)
                ->where('barcode', '=', NULL)
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $products->appends(['filter' => $filter]);

        return view('admin.products.null_itemcode_products', [
            'products' => $products,
            'filter' => $filter,
            'page' => $page
        ]);
    }

    protected function new_products(Request $request)
    {
        $page = $request->query('page') !== null ? $request->query('page') : 1;

        $filter = $request->query('filter');

        if (!empty($filter)) {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 1)
                ->where('stock', '=', 0)
                ->where('product_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $products = Product::where('parent_id', '=', 0)
                ->where('deleted', '=', 1)
                ->where('stock', '=', 0)
                ->orderBy('id', 'desc')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $products->appends(['filter' => $filter]);

        return view('admin.products.new_products', [
            'products' => $products,
            'filter' => $filter,
            'page' => $page
        ]);
    }

    //Ajax
    protected function update_product_stock(Request $request)
    {
        $stock = $request->input('stock');
        $id = $request->input('id');

        $updateArr = Product::find($id)->update([
            'stock' => $stock,
            'deleted' => 0
        ]);
        if ($updateArr) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success"
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 105,
                'message' => "Some error found"
            ]);
        }
    }

    protected function product_suggestions(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $suggestions = ProductSuggestion::where('product_name', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(50);
        } else {
            $suggestions = ProductSuggestion::sortable(['id' => 'desc'])
                ->paginate(50);
        }
        $suggestions->appends(['filter' => $filter]);

        return view('admin.products.product_suggestions', [
            'suggestions' => $suggestions,
            'filter' => $filter
        ]);
    }

    protected function bulk_upload_single_column(Request $request)
    {
        return view('admin.products.bulk_upload_single_column');
    }

    protected function bulk_upload_single_column_post(Request $request)
    {
        $file = file($request->file->getRealPath());
        $data = array_slice($file, 1);

        $parts = (array_chunk($data, 200));
        // pp($parts);

        $batch = Bus::batch([])->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...           
        // })->name('Barcode Update')->dispatch();
        // })->name('Retaimart Product Barcode Update')->dispatch();
        // })->name('Product Tags Update')->dispatch();
        })->name('Product Arabic Names Update')->dispatch();

        foreach ($parts as $index => $part) {
            $data = array_map('str_getcsv', $part);
            // // Barcode update with product ID
            // $batch->add(new ProcessProductUpload(json_encode($data), $index));
            // Retailmart Barcode update with SKU code
            // $batch->add(new RetaimartProductBarcodeUpload(json_encode($data), $index));
            // // Tags update with product ID
            // $batch->add(new ProcessProductTagsUpload(json_encode($data), $index));
            // // Tags update with product ID
            $batch->add(new ProcessProductArabicNamesUpload(json_encode($data), $index));
        }
        // return $batch;
        \App\Model\AdminSetting::where('key', '=', 'batchId')->update([
            'value' => $batch->id
        ]);

        return redirect('admin/products/bulk_upload_single_column')->with('success', 'Process started');
    }
}
