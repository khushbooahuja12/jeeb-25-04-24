<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use DB;
use Artisan;

class HomeStaticStoreJsonUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HomeStaticStoreJsonUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the home static store JSON file for all stores';

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
        \Log::info('command:HomeStaticStoreJsonUpdate');
        $languages = ['en','ar'];
        // Plus - For main home page
        $home_static_type = 'plus';
        foreach ($languages as $language) {
            Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                'home_static_type' => $home_static_type,
                'lang' => $language,
                'store_key' => 0
            ]);
        }
        // Mall - For main home page
        $home_static_type = 'mall';
        foreach ($languages as $language) {
            Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                'home_static_type' => $home_static_type,
                'lang' => $language,
                'store_key' => 0
            ]);
        }
        // Instant - For main home page
        $home_static_type = 'instant';
        foreach ($languages as $language) {
            Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                'home_static_type' => $home_static_type,
                'lang' => $language,
                'store_key' => 0
            ]);
        }
        // Plus - For stores
        $home_static_stores = \App\Model\HomeStaticStore::select('store_key')->get();
        $home_static_type = 'plus';
        foreach ($home_static_stores as $key => $home_static_store) {
            foreach ($languages as $language) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $language,
                    'store_key' => $home_static_store->store_key
                ]);
            }
        }
        // Plus - For stores
        $home_static_type = 'mall';
        foreach ($home_static_stores as $key => $home_static_store) {
            foreach ($languages as $language) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $language,
                    'store_key' => $home_static_store->store_key
                ]);
            }
        }
        // Plus - For stores
        $home_static_type = 'instant';
        foreach ($home_static_stores as $key => $home_static_store) {
            foreach ($languages as $language) {
                Artisan::call('command:HomeStaticJsonUpdateFromDB', [
                    'home_static_type' => $home_static_type,
                    'lang' => $language,
                    'store_key' => $home_static_store->store_key
                ]);
            }
        }
        return 0;
    }

}
