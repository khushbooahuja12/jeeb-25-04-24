<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;
use App\Model\Coupon;

class CouponController extends CoreApiController
{

    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('coupon_code', 'like', '%' . $filter . '%')
                ->where('coupon_code', 'not like', 'SC%')
                ->where('is_hidden', '=', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        } else {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('coupon_code', 'not like', 'SC%')
                ->where('is_hidden', '=', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }
        $coupons->appends(['filter' => $filter]);

        return view('admin.coupons.index', ['coupons' => $coupons, 'filter' => $filter]);
    }

    public function create()
    {
        $brands = Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $category = Category::where('parent_id', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.coupons.create', ['brands' => $brands, 'category' => $category]);
    }

    public function store(Request $request)
    {
        $exist = Coupon::where('coupon_code', '=', $request->input('coupon_code'))->first();
        if ($exist) {
            return back()->withInput()->with('error', 'Coupon code already exist');
        }
        $insert_arr = [
            'type' => $request->input('type'),
            'offer_type' => $request->input('offer_type'),
            'min_amount' => $request->input('min_amount'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'coupon_code' => $request->input('coupon_code'),
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
            'discount' => $request->input('discount'),
            'expiry_date' => $request->input('expiry_date'),
            'uses_limit' => $request->input('uses_limit'),
            'model' => $request->input('model'),
            'status' => 1
        ];
        if ($request->hasFile('coupon_image')) {
            $path = "/images/coupon_images/";
            $check = $this->uploadFile($request, 'coupon_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['coupon_image'] = $returnArr->id;
            endif;
        }
        if ($request->hasFile('coupon_image_ar')) {
            $path = "/images/coupon_images/";
            $check = $this->uploadFile($request, 'coupon_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['coupon_image_ar'] = $returnArr->id;
            endif;
        }

        $add = Coupon::create($insert_arr);
        if ($add) {
            return redirect('admin/coupons')->with('success', 'Coupon added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding coupon');
    }

    public function show($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            return view('admin.coupons.show', ['coupon' => $coupon]);
        } else {
            return redirect('admin/coupons')->with('error', 'Coupon not found');
        }
    }

    public function recreate($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        $brands = Brand::orderBy('id', 'desc')->get();
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();
        if ($coupon) {
            return view('admin.coupons.recreate', ['coupon' => $coupon, 'brands' => $brands, 'category' => $category]);
        } else {
            return redirect('admin/coupons')->with('error', 'Coupon not found');
        }
    }

    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            $insert_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'coupon_code' => $request->input('coupon_code'),
                'title_en' => $request->input('title_en'),
                'title_ar' => $request->input('title_ar'),
                'description_en' => $request->input('description_en'),
                'description_ar' => $request->input('description_ar'),
                'discount' => $request->input('discount'),
                'expiry_date' => $request->input('expiry_date'),
                'uses_limit' => $request->input('uses_limit'),
                'status' => 1
            ];
            if ($request->hasFile('coupon_image')) {
                $path = "/images/coupon_images/";
                $check = $this->uploadFile($request, 'coupon_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['coupon_image'] = $returnArr->id;
                endif;
            }
            if ($request->hasFile('coupon_image_ar')) {
                $path = "/images/coupon_images/";
                $check = $this->uploadFile($request, 'coupon_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['coupon_image_ar'] = $returnArr->id;
                endif;
            }

            $add = Coupon::create($insert_arr);
            if ($add) {
                Coupon::find($id)->update(['status' => 2]);
                return redirect('admin/coupons')->with('success', 'Coupon recreated successfully');
            }
            return back()->withInput()->with('error', 'Error while recreating coupon');
        } else {
            return redirect('admin/coupons')->with('error', 'Coupon not found');
        }
    }

    public function change_coupon_status(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('action');
        $update = Coupon::find($id)->update(['status' => $status]);
        if ($update) {
            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Status updated successfully']);
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error while updating status']);
        }
    }

    public function edit($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        $brands = Brand::orderBy('id', 'desc')->get();
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();

        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'brands' => $brands,
            'category' => $category
        ]);
    }

    public function update_coupon(Request $request, $id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            if ($coupon->coupon_code != $request->input('coupon_code')) {
                $duplicate = Coupon::where('coupon_code', '=', $request->input('coupon_code'))
                    ->where('coupon_code', '!=', $coupon->coupon_code)
                    ->first();
                if ($duplicate) {
                    return redirect('admin/coupons/edit/' . base64url_encode($id))->with('error', 'Coupon code already exist');
                }
            }
            $update_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'coupon_code' => $request->input('coupon_code'),
                'title_en' => $request->input('title_en'),
                'title_ar' => $request->input('title_ar'),
                'description_en' => $request->input('description_en'),
                'description_ar' => $request->input('description_ar'),
                'discount' => $request->input('discount'),
                'expiry_date' => $request->input('expiry_date'),
                'uses_limit' => $request->input('uses_limit'),
                'model' => $request->input('model'),
                'status' => 1
            ];
            if ($request->hasFile('coupon_image')) {
                $path = "/images/coupon_images/";
                $check = $this->uploadFile($request, 'coupon_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    if ($coupon->coupon_image != '') {
                        $destinationPath = public_path("images/coupon_images/");
                        if (!empty($coupon->getCouponImage) && file_exists($destinationPath . $coupon->getCouponImage->file_name)) {
                            unlink($destinationPath . $coupon->getCouponImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $coupon->coupon_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }

                    $update_arr['coupon_image'] = $returnArr->id;
                endif;
            }

            $update = Coupon::find($id)->update($update_arr);
            if ($update) {
                return redirect('admin/coupons')->with('success', 'Coupon updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating coupon');
        } else {
            return redirect('admin/coupons')->with('error', 'Coupon not found');
        }
    }

    // Hidden Coupons
    public function index_hidden(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('coupon_code', 'like', '%' . $filter . '%')
                ->where('is_hidden', '=', 1)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        } else {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('is_hidden', '=', 1)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }
        $coupons->appends(['filter' => $filter]);

        return view('admin.coupons_hidden.index', ['coupons' => $coupons, 'filter' => $filter]);
    }

    public function create_hidden()
    {
        $brands = Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $category = Category::where('parent_id', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.coupons_hidden.create', ['brands' => $brands, 'category' => $category]);
    }

    public function store_hidden(Request $request)
    {
        $exist = Coupon::where('coupon_code', '=', $request->input('coupon_code'))->first();
        if ($exist) {
            return back()->withInput()->with('error', 'Coupon code already exist');
        }
        $insert_arr = [
            'type' => $request->input('type'),
            'offer_type' => $request->input('offer_type'),
            'min_amount' => $request->input('min_amount'),
            'fk_category_id' => $request->input('fk_category_id'),
            'fk_brand_id' => $request->input('fk_brand_id'),
            'coupon_code' => $request->input('coupon_code'),
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'description_en' => $request->input('description_en'),
            'description_ar' => $request->input('description_ar'),
            'discount' => $request->input('discount'),
            'expiry_date' => $request->input('expiry_date'),
            'uses_limit' => $request->input('uses_limit'),
            'is_hidden' => 1,
            'status' => 1
        ];
        if ($request->hasFile('coupon_image')) {
            $path = "/images/coupon_images/";
            $check = $this->uploadFile($request, 'coupon_image', $path);
            if ($check) :
                $nameArray = explode('.', $check);
                $ext = end($nameArray);

                $req = [
                    'file_path' => $path,
                    'file_name' => $check,
                    'file_ext' => $ext
                ];

                $returnArr = $this->insertFile($req);
                $insert_arr['coupon_image'] = $returnArr->id;
            endif;
        }

        $add = Coupon::create($insert_arr);
        if ($add) {
            return redirect('admin/coupons_hidden')->with('success', 'Coupon added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding coupon');
    }

    public function show_hidden($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            return view('admin.coupons_hidden.show', ['coupon' => $coupon]);
        } else {
            return redirect('admin/coupons_hidden')->with('error', 'Coupon not found');
        }
    }

    public function recreate_hidden($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        $brands = Brand::orderBy('id', 'desc')->get();
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();
        if ($coupon) {
            return view('admin.coupons_hidden.recreate', ['coupon' => $coupon, 'brands' => $brands, 'category' => $category]);
        } else {
            return redirect('admin/coupons_hidden')->with('error', 'Coupon not found');
        }
    }

    public function update_hidden(Request $request, $id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            $insert_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'coupon_code' => $request->input('coupon_code'),
                'title_en' => $request->input('title_en'),
                'title_ar' => $request->input('title_ar'),
                'description_en' => $request->input('description_en'),
                'description_ar' => $request->input('description_ar'),
                'discount' => $request->input('discount'),
                'expiry_date' => $request->input('expiry_date'),
                'uses_limit' => $request->input('uses_limit'),
                'is_hidden' => 1,
                'status' => 1
            ];
            if ($request->hasFile('coupon_image')) {
                $path = "/images/coupon_images/";
                $check = $this->uploadFile($request, 'coupon_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    $returnArr = $this->insertFile($req);
                    $insert_arr['coupon_image'] = $returnArr->id;
                endif;
            }

            $add = Coupon::create($insert_arr);
            if ($add) {
                Coupon::find($id)->update(['status' => 2]);
                return redirect('admin/coupons_hidden')->with('success', 'Coupon recreated successfully');
            }
            return back()->withInput()->with('error', 'Error while recreating coupon');
        } else {
            return redirect('admin/coupons_hidden')->with('error', 'Coupon not found');
        }
    }

    public function edit_hidden($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        $brands = Brand::orderBy('id', 'desc')->get();
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();

        return view('admin.coupons_hidden.edit', [
            'coupon' => $coupon,
            'brands' => $brands,
            'category' => $category
        ]);
    }

    public function update_coupon_hidden(Request $request, $id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            if ($coupon->coupon_code != $request->input('coupon_code')) {
                $duplicate = Coupon::where('coupon_code', '=', $request->input('coupon_code'))
                    ->where('coupon_code', '!=', $coupon->coupon_code)
                    ->first();
                if ($duplicate) {
                    return redirect('admin/coupons_hidden/edit/' . base64url_encode($id))->with('error', 'Coupon code already exist');
                }
            }
            $update_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'coupon_code' => $request->input('coupon_code'),
                'title_en' => $request->input('title_en'),
                'title_ar' => $request->input('title_ar'),
                'description_en' => $request->input('description_en'),
                'description_ar' => $request->input('description_ar'),
                'discount' => $request->input('discount'),
                'expiry_date' => $request->input('expiry_date'),
                'uses_limit' => $request->input('uses_limit'),
                'is_hidden' => 1,
                'status' => 1
            ];
            if ($request->hasFile('coupon_image')) {
                $path = "/images/coupon_images/";
                $check = $this->uploadFile($request, 'coupon_image', $path);
                if ($check) :
                    $nameArray = explode('.', $check);
                    $ext = end($nameArray);

                    $req = [
                        'file_path' => $path,
                        'file_name' => $check,
                        'file_ext' => $ext
                    ];

                    if ($coupon->coupon_image != '') {
                        $destinationPath = public_path("images/coupon_images/");
                        if (!empty($coupon->getCouponImage) && file_exists($destinationPath . $coupon->getCouponImage->file_name)) {
                            unlink($destinationPath . $coupon->getCouponImage->file_name);
                        }
                        $returnArr = $this->updateFile($req, $coupon->coupon_image);
                    } else {
                        $returnArr = $this->insertFile($req);
                    }

                    $update_arr['coupon_image'] = $returnArr->id;
                endif;
            }

            $update = Coupon::find($id)->update($update_arr);
            if ($update) {
                return redirect('admin/coupons_hidden')->with('success', 'Coupon updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating coupon');
        } else {
            return redirect('admin/coupons_hidden')->with('error', 'Coupon not found');
        }
    }

    protected function create_multiple_hidden()
    {
        return view('admin.coupons_hidden.create_multiple');
    }

    protected function bulk_upload_hidden(Request $request)
    {
        $path = "/coupon_files/";
        $file = $this->uploadFile($request, 'coupons_csv', $path);

        $coupons = csvToArray(public_path('/coupon_files/') . $file);

        if ($coupons) {

            foreach ($coupons as $key => $value) {

                $insert_arr = [
                    'type' => 2,
                    'offer_type' => 1,
                    'min_amount' => $value[0],
                    'fk_category_id' => NULL,
                    'fk_brand_id' => NULL,
                    'coupon_code' => $value[1],
                    'title_en' => $value[2],
                    'title_ar' => $value[3],
                    'description_en' => '',
                    'description_ar' => '',
                    'discount' => $value[4],
                    'expiry_date' => $value[5],
                    'uses_limit' => 1,
                    'is_hidden' => 1,
                    'status' => 1
                ];
                $add = Coupon::create($insert_arr);
                if ($add) {
                    \Log::info('Hidden coupon created '.$add->id.') min_amount: '.$value[0].' coupon_code: '.$value[1]);
                } else {
                    \Log::info('Hidden coupon not created min_amount: '.$value[0].' coupon_code: '.$value[1]);
                }
                
            }

            return redirect('admin/coupons_hidden')->with('success', 'Hidden coupons added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding hidden coupons');
    }

}
