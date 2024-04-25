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

class ProcessBaseProductStorePrice extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $base_product_store_id;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $base_product_store_id)
    {
        $this->base_product_store_id = $base_product_store_id;
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
            $base_product = BaseProduct::find($base_product_store->fk_product_id);

            // Update if found
            if ($base_product_store->allow_margin == 1 && $base_product) {
                $distributorPriceArr = $this->calculateDistributorPrice($base_product_store->product_distributor_price_before_back_margin, $base_product_store->fk_store_id);
                $insert_arr['product_distributor_price'] = $distributorPriceArr[0];
                $insert_arr['back_margin'] = $distributorPriceArr[1];
                $priceArr = $this->calculatePriceFromFormula($insert_arr['product_distributor_price'], $base_product->fk_offer_option_id, $base_product->fk_brand_id, $base_product->fk_sub_category_id, $base_product_store->fk_store_id);
                $insert_arr['margin'] = $priceArr[1];
                $insert_arr['product_store_price'] = $priceArr[0];
                $insert_arr['base_price'] = $priceArr[2];
                $insert_arr['base_price_percentage'] = $priceArr[3];
                $insert_arr['discount_percentage'] = $priceArr[4];
                $insert_arr['fk_price_formula_id'] = $priceArr[5];
                $base_product_store->update($insert_arr);
                
                // Update base product
                $update_base_product_row = $this->update_base_product($base_product->id);
                if ($update_base_product_row) {
                    if ($update_base_product_row['status']) {
                        \Log::error('ProcessBaseProductStorePrice: updated for '.$this->base_product_store_id);
                    } else {
                        \Log::error('ProcessBaseProductStorePrice: failed for '.$this->base_product_store_id);
                    }
                } else {
                    \Log::error('ProcessBaseProductStorePrice: not found for '.$this->base_product_store_id);
                }
            } 
            \Log::error('ProcessBaseProductStorePrice: found for '.$this->base_product_store_id.' base_product_store: found for '.$base_product_store->id.' Allow margin: '.$base_product_store->allow_margin);
            
        } else {
            \Log::error('ProcessBaseProductStorePrice: not found for '.$this->base_product_store_id);
        }
    }
    
}