<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\RecipeTag;

class RecipeTagController extends CoreApiController
{

    public function index(Request $request)
    {
        $tags = RecipeTag::orderBy('id', 'desc')->get();
        return view('admin.recipe_tags.index', ['tags' => $tags]);
    }

    public function create(Request $request)
    {
        return view('admin.recipe_tags.create');
    }

    public function store(Request $request)
    {

        $insert_arr = [
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'tag' => $request->input('tag')
        ];
        $add = RecipeTag::create($insert_arr);
        if ($add) {
            return redirect('admin/recipe_tags')->with('success', 'Tag added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding tag');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $tag = RecipeTag::find($id);

        return view('admin.recipe_tags.edit', ['tag' => $tag]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $tag = RecipeTag::find($id);

        $update_arr = [
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'tag' => $request->input('tag')
        ];
        $update = RecipeTag::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/recipe_tags')->with('success', 'Tag updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating tag');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $tag = RecipeTag::find($id);
        if ($tag) {
            RecipeTag::find($id)->delete();
            return redirect('admin/recipe_tags')->with('success', 'Tag deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting tag');
        }
    }
}
