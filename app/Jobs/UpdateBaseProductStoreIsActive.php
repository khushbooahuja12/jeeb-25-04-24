<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessBaseProductStorePrice;

class UpdateBaseProductStoreIsActive implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $key;
    protected $base_products;
    protected $store_id;
    protected $status;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($key, $base_products,$store_id,$status)
    {
        $this->key = $key;
        $this->base_products = $base_products;
        $this->store_id = $store_id;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('UpdateBaseProductStoreIsActive[store_id:'.$this->store_id.',status:'.$this->status.'-'.$this->key.' started');
        
        // Check valid
        if (is_array($this->base_products) && count($this->base_products)) {
            // Dispatch a sub-job for each product ID
            Bus::batch(
                collect($this->base_products)->map(function ($base_product) {
                    return new ProcessBaseProductStoreIsActive($base_product['id'],$this->status);
                })
            )->name('UpdateBaseProductStoreIsActive[store_id:'.$this->store_id.',status:'.$this->status.']-'.$this->key)->dispatch();
        }
    }
}