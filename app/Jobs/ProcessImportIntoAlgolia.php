<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

require __DIR__ . "../../../vendor/autoload.php";

use Illuminate\Bus\Batchable;

class ProcessImportIntoAlgolia implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $batch;
    public $table;
    public $index;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batch, $table, $index)
    {
        $this->batch = $batch;
        $this->table = $table;
        $this->index = $index;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->index->saveObjects($this->batch);
    }
}
