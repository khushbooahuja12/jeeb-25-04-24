<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessOrderJson;

class UpdateOrderJson implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $key;
    protected $orders;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($key, $orders)
    {
        $this->key = $key;
        $this->orders = $orders;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('UpdateOrderJson-'.$this->key.' started');
        
        // Check valid
        if (is_array($this->orders) && count($this->orders)) {
            // Dispatch a sub-job for each product ID
            Bus::batch(
                collect($this->orders)->map(function ($order) {
                    return new ProcessOrderJson($order['id']);
                })
            )->name('UpdateOrderJson-'.$this->key)->dispatch();
        }
    }
}