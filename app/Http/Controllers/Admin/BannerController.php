<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Banner;
use App\Model\Category;

class BannerController extends CoreApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        if (!empty($filter)) {
            $banners = Banner::where('deleted', 0)
                ->where('heading_en', 'like', '%' . $filter . '%')
                ->orWhere('description_name', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->paginate(50);
        } else {
            $banners = Banner::where('deleted', 0)
                ->orderBy('id', 'desc')
                ->paginate(50);
        }
        $banners->appends(['filter' => $filter]);

        return view('admin.banners.index', ['banners' => $banners, 'filter' => $filter]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = Category::where('parent_id', '=', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        $brands = \App\Model\Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $classifications = \App\Model\Classification::where(['deleted' => 0, 'parent_id' => 0])->get();

        return view('admin.banners.create', [
            'category' => $category,
            'brands' => $brands,
            'classifications' => $classifications
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $total_banner_count = Banner::where('deleted', 0)->count();
        if ($total_banner_count <= 4) {
            $insert_arr = [
                'heading_en' => $request->input('heading_en'),
                'heading_ar' => $request->input('heading_ar'),
                'description_en' => $request->input('description_en'),
                'description_ar' => $request->input('description_ar'),
                'banner_name' => $request->input('banner_name'),
                'redirect_type' => $request->input('redirect_type'),
                'fk_classification_id' => $request->input('redirect_type') == 3 ? $request->input('fk_classification_id') : '',
            ];

            if ($request->hasFile('banner_image')) {
                $path = "/images/banner_images/";
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
            $add = Banner::create($insert_arr);

            if ($add) {
                return redirect('admin/banners')->with('success', 'Banner added successfully');
            }
        } else {
            return redirect('admin/banners')->with('error', 'You can add upto 5 banners');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function detail(Request $request, $id)
    {
        $id = base64url_decode($id);
        $banner = Banner::find($id);

        return view('admin.banners.detail', [
            'banner' => $banner
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = base64url_decode($id);
        $banner = Banner::find($id);

        $classifications = \App\Model\Classification::where(['deleted' => 0, 'parent_id' => 0])->get();

        return view('admin.banners.edit', [
            'banner' => $banner,
            'classifications' => $classifications
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);

        $banner = Banner::find($id);

        $update_arr = [
            'heading_en' => $request->input('heading_en'),
            'heading_ar' => $request->input('heading_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
            'banner_name' => $request->input('banner_name'),
            'redirect_type' => $request->input('redirect_type'),
            'fk_classification_id' => $request->input('redirect_type') == 3 ? $request->input('fk_classification_id') : '',
        ];

        if ($request->hasFile('banner_image')) {
            $path = "/images/banner_images/";
            $check = $this->uploadFile($request, 'banner_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($banner->banner_image != '') {
                    $destinationPath = public_path("images/banner_images/");
                    if (!empty($banner->getBannerImage) && file_exists($destinationPath . $banner->getBannerImage->file_name)) {
                        unlink($destinationPath . $banner->getBannerImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $banner->banner_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['banner_image'] = $returnArr->id;
            endif;
        }
        $update = Banner::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/banners')->with('success', 'Banner updated successfully');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $ids = base64url_decode($id);

        $banner = Banner::find($ids);
        if ($banner) {
            Banner::where('id', $ids)->update(['deleted' => 1]);
            return redirect('admin/banners')->with('success', 'Banner deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting banner');
        }
    }
}
