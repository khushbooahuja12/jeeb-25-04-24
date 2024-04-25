<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Jobs\ProcessBaseProductStock;

class UpdateBaseProductStock_Step2 implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $key;
    protected $stocks;
    protected $store_id;
    protected $batch_id;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($key, $stocks, $store_id, $batch_id)
    {
        $this->key = $key;
        $this->stocks = $stocks;
        $this->store_id = $store_id;
        $this->batch_id = $batch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check valid
        if (is_array($this->stocks) && count($this->stocks)) {
            // Dispatch a sub-job for each product ID
            $jobs = collect($this->stocks)->map(function ($stock) {
                return new ProcessBaseProductStock($stock['id']);
            });
            $last_index = count( $jobs ) - 1;
            $jobs[$last_index] = new ProcessBaseProductStock($this->stocks[$last_index]['id'],true);
            // Finally dispatch out of stock job
            Bus::batch($jobs)->name('UpdateBaseProductStock_Step2_StoreID:'.$this->store_id.'_batch_id:'.$this->batch_id.'_key:'.$this->key)->dispatch();
        }
    }
}