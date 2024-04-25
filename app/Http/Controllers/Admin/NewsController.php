<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;

class NewsController extends CoreApiController {

    public function index(Request $request) {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $news = \App\Model\News::where('deleted', '=', 0)
                    ->where('title', 'like', '%' . $filter . '%')
                    ->orWhere('description', 'like', '%' . $filter . '%')
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
            $news->appends(['filter' => $filter]);
        } else {
            $news = \App\Model\News::where('deleted', '=', 0)
                    ->sortable(['id' => 'desc'])
                    ->paginate(20);
        }
        $news->appends(['filter' => $filter]);

        return view('admin.news.index', ['news' => $news, 'filter' => $filter]);
    }

    public function create(Request $request) {
        return view('admin.news.create');
    }

    public function store(Request $request) {
        $insert_arr = [
            'title' => $request->input('title'),
            'description' => $request->input('description')
        ];

        if ($request->hasFile('image')) {
            $path = "/images/news_images/";
            $check = $this->uploadFile($request, 'image', $path);
            if ($check):
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
        $add = \App\Model\News::create($insert_arr);
        if ($add) {
            return redirect('admin/news')->with('success', 'News added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding news');
    }

    public function edit($id = null) {
        $id = base64url_decode($id);
        $news = \App\Model\News::find($id);

        return view('admin.news.edit', ['news' => $news]);
    }

    public function update(Request $request, $id = null) {
        $id = base64url_decode($id);

        $news = \App\Model\News::find($id);

        $update_arr = [
            'title' => $request->input('title'),
            'description' => $request->input('description')
        ];

        if ($request->hasFile('image')) {
            $path = "/images/news_images/";
            $check = $this->uploadFile($request, 'image', $path);
            if ($check):
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($news->image != '') {
                    $destinationPath = public_path("images/news_images/");
                    if (!empty($news->getNewsImage) && file_exists($destinationPath . $news->getNewsImage->file_name)) {
                        unlink($destinationPath . $news->getNewsImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $news->image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['image'] = $returnArr->id;
            endif;
        }
        $update = \App\Model\News::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/news')->with('success', 'News updated successfully');
        }
        return back()->withInput()->with('error', 'Error while updating news');
    }

    public function destroy($id = null) {
        $id = base64url_decode($id);
        $news = \App\Model\News::find($id);
        if ($news) {
            $update = \App\Model\News::find($id)->update(['deleted' => 1]);
            if ($update) {
                return redirect('admin/news')->with('success', 'News deleted successfully');
            } else {
                return back()->withInput()->with('error', 'Error while deleting news');
            }
        } else {
            return back()->withInput()->with('error', 'Error while deleting news');
        }
    }

}
