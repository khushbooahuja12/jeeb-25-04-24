<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\ApiValidation;
use App\Model\Brand;
use App\Model\Category;

class CategoryBrandController extends CoreApiController
{

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['get_all_categories', 'get_all_brands', 'get_brand_categories'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->user_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }
        $this->validation_model = new ApiValidation();
    }

    protected function get_all_categories(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization') != '') {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                } else {
                    $user_id = '';
                }
            } else {
                $user_id = '';
            }

            $categories = Category::where('deleted', '=', 0)
                ->where('parent_id', '=', 0)
                ->orderBy('category_name_en', 'asc')
                ->get();
            $category_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $row) {
                    $sub_category_arr = [];
                    if (count($row->getSubCategory)) {
                        foreach ($row->getSubCategory as $key1 => $row1) {
                            $sub_category_arr[$key1] = [
                                'id' => $row1->id,
                                'category_name' => $lang == 'ar' ? $row1->category_name_ar : $row1->category_name_en,
                                'category_image' => !empty($row1->getCategoryImage) ? asset('images/category_images') . '/' . $row1->getCategoryImage->file_name : '',
                                'store_count' => 2
                            ];
                        }
                    }
                    $category_arr[$key] = [
                        'id' => $row->id,
                        'category_name' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                        'category_image' => !empty($row->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategoryImage->file_name : '',
                        'store_count' => 2,
                        'subcategories' => $sub_category_arr
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'categories' => $category_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد معلومات" : "No data found";
                $this->result = [
                    'categories' => $category_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_all_brands(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization') != '') {
                $access_token = $request->header('Authorization');
                $auth = DB::table('oauth_access_tokens')
                    ->where('id', "$access_token")
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($auth) {
                    $user_id = $auth->user_id;
                } else {
                    $user_id = '';
                }
            } else {
                $user_id = '';
            }

            $brands = Brand::get();
            $brand_arr = [];
            if ($brands->count()) {
                foreach ($brands as $key => $row) {
                    $brand_arr[$key] = [
                        'id' => $row->id,
                        'brand_name' => $lang == 'ar' ? $row->brand_name_ar : $row->brand_name_en,
                        'brand_image' => !empty($row->getBrandImage) ? asset('images/brand_images') . '/' . $row->getBrandImage->file_name : '',
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'brands' => $brand_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد معلومات" : "No data found";
                $this->result = [
                    'brands' => $brand_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function get_brand_categories(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['brand_id']);

            $categories = \App\Model\BrandCategoryMapping::where(['fk_brand_id' => $request->input('brand_id')])
                ->get();

            $category_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $row) {
                    $category_arr[$key] = [
                        'id' => $row->fk_category_id,
                        'category_name' => $lang == 'ar' ? $row->getCategory->category_name_ar : $row->getCategory->category_name_en,
                        'category_image' => !empty($row->getCategory->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategory->getCategoryImage->file_name : ''
                    ];
                }
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
                $this->result = [
                    'categories' => $category_arr
                ];
            } else {
                $this->error = false;
                $this->status_code = 200;
                $this->message = $lang == 'ar' ? "لا يوجد معلومات" : "No data found";
                $this->result = [
                    'categories' => $category_arr
                ];
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }

    protected function view_all_fruitsNveg(Request $request)
    {
        try {
            $lang = $request->header('lang');
            
            $vegNfruits = Category::where('deleted', '=', 0)
                ->where('parent_id', '=', 16)
                ->orderBy('category_name_en', 'asc')
                ->limit(10)
                ->get();
            $vegNfruits_arr = [];
            if ($vegNfruits->count()) {
                foreach ($vegNfruits as $key => $row) {
                    $vegNfruits_arr[$key] = [
                        'id' => $row->id,
                        'category_name' => $lang == 'ar' ? $row->category_name_ar : $row->category_name_en,
                        'category_image' => !empty($row->getCategoryImage) ? asset('images/category_images') . '/' . $row->getCategoryImage->file_name : '',
                    ];
                }
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'vegNfruits' => $vegNfruits_arr
            ];
        } catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
            ]);
        }
        $response = $this->makeJson();
        return $response;
    }
}
