<?php

namespace App\Jobs;

use App\Model\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProductUpload implements ShouldQueue
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
            // $res = explode(',', $value[1]);
            // $tags = json_encode($res, JSON_UNESCAPED_UNICODE);
            $barcode = $value[1];
            $exist = Product::find($value[0]);
            if ($exist) {
                Product::find($value[0])->update(['barcode' => $barcode]);
            }
        }
    }
}
