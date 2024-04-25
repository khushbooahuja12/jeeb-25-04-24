<?php

namespace App\Http\Controllers\Api\Storekeeper;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Storekeeper;
use App\Model\Order;
use App\Model\OrderProduct;
//use App\Model\StorekeeperReview;
use App\Model\StorekeeperLocation;
use App\Model\StorekeeperSubcategory;

class HomeController extends CoreApiController
{

    protected $error = true;
    protected $status_code = 404;
    protected $message = "Invalid request format";
    protected $result;
    protected $requestParams = [];
    protected $headersParams = [];
    protected $order_status = [
        'Order successfully placed',
        'Your item packed',
        'Order Accepted',
        'Order Pickedup',
        'Order delivered successfully'
    ];

    public function __construct(Request $request)
    {
        $this->result = new \stdClass();

        //getting method name
        $fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['sub_categories'];

        //setting user id which will be accessable for all functions
        if (in_array($method_name, $methods_arr)) {
            $access_token = $request->header('Authorization');
            $auth = DB::table('oauth_access_tokens')
                ->where('id', "$access_token")
                ->where('user_type', 3)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($auth) {
                $this->storekeeper_id = $auth->user_id;
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 301,
                    'message' => "Invalid access token",
                    'result' => (object) []
                ]);
            }
        }
    }

    protected function sub_categories(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $sub_categories = StorekeeperSubcategory::join('categories', 'storekeeper_sub_categories.fk_sub_category_id', 'categories.id')
                ->select('categories.*')
                ->where('storekeeper_sub_categories.fk_storekeeper_id', '=', $this->storekeeper_id)
                ->where('categories.parent_id', '!=', 0)
                ->orderBy('categories.category_name_en', 'asc')
                ->get();
            $sub_categories_arr = [];
            if ($sub_categories->count()) {
                foreach ($sub_categories as $key => $value) {
                    $sub_categories_arr[$key] = [
                        'id' => $value->id,
                        'category_name' => $lang == 'ar' ? $value->category_name_ar : $value->category_name_en,
                        'category_image' => !empty($value->getCategoryImage) ? asset('images/category_images') . '/' . $value->getCategoryImage->file_name : '',
                        'is_highlighted' => $value->is_home_screen,
                    ];
                }
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = "Sub Categories";
            $this->result = [
                'sub_categories' => $sub_categories_arr,
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
