<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessUserScratchCard;

class UpdateUserScratchCard implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::error('UpdateUserScratchCard: started');

            // Dispatch a sub-job for each product ID
            Bus::batch(
                collect($this->data)->map(function ($value) {
                    if (isset($value[0]) && isset($value[1])) {
                        return new ProcessUserScratchCard($value[0], $value[1]);
                    } else {
                        \Log::error('UpdateUserScratchCard: required columns are not found in CSV');
                    }
                })
            )->name('UpdateUserScratchCard')->dispatch();
    }
}