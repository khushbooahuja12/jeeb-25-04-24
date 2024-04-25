<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CoreApiController;
use Illuminate\Http\Request;

require __DIR__ . "../../../../../vendor/autoload.php";

use Algolia\AlgoliaSearch\SearchClient;
use App\Model\Homepage;
use App\Model\HomeStatic;
use App\Model\HomeStaticStore;
use Throwable;
use App\Jobs\ProccessHomeStaticJsonFile;
use Artisan;

use App\Console\Commands\HomeStaticJsonUpdate;

class AppHomeController extends CoreApiController
{
    public function __construct(Request $request)
    {
        $this->products_table = $request->getHttpHost() == 'staging.jeeb.tech' || $request->getHttpHost() == 'localhost' ? 'dev_products' : 'products';
    }

    protected function plus()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $homepage_stores = HomeStaticStore::orderBy('id', 'asc')->get();
        $homestatic_title = 'JEEB Plus Homepage';
        $homestatic_key = 'plus';
        $homestatic_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $homestatic_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $group_enabled = false;
        return view('admin.app_homepage.homestatic', [
            'homepage' => $homepage,
            'homepage_stores' => $homepage_stores,
            'homestatic_title' => $homestatic_title,
            'homestatic_key' => $homestatic_key,
            'homestatic_ens' => $homestatic_ens,
            'homestatic_ars' => $homestatic_ars,
            'group_enabled' => $group_enabled,
        ]);
    }

    protected function stores_home_static_plus(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        $home_static_type = 'plus';
        $store_key = $request->input('store_key') ? $request->input('store_key') : 0;
        $lang = $request->input('lang');

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_stores_json/"; 

            $file_name = $store_key ? time().'_home_static_'.$home_static_type.'_'.$store_key.'.json' : time().'_home_static_'.$home_static_type.'.json';
            $path = \Storage::putFileAs('public/home_static_stores_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => $home_static_type,
                'store_key' => $store_key,
                'lang' => $lang,
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            if ($insert) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $lang,
                    'store_key' => $store_key
                ]);
            }
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return back()->with('success', 'Home static json added successfully');

    }

    protected function mall()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $homepage_stores = HomeStaticStore::orderBy('id', 'asc')->get();
        $homestatic_title = 'JEEB Mall Homepage';
        $homestatic_key = 'mall';
        $homestatic_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $homestatic_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $group_enabled = false;
        return view('admin.app_homepage.homestatic', [
            'homepage' => $homepage,
            'homepage_stores' => $homepage_stores,
            'homestatic_title' => $homestatic_title,
            'homestatic_key' => $homestatic_key,
            'homestatic_ens' => $homestatic_ens,
            'homestatic_ars' => $homestatic_ars,
            'group_enabled' => $group_enabled,
        ]);
    }

    protected function stores_home_static_mall(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        $home_static_type = 'mall';
        $store_key = $request->input('store_key') ? $request->input('store_key') : 0;
        $lang = $request->input('lang');

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_stores_json/"; 

            $file_name = $store_key ? time().'_home_static_'.$home_static_type.'_'.$store_key.'.json' : time().'_home_static_'.$home_static_type.'.json';
            $path = \Storage::putFileAs('public/home_static_stores_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => $home_static_type,
                'store_key' => $store_key,
                'lang' => $lang,
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            if ($insert) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $lang,
                    'store_key' => $store_key
                ]);
            }
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return back()->with('success', 'Home static json added successfully');

    }

    protected function instant()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $homepage_stores = HomeStaticStore::orderBy('id', 'asc')->get();
        $homestatic_title = 'JEEB Instant Homepage';
        $homestatic_key = 'instant';
        $homestatic_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $homestatic_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>$homestatic_key])->where(function($query) {
                                return $query->whereNull('store_key')->orWhere('store_key', '=', '0');
                            })->orderBy('id', 'desc')->limit(10)->get();
        $group_enabled = true;
        $homepage_stores = HomeStaticStore::orderBy('id', 'asc')->get();
        $first_instant_store_group = \App\Model\InstantStoreGroup::where(['deleted'=>0])->orderBy('id', 'asc')->first();
        $first_instant_store_group_id = $first_instant_store_group ? $first_instant_store_group->id : 1;
        return view('admin.app_homepage.homestatic', [
            'homepage' => $homepage,
            'homepage_stores' => $homepage_stores,
            'homestatic_title' => $homestatic_title,
            'homestatic_key' => $homestatic_key,
            'homestatic_ens' => $homestatic_ens,
            'homestatic_ars' => $homestatic_ars,
            'group_enabled' => $group_enabled,
            'first_instant_store_group_id' => $first_instant_store_group_id,
        ]);
    }

    protected function stores_home_static_instant(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        $home_static_type = 'instant';
        $store_key = $request->input('store_key') ? $request->input('store_key') : 0;
        $lang = $request->input('lang');

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_stores_json/"; 

            $file_name = $store_key ? time().'_home_static_'.$home_static_type.'_'.$store_key.'.json' : time().'_home_static_'.$home_static_type.'.json';
            $path = \Storage::putFileAs('public/home_static_stores_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => $home_static_type,
                'store_key' => $store_key,
                'lang' => $lang,
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            if ($insert) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $lang,
                    'store_key' => $store_key
                ]);
            }
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return back()->with('success', 'Home static json added successfully');

    }

    protected function stores()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $homepage_stores = HomeStaticStore::orderBy('id', 'asc')->get();
        $beauty_and_makeup_store_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'beauty_and_makeup_store'])->orderBy('id', 'desc')->limit(10)->get();
        $beauty_and_makeup_store_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'beauty_and_makeup_store'])->orderBy('id', 'desc')->limit(10)->get();
        return view('admin.app_homepage.stores', [
            'homepage' => $homepage,
            'homepage_stores' => $homepage_stores,
            'beauty_and_makeup_store_ens' => $beauty_and_makeup_store_ens,
            'beauty_and_makeup_store_ars' => $beauty_and_makeup_store_ars,
        ]);
    }

    protected function stores_home_static_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048',
            'store_key' => 'required'
        ]);
        // dd($request->file('home_static_file'));
        $store_key = $request->input('store_key') ? $request->input('store_key') : 0;
        $language = $request->input('lang');

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_stores_json/"; 

            $file_name = time().'_home_static_'.$store_key.'.json';
            $path = \Storage::putFileAs('public/home_static_stores_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => $store_key,
                'lang' => $language,
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            if ($insert) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'model' => $store_key,
                    'language' => $language
                ]);
            }
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage/stores')->with('success', 'Home static json added successfully');

    }

    protected function index()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $home_static_1_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_1'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_1_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_1'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_2_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_2'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_2_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_2'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_3_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_3'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_3_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_3'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_4_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_4'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_4_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_4'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_5_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_5'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_5_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_5'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_6_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_6'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_6_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_6'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_7_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_7'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_7_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_7'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_8_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_8'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_8_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_8'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_9_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_9'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_9_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_9'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_10_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_10'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_10_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_10'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_11_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_11'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_11_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_11'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_12_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_12'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_12_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_12'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_13_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_13'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_13_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_13'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_14_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_14'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_14_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_14'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_15_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_15'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_15_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_15'])->orderBy('id', 'desc')->limit(10)->get();
        
        $home_personalized_1_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_personalized_1'])->orderBy('id', 'desc')->limit(10)->get();
        $home_personalized_1_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_personalized_1'])->orderBy('id', 'desc')->limit(10)->get();
        $home_personalized_2_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_personalized_2'])->orderBy('id', 'desc')->limit(10)->get();
        $home_personalized_2_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_personalized_2'])->orderBy('id', 'desc')->limit(10)->get();
        $home_personalized_3_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_personalized_3'])->orderBy('id', 'desc')->limit(10)->get();
        $home_personalized_3_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_personalized_3'])->orderBy('id', 'desc')->limit(10)->get();
        return view('admin.app_homepage.index', [
            'homepage' => $homepage,
            'home_static_1_ens' => $home_static_1_ens,
            'home_static_1_ars' => $home_static_1_ars,
            'home_static_2_ens' => $home_static_2_ens,
            'home_static_2_ars' => $home_static_2_ars,
            'home_static_3_ens' => $home_static_3_ens,
            'home_static_3_ars' => $home_static_3_ars,
            'home_static_4_ens' => $home_static_4_ens,
            'home_static_4_ars' => $home_static_4_ars,
            'home_static_5_ens' => $home_static_5_ens,
            'home_static_5_ars' => $home_static_5_ars,
            'home_static_6_ens' => $home_static_6_ens,
            'home_static_6_ars' => $home_static_6_ars,
            'home_static_7_ens' => $home_static_7_ens,
            'home_static_7_ars' => $home_static_7_ars,
            'home_static_8_ens' => $home_static_8_ens,
            'home_static_8_ars' => $home_static_8_ars,
            'home_static_9_ens' => $home_static_9_ens,
            'home_static_9_ars' => $home_static_9_ars,
            'home_static_10_ens' => $home_static_10_ens,
            'home_static_10_ars' => $home_static_10_ars,
            'home_static_11_ens' => $home_static_11_ens,
            'home_static_11_ars' => $home_static_11_ars,
            'home_static_12_ens' => $home_static_12_ens,
            'home_static_12_ars' => $home_static_12_ars,
            'home_static_13_ens' => $home_static_13_ens,
            'home_static_13_ars' => $home_static_13_ars,
            'home_static_14_ens' => $home_static_14_ens,
            'home_static_14_ars' => $home_static_14_ars,
            'home_static_15_ens' => $home_static_15_ens,
            'home_static_15_ars' => $home_static_15_ars,
            'home_personalized_1_ens' => $home_personalized_1_ens,
            'home_personalized_1_ars' => $home_personalized_1_ars,
            'home_personalized_2_ens' => $home_personalized_2_ens,
            'home_personalized_2_ars' => $home_personalized_2_ars,
            'home_personalized_3_ens' => $home_personalized_3_ens,
            'home_personalized_3_ars' => $home_personalized_3_ars
        ]);
    }

    protected function home_static_1_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_1.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_1',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_2_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_2.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_2',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_3_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_3.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_3',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_4_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_4.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_4',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_5_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_5.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_5',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_6_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_6.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_6',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_7_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_7.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_7',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_8_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_8.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_8',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_9_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_9.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_9',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_10_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_10.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_10',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_11_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_11.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_11',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_12_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_12.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_12',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_13_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_13.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_13',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_14_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_14.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_14',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_static_15_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_15.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_15',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_personalized_1_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_personalized_1.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_personalized_1',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_personalized_2_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_personalized_2.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_personalized_2',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function home_personalized_3_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);
        // dd($request->file('home_static_file'));

        if ($request->hasFile('home_static_file')) {

            // $json_path = str_replace('\\', '/', storage_path("app/public/home_static_json/"));
            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_personalized_3.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_personalized_3',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage')->with('success', 'Home static json added successfully');

    }

    protected function proccessHomeStaticJsonFile(Request $request)
    {   
        $language = $request->input('language');
        $model = $request->input('model');

        $home_static_json_file = \App\Model\HomeStatic::where('lang',$language)->where(['home_static_type' => $model])->orderBy('id', 'desc')->first();
        $home_static_json_file->update(['last_processed_at' => \Carbon\Carbon::now()]);
        
        Artisan::call('command:HomeStaticJsonUpdate', [
            'model' => $model,
            'language' => $language
        ]);

        return response()->json(['message' => 'Background command has been queued for execution.']);
    }

    protected function home_static_instant()
    {
        $homepage = Homepage::orderBy('index', 'asc')->get();
        $home_static_1_ens = HomeStatic::where(['lang'=>'en', 'home_static_type'=>'home_static_instant'])->orderBy('id', 'desc')->limit(10)->get();
        $home_static_1_ars = HomeStatic::where(['lang'=>'ar', 'home_static_type'=>'home_static_instant'])->orderBy('id', 'desc')->limit(10)->get();
        
        return view('admin.app_homepage.instant.index', [
            'home_static_1_ens' => $home_static_1_ens,
            'home_static_1_ars' => $home_static_1_ars
        ]);
    }

    protected function home_static_instant_store(Request $request)
    {

        $request->validate([
            'home_static_file' => 'required|max:2048'
        ]);

        if ($request->hasFile('home_static_file')) {

            $json_url_base = "home_static_json/"; 

            $file_name = time().'_home_static_instant.json';
            $path = \Storage::putFileAs('public/home_static_json/', $request->file('home_static_file'),$file_name);

            $insert_arr = [
                'home_static_type' => 'home_static_instant',
                'lang' => $request->input('lang'),
                'file_name' => $json_url_base.$file_name,
                'IP' => $request->ip(),
            ];
            $insert = HomeStatic::create($insert_arr);
            
            if (!$insert) {
                return back()->withInput()->with('error', 'Error in adding home static json file');
            } 
        
        } else {
            return back()->withInput()->with('error', 'Home static json file required');
        }
        return redirect('admin/app_homepage/instant')->with('success', 'Home static json added successfully');

    }

}
