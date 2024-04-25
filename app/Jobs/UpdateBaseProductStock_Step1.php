<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;

class UpdateBaseProductStock_Step1 implements ShouldQueue
{

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $data;
    public $data_unencoded;
    public $key;
    protected $id;
    protected $company_id;
    protected $batch_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key, $id, $company_id, $batch_id)
    {
        $this->data = json_decode($data);
        $this->data_unencoded = $data;
        $this->key = $key;
        $this->id = $id;
        $this->company_id = $company_id;
        $this->batch_id = $batch_id;

        // $this->onQueue('step1');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('UpdateBaseProductStock_Step1 store '.$this->id.' with the fk_company_id '.$this->company_id.' - key '.$this->key);
        
        if (is_array($this->data)) {
            
            foreach ($this->data as $value) {

                if (
                    !empty($value) &&
                    isset($value[0]) && 
                    isset($value[1]) && 
                    isset($value[2]) && 
                    isset($value[3]) && 
                    isset($value[4]) && 
                    isset($value[5])
                ) {
                    $insertArr = [
                        'itemcode' => $value[0],
                        'barcode' => $value[1],
                        'packing' => $value[2],
                        'rsp' => $value[3],
                        'stock' => round($value[4], 0),
                        'batch_id' => $this->batch_id,
                        'product_name_en' => $value[5],
                        'store_no' => $this->id,
                        'company_id' => $this->company_id
                    ];
                    \App\Model\ProductStockFromCsv::create($insertArr);
                } {
                    \Log::info($value);
                }
                
            }
            
        } else {
            \Log::info($this->data);

            // Store the csv in the path
            $stock_files_path = str_replace('\\', '/', storage_path("app/public/stock_files/"));
            $filePath = $stock_files_path.'failed----------myCSVFile-'.$this->key.'.csv';
            $fp = fopen($filePath, 'w+');
            fputcsv($fp, $this->data_unencoded);
            fclose($fp);
            
        }
    }
}
