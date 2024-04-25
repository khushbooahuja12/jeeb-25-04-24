<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;
use Illuminate\Support\Facades\Auth;

class BrandController extends CoreApiController
{
    function __construct()
    {   
        // print_r(empty(isset(Auth::guard('vendor')->user()->store_id))); 
        $user = Auth::guard('vendor')->user();
        if(!$user || empty($user->store_id)){

            return view('vendor.auth.login');
        }

    }

    public function index(Request $request)
    {       

        $filter = $request->query('filter');
        $vendor = Auth::guard('vendor')->user();
        
        if(!$vendor || empty($vendor->store_id)){

            return view('vendor.auth.login');
        }

        $store_id = $vendor->store_id;

        if (!empty($filter)) {
            $brands = Brand::where('deleted', '=', 0)
                ->where('brand_name_en', 'like', '%' . $filter . '%')
                ->where('fk_store_id', '=', $store_id)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        } else {
            $brands = Brand::where('deleted', '=', 0)
                ->where('fk_store_id', '=', $store_id)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }

        $brands->appends(['filter' => $filter]);
        // print_r($brands);die;

        return view('vendor.brands.index', ['brands' => $brands, 'filter' => $filter]);
    }


    public function create(Request $request)
    {
        $categories = Category::where(['parent_id' => 0])->orderBy('id', 'desc')->get();
        return view('vendor.brands.create', ['category' => $categories]);
    }

    
    public function store(Request $request)
    {   
        $vendor = Auth::guard('vendor')->user();
        // print_r($vendor->store_id);die;
        $store_id = $vendor->store_id;
        
        $max_count = Brand::where(['is_home_screen' => 1])->count();
        if ($request->input('is_home_screen') == 1 && $max_count > 10) {
            return back()->withInput()->with('error', 'Max 10 brand allowed for home screen');
        }

        $insert_arr = [
            'brand_name_en' => $request->input('brand_name_en'),
            'brand_name_ar' => $request->input('brand_name_ar'),
            'fk_store_id' => $store_id,
            'is_home_screen' => $request->input('is_home_screen') ?? ""
        ];

        if ($request->hasFile('brand_image')) {
            $path = "/images/brand_images/";
            $check = $this->uploadFile($request, 'brand_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['brand_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('brand_image2')) {
            $path = "/images/brand_images/";
            $check = $this->uploadFile($request, 'brand_image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['brand_image2'] = $returnArr->id;
            endif;
        }
        
        $add = Brand::create($insert_arr);
        if ($add) {
            if ($add->is_home_screen == 1) {
                return redirect('vendor/home_brands')->with('success', 'Brand added successfully');
            } else {
                return redirect('vendor/brands')->with('success', 'Brand added successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $brand = Brand::find($id);
        $categories = Category::where(['parent_id' => 0])->orderBy('id', 'desc')->get();
        return view('vendor.brands.edit', ['brand' => $brand, 'category' => $categories]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $brand = Brand::find($id);

        $update_arr = [
            'brand_name_en' => $request->input('brand_name_en'),
            'brand_name_ar' => $request->input('brand_name_ar')
        ];

        if ($request->hasFile('brand_image')) {
            $path = "/images/brand_images/";
            $check = $this->uploadFile($request, 'brand_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($brand->brand_image != '') {
                    $destinationPath = public_path("images/brand_images/");
                    if (!empty($brand->getBrandImage) && file_exists($destinationPath . $brand->getBrandImage->file_name)) {
                        unlink($destinationPath . $brand->getBrandImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $brand->brand_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['brand_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('brand_image2')) {
            $path = "/images/brand_images/";
            $check = $this->uploadFile($request, 'brand_image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($brand->brand_image2 != '') {
                    $destinationPath = public_path("images/brand_images/");
                    if (!empty($brand->getBrandImage2) && file_exists($destinationPath . $brand->getBrandImage2->file_name)) {
                        unlink($destinationPath . $brand->getBrandImage2->file_name);
                    }
                    $returnArr = $this->updateFile($req, $brand->brand_image2);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['brand_image2'] = $returnArr->id;
            endif;
        }
        $update = Brand::find($id)->update($update_arr);
        if ($update) {
            if ($brand->is_home_screen == 1) {
                return redirect('vendor/home_brands')->with('success', 'Brand updated successfully');
            } else {
                return redirect('vendor/brands')->with('success', 'Brand updated successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while updating brand');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $brand = Brand::find($id);
        if ($brand) {
            $brand_product = \App\Model\Product::where(['fk_brand_id' => $id])->get();

            if ($brand_product->count()) {
                return back()->withInput()->with('error', 'Cant delete');
            } else {
                Brand::find($id)->delete();
                if ($brand->is_home_screen == 1) {
                    return redirect('vendor/home_brands')->with('success', 'Brand deleted successfully');
                } else {
                    return redirect('vendor/brands')->with('success', 'Brand deleted successfully');
                }
            }
        } else {
            return back()->withInput()->with('error', 'Error while deleting brand');
        }
    }

      public function images(Request $request)
    {
        $images = \App\Model\File::where('file_name', 'like', 'brand_images%')
            ->orderBy('id', 'desc')
            ->paginate(100);

        return view('vendor.brands.images', ['images' => $images]);
    }

    public function upload_images(Request $request)
    {
        return view('vendor.brands.upload_images');
    }

    public function store_multiple_images(Request $request)
    {
        if ($request->hasFile('brand_images')) {
            $path = "/images/brand_images/";
            $check = $this->uploadMultipleFile($request, 'brand_images', $path);

            foreach ($check as $key => $value) {
                $nameArray = explode('.', $value);
                $ext = end($nameArray);
                $req = [
                    'file_path' => $path,
                    'file_name' => $value,
                    'file_ext' => $ext
                ];
                $this->insertFile($req);
            }
            return redirect('vendor/brands/images')->with('success', 'Brand images uploaded!');
        }
        return back()->withInput()->with('error', 'Error while uploading images');
    }

   
    public function show($id = null)
    {
        $id = base64url_decode($id);

        $relatedCategories = \App\Model\BrandCategoryMapping::where(['fk_brand_id' => $id])
            ->get();

        $brand = Brand::find($id);
        if ($brand) {
            return view('vendor.brands.show', [
                'brand' => $brand,
                'relatedCategories' => $relatedCategories
            ]);
        }
        return back()->withInput()->with('error', 'Some error found !');
    }


    public function remove_from_home($id = null)
    {
        $id = base64url_decode($id);
        $brand = Brand::find($id);
        if ($brand) {
            Brand::find($id)->update(['is_home_screen' => 0]);
            return redirect('vendor/brands')->with('success', 'Brand removed from home successfully');
        } else {
            return back()->withInput()->with('error', 'Error while removing brand');
        }
    }

    public function add_remove_home(Request $request)
    {
        $is_home_screen = $request->input('is_home_screen');
        $id = $request->input('id');

        $updateArr = Brand::find($id)->update([
            'is_home_screen' => $is_home_screen
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


}
