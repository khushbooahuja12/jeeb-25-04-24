<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\BaseProductStore;

class BaseProductsStockUpdateInitiate extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:BaseProductsStockUpdateInitiate {store_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Intiating stock updating by switch all stocks allowed for stock update to 0';

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
        $store_id = $this->argument('store_id');
        \Log::info('php artisan command:BaseProductsStockUpdateInitiate '.$store_id);
        $product_store_update = BaseProductStore::where([
                'fk_store_id'=>$store_id,
                'deleted'=>0,
                'is_stock_update'=>1
            ])->update([
                'is_stock_update'=>0
            ]);
        if ($product_store_update) {
            \Log::info('Bulk Stock Update Initiaion Success');
        } else {
            \Log::info('Bulk Stock Update Initiaion Failed');
        }
    }
}
