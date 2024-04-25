<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStockUpdateFromCsv implements ShouldQueue
{

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $data;
    public $key;
    protected $id;
    protected $company_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key, $id, $company_id)
    {
        $this->data = json_decode($data);
        $this->key = $key;
        $this->id = $id;
        $this->company_id = $company_id;

        // $this->onQueue('step1');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('ProcessStockUpdateFromCsv3 store '.$this->id.' with the fk_company_id '.$this->company_id);
        
        foreach ($this->data as $value) {
            $itemCodeExist = \App\Model\Product::where(['itemcode' => $value[0], 'fk_company_id' => $this->company_id])->first();

            \Log::info('Updated store '.$this->id.' with the itemcode '.$value[0].' with the fk_company_id '.$this->company_id);

            if ($itemCodeExist) {
                \Log::info('itemCodeExist found '.$value[0]);
    
                $barCodeExist = \App\Model\Product::where(['barcode' => $value[1], 'itemcode' => $value[0], 'fk_company_id' => $this->company_id])->first();
                if ($barCodeExist) {
                    \Log::info('barCodeExist found '.$value[0].' - '.$value[1]);
                    $arr = [
                        'store' . $this->id => !empty(trim($value[4])) ? trim($value[4]) : 0,
                        'store' . $this->id . '_distributor_price' => (!empty($value[3]) && is_numeric($value[3])) ? trim($value[3]) : $barCodeExist->distributor_price,
                    ];
                    if (!empty($value[3]) && is_numeric($value[3])) {
                        $price = calculatePriceFromFormula(trim($value[3]));
                        $arr['store' . $this->id . '_price'] = $price[0];
                    }
                    \App\Model\Product::find($barCodeExist->id)->update($arr);
                } else {
                    \Log::info('barCodeExist not found'.$value[0].' - '.$value[1]);
                    if (!empty($itemCodeExist->barcode)) {
                        \App\Model\Product::find($itemCodeExist->id)->update(['is_stock_update' => 1]);
                    }

                    $file = \App\Model\File::find($itemCodeExist->product_image);
                    if ($file) {
                        $create = \App\Model\File::create([
                            'file_path' => $file->file_path,
                            'file_name' => $file->file_name,
                            'file_ext' => $file->file_ext
                        ]);
                        $product_image = $create->id;
                    } else {
                        $product_image = $itemCodeExist->product_image;
                    }

                    $distributor_price_key = 'store' . $this->id . '_distributor_price';
                    $insertArr = [
                        'deleted' => 1,
                        'stock' => 0,
                        'parent_id' => 0,
                        'itemcode' => $itemCodeExist->itemcode,
                        'barcode' => $value[1],
                        'product_name_en' => $itemCodeExist->product_name_en,
                        'product_name_ar' => $itemCodeExist->product_name_ar,
                        'product_image' => $product_image,
                        'product_image_url' => $itemCodeExist->product_image_url,
                        'unit' => $value[2],
                        'store' . $this->id . '_distributor_price' => !empty($value[3]) ? trim($value[3]) : $itemCodeExist->$distributor_price_key,
                        'distributor_id' => $itemCodeExist->distributor_id,
                        'store' . $this->id => !empty($value[4]) ? trim($value[4]) : 0,
                        'fk_category_id' => $itemCodeExist->fk_category_id,
                        'category_name' => $itemCodeExist->category_name,
                        'category_name_ar' => $itemCodeExist->category_name_ar,
                        'fk_sub_category_id' => $itemCodeExist->fk_sub_category_id,
                        'sub_category_name' => $itemCodeExist->sub_category_name,
                        'sub_category_name_ar' => $itemCodeExist->sub_category_name_ar,
                        'fk_brand_id' => $itemCodeExist->fk_brand_id,
                        'brand_name' => $itemCodeExist->brand_name,
                        'brand_name_ar' => $itemCodeExist->brand_name_ar,
                        '_tags' => $itemCodeExist->_tags,
                        'tags_ar' => $itemCodeExist->tags_ar,
                        'is_stock_update' => 1,
                        'fk_company_id' => $this->company_id
                    ];

                    if (!empty($value[3]) && is_numeric(trim($value[3]))) {
                        $price = calculatePriceFromFormula(trim($value[3]));
                        $insertArr['store' . $this->id . '_price'] = $price[0];
                        $insertArr['margin'] = $price[1];
                    } else {
                        $insertArr['store' . $this->id . '_price'] = $itemCodeExist->product_price;
                        $insertArr['margin'] = $itemCodeExist->margin;
                    }

                    \App\Model\Product::create($insertArr);
                }
            }
        }
    }
}
