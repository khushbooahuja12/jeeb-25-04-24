<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessStock;

class ProcessBaseProductStockUpdateFromCsvToDb_Step2_V3 implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $stocks;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Dispatch a sub-job for each user ID
        Bus::batch(
            collect($this->stocks)->map(function ($stock) {
                return new ProcessStock($stock->id);
            })
        )->name('Stock_update_bp_step2_V3')->dispatch();
    }
}