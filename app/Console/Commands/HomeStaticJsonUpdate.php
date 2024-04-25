<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use DB;

class HomeStaticJsonUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HomeStaticJsonUpdate {model} {language}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the home static JSON file for a specific language';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {   
        $language = $this->argument('language');
        $model = $this->argument('model');
        $home_static = \App\Model\HomeStatic::where('lang',$language)->where(['home_static_type' => $model])->orderBy('id', 'desc')->first();
        
        if($model == 'home_static_1'){
            if ($home_static) {
                $home_static_file_path = 'app/public/' . $home_static->file_name;
                $home_static_file = storage_path($home_static_file_path);
    
                if (file_exists($home_static_file)) {
                    $home_static_file_str = file_get_contents($home_static_file);
                    $home_static_arr = json_decode($home_static_file_str, true);
    
                    if (!empty($home_static_arr) && isset($home_static_arr['result']['dynamic_contents'])) {
                        $dynamicContents = $home_static_arr['result']['dynamic_contents'];
    
                        foreach ($dynamicContents as &$dynamicContent) {
                            if ($dynamicContent['ui_type'] == 2 || $dynamicContent['ui_type'] == 7) {
                                $filterTag = $dynamicContent['filter_tag'];
    
                                $numericFilters = [
                                    ['product_store_stock > 0'],
                                    ['fk_product_store_id != 0'],
                                    ['product_store_price != 0.0'],
                                ];
    
                                $body = [
                                    'query' => $dynamicContent['search_type'] == 1 ? $filterTag : '',
                                    'filters' => $dynamicContent['search_type'] == 1 ? '' : $filterTag,
                                    'numericFilters' => $numericFilters,
                                    'hitsPerPage' => 10,
                                    'enablePersonalization' => true,
                                    'userToken' => '777',
                                    'clickAnalytics' => true,
                                ];
    
                                $response = Http::withHeaders([
                                    'X-Algolia-Application-Id' => env('ALGOLIA_APP_ID'),
                                    'X-Algolia-API-Key' => env('ALGOLIA_API_KEY'),
                                ])->post('https://1DUJVKR8FC-dsn.algolia.net/1/indexes/'.env('ALGOLIA_PRODUCT_INDEX').'/query', $body);
    
                                if (!empty($response['hits'])) {
    
                                    $product_keys = [
                                        'product_type',
                                        'itemcode',
                                        'barcode',
                                        'product_name_en',
                                        'product_name_ar',
                                        'product_image_url',
                                        'other_names',
                                        'unit',
                                        'min_scale',
                                        'max_scale',
                                        'main_tags',
                                        'desc_en',
                                        'desc_ar',
                                        'characteristics_en',
                                        'characteristics_ar',
                                        'base_price',
                                        'product_store_price',
                                        'product_distributor_price',
                                        'product_distributor_price_before_back_margin',
                                        'stock',
                                        'product_store_stock',
                                        'product_store_updated_at',
                                        'margin',
                                        'back_margin',
                                        'base_price_percentage',
                                        'discount_percentage',
                                        'country_code',
                                        'country_icon',
                                        'category_name',
                                        'category_name_ar',
                                        'sub_category_name',
                                        'sub_category_name_ar',
                                        'brand_name',
                                        'brand_name_ar',
                                        'product_saving_price',
                                        'product_saving_percentage'
                                    ];
    
                                    $hits_arr = array();
                                    foreach ($response['hits'] as $key => $value) {
                                        foreach ($product_keys as $product_key) {
                                            $value[$product_key] = !is_string($value[$product_key]) ? strval($value[$product_key]) : $value[$product_key];
                                            $value['fk_brand_id'] = $value['fk_brand_id'] == null ? 0 : $value['fk_brand_id'];
                                        }
                                        $hits_arr[] = $value;
                                    }
    
                                    $dynamicContent['product_data'] = $hits_arr;
                                } else {
                                    $dynamicContent['product_data'] = [];
                                }
                            }
                        }
    
                        // Update the modified dynamicContents array back into $home_static_arr
                        $home_static_arr['result']['dynamic_contents'] = $dynamicContents;
    
                        // Convert the updated array back to JSON
                        $updated_home_static_file_str = json_encode($home_static_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
                        // Create and store the updated JSON file
                        $json_url_base = "home_static_json_data/";
                        $temp_file_name = 'temp_home_static_json_'.$home_static->lang.'.json';
                        $temp_file_store_path = storage_path('app/public/' . $json_url_base . $temp_file_name);
                        if(file_put_contents($temp_file_store_path, $updated_home_static_file_str)){
                            
                            $split_file_name = explode('/',$home_static->file_name);
                            $file_name = explode('.',$split_file_name[1]);
                            $file_store_path = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.($home_static->home_static_data_feeded + 1).'.'.$file_name[1]);
                            
                            // Rename temp file
                            rename($temp_file_store_path ,$file_store_path);
    
                            // Remove existing files
                            $existing_file = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'.'.$file_name[1]);
                            if(file_exists($existing_file)){
                                unlink($existing_file); //delete file
                            }
                            
                            $home_static_file = \App\Model\HomeStatic::where('file_name',$home_static->file_name)->first();
                            $home_static_file->update(['home_static_data_feeded' => $home_static->home_static_data_feeded + 1]);
                        }
                    }
                }
            }
        }

        if($model == 'home_static_instant'){
            
            //Get all instant store groups
            $instant_store_groups = DB::table('instant_store_groups')
                ->where('deleted','=', 0)
                ->get();

            if(!empty($instant_store_groups)){

                foreach ($instant_store_groups as $instant_store_group) {
                    
                    //Get all instant store group stores
                    $instant_store_group_stores = DB::table('instant_store_group_stores')
                        ->where('fk_group_id',$instant_store_group->id)->get()->pluck('fk_store_id')->toArray();

                    if (!empty($home_static)) {
                        
                        $home_static_file_path = 'app/public/' . $home_static->file_name;
                        $home_static_file = storage_path($home_static_file_path);

                        if (file_exists($home_static_file)) {
                            $home_static_file_str = file_get_contents($home_static_file);
                            $home_static_arr = json_decode($home_static_file_str, true);

                            if (!empty($home_static_arr) && isset($home_static_arr['result']['dynamic_contents'])) {
                                $dynamicContents = $home_static_arr['result']['dynamic_contents'];

                                foreach ($dynamicContents as &$dynamicContent) {
                                    if ($dynamicContent['ui_type'] == 2 || $dynamicContent['ui_type'] == 7) {
                                        $filterTag = $dynamicContent['filter_tag'];
                                        $numericFilters = [];
                                        foreach ($instant_store_group_stores as $key => $instant_store_group_store) {
                                            $numericFilters[] = [
                                                ['product_store_stock > 0'],
                                                ['fk_product_store_id != 0'],
                                                ['product_store_price != 0.0'],
                                                ['fk_store_id = '.$instant_store_group_store],
                                            ];
                                        }

                                        $body = [
                                            'query' => $dynamicContent['search_type'] == 1 ? $filterTag : '',
                                            'filters' => $dynamicContent['search_type'] == 1 ? '' : $filterTag,
                                            'numericFilters' => $numericFilters,
                                            'hitsPerPage' => 10,
                                            'enablePersonalization' => true,
                                            'userToken' => '777',
                                            'clickAnalytics' => true,
                                        ];

                                        $response = Http::withHeaders([
                                            'X-Algolia-Application-Id' => env('ALGOLIA_APP_ID'),
                                            'X-Algolia-API-Key' => env('ALGOLIA_API_KEY'),
                                        ])->post('https://1DUJVKR8FC-dsn.algolia.net/1/indexes/'.env('ALGOLIA_PRODUCT_INDEX').'/query', $body);

                                        if (!empty($response['hits'])) {

                                            $product_keys = [
                                                'product_type',
                                                'itemcode',
                                                'barcode',
                                                'product_name_en',
                                                'product_name_ar',
                                                'product_image_url',
                                                'other_names',
                                                'unit',
                                                'min_scale',
                                                'max_scale',
                                                'main_tags',
                                                'desc_en',
                                                'desc_ar',
                                                'characteristics_en',
                                                'characteristics_ar',
                                                'base_price',
                                                'product_store_price',
                                                'product_distributor_price',
                                                'product_distributor_price_before_back_margin',
                                                'stock',
                                                'product_store_stock',
                                                'product_store_updated_at',
                                                'margin',
                                                'back_margin',
                                                'base_price_percentage',
                                                'discount_percentage',
                                                'country_code',
                                                'country_icon',
                                                'category_name',
                                                'category_name_ar',
                                                'sub_category_name',
                                                'sub_category_name_ar',
                                                'brand_name',
                                                'brand_name_ar',
                                                'product_saving_price',
                                                'product_saving_percentage'
                                            ];

                                            $hits_arr = array();
                                            foreach ($response['hits'] as $key => $value) {
                                                foreach ($product_keys as $product_key) {
                                                    $value[$product_key] = !is_string($value[$product_key]) ? strval($value[$product_key]) : $value[$product_key];
                                                }
                                                $hits_arr[] = $value;
                                            }

                                            $dynamicContent['product_data'] = $hits_arr;

                                        } else {
                                            $dynamicContent['product_data'] = [];
                                        }
                                    }
                                }

                                // Update the modified dynamicContents array back into $home_static_arr
                                $home_static_arr['result']['dynamic_contents'] = $dynamicContents;

                                // Convert the updated array back to JSON
                                $updated_home_static_file_str = json_encode($home_static_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                                // Create and store the updated JSON file
                                $json_url_base = "home_static_json_data/";
                                $temp_file_name = $model =='home_static_1' ? 'temp_home_static_json_'.$home_static->lang.'.json' : 'temp_home_static_json_instant_'.$home_static->lang.'.json';
                                $temp_file_store_path = storage_path('app/public/' . $json_url_base . $temp_file_name);
                                if(file_put_contents($temp_file_store_path, $updated_home_static_file_str)){
                                    
                                    $split_file_name = explode('/',$home_static->file_name);
                                    $file_name = explode('.',$split_file_name[1]);
                                    $file_store_path = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.($home_static->home_static_data_feeded + 1).'_'.$instant_store_group->id.'.'.$file_name[1]);
                                    
                                    // Rename temp file
                                    rename($temp_file_store_path ,$file_store_path);

                                    // Remove existing files
                                    $existing_file = storage_path('app/public/' . $json_url_base . $file_name[0].'_'.$home_static->lang.'_'.$home_static->home_static_data_feeded.'_'.$instant_store_group->id.'.'.$file_name[1]);
                                    if(file_exists($existing_file)){
                                        unlink($existing_file); //delete file
                                    }
                                    
                                    $home_static_file = \App\Model\HomeStatic::where('file_name',$home_static->file_name)->first();
                                    $home_static_file->update(['home_static_data_feeded' => $home_static->home_static_data_feeded + 1]);
                                }
                            }
                        }
                    }
                }
            }
        }
    
        return 0;
    }

}
