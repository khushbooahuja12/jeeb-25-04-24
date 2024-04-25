<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\RecipeCategory;

class RecipeCategoryController extends CoreApiController
{

    public function index(Request $request)
    {
        $categories = RecipeCategory::orderBy('id', 'desc')->get();
        return view('admin.recipe_categories.index', ['categories' => $categories]);
    }

    public function create(Request $request)
    {
        return view('admin.recipe_categories.create');
    }

    public function store(Request $request)
    {

        $insert_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'tag' => $request->input('tag')
        ];
        // Upload recipe category image
        if ($request->hasFile('recipe_cat_img')) {
            $recipe_cat_img = $request->file('recipe_cat_img');
            $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/"));
            $images_url_base = "storage/images/recipes/"; 

            $path = "/images/recipes/";
            $filenameWithExt = $recipe_cat_img->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $recipe_cat_img->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $recipe_cat_img->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insert_arr['image'] = env('APP_URL') . $images_url_base . $fileNameToStore;
            endif;
        }

        $add = RecipeCategory::create($insert_arr);
        if ($add) {
            return redirect('admin/recipe_categories')->with('success', 'Category added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding category');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $category = RecipeCategory::find($id);

        return view('admin.recipe_categories.edit', ['category' => $category]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $category = RecipeCategory::find($id);

        $update_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar'),
            'tag' => $request->input('tag')
        ];
        // Upload recipe category image
        if ($request->hasFile('recipe_cat_img')) {
            $recipe_cat_img = $request->file('recipe_cat_img');
            $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/"));
            $images_url_base = "storage/images/recipes/"; 

            $path = "/images/recipes/";
            $filenameWithExt = $recipe_cat_img->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $recipe_cat_img->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $recipe_cat_img->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $update_arr['image'] = env('APP_URL') . $images_url_base . $fileNameToStore;
            endif;
        }

        $update = RecipeCategory::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/recipe_categories')->with('success', 'Category updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating category');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $category = RecipeCategory::find($id);
        if ($category) {
            RecipeCategory::find($id)->delete();
            return redirect('admin/recipe_categories')->with('success', 'Category deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting category');
        }
    }
}
