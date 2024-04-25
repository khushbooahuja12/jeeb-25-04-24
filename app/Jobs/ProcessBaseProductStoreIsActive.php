<?php

namespace App\Jobs;

use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProductStoreIsActive extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $base_product_store_id;
    protected $status;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $base_product_store_id, int $status)
    {
        $this->base_product_store_id = $base_product_store_id;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get base product
        $base_product_store = BaseProductStore::find($this->base_product_store_id);
        if ($base_product_store) {
            // Update if found
            if ($base_product_store) {
                $insert_arr['is_store_active'] = $this->status;
                $base_product_store->update($insert_arr);
                
                // Update base product
                $base_product = BaseProduct::find($base_product_store->fk_product_id);
                if ($base_product) {
                    $update_base_product_row = $this->update_base_product($base_product->id);
                    if ($update_base_product_row) {
                        if ($update_base_product_row['status']) {
                            \Log::error('ProcessBaseProductStoreIsActive: updated for '.$this->base_product_store_id);
                        } else {
                            \Log::error('ProcessBaseProductStoreIsActive: failed for '.$this->base_product_store_id);
                        }
                    } else {
                        \Log::error('ProcessBaseProductStoreIsActive: not found for '.$this->base_product_store_id);
                    }
                } else {
                    \Log::error('ProcessBaseProductStoreIsActive: updated for '.$this->base_product_store_id.' but base_product not found');
                }
            } 
            \Log::error('ProcessBaseProductStoreIsActive: found for '.$this->base_product_store_id.' base_product_store: found for '.$base_product_store->id.' Allow margin: '.$base_product_store->allow_margin);
            
        } else {
            \Log::error('ProcessBaseProductStoreIsActive: not found for '.$this->base_product_store_id);
        }
    }
    
}