<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use App\Model\IngredientTag;
use App\Model\Recipe;
use App\Model\RecipeCategory;
use App\Model\Step;
use App\Model\CategoryRecipe;
use App\Model\Product;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\RecipeVariant;
use App\Model\RecipeHomeStatic;
use App\Jobs\UpdateRecipeSingleColumn;
use Exception;
use Illuminate\Http\Request;

class RecipeController extends CoreApiController
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');

        if (!empty($filter)) {
            $recipes = Recipe::sortable(['id' => 'desc'])
                ->where('recipe_name_en', 'like', '%' . $filter . '%')
                ->where('deleted', '=', 0)
                ->paginate(50);
        } else {
            $recipes = Recipe::sortable(['id' => 'desc'])
                ->where('deleted', '=', 0)
                ->paginate(50);
        }
        $recipes->appends(['filter' => $filter]);

        return view('admin/recipe/index', [
            'recipes'  => $recipes,
            'filter' => $filter
        ]);
    }
    
    protected function app_homepage(Request $request)
    {
        $home_static_ens = RecipeHomeStatic::where('lang','=','en')->orderBy('id', 'desc')->limit(10)->get();
        $home_static_ars = RecipeHomeStatic::where('lang','=','ar')->orderBy('id', 'desc')->limit(10)->get();
        return view('admin.recipe.app_homepage', [
            'home_static_ens' => $home_static_ens,
            'home_static_ars' => $home_static_ars
        ]);
    }

    protected function home_static_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "recipe_home_static_json/"; 

            $file_name = time().'_home_static_1.json';
            $path = \Storage::putFileAs('public/recipe_home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = RecipeHomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/recipes/home')->with('success', 'Home static json added successfully');

    }

    public function view_recipe_variant($id)
    {
        return view('admin.recipe.view_recipe_variant', [
            'recipe'  => Recipe::find($id),
            'recipe_variants' => RecipeVariant::where('fk_recipe_id', $id)->get()
        ]);
    }

    public function create_recipe_variant($id)
    {
        return view('admin.recipe.create_recipe_variant', [
            'recipe'  => Recipe::find($id),
            'ingredient_tags' => IngredientTag::where('recipe_id', $id)->get()
        ]);
    }

    public function store_recipe_variant(Request $request)
    {
        $request->validate([
            'serving' => 'required|max:254',
            'price' => 'required'
        ]);

        $recipe = Recipe::find($request->input('id'));
        if (!$recipe) {
            return back()->withInput()->with('error', 'Recipe not found!');
        }

        // Create base product
        $insert_arr = [
            'product_type' => 'recipe',
            'recipe_id' => $recipe->id,
            'parent_id' => 0,
            'product_name_en' => $recipe->recipe_name_en,
            'product_name_ar' => $recipe->recipe_name_ar,
            'product_image_url' => $recipe->recipe_img_url,
            'product_image' => $recipe->recipe_img,
            'base_price' => $request->input('price'),
            '_tags' => '',
            'stock' => 1,
            'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
        ];
        $base_product = BaseProduct::create($insert_arr);
        if (!$base_product) {
            return back()->withInput()->with('error', 'Error while adding base product');
        }
        $insert_arr = [
            'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
            'allow_margin' => 0,
            'margin' => 0,
            'distributor_price' => $request->input('distributor_price') ?? 0,
            'product_price' => $request->input('price'),
            'stock' => 1,
            'is_active' => 1,
            'fk_product_id' => $base_product->id,
            'fk_store_id' => env("RECIPIE_STORE_ID"),
        ];
        $base_product_store = BaseProductStore::create($insert_arr);
        if (!$base_product_store) {
            return back()->withInput()->with('error', 'Error while adding base product');
        }
        
        // Create product
        $insert_arr = [
            'product_type' => 'recipe',
            'recipe_id' => $recipe->id,
            'parent_id' => 0,
            'fk_company_id' => 0,
            'product_name_en' => $recipe->recipe_name_en,
            'product_name_ar' => $recipe->recipe_name_ar,
            'product_image_url' => $recipe->recipe_img_url,
            'product_image' => $recipe->recipe_img,
            'product_price' => $request->input('price'),
            'distributor_price' => $request->input('distributor_price') ?? 0,
            '_tags' => '',
            'stock' => 1,
            'store1' => 1,
            'store2' => 1,
            'store3' => 1,
            'store4' => 1,
            'store5' => 1,
            'store6' => 1,
            'store7' => 1,
            'store8' => 1,
            'store9' => 1,
            'store10' => 1,
            'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
            'store1_distributor_price' => $request->input('distributor_price') ?? 0,
            'store2_distributor_price' => $request->input('distributor_price') ?? 0,
            'store3_distributor_price' => $request->input('distributor_price') ?? 0,
            'store4_distributor_price' => $request->input('distributor_price') ?? 0,
            'store5_distributor_price' => $request->input('distributor_price') ?? 0,
            'store6_distributor_price' => $request->input('distributor_price') ?? 0,
            'store7_distributor_price' => $request->input('distributor_price') ?? 0,
            'store8_distributor_price' => $request->input('distributor_price') ?? 0,
            'store9_distributor_price' => $request->input('distributor_price') ?? 0,
            'store10_distributor_price' => $request->input('distributor_price') ?? 0,
            'store1_price' => $request->input('price'),
            'store2_price' => $request->input('price'),
            'store3_price' => $request->input('price'),
            'store4_price' => $request->input('price'),
            'store5_price' => $request->input('price'),
            'store6_price' => $request->input('price'),
            'store7_price' => $request->input('price'),
            'store8_price' => $request->input('price'),
            'store9_price' => $request->input('price'),
            'store10_price' => $request->input('price'),
        ];

        $product = Product::create($insert_arr);
        if (!$product) {
            return back()->withInput()->with('error', 'Error while adding Product');
        }
        
        // Add the variant with product ID
        $ingredient_id = $request->input('ingredient_id');
        $ingredient_en = $request->input('ingredient_en');
        $ingredient_ar = $request->input('ingredient_ar');
        $ingredient_tag = $request->input('ingredient_tag');
        $ingredient_image_url = $request->input('ingredient_image_url');
        $ingredient_pantry_item = $request->input('ingredient_pantry_item');
        $ingredient_quantity = $request->input('ingredient_quantity');

        $ingredients = [];
        if ($ingredient_id != null && is_array($ingredient_id)) {
            for ($i = 0; $i < count($ingredient_id); $i++) {
                $ingredients[] = array(
                    'id' => $ingredient_id[$i],
                    'desc_en' => $ingredient_en[$i],
                    'desc_ar' => $ingredient_ar[$i],
                    'tag' => $ingredient_tag[$i],
                    'image_url' => $ingredient_image_url[$i],
                    'pantry_item' => $ingredient_pantry_item[$i],
                    'quantity' => $ingredient_quantity[$i],
                );
            }
        }

        // Add the variant with product ID
        $pantry_item_id = $request->input('pantry_item_id');
        $pantry_item_en = $request->input('pantry_item_en');
        $pantry_item_ar = $request->input('pantry_item_ar');
        $pantry_item_tag = $request->input('pantry_item_tag');
        $pantry_item_image_url = $request->input('pantry_item_image_url');
        $pantry_item_pantry_item = $request->input('pantry_item_pantry_item');
        $pantry_item_quantity = $request->input('pantry_item_quantity');
        $pantry_item_price = $request->input('pantry_item_price');
        $pantry_item_unit = $request->input('pantry_item_unit');
        $pantry_item_product_id = $request->input('pantry_item_product_id');
        $pantry_item_base_product_id = $request->input('pantry_item_base_product_id');
        $pantry_item_base_product_store_id = $request->input('pantry_item_base_product_store_id');

        $pantry_items = [];
        if ($pantry_item_id != null && is_array($pantry_item_id)) {
            for ($i = 0; $i < count($pantry_item_id); $i++) {
                $pantry_items[] = array(
                    'id' => $pantry_item_id[$i],
                    'desc_en' => $pantry_item_en[$i],
                    'desc_ar' => $pantry_item_ar[$i],
                    'tag' => $pantry_item_tag[$i],
                    'image_url' => $pantry_item_image_url[$i],
                    'pantry_item' => $pantry_item_pantry_item[$i],
                    'quantity' => $pantry_item_quantity[$i],
                    'price' => $pantry_item_price[$i],
                    'unit' => $pantry_item_unit[$i],
                    'product_id' => $pantry_item_product_id[$i],
                    'base_product_id' => $pantry_item_base_product_id[$i],
                    'base_product_store_id' => $pantry_item_base_product_store_id[$i]
                );
            }
        }

        $insertArr = [
            'fk_recipe_id' => $recipe->id,
            'fk_product_id' => $product->id,
            'base_product_id' => $base_product->id,
            'base_product_store_id' => $base_product_store->id,
            'serving' => $request->input('serving'),
            'price' => $request->input('price'),
            'ingredients' =>  json_encode($ingredients),
            'pantry_items' =>  json_encode($pantry_items)
        ];
        $recipe_variant = RecipeVariant::create($insertArr);

        if (!$recipe_variant) {
            return back()->withInput()->with('error', 'Error while adding recipe');
        }
        Product::find($product->id)->update(['recipe_variant_id' => $recipe_variant->id]);
        BaseProduct::find($base_product->id)->update(['recipe_variant_id' => $recipe_variant->id]);
        return redirect('admin/recipes/edit/'.$request->input('id').'/view_recipe_variant')->with('success', 'Recipe variant added successfully');
    }

    public function destroy_recipe_variant(Request $request, $id)
    {
        try {
            $recipe_variant = RecipeVariant::find($id);
            if ($recipe_variant && $recipe_variant->fk_product_id) {
                Product::destroy($recipe_variant->fk_product_id);
            }
            RecipeVariant::destroy($id);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error while adding recipe');
        }
        return redirect('admin/recipes/edit/'.$recipe_variant->fk_recipe_id.'/view_recipe_variant')->with('success', 'Recipe deleted successfully');
    }

    public function create()
    {
        return view('admin.recipe.create', [
            'categories'  => RecipeCategory::all()
        ]);
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'recipe_name_en' => 'required|max:254',
        //     'recipe_name_ar' => 'required|max:254',
        //     'recipe_desc_en' => 'max:999',
        //     'recipe_desc_ar' => 'max:999',
        //     'recipe_img' => 'max:1024|mimes:jpeg,jpg,png',
        // ]);

        // $product_ids1 = $request->input('product_ids1');
        // $quantities1 = $request->input('quantities1');
        // $product_ids2 = $request->input('product_ids2');
        // $quantities2 = $request->input('quantities2');
        // $product_ids3 = $request->input('product_ids3');
        // $quantities3 = $request->input('quantities3');
        // $desc_en = $request->input('desc_en');
        // $desc_ar = $request->input('desc_ar');
        
        $recipe_img = $request->file('recipe_img');

        // $ingredient_en = $request->input('ingredient_en');
        // $ingredient_ar = $request->input('ingredient_ar');
        // $ingredient_tag = $request->input('ingredient_tag');
        // $ingredient_img = $request->file('ingredient_img');

        // $steps_en = $request->input('steps_en');
        // $steps_ar = $request->input('steps_ar');
        // $time = $request->input('time');
        $categories = $request->input('categories');

        $insertArr = [
            'recipe_name_en' => $request->input('recipe_name_en'),
            'recipe_name_ar' => $request->input('recipe_name_ar'),
            'duration' => $request->input('duration'),
            'serving' => $request->input('serving'),
            'nutrition' => $request->input('nutrition'),
            'recipe_desc_en' => $request->input('recipe_desc_en'),
            'recipe_desc_ar' => $request->input('recipe_desc_ar'),
            '_tags' => $request->input('tags'),
        ];

        // Upload recipe image
        if ($request->hasFile('recipe_img')) {
            $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/"));
            $images_url_base = "storage/images/recipes/"; 

            $path = "/images/recipes/";
            $filenameWithExt = $recipe_img->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $recipe_img->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $recipe_img->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $insertArr['recipe_img_url'] = env('APP_URL') . $images_url_base . $fileNameToStore;
                $req = [
                    'file_path' => '/'.$images_url_base,
                    'file_name' => $fileNameToStore,
                    'file_ext' => $extension
                ];

                $returnArr = $this->insertFile($req);
                $insertArr['recipe_img'] = $returnArr->id;
            endif;
        }

        $recipe = Recipe::create($insertArr);

        if ($recipe) {
            // insert other table records i.e. ingredients and steps
            // if ($product_ids1 != null) {
            //     for ($i = 0; $i < count($request->input('product_ids1')); $i++) {
            //         Ingredient::create([
            //             'recipe_id'   => $recipe->id,
            //             'product_id'  => $product_ids1[$i],
            //             'quantity'  => $quantities1[$i],
            //             'product_id2'  => $product_ids2[$i],
            //             'quantity2'  => $quantities2[$i],
            //             'product_id3'  => $product_ids3[$i],
            //             'quantity3'  => $quantities3[$i],
            //             'desc_en'  => $desc_en[$i],
            //             'desc_ar'  => $desc_ar[$i],
            //         ]);
            //     }
            // }
            // if ($ingredient_en != null) {
            //     for ($i = 0; $i < count($request->input('ingredient_en')); $i++) {
            //         $image_url = '';
            //         if (isset($ingredient_img[$i]) && $ingredient_img[$i]) {
            //             $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/$recipe->id/ingredients/"));
            //             $images_url_base = "storage/images/recipes/$recipe->id/ingredients/"; 
        
            //             $path = "/images/recipes/$recipe->id/ingredients/";
            //             $filenameWithExt = $ingredient_img[$i]->getClientOriginalName();
            //             $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            //             $extension = $ingredient_img[$i]->getClientOriginalExtension();
            //             $fileNameToStore = $filename.'_'.time().'.'.$extension;
            //             // Upload Image
            //             $check = $ingredient_img[$i]->storeAs('public/'.$path,$fileNameToStore);
            //             if ($check) :
            //                 $image_url = env('APP_URL') . $images_url_base . $fileNameToStore;
            //             endif;
            //         }
        
            //         IngredientTag::create([
            //             'recipe_id'  => $recipe->id,
            //             'desc_en'    => $ingredient_en[$i],
            //             'desc_ar'    => $ingredient_ar[$i],
            //             'tag'    => $ingredient_tag[$i],
            //             'image_url'    => $image_url
            //         ]);
            //     }
            // }
            // if ($steps_en != null) {
            //     for ($i = 0; $i < count($request->input('steps_en')); $i++) {
            //         Step::create([
            //             'recipe_id'  => $recipe->id,
            //             'step_en'    => $steps_en[$i],
            //             'step_ar'    => $steps_ar[$i],
            //             'time'       => $time[$i]
            //         ]);
            //     }
            // }
            CategoryRecipe::where('recipe_id', $recipe->id)->delete();

            if ($categories != null) {
                foreach ($categories as $category) {
                    CategoryRecipe::create([
                        'category_id' => $category,
                        'recipe_id'   => $recipe->id
                    ]);
                }
            }
        } else {
            return back()->withInput()->with('error', 'Error while adding recipe');
        }
        return redirect('admin/recipes/edit/'.($recipe->id))->with('success', 'Recipe added successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recipe = Recipe::find($id);

        $categories_ids = CategoryRecipe::where('recipe_id', $id)->pluck('category_id')->toArray();
        $added_categories = RecipeCategory::whereIn('id', $categories_ids)->pluck('id')->toArray();
        // $ingredients = Ingredient::where('recipe_id', $id)->get();
        $ingredient_tags = IngredientTag::where('recipe_id', $id)->get();
        $steps = Step::where('recipe_id', $id)->get();
        $recipe_variants = RecipeVariant::where('fk_recipe_id', $id)->get();

        $products = Product::select('id', 'product_name_en', 'unit')
            ->where('deleted', '=', 0)
            ->where('parent_id', '=', 0)
            ->orderBy('product_name_en', 'asc')
            ->get();

        return view('admin.recipe.edit', [
            'recipe'  => $recipe,
            'ingredient_tags' => $ingredient_tags,
            'steps'    => $steps,
            'recipe_variants'    => $recipe_variants,
            'categories'   => RecipeCategory::all(),
            'added_categories' => $added_categories,
            'products' => $products
        ]);
    }

    public function update(Request $request, $id)
    {
        $recipe = Recipe::find($id);
        $recipe_img = $request->file('recipe_img');

        $categories = $request->input('categories');

        $updateArr = [
            'recipe_name_en' => $request->input('recipe_name_en'),
            'recipe_name_ar' => $request->input('recipe_name_ar'),
            'duration' => $request->input('duration'),
            'serving' => $request->input('serving'),
            'nutrition' => $request->input('nutrition'),
            'recipe_desc_en' => $request->input('recipe_desc_en'),
            'recipe_desc_ar' => $request->input('recipe_desc_ar'),
            '_tags' => $request->input('tags'),
            'homepage_tag_en' => $request->input('homepage_tag_en'),
            'homepage_tag_ar' => $request->input('homepage_tag_ar'),
        ];
        
        // Upload recipe image
        if ($request->hasFile('recipe_img')) {
            $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/"));
            $images_url_base = "storage/images/recipes/"; 

            $path = "/images/recipes/";
            $filenameWithExt = $recipe_img->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $recipe_img->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            // Upload Image
            $check = $recipe_img->storeAs('public/'.$path,$fileNameToStore);
            if ($check) :
                $updateArr['recipe_img_url'] = env('APP_URL') . $images_url_base . $fileNameToStore;
                $req = [
                    'file_path' => '/'.$images_url_base,
                    'file_name' => $fileNameToStore,
                    'file_ext' => $extension
                ];

                $returnArr = $this->insertFile($req);
                $updateArr['recipe_img'] = $returnArr->id;
            endif;
        }

        $update = Recipe::find($id)->update($updateArr);
        if ($update) {
            
            CategoryRecipe::where('recipe_id', $id)->delete();
            if ($categories != null) {
                foreach ($categories as $category) {
                    CategoryRecipe::create([
                        'category_id' => $category,
                        'recipe_id'     => $id
                    ]);
                }
            }
        } else {
            return back()->withInput()->with('error', 'Error while updating recipe');
        }
        return redirect('admin/recipes')->with('success', 'Recipe updated successfully');
    }

    // Add ingredient via AJAX
    public function add_ingredient_save(Request $request) {
        // Validation
        if (
            $request->input('recipe_id') == '' ||
            $request->input('ingredient_en')=='' || 
            $request->input('ingredient_ar')=='' 
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        if ($request->input('pantry_item')==1) {
            if (
                $request->input('ingredient_unit') == '' ||
                $request->input('ingredient_price')=='' 
            ) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Unit and price are required fields!']);
            }
        }
        // Process
        $recipe_id = $request->input('recipe_id');
        $recipe = Recipe::find($recipe_id);
        if ($recipe) {
            $create_arr = [
                'recipe_id'  => $request->input('recipe_id'),
                'desc_en'    => $request->input('ingredient_en'),
                'desc_ar'    => $request->input('ingredient_ar'),
                'tag'    => $request->input('ingredient_tag'),
                'image_url'    => $request->input('ingredient_img_url'),
                'pantry_item'    => $request->input('pantry_item'),
                'unit'    => $request->input('ingredient_unit'),
                'price'    => $request->input('ingredient_price'),
                'fk_product_id'    => $request->input('product_id')
            ];

            if ($request->hasFile('ingredient_img')) {
                $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/$recipe_id/ingredients/"));
                $images_url_base = "storage/images/recipes/$recipe_id/ingredients/"; 

                $ingredient_img = $request->file('ingredient_img');
                $path = "/images/recipes/$recipe_id/ingredients/";
                $filenameWithExt = $ingredient_img->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $ingredient_img->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $ingredient_img->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $create_arr['image_url'] = env('APP_URL') . $images_url_base . $fileNameToStore;
                endif;
            }

            $ingredient = IngredientTag::create($create_arr);
        
            if ($ingredient) {
                // Create product
                if ($ingredient->pantry_item==1) {
                    // Create base product
                    $insert_arr = [
                        'product_type' => 'pantry_item',
                        'recipe_id' => $recipe->id,
                        'recipe_ingredient_id' => $ingredient->id,
                        'parent_id' => $recipe->id,
                        'product_name_en' => $ingredient->desc_en,
                        'product_name_ar' => $ingredient->desc_ar,
                        'product_image_url' => $ingredient->image_url,
                        'product_image' => 0,
                        'base_price' => $ingredient->price,
                        'product_store_price' => $ingredient->price,
                        'fk_store_id' => env("RECIPIE_STORE_ID"),
                        'product_store_stock' => 100,
                        '_tags' => '',
                        'stock' => 1,
                        'unit' => $ingredient->unit,
                        'allow_margin' => 0
                    ];
                    $base_product = BaseProduct::create($insert_arr);
                    if (!$base_product) {
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $ingredient]);
                    }
                    $insert_arr = [
                        'product_type' => $base_product->product_type,
                        'recipe_id' => $base_product->recipe_id,
                        'recipe_ingredient_id' => $base_product->recipe_ingredient_id,
                        'parent_id' => $base_product->parent_id,
                        'unit' => $base_product->unit,
                        'allow_margin' => 0,
                        'margin' => 0,
                        'product_name_en' => $base_product->product_name_en,
                        'product_name_ar' => $base_product->product_name_ar,
                        'product_image_url' => $base_product->product_image_url,
                        'product_image' => 0,
                        'product_distributor_price' => $request->input('distributor_price') ?? 0,
                        'base_price' => $base_product->base_price,
                        'product_store_price' => $base_product->product_store_price,
                        'product_store_stock' => $base_product->product_store_stock,
                        'stock' => $base_product->stock,
                        'is_active' => 1,
                        'fk_product_id' => $base_product->id,
                        'fk_store_id' => env("RECIPIE_STORE_ID"),
                    ];
                    $base_product_store = BaseProductStore::create($insert_arr);
                    if (!$base_product_store) {
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $ingredient]);
                    }else{
                        $base_product->update(['fk_product_store_id' => $base_product_store->id]);
                    }
        
                    // Create Product
                    $insert_arr = [
                        'product_type' => 'pantry_item',
                        'recipe_id' => $recipe->id,
                        'recipe_ingredient_id' => $ingredient->id,
                        'parent_id' => $recipe->id,
                        'fk_company_id' => 0,
                        'product_name_en' => $ingredient->desc_en,
                        'product_name_ar' => $ingredient->desc_ar,
                        'product_image_url' => $ingredient->image_url,
                        'product_image' => 0,
                        'product_price' => $ingredient->price,
                        'distributor_price' => $request->input('distributor_price') ?? 0,
                        '_tags' => '',
                        'stock' => 1,
                        'store1' => 1,
                        'store2' => 1,
                        'store3' => 1,
                        'store4' => 1,
                        'store5' => 1,
                        'store6' => 1,
                        'store7' => 1,
                        'store8' => 1,
                        'store9' => 1,
                        'store10' => 1,
                        'unit' => $ingredient->unit,
                        'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                        'store1_price' => $ingredient->price,
                        'store2_price' => $ingredient->price,
                        'store3_price' => $ingredient->price,
                        'store4_price' => $ingredient->price,
                        'store5_price' => $ingredient->price,
                        'store6_price' => $ingredient->price,
                        'store7_price' => $ingredient->price,
                        'store8_price' => $ingredient->price,
                        'store9_price' => $ingredient->price,
                        'store10_price' => $ingredient->price,
                    ];
                    $product = Product::create($insert_arr);
                    if (!$product) {
                        return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding Product', 'data' => $ingredient]);
                    }
                    $ingredient->update([
                        'fk_product_id'=>$product->id,
                        'base_product_id' => $base_product->id,
                        'base_product_store_id' => $base_product_store->id
                    ]);
                }
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $ingredient]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Edit ingredient via AJAX
    public function edit_ingredient_save(Request $request) {
        // Validation
        if (
            $request->input('id')=='' ||
            $request->input('recipe_id') == '' ||
            $request->input('ingredient_en')=='' || 
            $request->input('ingredient_ar')=='' 
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        if ($request->input('pantry_item')==1) {
            if (
                $request->input('ingredient_unit') == '' ||
                $request->input('ingredient_price')=='' 
            ) {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Unit and price are required fields!']);
            }
        }
        // Process
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $ingredient = IngredientTag::find($id);
        $recipe = Recipe::find($recipe_id);
        if ($ingredient && $recipe) {
            $update_arr = [
                'recipe_id'  => $request->input('recipe_id'),
                'desc_en'    => $request->input('ingredient_en'),
                'desc_ar'    => $request->input('ingredient_ar'),
                'tag'    => $request->input('ingredient_tag'),
                'image_url'    => $request->input('ingredient_img_url'),
                'unit'    => $request->input('ingredient_unit'),
                'price'    => $request->input('ingredient_price'),
                'fk_product_id'    => $request->input('product_id')
            ];

            if ($request->hasFile('ingredient_img')) {
                $images_path = str_replace('\\', '/', storage_path("app/public/images/recipes/$recipe_id/ingredients/"));
                $images_url_base = "storage/images/recipes/$recipe_id/ingredients/"; 

                $ingredient_img = $request->file('ingredient_img');
                $path = "/images/recipes/$recipe_id/ingredients/";
                $filenameWithExt = $ingredient_img->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $ingredient_img->getClientOriginalExtension();
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                $check = $ingredient_img->storeAs('public/'.$path,$fileNameToStore);
                if ($check) :
                    $update_arr['image_url'] = env('APP_URL') . $images_url_base . $fileNameToStore;
                endif;
            }

            $update = $ingredient->update($update_arr);
        
            if ($update) {
                if ($ingredient->pantry_item==1) {
                    
                    // ---------------------------
                    // Setting up base product
                    // ---------------------------
                    $base_product = BaseProduct::find($ingredient->base_product_id);
                    if ($base_product) {
                        // Update base product
                        $update_arr = [
                            'product_type' => 'pantry_item',
                            'recipe_id' => $recipe->id,
                            'recipe_ingredient_id' => $ingredient->id,
                            'parent_id' => $recipe->id,
                            'product_name_en' => $ingredient->desc_en,
                            'product_name_ar' => $ingredient->desc_ar,
                            'product_image_url' => $ingredient->image_url,
                            'product_image' => 0,
                            'base_price' => $ingredient->price,
                            'product_store_price' => $ingredient->price,
                            'fk_store_id' => env("RECIPIE_STORE_ID"),
                            'product_store_stock' => 100,
                            '_tags' => '',
                            'stock' => 1,
                            'unit' => $ingredient->unit,
                            'allow_margin' => 0
                        ];
                        $base_product_update = $base_product->update($update_arr);
                        if (!$base_product_update) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $ingredient]);
                        }

                    } else {
                        // Create base product
                        $insert_arr = [
                            'product_type' => 'pantry_item',
                            'recipe_id' => $recipe->id,
                            'recipe_ingredient_id' => $ingredient->id,
                            'parent_id' => $recipe->id,
                            'product_name_en' => $ingredient->desc_en,
                            'product_name_ar' => $ingredient->desc_ar,
                            'product_image_url' => $ingredient->image_url,
                            'product_image' => 0,
                            'base_price' => $ingredient->price,
                            'product_store_price' => $ingredient->price,
                            'fk_store_id' => env("RECIPIE_STORE_ID"),
                            'product_store_stock' => 100,
                            '_tags' => '',
                            'stock' => 1,
                            'unit' => $ingredient->unit,
                            'allow_margin' => 0
                        ];
                        $base_product = BaseProduct::create($insert_arr);
                        if (!$base_product) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $ingredient]);
                        }
            
                        $ingredient->update([
                            'base_product_id'=>$base_product->id,
                        ]);

                    }

                    $base_product_store = BaseProductStore::find($ingredient->base_product_store_id);
                    if ($base_product_store) {
                        // Update base product store
                        $update_arr = [
                            'product_type' => $base_product->product_type,
                            'recipe_id' => $base_product->recipe_id,
                            'recipe_ingredient_id' => $base_product->recipe_ingredient_id,
                            'parent_id' => $base_product->parent_id,
                            'unit' => $base_product->unit,
                            'allow_margin' => 0,
                            'margin' => 0,
                            'product_name_en' => $base_product->product_name_en,
                            'product_name_ar' => $base_product->product_name_ar,
                            'product_image_url' => $base_product->product_image_url,
                            'product_image' => 0,
                            'product_distributor_price' => $request->input('distributor_price') ?? 0,
                            'base_price' => $base_product->base_price,
                            'product_store_price' => $base_product->product_store_price,
                            'product_store_stock' => $base_product->product_store_stock,
                            'stock' => $base_product->stock,
                            'is_active' => 1,
                            'fk_product_id' => $base_product->id,
                            'fk_store_id' => env("RECIPIE_STORE_ID"),
                        ];
                        $base_product_store_update = $base_product_store->update($update_arr);
                        if (!$base_product_store_update) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $ingredient]);
                        }
                    } else {
                        // Create base product store
                        $insert_arr = [
                            'product_type' => $base_product->product_type,
                            'recipe_id' => $base_product->recipe_id,
                            'recipe_ingredient_id' => $base_product->recipe_ingredient_id,
                            'parent_id' => $base_product->parent_id,
                            'unit' => $base_product->unit,
                            'allow_margin' => 0,
                            'margin' => 0,
                            'product_name_en' => $base_product->product_name_en,
                            'product_name_ar' => $base_product->product_name_ar,
                            'product_image_url' => $base_product->product_image_url,
                            'product_image' => 0,
                            'product_distributor_price' => $request->input('distributor_price') ?? 0,
                            'base_price' => $base_product->base_price,
                            'product_store_price' => $base_product->product_store_price,
                            'product_store_stock' => $base_product->product_store_stock,
                            'stock' => $base_product->stock,
                            'is_active' => 1,
                            'fk_product_id' => $base_product->id,
                            'fk_store_id' => env("RECIPIE_STORE_ID"),
                        ];
                        $base_product_store = BaseProductStore::create($insert_arr);
                        if (!$base_product_store) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $ingredient]);
                        }
            
                        $ingredient->update([
                            'base_product_store_id'=>$base_product_store->id
                        ]);
                    }

                    // ---------------------------
                    // Setting up product
                    // ---------------------------
                    $product = Product::find($ingredient->fk_product_id);
                    if ($product) {
                        // Update product
                        $update_arr = [
                            'product_type' => 'pantry_item',
                            'recipe_id' => $recipe->id,
                            'recipe_ingredient_id' => $ingredient->id,
                            'parent_id' => $recipe->id,
                            'fk_company_id' => 0,
                            'product_name_en' => $ingredient->desc_en,
                            'product_name_ar' => $ingredient->desc_ar,
                            'product_image_url' => $ingredient->image_url,
                            'product_image' => 0,
                            'product_price' => $ingredient->price,
                            'distributor_price' => $request->input('distributor_price') ?? 0,
                            '_tags' => '',
                            'stock' => 1,
                            'store1' => 1,
                            'store2' => 1,
                            'store3' => 1,
                            'store4' => 1,
                            'store5' => 1,
                            'store6' => 1,
                            'store7' => 1,
                            'store8' => 1,
                            'store9' => 1,
                            'store10' => 1,
                            'unit' => $ingredient->unit,
                            'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store1_price' => $ingredient->price,
                            'store2_price' => $ingredient->price,
                            'store3_price' => $ingredient->price,
                            'store4_price' => $ingredient->price,
                            'store5_price' => $ingredient->price,
                            'store6_price' => $ingredient->price,
                            'store7_price' => $ingredient->price,
                            'store8_price' => $ingredient->price,
                            'store9_price' => $ingredient->price,
                            'store10_price' => $ingredient->price,
                        ];
                        $product_update = $product->update($update_arr);
                        if (!$product_update) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $ingredient]);
                        }
                    } else {
                        // Create product
                        $insert_arr = [
                            'product_type' => 'pantry_item',
                            'recipe_id' => $recipe->id,
                            'recipe_ingredient_id' => $ingredient->id,
                            'parent_id' => $recipe->id,
                            'fk_company_id' => 0,
                            'product_name_en' => $ingredient->desc_en,
                            'product_name_ar' => $ingredient->desc_ar,
                            'product_image_url' => $ingredient->image_url,
                            'product_image' => 0,
                            'product_price' => $ingredient->price,
                            'distributor_price' => $request->input('distributor_price') ?? 0,
                            '_tags' => '',
                            'stock' => 1,
                            'store1' => 1,
                            'store2' => 1,
                            'store3' => 1,
                            'store4' => 1,
                            'store5' => 1,
                            'store6' => 1,
                            'store7' => 1,
                            'store8' => 1,
                            'store9' => 1,
                            'store10' => 1,
                            'unit' => $ingredient->unit,
                            'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                            'store1_price' => $ingredient->price,
                            'store2_price' => $ingredient->price,
                            'store3_price' => $ingredient->price,
                            'store4_price' => $ingredient->price,
                            'store5_price' => $ingredient->price,
                            'store6_price' => $ingredient->price,
                            'store7_price' => $ingredient->price,
                            'store8_price' => $ingredient->price,
                            'store9_price' => $ingredient->price,
                            'store10_price' => $ingredient->price,
                        ];
                        $product = Product::create($insert_arr);
                        if (!$product) {
                            return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding Product', 'data' => $ingredient]);
                        }
                        $ingredient->update([
                            'fk_product_id'=>$product->id
                        ]);
                    }
                }

                $ingredient = IngredientTag::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $ingredient]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Delete ingredient via AJAX
    public function delete_ingredient_save(Request $request) {
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $ingredient = IngredientTag::find($id);
        $recipe = Recipe::find($recipe_id);
        if ($ingredient && $recipe) {
            $delete = $ingredient->delete();
            if ($delete) {
                $product = Product::find($ingredient->fk_product_id);
                $product_delete = $product ? $product->delete() : false;
                $base_product_store = BaseProductStore::find($ingredient->base_product_store_id);
                $base_product_store_delete = $base_product_store ? $base_product_store->delete() : false;
                $base_product = BaseProduct::find($ingredient->base_product_id);
                $base_product_delete = $base_product ? $base_product->delete() : false;
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Deleted', 'data' => $ingredient]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
        }
    }

    // Edit step via AJAX
    public function edit_step_save(Request $request) {
        // Validation
        if (
            $request->input('id')=='' ||
            $request->input('recipe_id') == '' ||
            $request->input('step_en') == '' ||
            $request->input('step_ar') == ''
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        // Process
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $steps_ingredients = $request->input('steps_ingredient');
        $step = Step::find($id);
        $recipe = Recipe::find($recipe_id);
        if ($step && $recipe) {

            $ingredients = [];
            if ($steps_ingredients != null && is_array($steps_ingredients)) {
                foreach ($steps_ingredients as $key => $steps_ingredient) {
                    $steps_ingredient_en = $request->input('steps_ingredient_en_'.$steps_ingredient) ? $request->input('steps_ingredient_en_'.$steps_ingredient) : "";
                    $steps_ingredient_ar = $request->input('steps_ingredient_ar_'.$steps_ingredient) ? $request->input('steps_ingredient_ar_'.$steps_ingredient) : "";
                    $steps_ingredient_tag = $request->input('steps_ingredient_tag_'.$steps_ingredient) ? $request->input('steps_ingredient_tag_'.$steps_ingredient) : "";
                    $steps_ingredient_image_url = $request->input('steps_ingredient_image_url_'.$steps_ingredient) ? $request->input('steps_ingredient_image_url_'.$steps_ingredient) : "";
                    $steps_ingredient_pantry_item = $request->input('steps_ingredient_pantry_item_'.$steps_ingredient) ? $request->input('steps_ingredient_pantry_item_'.$steps_ingredient) : "";
                    $steps_ingredient_quantity = $request->input('steps_ingredient_quantity_'.$steps_ingredient) ? $request->input('steps_ingredient_quantity_'.$steps_ingredient) : "";
                    $ingredients[] = array(
                        'id' => $steps_ingredient,
                        'desc_en' => $steps_ingredient_en,
                        'desc_ar' => $steps_ingredient_ar,
                        'tag' => $steps_ingredient_tag,
                        'image_url' => $steps_ingredient_image_url,
                        'pantry_item' => $steps_ingredient_pantry_item,
                        'quantity' => $steps_ingredient_quantity,
                    );
                }
            }
            
            $update_arr = [
                'recipe_id'  => $request->input('recipe_id'),
                'step_en'    => $request->input('step_en'),
                'step_ar'    => $request->input('step_ar'),
                'time'    => $request->input('time'),
                'ingredients' => json_encode($ingredients)
            ];

            $update = $step->update($update_arr);
        
            if ($update) {
                $step = Step::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $step]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Add step via AJAX
    public function add_step_save(Request $request) {
        // Validation
        if (
            $request->input('recipe_id') == '' ||
            $request->input('step_en') == '' ||
            $request->input('step_ar') == ''
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        // Process
        $recipe_id = $request->input('recipe_id');
        $steps_ingredients = $request->input('steps_ingredient');
        $recipe = Recipe::find($recipe_id);
        if ($recipe) {
            
            $ingredients = [];
            if ($steps_ingredients != null && is_array($steps_ingredients)) {
                foreach ($steps_ingredients as $key => $steps_ingredient) {
                    $steps_ingredient_en = $request->input('steps_ingredient_en_'.$steps_ingredient) ? $request->input('steps_ingredient_en_'.$steps_ingredient) : "";
                    $steps_ingredient_ar = $request->input('steps_ingredient_ar_'.$steps_ingredient) ? $request->input('steps_ingredient_ar_'.$steps_ingredient) : "";
                    $steps_ingredient_tag = $request->input('steps_ingredient_tag_'.$steps_ingredient) ? $request->input('steps_ingredient_tag_'.$steps_ingredient) : "";
                    $steps_ingredient_image_url = $request->input('steps_ingredient_image_url_'.$steps_ingredient) ? $request->input('steps_ingredient_image_url_'.$steps_ingredient) : "";
                    $steps_ingredient_pantry_item = $request->input('steps_ingredient_pantry_item_'.$steps_ingredient) ? $request->input('steps_ingredient_pantry_item_'.$steps_ingredient) : "";
                    $steps_ingredient_quantity = $request->input('steps_ingredient_quantity_'.$steps_ingredient) ? $request->input('steps_ingredient_quantity_'.$steps_ingredient) : "";
                    $ingredients[] = array(
                        'id' => $steps_ingredient,
                        'desc_en' => $steps_ingredient_en,
                        'desc_ar' => $steps_ingredient_ar,
                        'tag' => $steps_ingredient_tag,
                        'image_url' => $steps_ingredient_image_url,
                        'pantry_item' => $steps_ingredient_pantry_item,
                        'quantity' => $steps_ingredient_quantity,
                    );
                }
            }

            $create_arr = [
                'recipe_id'  => $request->input('recipe_id'),
                'step_en'    => $request->input('step_en'),
                'step_ar'    => $request->input('step_ar'),
                'time'    => $request->input('time'),
                'ingredients' => json_encode($ingredients)
            ];

            $step = Step::create($create_arr);
        
            if ($step) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $step]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Delete step via AJAX
    public function delete_step_save(Request $request) {
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $step = Step::find($id);
        $recipe = Recipe::find($recipe_id);
        if ($step && $recipe) {
            $delete = $step->delete();
            if ($delete) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Deleted', 'data' => $step]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
        }
    }

    // Add variant via AJAX
    public function add_variant_save(Request $request) {
        // Validation
        if (
            $request->input('recipe_id') == '' ||
            $request->input('serving') == '' ||
            $request->input('price') == ''
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        // Process
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $variant_ingredients = $request->input('variant_ingredients');
        $variant_pantry_items = $request->input('variant_pantry_items');
        $recipe = Recipe::find($recipe_id);
        
        if ($recipe) {
            // Create base product
            $insert_arr = [
                'product_type' => 'recipe',
                'recipe_id' => $recipe->id,
                'parent_id' => 0,
                'product_name_en' => $recipe->recipe_name_en,
                'product_name_ar' => $recipe->recipe_name_ar,
                'product_image_url' => $recipe->recipe_img_url,
                'base_price' => $request->input('price'),
                'product_store_price' => $request->input('price'),
                'product_store_stock' => 100,
                'fk_store_id' => env("RECIPIE_STORE_ID"),
                '_tags' => '',
                'stock' => 1,
                'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                'allow_margin' => 0,
                'margin' => 0,
                'product_distributor_price' => $request->input('distributor_price') ?? 0,
                'is_active' => 1,
            ];
            $base_product = BaseProduct::create($insert_arr);
            if (!$base_product) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $base_product]);
            }
            $insert_arr['fk_product_id'] = $base_product->id;
            $base_product_store = BaseProductStore::create($insert_arr);
            if (!$base_product_store) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $base_product]);
            }
            BaseProduct::find($base_product->id)->update(['fk_product_store_id' => $base_product_store->id]);
            
            // Create product
            $insert_arr = [
                'product_type' => 'recipe',
                'recipe_id' => $recipe->id,
                'recipe_variant_id' => 0,
                'parent_id' => 0,
                'fk_company_id' => 0,
                'product_name_en' => $recipe->recipe_name_en,
                'product_name_ar' => $recipe->recipe_name_ar,
                'product_image_url' => $recipe->recipe_img_url,
                'product_image' => $recipe->recipe_img,
                'product_price' => $request->input('price'),
                'distributor_price' => $request->input('distributor_price') ?? 0,
                '_tags' => '',
                'stock' => 1,
                'store1' => 1,
                'store2' => 1,
                'store3' => 1,
                'store4' => 1,
                'store5' => 1,
                'store6' => 1,
                'store7' => 1,
                'store8' => 1,
                'store9' => 1,
                'store10' => 1,
                'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                'store1_price' => $request->input('price'),
                'store2_price' => $request->input('price'),
                'store3_price' => $request->input('price'),
                'store4_price' => $request->input('price'),
                'store5_price' => $request->input('price'),
                'store6_price' => $request->input('price'),
                'store7_price' => $request->input('price'),
                'store8_price' => $request->input('price'),
                'store9_price' => $request->input('price'),
                'store10_price' => $request->input('price'),
            ];
            $product = Product::create($insert_arr);
            if (!$product) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding Product', 'data' => $product]);
            }

            $ingredients = [];
            if ($variant_ingredients != null && is_array($variant_ingredients)) {
                foreach ($variant_ingredients as $key => $variant_ingredient) {
                    $variant_ingredient_en = $request->input('variant_ingredient_en_'.$variant_ingredient) ? $request->input('variant_ingredient_en_'.$variant_ingredient) : "";
                    $variant_ingredient_ar = $request->input('variant_ingredient_ar_'.$variant_ingredient) ? $request->input('variant_ingredient_ar_'.$variant_ingredient) : "";
                    $variant_ingredient_tag = $request->input('variant_ingredient_tag_'.$variant_ingredient) ? $request->input('variant_ingredient_tag_'.$variant_ingredient) : "";
                    $variant_ingredient_image_url = $request->input('variant_ingredient_image_url_'.$variant_ingredient) ? $request->input('variant_ingredient_image_url_'.$variant_ingredient) : "";
                    $variant_ingredient_pantry_item = $request->input('variant_ingredient_pantry_item_'.$variant_ingredient) ? $request->input('variant_ingredient_pantry_item_'.$variant_ingredient) : "";
                    $variant_ingredient_quantity = $request->input('variant_ingredient_quantity_'.$variant_ingredient) ? $request->input('variant_ingredient_quantity_'.$variant_ingredient) : "";
                    $ingredients[] = array(
                        'id' => $variant_ingredient,
                        'desc_en' => $variant_ingredient_en,
                        'desc_ar' => $variant_ingredient_ar,
                        'tag' => $variant_ingredient_tag,
                        'image_url' => $variant_ingredient_image_url,
                        'pantry_item' => $variant_ingredient_pantry_item,
                        'quantity' => $variant_ingredient_quantity,
                    );
                }
            }

            $pantry_items = [];
            if ($variant_pantry_items != null && is_array($variant_pantry_items)) {
                foreach ($variant_pantry_items as $key => $pantry_item) {
                    $pantry_item_en = $request->input('variant_pantry_item_en_'.$pantry_item) ? $request->input('variant_pantry_item_en_'.$pantry_item) : "";
                    $pantry_item_ar = $request->input('variant_pantry_item_ar_'.$pantry_item) ? $request->input('variant_pantry_item_ar_'.$pantry_item) : "";
                    $pantry_item_tag = $request->input('variant_pantry_item_tag_'.$pantry_item) ? $request->input('variant_pantry_item_tag_'.$pantry_item) : "";
                    $pantry_item_image_url = $request->input('variant_pantry_item_image_url_'.$pantry_item) ? $request->input('variant_pantry_item_image_url_'.$pantry_item) : "";
                    $pantry_item_pantry_item = $request->input('variant_pantry_item_pantry_item_'.$pantry_item) ? $request->input('variant_pantry_item_pantry_item_'.$pantry_item) : "";
                    $pantry_item_quantity = $request->input('variant_pantry_item_quantity_'.$pantry_item) ? $request->input('variant_pantry_item_quantity_'.$pantry_item) : "";
                    $pantry_item_price = $request->input('variant_pantry_item_price_'.$pantry_item) ? $request->input('variant_pantry_item_price_'.$pantry_item) : "";
                    $pantry_item_unit = $request->input('variant_pantry_item_unit_'.$pantry_item) ? $request->input('variant_pantry_item_unit_'.$pantry_item) : "";
                    $pantry_item_product_id = $request->input('variant_pantry_item_product_id_'.$pantry_item) ? $request->input('variant_pantry_item_product_id_'.$pantry_item) : "";
                    $pantry_item_base_product_id = $request->input('variant_pantry_item_base_product_id_'.$pantry_item) ? $request->input('variant_pantry_item_base_product_id_'.$pantry_item) : "";
                    $pantry_items[] = array(
                        'id' => $pantry_item,
                        'desc_en' => $pantry_item_en,
                        'desc_ar' => $pantry_item_ar,
                        'tag' => $pantry_item_tag,
                        'image_url' => $pantry_item_image_url,
                        'pantry_item' => $pantry_item_pantry_item,
                        'quantity' => $pantry_item_quantity,
                        'price' => $pantry_item_price,
                        'unit' => $pantry_item_unit,
                        'product_id' => strval($pantry_item_product_id),
                        'base_product_id' => strval($pantry_item_base_product_id)
                    );
                }
            }
            
            $insert_arr = [
                'fk_recipe_id'  => $request->input('recipe_id'),
                'fk_product_id' => $product->id,
                'base_product_id'  => $base_product->id,
                'base_product_store_id'  => $base_product_store->id,
                'serving'    => $request->input('serving'),
                'price'    => $request->input('price'),
                'fk_product_id' => $product->id,
                'ingredients' => json_encode($ingredients),
                'pantry_items' => json_encode($pantry_items)
            ];

            $recipe_variant_id = RecipeVariant::insertGetId($insert_arr);
            Product::find($product->id)->update(['recipe_variant_id' => $recipe_variant_id]);
            BaseProduct::find($base_product->id)->update(['recipe_variant_id' => $recipe_variant_id]);
        
            if ($recipe_variant_id) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $recipe_variant_id]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Edit variant via AJAX
    public function edit_variant_save(Request $request) {
        // Validation
        if (
            $request->input('id')=='' ||
            $request->input('recipe_id') == '' ||
            $request->input('serving') == '' ||
            $request->input('price') == ''
        ) {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Please fill required fields!']);
        }
        // Process
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $variant_ingredients = $request->input('variant_ingredients');
        $variant_pantry_items = $request->input('variant_pantry_items');
        $variant = RecipeVariant::find($id);
        $recipe = Recipe::find($recipe_id);
        
        if ($variant && $recipe) {
            // ---------------------------
            // Setting up base product
            // ---------------------------
            $base_product = BaseProduct::find($variant->base_product_id);
            if ($base_product) {
                // Update base product
                $update_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'fk_company_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'product_image' => $recipe->recipe_img,
                    'base_price' => $request->input('price'),
                    'product_store_price' => $request->input('price'),
                    'product_store_stock' => 100,
                    'fk_store_id' => env("RECIPIE_STORE_ID"),
                    '_tags' => '',
                    'stock' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                ];
                $base_product_update = $base_product->update($update_arr);
                if (!$base_product_update) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $base_product]);
                }
                $variant->update([
                    'base_product_id'=>$base_product->id,
                ]);

            } else {
                // Create base product
                $insert_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'base_price' => $request->input('price'),
                    'product_store_price' => $request->input('price'),
                    'product_store_stock' => 100,
                    'fk_store_id' => env("RECIPIE_STORE_ID"),
                    '_tags' => '',
                    'stock' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                    'allow_margin' => 0,
                    'margin' => 0,
                    'product_distributor_price' => $request->input('distributor_price') ?? 0,
                    'is_active' => 1,
                ];
                $base_product = BaseProduct::create($insert_arr);
                if (!$base_product) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $base_product]);
                }
                $variant->update([
                    'base_product_id'=>$base_product->id,
                ]);
            }

            $base_product_store = BaseProductStore::find($variant->base_product_store_id);
            if ($base_product_store) {
                // Update base product store
                $update_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'base_price' => $request->input('price'),
                    'product_store_price' => $request->input('price'),
                    'product_store_stock' => 100,
                    'fk_store_id' => env("RECIPIE_STORE_ID"),
                    '_tags' => '',
                    'stock' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                    'allow_margin' => 0,
                    'margin' => 0,
                    'product_distributor_price' => $request->input('distributor_price') ?? 0,
                    'is_active' => 1,
                ];
                $base_product_store_update = $base_product_store->update($update_arr);
                if (!$base_product_store_update) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $base_product_store]);
                }
                $variant->update([
                    'base_product_store_id'=>$base_product_store->id
                ]);
            } else {
                // Create base product store
                $insert_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'base_price' => $request->input('price'),
                    'product_store_price' => $request->input('price'),
                    'product_store_stock' => 100,
                    'fk_store_id' => env("RECIPIE_STORE_ID"),
                    '_tags' => '',
                    'stock' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                    'allow_margin' => 0,
                    'margin' => 0,
                    'product_distributor_price' => $request->input('distributor_price') ?? 0,
                    'is_active' => 1,
                ];
                $base_product_store = BaseProductStore::create($insert_arr);
                if (!$base_product_store) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding base product', 'data' => $base_product_store]);
                }
                $variant->update([
                    'base_product_store_id'=>$base_product_store->id
                ]);
            }
            BaseProduct::find($base_product->id)->update(['fk_product_store_id' => $base_product_store->id]);

            // Setting up product
            $product = Product::find($variant->fk_product_id);
            if ($product) {
                // Update product
                $update_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'fk_company_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'product_image' => $recipe->recipe_img,
                    'product_price' => $request->input('price'),
                    'distributor_price' => $request->input('distributor_price') ?? 0,
                    '_tags' => '',
                    'stock' => 1,
                    'store1' => 1,
                    'store2' => 1,
                    'store3' => 1,
                    'store4' => 1,
                    'store5' => 1,
                    'store6' => 1,
                    'store7' => 1,
                    'store8' => 1,
                    'store9' => 1,
                    'store10' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                    'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store1_price' => $request->input('price'),
                    'store2_price' => $request->input('price'),
                    'store3_price' => $request->input('price'),
                    'store4_price' => $request->input('price'),
                    'store5_price' => $request->input('price'),
                    'store6_price' => $request->input('price'),
                    'store7_price' => $request->input('price'),
                    'store8_price' => $request->input('price'),
                    'store9_price' => $request->input('price'),
                    'store10_price' => $request->input('price'),
                ];
                $product_update = $product->update($update_arr);
                if (!$product_update) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while updating Product', 'data' => $product_update]);
                }
            } else {
                // Create product
                $insert_arr = [
                    'product_type' => 'recipe',
                    'recipe_id' => $recipe->id,
                    'recipe_variant_id' => $variant->id,
                    'parent_id' => 0,
                    'fk_company_id' => 0,
                    'product_name_en' => $recipe->recipe_name_en,
                    'product_name_ar' => $recipe->recipe_name_ar,
                    'product_image_url' => $recipe->recipe_img_url,
                    'product_image' => $recipe->recipe_img,
                    'product_price' => $request->input('price'),
                    'distributor_price' => $request->input('distributor_price') ?? 0,
                    '_tags' => '',
                    'stock' => 1,
                    'store1' => 1,
                    'store2' => 1,
                    'store3' => 1,
                    'store4' => 1,
                    'store5' => 1,
                    'store6' => 1,
                    'store7' => 1,
                    'store8' => 1,
                    'store9' => 1,
                    'store10' => 1,
                    'unit' => $request->input('serving') == 1 ? $request->input('serving').' serving' : $request->input('serving').' servings',
                    'store1_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store2_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store3_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store4_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store5_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store6_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store7_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store8_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store9_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store10_distributor_price' => $request->input('distributor_price') ?? 0,
                    'store1_price' => $request->input('price'),
                    'store2_price' => $request->input('price'),
                    'store3_price' => $request->input('price'),
                    'store4_price' => $request->input('price'),
                    'store5_price' => $request->input('price'),
                    'store6_price' => $request->input('price'),
                    'store7_price' => $request->input('price'),
                    'store8_price' => $request->input('price'),
                    'store9_price' => $request->input('price'),
                    'store10_price' => $request->input('price'),
                ];
                $product = Product::create($insert_arr);
                if (!$product) {
                    return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Error while adding Product', 'data' => $product]);
                }
            }

            $ingredients = [];
            if ($variant_ingredients != null && is_array($variant_ingredients)) {
                foreach ($variant_ingredients as $key => $variant_ingredient) {
                    $variant_ingredient_en = $request->input('variant_ingredient_en_'.$variant_ingredient) ? $request->input('variant_ingredient_en_'.$variant_ingredient) : "";
                    $variant_ingredient_ar = $request->input('variant_ingredient_ar_'.$variant_ingredient) ? $request->input('variant_ingredient_ar_'.$variant_ingredient) : "";
                    $variant_ingredient_tag = $request->input('variant_ingredient_tag_'.$variant_ingredient) ? $request->input('variant_ingredient_tag_'.$variant_ingredient) : "";
                    $variant_ingredient_image_url = $request->input('variant_ingredient_image_url_'.$variant_ingredient) ? $request->input('variant_ingredient_image_url_'.$variant_ingredient) : "";
                    $variant_ingredient_pantry_item = $request->input('variant_ingredient_pantry_item_'.$variant_ingredient) ? $request->input('variant_ingredient_pantry_item_'.$variant_ingredient) : "";
                    $variant_ingredient_quantity = $request->input('variant_ingredient_quantity_'.$variant_ingredient) ? $request->input('variant_ingredient_quantity_'.$variant_ingredient) : "";
                    $ingredients[] = array(
                        'id' => $variant_ingredient,
                        'desc_en' => $variant_ingredient_en,
                        'desc_ar' => $variant_ingredient_ar,
                        'tag' => $variant_ingredient_tag,
                        'image_url' => $variant_ingredient_image_url,
                        'pantry_item' => $variant_ingredient_pantry_item,
                        'quantity' => $variant_ingredient_quantity,
                    );
                }
            }

            $pantry_items = [];
            if ($variant_pantry_items != null && is_array($variant_pantry_items)) {
                foreach ($variant_pantry_items as $key => $pantry_item) {
                    $pantry_item_en = $request->input('variant_pantry_item_en_'.$pantry_item) ? $request->input('variant_pantry_item_en_'.$pantry_item) : "";
                    $pantry_item_ar = $request->input('variant_pantry_item_ar_'.$pantry_item) ? $request->input('variant_pantry_item_ar_'.$pantry_item) : "";
                    $pantry_item_tag = $request->input('variant_pantry_item_tag_'.$pantry_item) ? $request->input('variant_pantry_item_tag_'.$pantry_item) : "";
                    $pantry_item_image_url = $request->input('variant_pantry_item_image_url_'.$pantry_item) ? $request->input('variant_pantry_item_image_url_'.$pantry_item) : "";
                    $pantry_item_pantry_item = $request->input('variant_pantry_item_pantry_item_'.$pantry_item) ? $request->input('variant_pantry_item_pantry_item_'.$pantry_item) : "";
                    $pantry_item_quantity = $request->input('variant_pantry_item_quantity_'.$pantry_item) ? $request->input('variant_pantry_item_quantity_'.$pantry_item) : "";
                    $pantry_item_price = $request->input('variant_pantry_item_price_'.$pantry_item) ? $request->input('variant_pantry_item_price_'.$pantry_item) : "";
                    $pantry_item_unit = $request->input('variant_pantry_item_unit_'.$pantry_item) ? $request->input('variant_pantry_item_unit_'.$pantry_item) : "";
                    $pantry_item_product_id = $request->input('variant_pantry_item_product_id_'.$pantry_item) ? $request->input('variant_pantry_item_product_id_'.$pantry_item) : "";
                    $pantry_item_base_product_id = $request->input('variant_pantry_item_base_product_id_'.$pantry_item) ? $request->input('variant_pantry_item_base_product_id_'.$pantry_item) : "";
                    $pantry_items[] = array(
                        'id' => $pantry_item,
                        'desc_en' => $pantry_item_en,
                        'desc_ar' => $pantry_item_ar,
                        'tag' => $pantry_item_tag,
                        'image_url' => $pantry_item_image_url,
                        'pantry_item' => $pantry_item_pantry_item,
                        'quantity' => $pantry_item_quantity,
                        'price' => $pantry_item_price,
                        'unit' => $pantry_item_unit,
                        'product_id' => strval($pantry_item_product_id),
                        'base_product_id' => strval($pantry_item_base_product_id)
                    );
                }
            }
            
            $update_arr = [
                'recipe_id'  => $request->input('recipe_id'),
                'serving'    => $request->input('serving'),
                'price'    => $request->input('price'),
                'fk_product_id' => $product->id,
                'ingredients' => json_encode($ingredients),
                'pantry_items' => json_encode($pantry_items)
            ];

            $update = $variant->update($update_arr);
        
            if ($update) {
                $variant = RecipeVariant::find($id);
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Saved', 'data' => $variant]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on saving']);
        }
    }

    // Delete variant via AJAX
    public function delete_variant_save(Request $request) {
        $id = $request->input('id');
        $recipe_id = $request->input('recipe_id');
        $variant = RecipeVariant::find($id);
        $recipe = Recipe::find($recipe_id);
        if ($variant && $recipe) {
            $delete = $variant->delete();
            if ($delete) {
                return response()->json(['status' => true, 'error_code' => 200, 'message' => 'Deleted', 'data' => $variant]);
            } else {
                return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
            }
        } else {
            return response()->json(['status' => false, 'error_code' => 201, 'message' => 'Error on deleting']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {

            // Ingredient::where("recipe_id", $id)->delete();
            // IngredientTag::where("recipe_id", $id)->delete();
            // Step::where("recipe_id", $id)->delete();
            // Product::where("recipe_id", $id)->where("product_type", "recipe")->delete();
            // RecipeVariant::where("fk_recipe_id", $id)->delete();
            Product::where("recipe_id", $id)->where("product_type", "recipe")->update(['deleted'=>1]);
            Product::where("recipe_id", $id)->where("product_type", "pantry_item")->update(['deleted'=>1]);
            Recipe::find($id)->update(['deleted'=>1]);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error while adding recipe');
        }
        return redirect('admin/recipes')->with('success', 'Recipe deleted successfully');
    }

    protected function set_featured_recipe(Request $request)
    {
        $value = $request->input('value');
        $is_switched_on = $request->input('is_switched_on');
        $id = $request->input('id');

        if ($value == 'featured') {
            $updateArr['is_featured'] = $is_switched_on ? 1 : 0;
        } elseif ($value == 'recommended') {
            $updateArr['recommended'] = $is_switched_on ? 1 : 0;
        } elseif ($value == 'veg') {
            $updateArr['veg'] = $is_switched_on ? 1 : 0;
        } elseif ($value == 'home') {
            $updateArr['is_home'] = $is_switched_on ? 1 : 0;
        } elseif ($value == 'active') {
            $updateArr['active'] = $is_switched_on ? 1 : 0;
        }

        $update = Recipe::find($id)->update($updateArr);

        if ($update) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Updated!"
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 105,
                'message' => "Some error found"
            ]);
        }
    }
    
    protected function bulk_upload_single_column_post(Request $request)
    {
        $file = file($request->file->getRealPath());
        $data = array_slice($file, 1);
        $parts = (array_chunk($data, 1000));
        $key = $request->input('key');

        if ($key && count($parts)) {
            foreach ($parts as $part) {
                $data = array_map('str_getcsv', $part);
                // Tags upload
                UpdateRecipeSingleColumn::dispatch($key, $data);
            }
        }

        return redirect('admin/base_products/bulk_upload_single_column')->with('success', 'Process started');
    }

    protected function get_products(Request $request)
    {
        $products = Product::select('id', 'product_name_en', 'unit')
            ->where('deleted', '=', 0)
            ->where('stock', '=', 1)
            ->where('parent_id', '=', 0)
            ->orderBy('product_name_en', 'asc')
            ->get();

        if ($products->count()) {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success",
                'result' => ['products' => $products]
            ]);
        } else {
            return response()->json([
                'error' => false,
                'status_code' => 200,
                'message' => "Success",
                'result' => ['products' => []]
            ]);
        }
    }
}
