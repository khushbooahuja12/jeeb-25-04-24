<?php

namespace App\Jobs;

use App\Model\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetaimartProductBarcodeUpload implements ShouldQueue
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
            $barcode = trim($value[1]);
            // $exist = Product::find($value[0]);
            $exist = Product::where(['itemcode'=>trim($value[0]),'fk_company_id'=>2])->first();
            \Log::info('Barcode Update for retailmart skucode: '.$value[0].' barcode: '.$barcode);
            
            if ($exist) {
                $exist->update(['barcode' => $barcode]);
                \Log::info('Found and updated barcode: '.$barcode);
            }
        }
    }
}
