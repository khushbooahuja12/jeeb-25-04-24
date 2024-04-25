<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Category;
use App\Model\File;

class CategoryController extends CoreApiController
{

    public function index(Request $request)
    {
        $filter = $request->query('filter');
        if (!empty($filter)) {
            $categories = Category::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->where('category_name_en', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->paginate(50);
        } else {
            $categories = Category::where('parent_id', '=', 0)
                ->where('deleted', '=', 0)
                ->orderBy('id', 'desc')
                ->paginate(50);
        }

        return view('admin.categories.index', ['categories' => $categories, 'filter' => $filter]);
    }

    public function home_categories(Request $request)
    {
        $categories = Category::where(['parent_id' => 0, 'is_home_screen' => 1, 'deleted' => 0])
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.categories.home_categories', ['categories' => $categories]);
    }

    public function create(Request $request)
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $max_count = Category::where(['is_home_screen' => 1])->count();
        if ($request->input('is_home_screen') == 1 && $max_count > 10) {
            return back()->withInput()->with('error', 'Max 10 category allowed for home screen');
        }

        $insert_arr = [
            'category_name_en' => $request->input('category_name_en'),
            'category_name_ar' => $request->input('category_name_ar'),
            'parent_id' => $request->input('parent_id') ?? 0,
            'is_home_screen' => $request->input('is_home_screen') ?? 0
        ];

        if ($request->hasFile('category_image')) {
            $path = "/images/category_images/";
            $check = $this->uploadFile($request, 'category_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['category_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('category_image2')) {
            $path = "/images/category_images/";
            $check = $this->uploadFile($request, 'category_image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['category_image2'] = $returnArr->id;
            endif;
        }
        $add = Category::create($insert_arr);
        if ($add) {
            if ($request->input('parent_id') != '') {
                return redirect('admin/categories/show/' . base64url_encode($request->input('parent_id')))->with('success', 'Sub Category added successfully');
            } else {
                return redirect('admin/categories')->with('success', 'Category added successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while adding category');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $category = Category::find($id);

        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $category = Category::find($id);

        $update_arr = [
            'category_name_en' => $request->input('category_name_en'),
            'category_name_ar' => $request->input('category_name_ar'),
            'parent_id' => $request->input('parent_id') ?? 0
        ];

        if ($request->hasFile('category_image')) {
            $path = "/images/category_images/";
            $check = $this->uploadFile($request, 'category_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($category->category_image != '' && File::find($category->category_image)) {
                    $destinationPath = public_path("images/category_images/");
                    // if (!empty($category->getCategoryImage) && file_exists($destinationPath . $category->getCategoryImage->file_name)) {
                    //     unlink($destinationPath . $category->getCategoryImage->file_name);
                    // }
                    $returnArr = $this->updateFile($req, $category->category_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['category_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('category_image2')) {
            $path = "/images/category_images/";
            $check = $this->uploadFile($request, 'category_image2', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($category->category_image2 != '') {
                    $destinationPath = public_path("images/category_images/");
                    // if (!empty($category->getCategoryImage2) && file_exists($destinationPath . $category->getCategoryImage2->file_name)) {
                    //     unlink($destinationPath . $category->getCategoryImage2->file_name);
                    // }
                    $returnArr = $this->updateFile($req, $category->category_image2);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['category_image2'] = $returnArr->id;
            endif;
        }
        $update = Category::find($id)->update($update_arr);
        if ($update) {
            if ($category->parent_id != '') {
                return redirect('admin/categories/show/' . base64url_encode($request->input('parent_id')))->with('success', 'Sub category updated successfully');
            } else {
                if ($category->is_home_screen == 1) {
                    return redirect('admin/home_categories')->with('success', 'Category updated successfully');
                } else {
                    return redirect('admin/categories')->with('success', 'Category updated successfully');
                }
            }
        }
        return back()->withInput()->with('error', 'Error while updating category');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $category = Category::find($id);
        if ($category) {
            Category::find($id)->update(['deleted' => 1]);

            if ($category->parent_id != '') {
                return redirect('admin/categories/show/' . base64url_encode($category->parent_id))->with('success', 'Sub category deleted successfully');
            } else {
                if ($category->is_home_screen == 1) {
                    return redirect('admin/home_categories')->with('success', 'Category deleted successfully');
                } else {
                    return redirect('admin/categories')->with('success', 'Category deleted successfully');
                }
            }
        } else {
            return back()->withInput()->with('error', 'Error while deleting category');
        }
    }

    public function remove_from_home($id = null)
    {
        $id = base64url_decode($id);
        $category = Category::find($id);
        if ($category) {
            Category::find($id)->update(['is_home_screen' => 0]);
            return redirect('admin/home_categories')->with('success', 'Category removed from home successfully');
        } else {
            return back()->withInput()->with('error', 'Error while removing category');
        }
    }

    public function add_remove_home(Request $request)
    {
        $is_home_screen = $request->input('is_home_screen');
        $id = $request->input('id');
        
        if (Category::where(['is_home_screen' => 1])->count() >= 4 && $is_home_screen == 1) {
            return response()->json([
                'error' => true,
                'status_code' => 105,
                'message' => "You can only select 4 categories for this section"
            ]);
        }

        $updateArr = Category::find($id)->update([
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
                'error' => true,
                'status_code' => 105,
                'message' => "Some error found"
            ]);
        }
    }

    public function show($id = null)
    {
        $id = base64url_decode($id);

        $category = Category::find($id);
        if ($category) {
            return view('admin.categories.show', ['category' => $category]);
        }
        return back()->withInput()->with('error', 'Some error found !');
    }

    public function create_sub_category(Request $request, $id = null)
    {
        $id = $this->base64url_decode($id);
        $parent_categories = Category::where('parent_id', '=', 0)->orderBy('category_name_en', 'asc')->get();
        return view('admin.categories.create_sub_category', ['parent_categories' => $parent_categories, 'id' => $id]);
    }

    public function edit_sub_category($id = null)
    {
        $id = base64url_decode($id);
        $category = Category::find($id);
        $parent_categories = Category::where('parent_id', '=', 0)->orderBy('category_name_en', 'asc')->get();
        return view('admin.categories.edit_sub_category', ['category' => $category, 'parent_categories' => $parent_categories]);
    }

    protected function classifications(Request $request)
    {
        $classifications = \App\Model\Classification::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        return view('admin.categories.classifications', ['classifications' => $classifications]);
    }

    protected function create_classification(Request $request)
    {
        return view('admin.categories.create_classification');
    }

    protected function store_classification(Request $request)
    {
        $insert_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'parent_id' => $request->input('parent_id') ?? 0
        ];

        if ($request->hasFile('banner_image')) {
            $path = "/images/classification_images/";
            $check = $this->uploadFile($request, 'banner_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['banner_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('stamp_image')) {
            $path = "/images/classification_images/";
            $check = $this->uploadFile($request, 'stamp_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['stamp_image'] = $returnArr->id;
            endif;
        }

        $add = \App\Model\Classification::create($insert_arr);
        if ($add) {
            if ($request->input('parent_id') != '') {
                return redirect('admin/classification_detail/' . base64url_encode($request->input('parent_id')))->with('success', 'Sub Classification added successfully');
            } else {
                return redirect('admin/classifications')->with('success', 'Classification added successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while adding classification');
    }

    public function edit_classification($id = null)
    {
        $id = base64url_decode($id);

        $classification = \App\Model\Classification::find($id);

        return view('admin.categories.edit_classification', ['classification' => $classification]);
    }

    public function update_classification(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $classification = \App\Model\Classification::find($id);

        $update_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'parent_id' => $request->input('parent_id') ?? 0
        ];

        if ($request->hasFile('banner_image')) {
            $path = "/images/classification_images/";
            $check = $this->uploadFile($request, 'banner_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($classification->banner_image != '') {
                    $destinationPath = public_path("images/classification_images/");
                    // if (!empty($classification->getBannerImage) && file_exists($destinationPath . $classification->getBannerImage->file_name)) {
                    //     unlink($destinationPath . $classification->getBannerImage->file_name);
                    // }
                    $returnArr = $this->updateFile($req, $classification->banner_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['banner_image'] = $returnArr->id;
            endif;
        }

        if ($request->hasFile('stamp_image')) {
            $path = "/images/classification_images/";
            $check = $this->uploadFile($request, 'stamp_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($classification->stamp_image != '') {
                    $destinationPath = public_path("images/classification_images/");
                    // if (!empty($classification->getStampImage) && file_exists($destinationPath . $classification->getStampImage->file_name)) {
                    //     unlink($destinationPath . $classification->getStampImage->file_name);
                    // }
                    $returnArr = $this->updateFile($req, $classification->stamp_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['stamp_image'] = $returnArr->id;
            endif;
        }

        $update = \App\Model\Classification::find($id)->update($update_arr);
        if ($update) {
            if ($classification->parent_id != '') {
                return redirect('admin/classification_detail/' . base64url_encode($request->input('parent_id')))->with('success', 'Sub classification updated successfully');
            } else {
                return redirect('admin/classifications')->with('success', 'Classification updated successfully');
            }
        }
        return back()->withInput()->with('error', 'Error while updating classification');
    }

    protected function destroy_classification($id = null)
    {
        $id = base64url_decode($id);
        $classification = \App\Model\Classification::find($id);
        if ($classification) {
            \App\Model\Classification::find($id)->update(['deleted' => 1]);

            if ($classification->parent_id != '') {
                return redirect('admin/classification_detail/' . base64url_encode($classification->parent_id))->with('success', 'Sub category deleted successfully');
            } else {
                return redirect('admin/classifications')->with('success', 'Classification deleted successfully');
            }
        } else {
            return back()->withInput()->with('error', 'Error while deleting classification');
        }
    }

    protected function classification_detail($id = null)
    {
        $id = base64url_decode($id);

        $classification = \App\Model\Classification::find($id);

        if ($classification) {
            if ($classification->parent_id == 0) {
                $classified_products = \App\Model\ClassifiedProduct::where('fk_classification_id', '=', $classification->id)
                    ->get();
            } else {
                $classified_products = \App\Model\ClassifiedProduct::where('fk_classification_id', '=', $classification->parent_id)
                    ->where('fk_sub_classification_id', '=', $classification->id)
                    ->get();
            }


            return view('admin.categories.classification_detail', [
                'classification' => $classification,
                'classified_products' => $classified_products
            ]);
        }
        return back()->withInput()->with('error', 'Some error found !');
    }

    protected function create_sub_classification(Request $request, $id = null)
    {
        $id = $this->base64url_decode($id);
        $parent_classifications = \App\Model\Classification::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('name_en', 'asc')->get();
        return view('admin.categories.create_sub_classification', ['parent_classifications' => $parent_classifications, 'id' => $id]);
    }

    protected function edit_sub_classification($id = null)
    {
        $id = base64url_decode($id);
        $classification = \App\Model\Classification::find($id);
        $parent_classifications = \App\Model\Classification::where('parent_id', '=', 0)
            ->orderBy('name_en', 'asc')->get();
        return view('admin.categories.edit_sub_classification', [
            'classification' => $classification,
            'parent_classifications' => $parent_classifications
        ]);
    }

    protected function deleteClassified(Request $request)
    {
        $delete = \App\Model\ClassifiedProduct::find($request->input('id'))->delete();
        if ($delete) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Product removed !"
            ]);
        } else {
            return response()->json([
                'error' => true,
                'status_code' => 105,
                'message' => "Some error found !"
            ]);
        }
    }
}
