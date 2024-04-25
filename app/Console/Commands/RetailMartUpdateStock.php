<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use App\Jobs\RetailMartUpdateSingleStoreStock;

class RetailMartUpdateStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'RetailMart:UpdateStock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update store stocks regularly';

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
    public function handle()
    {
        try {
            // Constatnt RetialMart ID from DB
            $retailmart_id = env("RETAILMART_ID");
            // Get stores under RetialMart
            $stores = \App\Model\Store::where(['company_id' => $retailmart_id, 'deleted' => 0, 'status' => 1])
                    ->orderBy('id','desc')->get(['api_url','id']);
            if ($stores->first()) {
                foreach ($stores as $store) {
                    // Update the single store data
                    \Artisan::call('RetailMart:UpdateSingleStoreStock',['id'=>$store->id]);
                }
            }
            \Log::info('Updated all '.$stores->count().' stores');
        } catch (\Exception $e) {
            \Log::error("Updating stores stock failed:: ". $e->getCode() . " :: " . $e->getMessage() . " at " . $e->getLine() . " of " . $e->getFile());
        }
        
        return 0;
    }
}
