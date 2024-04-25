<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\CoreApiController;
use App\Model\FavoriteRecipe;
use App\Model\IngredientTag;
use App\Model\OauthAccessToken;
use App\Model\Product;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Model\Recipe;
use App\Model\Step;
use App\Model\RecipeCategory;
use App\Model\RecipeDiet;
use App\Model\RecipeTag;
use App\Model\RecipeVariant;
use App\Model\Store;
use App\Model\User;
use App\Model\UserCart;

class RecipeController extends CoreApiController
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

        $methods_arr = [
            'mark_recipe_favorite', 'view_all_favorite_recipes'
        ];

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
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function recipes_home_static_personalized(Request $request)
    {
        try {
            
            $lang = $request->header('lang');
            $lang = $lang=='ar' ? 'ar' : 'en';
            
            if ($request->hasHeader('Authorization')) {
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

            $home_static = \App\Model\RecipeHomeStatic::where('lang','=',$lang)->orderBy('id','desc')->first();

            if ($home_static && $home_static->file_name) {
                $file_url = storage_path("app/public/".$home_static->file_name);
            } else {
                // If home_static is not available
                $error_message = $lang == 'ar' ? 'Home_static json is not uploaded' : 'Home_static json is not uploaded.';
                throw new Exception($error_message, 105);
            }

            if (file_exists($file_url)) {
                $jsonString = file_get_contents($file_url);
                $jsonData = json_decode($jsonString, true);
                if (isset($jsonData) && isset($jsonData['result'])) {
                    if ($user_id!='') {
                        // Favourite recipes
                        $favorite_recipes = Recipe::join('recipe_favorites', 'recipes.id', '=', 'recipe_favorites.fk_recipe_id')
                            ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                            ->select('recipes.*')
                            ->where('recipe_favorites.fk_user_id', '=', $user_id)
                            ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                            ->groupBy('recipes.id')
                            ->orderBy('recipes.created_at', 'asc')
                            ->limit(10)
                            ->get();
                        $favorite_arr = [];
                        if ($favorite_recipes->count()) {
                            foreach ($favorite_recipes as $key => $value) {
                                if ($lang == 'ar') {
                                    $serving_name = 'وجبة ' . $value->serving;
                                } else {
                                    $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                                }
                                $favorite_arr[$key] = [
                                    'id' => $value->id,
                                    'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                                    'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                                    'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                                    'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                                    'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                                    'serving' => $serving_name,
                                    'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                                    'nutrition' => $value->nutrition . ' kcal'
                                ];
                            }
                        }            
                        $jsonData['result']['favorite'] = $favorite_arr;
                    }
                }
                return response()->json($jsonData);
            } else {
                return response()->json([
                    'error' => true,
                    'status_code' => 200,
                    'message' => $lang=='ar' ? 'لا توجد بيانات للصفحة الرئيسية' : 'No data found for homepage',
                    'result' => []
                ]);
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

    protected function recipe_home_personalized(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization')) {
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

            // Recipe home heading
            $heading = $lang=='ar' ? 'ما الذي تخطط لطهيه اليوم؟' : 'what are you planning to cook today?';

            // Favourite recipes
            $favorite_recipes = Recipe::join('recipe_favorites', 'recipes.id', '=', 'recipe_favorites.fk_recipe_id')
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->select('recipes.*')
                ->where('recipe_favorites.fk_user_id', '=', $user_id)
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->limit(10)
                ->get();
            $favorite_arr = [];
            if ($favorite_recipes->count()) {
                foreach ($favorite_recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $favorite_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'heading' => $heading,
                'favorite' => $favorite_arr
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

    protected function recipes_home(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization')) {
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

            // Recipe home heading
            $heading = $lang=='ar' ? 'ما الذي تخطط لطهيه اليوم؟' : 'what are you planning to cook today?';

            // Recipe tags
            $tags = RecipeTag::all();
            $tags_arr = [];
            if ($tags->count()) {
                foreach ($tags as $key => $value) {
                    $tags_arr[$key] = [
                        'id' => $value->id,
                        'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                        'tag' => $value->tag
                    ];
                }
            }

            // Recipe diets
            $diets = RecipeDiet::all();
            $diets_arr = [];
            if ($diets->count()) {
                foreach ($diets as $key => $value) {
                    $diets_arr[$key] = [
                        'id' => $value->id,
                        'title' => $lang == 'ar' ? $value->title_ar : $value->title_en,
                        'tag' => $value->tag
                    ];
                }
            }

            // Favourite recipes
            $favorite_recipes = Recipe::join('recipe_favorites', 'recipes.id', '=', 'recipe_favorites.fk_recipe_id')
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->select('recipes.*')
                ->where('recipe_favorites.fk_user_id', '=', $user_id)
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->limit(10)
                ->get();
            $favorite_arr = [];
            if ($favorite_recipes->count()) {
                foreach ($favorite_recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $favorite_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            // Featured recipes
            $featured_recipes = Recipe::where(['is_featured' => 1])
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->select('recipes.*')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->limit(10)
                ->get();
            $featured_arr = [];
            if ($featured_recipes->count()) {
                foreach ($featured_recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $featured_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            // Recommended recipes
            $recommended_recipes = Recipe::where(['recommended' => 1])
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->select('recipes.*')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->limit(10)
                ->get();
            $recommended_arr = [];
            if ($recommended_recipes->count()) {
                foreach ($recommended_recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $recommended_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            // Recipe categories
            $categories = RecipeCategory::orderBy('id', 'desc')
                ->limit(4)
                ->get();
            $categories_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $value) {
                    $categories_arr[$key] = [
                        'id' => $value->id,
                        'name' => $lang == 'ar' ? $value->name_ar : $value->name_en,
                        'image' => $value->image,
                        'tag' => $value->tag
                    ];
                }
            }

            // Cooking time
            $timing = array(
                'minimum' => 0,
                'maximum' => 300,
                'interval' => 30
            );

            // Nutrition / calories
            $nutrition = array(
                'minimum' => 10,
                'maximum' => 200,
                'interval' => 10
            );

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'heading' => $heading,
                'timing' => $timing,
                'nutrition' => $nutrition,
                'tags' => $tags_arr,
                'diets' => $diets_arr,
                'featured' => $featured_arr,
                'favorite' => $favorite_arr,
                'recommended' => $recommended_arr,
                'categories' => $categories_arr
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

    protected function recipes_categories(Request $request)
    {
        try {
            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization')) {
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

            $categories = RecipeCategory::orderBy('name_en', 'asc')
                ->get();

            $categories_arr = [];
            if ($categories->count()) {
                foreach ($categories as $key => $value) {
                    $categories_arr[$key] = [
                        'id' => $value->id,
                        'name' => $lang == 'ar' ? $value->name_ar : $value->name_en,
                        'image' => $value->image
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'categories' => $categories_arr
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

    protected function view_home_recipes(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $recipes = Recipe::leftJoin('recipe_selected_categories', 'recipes.id', '=', 'recipe_selected_categories.recipe_id')
                ->select('recipes.*')
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->select('recipes.*', 'recipe_selected_categories.category_id')
                ->where('recipes.is_featured', '=', 0)
                ->where('recipes.is_home', '=', 1)
                ->groupBy('recipes.id');

            if ($request->input('category_id') != '') {
                $recipes = $recipes->where('recipe_selected_categories.category_id', '=', $request->input('category_id'));
            }

            $recipes = $recipes
                ->orderBy('recipes.created_at', 'asc')
                ->get();

            $recipes_arr = [];
            if ($recipes->count()) {
                foreach ($recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $recipes_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'recipes' => $recipes_arr,
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

    protected function view_all_recipes(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $recipes = Recipe::leftJoin('recipe_selected_categories', 'recipes.id', '=', 'recipe_selected_categories.recipe_id')
                ->select('recipes.*')
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->select('recipes.*', 'recipe_selected_categories.category_id');

            if ($request->input('category_id') != '') {
                $recipes = $recipes->where('recipe_selected_categories.category_id', '=', $request->input('category_id'));
            }

            $recipes = $recipes
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'desc')
                ->get();

            $recipes_arr = [];
            if ($recipes->count()) {
                foreach ($recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $recipes_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'recipes' => $recipes_arr,
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

    protected function recipe_detail(Request $request)
    {
        try {
            $this->required_input($request->input(), ['id']);

            $lang = $request->header('lang');

            if ($request->hasHeader('Authorization')) {
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

                $latLong = OauthAccessToken::where(['id' => $access_token])->first();
                if ($latLong) {
                    $latitude = $latLong->latitude;
                    $longitude = $latLong->longitude;
                } else {
                    $latitude = '';
                    $longitude = '';
                }
            } else {
                $user_id = '';
            }

            $recipe = Recipe::where(['id'=>$request->input('id'),'deleted'=>0])->first();

            $ingredient_arr = [];
            $steps_arr = [];
            $variant_arr = [];

            if ($recipe) {
                $isFavorite = FavoriteRecipe::where(['fk_user_id' => $user_id, 'fk_recipe_id' => $recipe->id])->first();
                if ($lang == 'ar') {
                    $serving_name = 'وجبة ' . $recipe->serving;
                } else {
                    $serving_name = $recipe->serving == 1 ? $recipe->serving . ' serving' : $recipe->serving . ' servings';
                }
                $recipes_arr = [
                    'id' => $recipe->id,
                    'recipe_image' => $recipe->getRecipeImage ? asset('/') . $recipe->getRecipeImage->file_path . $recipe->getRecipeImage->file_name : '',
                    'recipe_name' => $lang == 'ar' ? $recipe->recipe_name_ar : $recipe->recipe_name_en,
                    'recipe_desc' => $lang == 'ar' ? $recipe->recipe_desc_ar : $recipe->recipe_desc_en,
                    'created_at' => date('Y-m-d H:i:s', strtotime($recipe->created_at)),
                    'duration' => $lang == 'ar' ? 'دقيقة ' . $recipe->duration : $recipe->duration . ' minutes',
                    'serving' => $serving_name,
                    'home_tag' => $lang == 'ar' ? $recipe->homepage_tag_ar : $recipe->homepage_tag_en,
                    'nutrition' => $recipe->nutrition . ' kcal',
                    'is_favorite' => $isFavorite ? 1 : 0
                ];

                $ingredients = IngredientTag::where(['recipe_id' => $request->input('id')])
                    ->orderBy('id', 'asc')
                    ->get();

                if ($ingredients->count()) {
                    foreach ($ingredients as $key => $value) {
                        $ingredient_arr[$key] = [
                            'id' => $value->id,
                            'desc' => $lang == 'ar' ? $value->desc_ar : $value->desc_en,
                            // 'product' => $product_arr ? $product_arr[0] : (object) []
                            'tag' => $value->tag,
                            'image_url' => $value->image_url ? $value->image_url : "",
                            'pantry_item' => $value->pantry_item
                        ];
                    }
                }

                $steps = Step::where(['recipe_id' => $request->input('id')])
                    ->orderBy('id', 'asc')
                    ->get();
                if ($steps->count()) {
                    foreach ($steps as $key => $value) {
                        // ingredients
                        $ingredient_vl = json_decode($value->ingredients);
                        $ingredient_items = [];
                        $ingredient_key = 0;
                        if (is_array($ingredient_vl) && !empty($ingredient_vl)) {
                            foreach ($ingredient_vl as $key2 => $value2) {
                                $ingredient_vl[$key2]->id = $value2->id ? (int) $value2->id : 0;
                                $ingredient_vl[$key2]->desc = $lang == 'ar' ? $value2->desc_ar : $value2->desc_en;
                                $ingredient_vl[$key2]->image_url = $value2->image_url ? $value2->image_url : '';
                                unset($ingredient_vl[$key2]->desc_en);
                                unset($ingredient_vl[$key2]->desc_ar);
                                $ingredient_vl[$key2]->pantry_item = isset($value2->pantry_item) ? (int) $value2->pantry_item : 0;
                                $ingredient_items[$ingredient_key] = $ingredient_vl[$key2];
                                $ingredient_key++;
                            }
                        }
                        // set and return
                        $steps_arr[$key] = [
                            'id' => $value->id,
                            'desc' => $lang == 'ar' ? $value->step_ar : $value->step_en,
                            'time' => $value->time,
                            'ingredients' => $ingredient_vl
                        ];
                    }
                }

                $variants = RecipeVariant::where(['fk_recipe_id' => $request->input('id')])
                ->orderBy('id', 'asc')
                ->get();
                if ($variants->count()) {
                    foreach ($variants as $key => $value) {
                        $ingredient_vl = json_decode($value->ingredients);
                        $ingredient_items = [];
                        $ingredient_key = 0;
                        if (is_array($ingredient_vl) && !empty($ingredient_vl)) {
                            foreach ($ingredient_vl as $key2 => $value2) {
                                $ingredient_vl[$key2]->id = $value2->id ? (int) $value2->id : 0;
                                $ingredient_vl[$key2]->desc = $lang == 'ar' ? $value2->desc_ar : $value2->desc_en;
                                $ingredient_vl[$key2]->image_url = $value2->image_url ? $value2->image_url : '';
                                unset($ingredient_vl[$key2]->desc_en);
                                unset($ingredient_vl[$key2]->desc_ar);
                                $ingredient_vl[$key2]->pantry_item = isset($value2->pantry_item) ? (int) $value2->pantry_item : 0;
                                $ingredient_items[$ingredient_key] = $ingredient_vl[$key2];
                                $ingredient_key++;
                            }
                        }
                        $pantry_items_vl = json_decode($value->pantry_items);
                        $pantry_items = [];
                        $pantry_item_key = 0;
                        if (is_array($pantry_items_vl) && !empty($pantry_items_vl)) {
                            foreach ($pantry_items_vl as $key2 => $value2) {
                                $pantry_items_vl[$key2]->id = $value2->id ? (int) $value2->id : 0;
                                $pantry_items_vl[$key2]->desc = $lang == 'ar' ? $value2->desc_ar : $value2->desc_en;
                                $pantry_items_vl[$key2]->image_url = $value2->image_url ? $value2->image_url : '';
                                unset($pantry_items_vl[$key2]->desc_en);
                                unset($pantry_items_vl[$key2]->desc_ar);
                                $pantry_items_vl[$key2]->pantry_item = isset($value2->pantry_item) ? (int) $value2->pantry_item : 0;
                                $pantry_items_vl[$key2]->base_product_id = isset($value2->base_product_id) ? strval($value2->base_product_id) : "";
                                $pantry_items[$pantry_item_key] = $pantry_items_vl[$key2];
                                $pantry_item_key++;
                            }
                        }
                        $variant_arr[$key] = [
                            'id' => $value->id,
                            'serving' => $value->serving,
                            'price' => $value->price,
                            'product_id' => $value->fk_product_id,
                            'base_product_id' => $value->base_product_id ? $value->base_product_id : 0,
                            'ingredient' => $ingredient_items,
                            'pantry_items' => $pantry_items,
                        ];
                    }
                }
            } else {
                $recipes_arr = (object) [];
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'recipe' => $recipes_arr,
                'ingredient' => $ingredient_arr,
                'steps' => $steps_arr,
                'variants' => $variant_arr
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

    protected function mark_recipe_favorite(Request $request)
    {
        try {
            $lang = $request->header('lang');
            $this->required_input($request->input(), ['recipe_id']);

            $exist = FavoriteRecipe::where(['fk_user_id' => $this->user_id, 'fk_recipe_id' => $request->input('recipe_id')])->first();
            if ($exist) {
                FavoriteRecipe::find($exist->id)->delete();
                $message = $lang == 'ar' ? "تمت إزالته من قائمة المفضلة" : "Removed from favorite list";
                $is_favorite = 0;
            } else {
                $insert_arr = [
                    'fk_user_id' => $this->user_id,
                    'fk_recipe_id' => $request->input('recipe_id')
                ];
                FavoriteRecipe::create($insert_arr);
                $message = $lang == 'ar' ? "تمت إضافته إلى قائمة المفضلة" : "Added into the favorite list";
                $is_favorite = 1;
            }
            $this->error = false;
            $this->status_code = 200;
            $this->message = $message;
            $this->result = [
                'is_favorite' => $is_favorite
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

    protected function view_all_favorite_recipes(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $favorite_recipes = Recipe::join('recipe_favorites', 'recipes.id', '=', 'recipe_favorites.fk_recipe_id')
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->select('recipes.*')
                ->where('recipe_favorites.fk_user_id', '=', $this->user_id)
                // ->where('recipes.is_home', '=', 1)
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->get();

            $favorite_recipes_arr = [];
            if ($favorite_recipes->count()) {
                foreach ($favorite_recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $favorite_recipes_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'recipes' => $favorite_recipes_arr,
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

    protected function view_all_featured_recipes(Request $request)
    {
        try {
            $lang = $request->header('lang');

            $recipes = Recipe::where(['is_featured' => 1])
                ->join('recipe_variants', 'recipes.id', '=', 'recipe_variants.fk_recipe_id')
                ->select('recipes.*')
                ->where(['recipes.deleted'=>0,'recipes.active'=>1])
                ->groupBy('recipes.id')
                ->orderBy('recipes.created_at', 'asc')
                ->get();

            $recipes_arr = [];
            if ($recipes->count()) {
                foreach ($recipes as $key => $value) {
                    if ($lang == 'ar') {
                        $serving_name = 'وجبة ' . $value->serving;
                    } else {
                        $serving_name = $value->serving == 1 ? $value->serving . ' serving' : $value->serving . ' servings';
                    }
                    $recipes_arr[$key] = [
                        'id' => $value->id,
                        'recipe_image' => $value->getRecipeImage ? asset('/') . $value->getRecipeImage->file_path . $value->getRecipeImage->file_name : '',
                        'recipe_name' => $lang == 'ar' ? $value->recipe_name_ar : $value->recipe_name_en,
                        'recipe_desc' => $lang == 'ar' ? $value->recipe_desc_ar : $value->recipe_desc_en,
                        'created_at' => date('Y-m-d H:i:s', strtotime($value->created_at)),
                        'duration' => $lang == 'ar' ? 'دقيقة ' . $value->duration : $value->duration . ' minutes',
                        'serving' => $serving_name,
                        'home_tag' => $lang == 'ar' ? $value->homepage_tag_ar : $value->homepage_tag_en,
                        'nutrition' => $value->nutrition . ' kcal'
                    ];
                }
            }

            $this->error = false;
            $this->status_code = 200;
            $this->message = $lang =='ar' ? "نجحت العملية" : "Success";
            $this->result = [
                'recipes' => $recipes_arr,
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
