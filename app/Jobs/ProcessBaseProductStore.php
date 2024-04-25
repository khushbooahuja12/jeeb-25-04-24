<?php

namespace App\Jobs;

use App\Model\BaseProductStore;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProductStore extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $key;
    protected $base_product_id;
    protected $value;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(string $key, int $base_product_id, string $value)
    {
        $this->key = $key;
        $this->base_product_id = $base_product_id;
        $this->value = trim($value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get base product
        $base_product_store = BaseProductStore::find($this->base_product_id);
        if ($base_product_store) {
            $base_product_store->update([$this->key=>$this->value]);
            // Update base product
            $this->update_base_product($base_product_store->fk_product_id);
            \Log::error('UpdateBaseProductSingleColumn_'.$this->key.': updatred for '.$this->base_product_id.' as '.$this->value);
        } else {
            \Log::error('UpdateBaseProductSingleColumn_'.$this->key.': not found for '.$this->base_product_id.' as '.$this->value);
        }
    }
    
}