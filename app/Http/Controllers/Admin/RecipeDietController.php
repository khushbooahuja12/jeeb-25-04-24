<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\RecipeDiet;

class RecipeDietController extends CoreApiController
{

    public function index(Request $request)
    {
        $diets = RecipeDiet::orderBy('id', 'desc')->get();
        return view('admin.recipe_diets.index', ['diets' => $diets]);
    }

    public function create(Request $request)
    {
        return view('admin.recipe_diets.create');
    }

    public function store(Request $request)
    {

        $insert_arr = [
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'tag' => $request->input('tag')
        ];
        $add = RecipeDiet::create($insert_arr);
        if ($add) {
            return redirect('admin/recipe_diets')->with('success', 'Diet added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding diet');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $diet = RecipeDiet::find($id);

        return view('admin.recipe_diets.edit', ['diet' => $diet]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $diet = RecipeDiet::find($id);

        $update_arr = [
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'tag' => $request->input('tag')
        ];
        $update = RecipeDiet::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/recipe_diets')->with('success', 'Diet updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating diet');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $diet = RecipeDiet::find($id);
        if ($diet) {
            RecipeDiet::find($id)->delete();
            return redirect('admin/recipe_diets')->with('success', 'Diet deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting diet');
        }
    }
}
