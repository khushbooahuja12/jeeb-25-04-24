<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Ads;
use App\Model\Category;

class AdsController extends CoreApiController
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
            $ads = Ads::where('deleted', 0)
                ->where('name', 'like', '%' . $filter . '%')
                ->orderBy('id', 'desc')
                ->paginate(50);
        } else {
            $ads = Ads::where('deleted', 0)
                ->orderBy('id', 'desc')
                ->paginate(50);
        }
        $ads->appends(['filter' => $filter]);

        return view('admin.ads.index', ['ads' => $ads, 'filter' => $filter]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();
        return view('admin.ads.create', ['category' => $category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $insert_arr = [
            'name' => $request->input('ads_name'),
            'redirect_type' => $request->input('redirect_type'),
        ];

        if ($request->hasFile('ads_image')) {
            $path = "/images/ads_images/";
            $check = $this->uploadFile($request, 'ads_image', $path);
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
        $add = Ads::create($insert_arr);

        if ($add) {
            return redirect('admin/ads')->with('success', 'Ads added successfully');
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
        $ads = Ads::find($id);

        return view('admin.ads.detail', [
            'ads' => $ads
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
        $ads = Ads::find($id);

        return view('admin.ads.edit', [
            'ads' => $ads
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

        $ads = Ads::find($id);

        $update_arr = [
            'name' => $request->input('ads_name'),
            'redirect_type' => $request->input('redirect_type'),
        ];

        if ($request->hasFile('ads_image')) {
            $path = "/images/ads_images/";
            $check = $this->uploadFile($request, 'ads_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($ads->image != '') {
                    $destinationPath = public_path("images/ads_images/");
                    if (!empty($ads->getAdsImage) && file_exists($destinationPath . $ads->getAdsImage->file_name)) {
                        unlink($destinationPath . $ads->getAdsImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $ads->image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['image'] = $returnArr->id;
            endif;
        }
        $update = Ads::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/ads')->with('success', 'Ads updated successfully');
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

        $ads = Ads::find($ids);
        if ($ads) {
            Ads::where('id', $ids)->update(['deleted' => 1]);
            return redirect('admin/ads')->with('success', 'Ads deleted successfully');
        } else {
            return back()->withInput()->with('error', 'Error while deleting banner');
        }
    }
}
