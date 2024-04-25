<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessRecipe;

class UpdateRecipeSingleColumn implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $data;
    protected $key;

    /**
     * Create a new job instance.
     *
     * @param  array  $userIds
     * @return void
     */
    public function __construct($key, $data)
    {
        $this->data = $data;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::error('UpdateRecipeSingleColumn_'.$this->key.': started');

        // Check valid key
        if (
            $this->key=='_tags'
        ) {
            // Dispatch a sub-job for each product ID
            Bus::batch(
                collect($this->data)->map(function ($value) {
                    if (isset($value[0]) && isset($value[1])) {
                        return new ProcessRecipe($this->key, $value[0], $value[1]);
                    } else {
                        \Log::error('UpdateRecipeSingleColumn_'.$this->key.': required columns are not found in CSV');
                    }
                })
            )->name('UpdateRecipeSingleColumn_'.$this->key)->dispatch();
        }
    }
}