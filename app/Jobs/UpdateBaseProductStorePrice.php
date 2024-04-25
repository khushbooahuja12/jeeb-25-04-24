<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessBaseProductStorePrice;

class UpdateBaseProductStorePrice implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $key;
    protected $base_products;
    protected $store_id;
    protected $subcategory_id;
    protected $brand_id;
    protected $offer_id;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($key, $base_products,$store_id=0,$subcategory_id=0,$brand_id=0,$offer_id=0)
    {
        $this->key = $key;
        $this->base_products = $base_products;
        $this->store_id = $store_id;
        $this->subcategory_id = $subcategory_id;
        $this->brand_id = $brand_id;
        $this->offer_id = $offer_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('UpdateBaseProductStorePrice[store_id:'.$this->store_id.',subcategory_id:'.$this->subcategory_id.',brand_id:'.$this->brand_id.',offer_id:'.$this->offer_id.']-'.$this->key.' started');
        
        // Check valid
        if (is_array($this->base_products) && count($this->base_products)) {
            // Dispatch a sub-job for each product ID
            Bus::batch(
                collect($this->base_products)->map(function ($base_product) {
                    return new ProcessBaseProductStorePrice($base_product['id']);
                })
            )->name('UpdateBaseProductStorePrice[store_id:'.$this->store_id.',subcategory_id:'.$this->subcategory_id.',brand_id:'.$this->brand_id.',offer_id:'.$this->offer_id.']-'.$this->key)->dispatch();
        }
    }
}