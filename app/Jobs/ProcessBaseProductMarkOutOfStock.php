<?php

namespace App\Jobs;

use App\Model\BaseProductStore;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProductMarkOutOfStock extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $base_product_store_id;

    // To match the base_products_store
    protected $base_product_store;

    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $base_product_store_id)
    {
        $this->base_product_store_id = $base_product_store_id;
        $this->base_product_store = false;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Fetch base_product_store record
        $this->base_product_store = BaseProductStore::find($this->base_product_store_id);
        // Fetch stock data from the database based on $this->stockId
        if ($this->base_product_store) {

            BaseProductStore::find($this->base_product_store_id)->update([
                'is_stock_update'=>1,
                'stock'=>0,
                'product_store_stock'=>0
            ]);
            // Update base product
            $this->update_base_product($this->base_product_store->fk_product_id);

        } else {

            \Log::info('Bulk Stock Update From Server: stock record not found for ID: '.$this->stock_id);

        }

    }
    
}