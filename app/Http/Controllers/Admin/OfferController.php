<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Offer;
use App\Model\Category;

class OfferController extends CoreApiController {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $offers = Offer::orderBy('id', 'desc')->get();
        return view('admin.offers.index', ['offers' => $offers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();
        return view('admin.offers.create', ['category' => $category]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $insert_arr = [
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'heading_en' => $request->input('heading_en'),
            'heading_ar' => $request->input('heading_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
        ];

        if ($request->hasFile('offer_image')) {
            $path = "/images/offer_images/";
            $check = $this->uploadFile($request, 'offer_image', $path);
            if ($check):
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['offer_image'] = $returnArr->id;
            endif;
        }
        $add = Offer::create($insert_arr);
        if ($add) {
            return redirect('admin/offers')->with('success', 'Offer added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function detail(Request $request, $id) {
        $id = base64url_decode($id);
        $offer = Offer::find($id);

        return view('admin.offers.detail', [
            'offer' => $offer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $id = base64url_decode($id);
        $offer = Offer::find($id);

        return view('admin.offers.edit', [
            'offer' => $offer
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $id = base64url_decode($id);

        $offer = Offer::find($id);

        $update_arr = [
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'heading_en' => $request->input('heading_en'),
            'heading_ar' => $request->input('heading_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
        ];

        if ($request->hasFile('offer_image')) {
            $path = "/images/offer_images/";
            $check = $this->uploadFile($request, 'offer_image', $path);
            if ($check):
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];
                if ($offer->offer_image != '') {
                    $destinationPath = public_path("images/offer_images/");
                    if (!empty($offer->getOfferImage) && file_exists($destinationPath . $offer->getOfferImage->file_name)) {
                        unlink($destinationPath . $offer->getOfferImage->file_name);
                    }
                    $returnArr = $this->updateFile($req, $offer->offer_image);
                } else {
                    $returnArr = $this->insertFile($req);
                }
                $update_arr['offer_image'] = $returnArr->id;
            endif;
        }
        $update = Offer::find($id)->update($update_arr);
        if ($update) {
            return redirect('admin/offers')->with('success', 'Offer updated successfully');
        }
        return back()->withInput()->with('error', 'Error while adding brand');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
