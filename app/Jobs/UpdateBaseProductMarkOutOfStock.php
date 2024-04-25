<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Model\BaseProductStore;
use App\Model\ProductStockFromCsv;
use App\Jobs\ProcessBaseProductMarkOutOfStock;

class UpdateBaseProductMarkOutOfStock implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $batch_id;
    protected $store_id;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($batch_id, $store_id)
    {
        $this->batch_id = $batch_id;
        $this->store_id = $store_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // How many records processed
        $all_stock_records_count = ProductStockFromCsv::where(['batch_id'=>$this->batch_id])->count();
        $processed_stock_records_count = ProductStockFromCsv::where(['batch_id'=>$this->batch_id,'checked'=>1])->count();
        \Log::info('UpdateBaseProductMarkOutOfStock: All stocks-'.$all_stock_records_count.', processed stocks count-'.$processed_stock_records_count);

        if ($all_stock_records_count==$processed_stock_records_count) {
            $perPage = 10000; // Number of items per page
            $query = BaseProductStore::where([
                    'fk_store_id'=>$this->store_id,
                    'deleted'=>0,
                    'is_stock_update'=>0,
                    'stock'=>0
                ])->select('id');
            $paginator = $query->paginate($perPage);
            $lastPage = $paginator->lastPage();

            for ($i=1; $i <= $lastPage; $i++) { 
                
                $stocks = $query->paginate($perPage, ['*'], 'page', $i);
                $this->base_product_stores = $stocks->map(function ($base_product_store) {
                    if ($base_product_store !== null && is_object($base_product_store)) {
                        return [
                            'id' => $base_product_store->id
                        ];
                    }
                })->toArray();
                // Dispatch a sub-job for each product ID
                Bus::batch(
                    collect($this->base_product_stores)->map(function ($base_product_store) {
                        return new ProcessBaseProductMarkOutOfStock($base_product_store['id']);
                    })
                )->name('UpdateBaseProductMarkOutOfStock_StoreID:'.$this->store_id.'_batch:'.$this->batch_id.'_id:'.$i)->dispatch();
                \Log::info('UpdateBaseProductMarkOutOfStock: Started id-'.$i);

                // sleep(1);
            }
            
        } else {
            \Log::info('UpdateBaseProductMarkOutOfStock: Update Not Completed Yet');
        }
    }
}