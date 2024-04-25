<?php

namespace App\Jobs;

use App\Model\BaseProductStore;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProductItemcodeUpload implements ShouldQueue
{

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key)
    {
        $this->data = json_decode($data);
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->data as $value) {
            $itemcode = trim($value[3]);
            $exist = BaseProductStore::find($value[0]);
            if ($exist && $exist->fk_product_id==$value[2]) {
                BaseProductStore::find($value[0])->update(['itemcode' => $itemcode]);
                \Log::error('Bulk BaseProductStore: Adding id: '.$value[0].' itemcode: '.$itemcode);
            }
        }
    }
}
