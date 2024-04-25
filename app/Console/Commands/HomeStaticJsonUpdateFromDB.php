<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use DB;

class HomeStaticJsonUpdateFromDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HomeStaticJsonUpdateFromDB {home_static_type} {lang} {store_key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the home static JSON file for a specific language through DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function get_product_dictionary_bp($products, $lang)
    {
        
        $product_arr = [];
        
        if ($products && $products->count()) {
            foreach ($products as $key => $row) {
                $product_arr[$key] = [
                    'id' => $row->id,
                    'product_id' => $row->id,
                    'fk_product_id' => $row->fk_product_id,
                    'fk_product_store_id' => $row->fk_product_store_id,
                    'product_category_id' => $row->fk_category_id,
                    'product_sub_category_id' => $row->fk_sub_category_id,
                    'product_category' => $lang == 'ar' ? $row->category_name_ar ?? '': $row->category_name_en ?? '',
                    'product_brand_id' => $row->brand_id ?? 0,
                    'product_brand' => $lang == 'ar' ? $row->brand_name_ar ?? '' : $row->brand_name_en ?? '',
                    'product_name' => $lang == 'ar' ? $row->product_name_ar ?? '' : $row->product_name_en ?? '',
                    'product_name_en' => $row->product_name_en ?? '',
                    'product_name_ar' => $row->product_name_ar ?? '',
                    'product_image' => $row->product_image_url ?? '',
                    'product_image_url' => $row->product_image_url ?? '',
                    'base_price' => $row->base_price ?? "0.00",
                    'product_price' => $row->product_store_price ?? "0.00",
                    'product_price_before_discount' => $row->base_price ?? "0.00",
                    'quantity' => $row->unit ?? '',
                    'unit' => $row->unit ?? '',
                    'is_favorite' => '0',
                    'product_discount' => $row->margin ?? "0.00",
                    'min_scale' => $row->min_scale ?? '',
                    'max_scale' => $row->max_scale ?? '',
                    'country_code' => $row->country_code ?? '',
                    'country_icon' => $row->country_icon ?? '',
                    'fk_store_id' => $row->fk_store_id ?? 0,
                    'product_store_price' => $row->product_store_price ?? '0.00',
                    'product_store_stock' => $row->product_store_stock ?? 0,
                    'itemcode' => $row->itemcode ?? '',
                    'barcode' => $row->barcode ?? '',
                    'allow_margin' => $row->allow_margin ?? 0,
                    'paythem_product_id' => $row->paythem_product_id ?? 0,
                    '_tags' => $row->_tags ? explode(',',$row->_tags) : [],
                    'main_tags' => $row->main_tags ?? '',
                    'search_filters' => $row->search_filters ? explode(',',$row->search_filters) : [],
                    'offers' => $row->offers ? explode(',',$row->offers) : [],
                    'custom_tag_bundle' => $row->custom_tag_bundle ?? '',
                    'desc_en' => $row->desc_en ?? '',
                    'desc_ar' => $row->desc_ar ?? '',
                    'characteristics_en' => $row->characteristics_en ?? '',
                    'characteristics_ar' => $row->characteristics_ar ?? '',
                    'cart_quantity' => 0,
                    'created_at' => strtotime($row->created_at) ?? 0,
                    'updated_at' => strtotime($row->updated_at) ?? 0,
                    'product_saving_price' => (float) $row->product_saving_price ?? 0,
                    'product_saving_percentage' => (float) $row->product_saving_percentage ?? 0
                ];
            }
        }
        return $product_arr;
    }
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {   
        $home_static_type = $this->argument('home_static_type');
        $lang = $this->argument('lang');
        $store_key = $this->argument('store_key');

        $home_static = \App\Model\HomeStatic::where('lang','=',$lang);
        $home_static = $home_static->where('home_static_type','=',$home_static_type);
        if ($store_key) {
            $home_static = $home_static->where('store_key','=',$store_key);
        } else {
            $home_static = $home_static->where(function($query) {
                return $query->whereNull('store_key')->orWhere('store_key', '=', 0);
            });
        }
        $home_static = $home_static->orderBy('id', 'desc')->first();
        if ($home_static) {
            \Log::info('Home static for '.$home_static_type.' in '.$lang.' store: '.$store_key.' file_url '.$home_static->file_name);
            
            $home_static_file_path = 'app/public/' . $home_static->file_name;
            $home_static_file = storage_path($home_static_file_path);
            
            if (file_exists($home_static_file)) {
        
                $home_static_file_str = file_get_contents($home_static_file);
                $home_static_arr = json_decode($home_static_file_str, true);

                if (!empty($home_static_arr) && isset($home_static_arr['result']['dynamic_contents'])) {
                    $dynamicContents = $home_static_arr['result']['dynamic_contents'];
                    
                    if($home_static_type == 'instant'){

                        //Get all instant store groups
                        $instant_store_groups = DB::table('instant_store_groups')
                            ->where('deleted','=', 0)
                            ->get();

                        if(!empty($instant_store_groups)){
                            foreach ($instant_store_groups as $instant_store_group) {
                                //Get all instant store group stores
                                $instant_store_group_stores = DB::table('instant_store_group_stores')
                                ->where('fk_group_id',$instant_store_group->id)->get()->pluck('fk_store_id')->toArray();

                                // Process and save products data
                                foreach ($dynamicContents as $key => $dynamicContent) {
                                    if ($dynamicContent['ui_type'] == 2 || $dynamicContent['ui_type'] == 7) {
                                        $filterTag = $dynamicContent['filter_tag'];
                                        
                                        $products = \App\Model\BaseProductStore::leftJoin('categories AS A', 'A.id','=', 'base_products_store.fk_category_id')
                                            ->leftJoin('categories AS B', 'B.id','=', 'base_products_store.fk_sub_category_id')
                                            ->leftJoin('brands','base_products_store.fk_brand_id', '=', 'brands.id')
                                            ->select(
                                                'base_products_store.*',
                                                'base_products_store.fk_product_id AS fk_product_id',
                                                'base_products_store.id AS fk_product_store_id',
                                                'A.id as category_id',
                                                'A.category_name_en',
                                                'A.category_name_ar',
                                                'B.id as sub_category_id',
                                                'B.category_name_en as sub_category_name_en',
                                                'B.category_name_ar as sub_category_name_ar',
                                                'brands.id as brand_id',
                                                'brands.brand_name_en',
                                                'brands.brand_name_ar',
                                                DB::raw('ROUND(base_price - product_store_price,2) as product_saving_price'),
                                                DB::raw('ROUND((base_price - product_store_price)*100 / base_price,2)  as product_saving_percentage')
                                            )
                                            ->where('base_products_store.product_type', '=', 'product')
                                            ->whereIn('base_products_store.fk_store_id', $instant_store_group_stores)
                                            ->where('base_products_store.product_store_stock', '>', 0)
                                            ->where('base_products_store.fk_product_id', '!=', 0)
                                            ->where('base_products_store.product_store_price', '!=', 0)
                                            ->where('base_products_store._tags', 'LIKE', '%'.$filterTag.'%');
                    
                                        if ($store_key) {
                                            $products = $products->where('base_products_store.search_filters', 'LIKE', '%'.$store_key.',%');
                                        }
            
                                        $products = $products->orderBy('created_at','desc')
                                            ->limit(10)
                                            ->get();  
            
                                        if ($products->count()) {
                                            $products_formatted = $this->get_product_dictionary_bp($products,$lang);
                                            $dynamicContents[$key]['product_data'] = $products_formatted;
                                        } else {
                                            $dynamicContents[$key]['product_data'] = [];
                                        }
                                    }
                                }
            
                                // Update the modified dynamicContents array back into $home_static_arr
                                $home_static_arr['result']['dynamic_contents'] = $dynamicContents;
            
                                // Convert the updated array back to JSON
                                $updated_home_static_file_str = json_encode($home_static_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
                                // Create and store the updated JSON file
                                $json_url_base = "home_static_json_data/";
                                $temp_file_name = 'temp_home_static_json_'.$home_static_type.'_'.$home_static->lang.'_'.$instant_store_group->id.'.json';
                                $temp_file_store_path = storage_path('app/public/' . $json_url_base . $temp_file_name);
                                if(file_put_contents($temp_file_store_path, $updated_home_static_file_str)){
                                    
                                    $split_file_name = explode('/',$home_static->file_name);
                                    $file_name = explode('.',$split_file_name[1]);
                                    $file_store_path = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.($home_static->home_static_data_feeded + 1).'_'.$instant_store_group->id.'.'.$file_name[1]);
                                    
                                    // Rename temp file
                                    rename($temp_file_store_path ,$file_store_path);
            
                                    // Update the new url
                                    $home_static_file = \App\Model\HomeStatic::where('file_name',$home_static->file_name)->first();
                                    $home_static_file_updated = $home_static_file->update([
                                        'home_static_data_feeded' => $home_static->home_static_data_feeded + 1,
                                        'last_processed_at' => \Carbon\Carbon::now()
                                    ]);
                                    if ($home_static_file_updated) {
                                        // Remove existing files
                                        $existing_file = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'_'.$instant_store_group->id.'.'.$file_name[1]);
                                        if(file_exists($existing_file)){
                                            unlink($existing_file); //delete file
                                        }
                                        
                                    }
                                }

                            }
                        }
                                
                    }
                    else {
                        
                        if($home_static_type == 'plus'){
                            //Get all plus active stores
                            $active_stores = DB::table('stores')
                                            ->where([
                                                'deleted'=>0,
                                                'status'=>1,
                                                'schedule_active'=>1,
                                                'jeeb_groceries'=>1
                                            ])->get()->pluck('id')->toArray();
                        } else {
                            //Get all mall active stores
                            $active_stores = DB::table('stores')
                                            ->where([
                                                'deleted'=>0,
                                                'status'=>1,
                                                'schedule_active'=>1
                                            ])->get()->pluck('id')->toArray();
                        }
                        

                        // Process and save products data
                        foreach ($dynamicContents as $key => $dynamicContent) {
                            if ($dynamicContent['ui_type'] == 2 || $dynamicContent['ui_type'] == 7) {
                                $filterTag = $dynamicContent['filter_tag'];
                                
                                $products = \App\Model\BaseProduct::leftJoin('categories AS A', 'A.id','=', 'base_products.fk_category_id')
                                    ->leftJoin('categories AS B', 'B.id','=', 'base_products.fk_sub_category_id')
                                    ->leftJoin('brands','base_products.fk_brand_id', '=', 'brands.id')
                                    ->select(
                                        'base_products.*',
                                        'base_products.id AS fk_product_id',
                                        'base_products.fk_product_store_id AS fk_product_store_id',
                                        'A.id as category_id',
                                        'A.category_name_en',
                                        'A.category_name_ar',
                                        'B.id as sub_category_id',
                                        'B.category_name_en as sub_category_name_en',
                                        'B.category_name_ar as sub_category_name_ar',
                                        'brands.id as brand_id',
                                        'brands.brand_name_en',
                                        'brands.brand_name_ar',
                                        DB::raw('ROUND(base_price - product_store_price,2) as product_saving_price'),
                                        DB::raw('ROUND((base_price - product_store_price)*100 / base_price,2)  as product_saving_percentage')
                                    )
                                    ->where('base_products.product_type', '=', 'product')
                                    ->whereIn('base_products.fk_store_id', $active_stores)
                                    ->where('base_products.product_store_stock', '>', 0)
                                    ->where('base_products.fk_product_store_id', '!=', 0)
                                    ->where('base_products.product_store_price', '!=', 0)
                                    ->where('base_products._tags', 'LIKE', '%'.$filterTag.'%');
                                    
                                if ($store_key) {
                                    $products = $products->where('base_products.search_filters', 'LIKE', '%'.$store_key.',%');
                                }
    
                                $products = $products->orderBy('created_at','desc')
                                    ->limit(10)
                                    ->get();  
    
                                if ($products->count()) {
                                    $products_formatted = $this->get_product_dictionary_bp($products,$lang);
                                    $dynamicContents[$key]['product_data'] = $products_formatted;
                                } else {
                                    $dynamicContents[$key]['product_data'] = [];
                                }
                            }
                        }
    
                        // Update the modified dynamicContents array back into $home_static_arr
                        $home_static_arr['result']['dynamic_contents'] = $dynamicContents;
    
                        // Convert the updated array back to JSON
                        $updated_home_static_file_str = json_encode($home_static_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
                        // Create and store the updated JSON file
                        $json_url_base = "home_static_json_data/";
                        $temp_file_name = 'temp_home_static_json_'.$home_static_type.'_'.$home_static->lang.'.json';
                        $temp_file_store_path = storage_path('app/public/' . $json_url_base . $temp_file_name);
                        if(file_put_contents($temp_file_store_path, $updated_home_static_file_str)){
                            
                            $split_file_name = explode('/',$home_static->file_name);
                            $file_name = explode('.',$split_file_name[1]);
                            $file_store_path = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.($home_static->home_static_data_feeded + 1).'.'.$file_name[1]);
                            
                            // Rename temp file
                            rename($temp_file_store_path ,$file_store_path);
    
                            // Update the new url
                            $home_static_file = \App\Model\HomeStatic::where('file_name',$home_static->file_name)->first();
                            $home_static_file_updated = $home_static_file->update([
                                'home_static_data_feeded' => $home_static->home_static_data_feeded + 1,
                                'last_processed_at' => \Carbon\Carbon::now()
                            ]);
                            if ($home_static_file_updated) {
                                // Remove existing files
                                $existing_file = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
                                if(file_exists($existing_file)){
                                    unlink($existing_file); //delete file
                                }
                                
                            }
                        }
                    }

                }
            }
        } else {
            \Log::info('Home static for '.$home_static_type.' in '.$lang.' store: '.$store_key.' file_url not found!');
        }
        
        return 0;
    }

}
