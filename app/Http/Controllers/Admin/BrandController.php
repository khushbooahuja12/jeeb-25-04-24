<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;

class BrandController extends CoreApiController
{

    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $brands = Brand::where('deleted', '=', 0)
                ->where('brand_name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        } else {
            $brands = Brand::where('deleted', '=', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }
        $brands->appends(['filter' => $filter]);

        return view('admin.brands.index', ['brands' => $brands, 'filter' => $filter]);
    }

    public function home_brands(Request $request)
    {
        $brands = Brand::where(['is_home_screen' => 1, 'deleted' => 0])
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.brands.home_brands', ['brands' => $brands]);
    }

    public function create(Request $request)
    {
        $categories = Category::where(['parent_id' => 0])->orderBy('id', 'desc')->get();
        return view('admin.brands.create', ['category' => $categories]);
    }

    public function create_home(Request $request)
    {
        $categories = Category::where(['parent_id' => 0])->orderBy('id', 'desc')->get();
        return view('admin.brands.create_home', ['category' => $categories]);
    }

    public function store(Request $request)
    {
        $max_count = Brand::where(['is_home_screen' => 1])->count();
        if ($request->input('is_home_screen') == 1 && $max_count > 10) {
            return back()->withInput()->with('error', 'Max 10 brand allowed for home screen');
        }

        $insert_arr = [
            'brand_name_en' => $request->input('brand_name_en'),
            'brand_name_ar' => $request->input('brand_name_ar'),
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
                return redirect('admin/home_brands')->with('success', 'Brand added successfully');
            } else {
                return redirect('admin/brands')->with('success', 'Brand added successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $brand = Brand::find($id);
        $categories = Category::where(['parent_id' => 0])->orderBy('id', 'desc')->get();
        return view('admin.brands.edit', ['brand' => $brand, 'category' => $categories]);
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
                return redirect('admin/home_brands')->with('success', 'Brand updated successfully');
            } else {
                return redirect('admin/brands')->with('success', 'Brand updated successfully');
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
                    return redirect('admin/home_brands')->with('success', 'Brand deleted successfully');
                } else {
                    return redirect('admin/brands')->with('success', 'Brand deleted successfully');
                }
            }
        } else {
            return back()->withInput()->with('error', 'Error while deleting brand');
        }
    }

    public function remove_from_home($id = null)
    {
        $id = base64url_decode($id);
        $brand = Brand::find($id);
        if ($brand) {
            Brand::find($id)->update(['is_home_screen' => 0]);
            return redirect('admin/home_brands')->with('success', 'Brand removed from home successfully');
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

    public function images(Request $request)
    {
        $images = \App\Model\File::where('file_name', 'like', 'brand_images%')
            ->orderBy('id', 'desc')
            ->paginate(100);

        return view('admin.brands.images', ['images' => $images]);
    }

    public function upload_images(Request $request)
    {
        return view('admin.brands.upload_images');
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
            return redirect('admin/brands/images')->with('success', 'Brand images uploaded!');
        }
        return back()->withInput()->with('error', 'Error while uploading images');
    }

    public function create_multiple(Request $request)
    {
        return view('admin.brands.create_multiple');
    }

    public function bulk_upload(Request $request)
    {
        $path = "/brand_files/";
        $file = $this->uploadFile($request, 'brand_csv', $path);

        $brands = csvToArray(public_path('/brand_files/') . $file);

        if ($brands) {
            foreach ($brands as $key => $value) {
                //                $update_arr = [
                //                    'brand_name_en' => $value[1],
                //                    'brand_name_ar' => $value[2]
                //                ];
                $insert_arr = [
                    'brand_name_en' => $value[0],
                    'brand_name_ar' => $value[1],
                    'brand_image' => $value[2]
                ];
                //                Brand::find($value[0])->update($update_arr);
                Brand::create($insert_arr);
            }
            return redirect('admin/brands')->with('success', 'Brands added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding Product');
    }

    public function show($id = null)
    {
        $id = base64url_decode($id);

        $relatedCategories = \App\Model\BrandCategoryMapping::where(['fk_brand_id' => $id])
            ->get();

        $brand = Brand::find($id);
        if ($brand) {
            return view('admin.brands.show', [
                'brand' => $brand,
                'relatedCategories' => $relatedCategories
            ]);
        }
        return back()->withInput()->with('error', 'Some error found !');
    }
}
