<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use App\Model\Brand;
use App\Model\Category;
use App\Model\Coupon;
use Illuminate\Support\Facades\Auth;

class CouponsController extends CoreApiController
{
    public function __construct(Request $request)
    {   
        if (!Auth::guard('vendor')->check()) {
            return redirect('vendor/login');
        }

        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $vendor = Auth::guard('vendor')->user();
        
        if(!$vendor || empty($vendor->store_id)){

            return view('vendor.auth.login');
        }

        
        $store_id = $vendor->store_id;
 
        if (!empty($filter)) {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('fk_store_id', '=', $store_id)
                ->where('coupon_code', 'like', '%' . $filter . '%')
                // ->where('coupon_code', 'not like', 'SC%')
                // ->where('is_hidden', '=', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        } else {
            $coupons = Coupon::where('status', '!=', 2)
                ->where('fk_store_id', '=', $store_id)
                // ->where('coupon_code', 'not like', 'SC%')
                // ->where('is_hidden', '=', 0)
                ->sortable(['id' => 'desc'])
                ->paginate(20);
        }
        // echo '<pre>'; print_r($coupons);die;
        $coupons->appends(['filter' => $filter]);

        return view('vendor.coupons.index', ['coupons' => $coupons, 'filter' => $filter]);
    }

    public function create_coupon(Request $request)
    {

        $brands = Brand::where('deleted', '=', 0)
            ->orderBy('brand_name_en', 'asc')
            ->get();

        $category = Category::where('parent_id', 0)
            ->where('deleted', '=', 0)
            ->orderBy('id', 'desc')
            ->get();

        return view('vendor.coupons.create', ['brands' => $brands, 'category' => $category]);

    }


    public function store_coupon(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        // print_r($vendor->store_id);die;
        $store_id = $vendor->store_id;

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
            'status' => 1,
            'fk_store_id' => $store_id
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
            return redirect('vendor/coupons_list')->with('success', 'Coupon added successfully');
        }
        return back()->withInput()->with('error', 'Error while adding coupon');


    }

    public function recreate_coupon($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        $brands = Brand::orderBy('id', 'desc')->get();
        $category = Category::where('parent_id', 0)->orderBy('id', 'desc')->get();
        if ($coupon) {
            return view('vendor.coupons.recreate', ['coupon' => $coupon, 'brands' => $brands, 'category' => $category]);
        } else {
            return redirect('vendor/coupons_list')->with('error', 'Coupon not found');
        }
    }

    public function update(Request $request, $id)
    {
        $id = base64url_decode($id);
        $vendor = Auth::guard('vendor')->user();
        $store_id = $vendor->store_id;

        $coupon = Coupon::find($id);
        if ($coupon) {
            $insert_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'coupon_code' => $request->input('coupon_code'),
                'fk_store_id' => $store_id,
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
                return redirect('vendor/coupons_list')->with('success', 'Coupon recreated successfully');
            }
            return back()->withInput()->with('error', 'Error while recreating coupon');
        } else {
            return redirect('vendor/coupons_list')->with('error', 'Coupon not found');
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

        return view('vendor.coupons.edit', [
            'coupon' => $coupon,
            'brands' => $brands,
            'category' => $category
        ]);
    }

    public function update_coupon(Request $request, $id)
    {
        $id = base64url_decode($id);
        $vendor = Auth::guard('vendor')->user();
        $store_id = $vendor->store_id;

        $coupon = Coupon::find($id);
        if ($coupon) {
            if ($coupon->coupon_code != $request->input('coupon_code')) {
                $duplicate = Coupon::where('coupon_code', '=', $request->input('coupon_code'))
                    ->where('coupon_code', '!=', $coupon->coupon_code)
                    ->first();
                if ($duplicate) {
                    return redirect('vendor/coupons/edit/' . base64url_encode($id))->with('error', 'Coupon code already exist');
                }
            }
            $update_arr = [
                'type' => $request->input('type'),
                'offer_type' => $request->input('offer_type'),
                'min_amount' => $request->input('min_amount'),
                'fk_category_id' => $request->input('fk_category_id'),
                'fk_brand_id' => $request->input('fk_brand_id'),
                'fk_store_id' => $store_id,
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
                return redirect('vendor/coupons_list')->with('success', 'Coupon updated successfully');
            }
            return back()->withInput()->with('error', 'Error while updating coupon');
        } else {
            return redirect('vendor/coupons_list')->with('error', 'Coupon not found');
        }
    }

    public function show($id)
    {
        $id = base64url_decode($id);

        $coupon = Coupon::find($id);
        if ($coupon) {
            return view('vendor.coupons.show', ['coupon' => $coupon]);
        } else {
            return redirect('vendor/coupons_list')->with('error', 'Coupon not found');
        }
    }

}
