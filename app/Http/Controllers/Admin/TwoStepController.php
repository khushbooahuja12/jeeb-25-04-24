<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\TwoStepTag;

class TwoStepController extends CoreApiController
{

    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $twosteptags = TwoStepTag::where('name_en', 'like', '%' . $filter . '%')
                ->sortable(['id' => 'desc'])
                ->paginate(20);
            $twosteptags->appends(['filter' => $filter]);
        } else {
            $twosteptags = TwoStepTag::sortable(['id' => 'desc'])
                ->paginate(20);
        }
        $twosteptags->appends(['filter' => $filter]);

        return view('admin.twosteptags.index', ['tags' => $twosteptags, 'filter' => $filter]);
    }

    public function create(Request $request)
    {
        return view('admin.twosteptags.create');
    }

    public function store(Request $request)
    {
        $insert_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar')
        ];
        $add = TwoStepTag::create($insert_arr);
        if ($add) {
            return redirect('admin/twosteptags')->with('success', 'Tag added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding tag');
    }

    public function edit($id = null)
    {
        $id = base64url_decode($id);

        $twosteptag = TwoStepTag::find($id);
        return view('admin.twosteptags.edit', ['tag' => $twosteptag]);
    }

    public function update(Request $request, $id = null)
    {
        $id = base64url_decode($id);

        $twosteptag = TwoStepTag::find($id);

        $update_arr = [
            'name_en' => $request->input('name_en'),
            'name_ar' => $request->input('name_ar')
        ];

        $update = TwoStepTag::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/twosteptags')->with('success', 'Tag updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating tag');
    }

    public function destroy($id = null)
    {
        $id = base64url_decode($id);
        $twosteptag = TwoStepTag::find($id);
        if ($twosteptag) {
            TwoStepTag::find($id)->delete();
            return redirect('admin/twosteptags')->with('success', 'Tag deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting tag');
        }
    }
}
