<?php

namespace App\Jobs;

use App\Model\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStockUpdateFromCsvToDb_Step2 implements ShouldQueue
{

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $data;
    public $key;
    public $stock_per_set;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key, $stock_per_set)
    {
        $this->data = $data;
        $this->key = $key;
        $this->stock_per_set = $stock_per_set;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        \Log::info('Updating stock of batch ID: '.$this->key.'');

        $stocks = \App\Model\ProductStockFromCsv::where([
            'batch_id'=>$this->key
            // 'checked'=>0
            ])->paginate($this->stock_per_set, ['*'], 'page', $this->data);
            // \Log::info($send_stocks->first());

        $checked_count = 0;
        $products_matched_count = 0;
        $updated_count = 0;
        $added_new_product_count = 0;

        if (!$stocks) {
            \Log::info('No stocks found for the batch ID: '.$this->key.'');
        } else {
            foreach ($stocks as $stock) {

                $checked = 1;
                $updated = 0;
                $added_new_product = 0;
                $checked_count++;
                $itemcode = $stock->itemcode;
                $itemcode_without_0 = ltrim($stock->itemcode, '0');
                $barcode = $stock->barcode;
                $barcode_without_0 = ltrim($stock->barcode, '0');
        
                $itemCodeExist = \App\Model\Product::whereIn('itemcode', [$itemcode, $itemcode_without_0])
                ->where(['fk_company_id' => $stock->company_id, 'deleted' => 0])
                ->first();
        
                if ($itemCodeExist) {
                    $itemCodeExist->update(['itemcode'=>$itemcode_without_0]);
    
                    $products_matched_count++;
                    $barCodeExist = \App\Model\Product::where('itemcode','=', $itemCodeExist->itemcode)
                    ->whereIn('barcode', [$barcode, $barcode_without_0])
                    ->where(['fk_company_id' => $stock->company_id])
                    ->first();
                    if ($barCodeExist) {
    
                        $updated_count++;
                        $distributor_price_key = 'store' . $stock->store_no . '_distributor_price';
                        $arr = [
                            'store' . $stock->store_no => !empty(trim($stock->stock)) ? (int) trim($stock->stock) : 0,
                            'store' . $stock->store_no . '_distributor_price' => (!empty($stock->rsp)) ? trim($stock->rsp) : $barCodeExist->$distributor_price_key,
                        ];
                        if (!empty($stock->rsp)) {
                            $price = calculatePriceFromFormula(trim($stock->rsp));
                            $arr['store' . $stock->store_no . '_price'] = $price[0];
                        }
                        $update_row = \App\Model\Product::find($barCodeExist->id)->update($arr);
                        if ($update_row) {
                            $updated = 1;
                        } else {
                            \Log::info('Bulk Stock Update From Server: Updating stock failed for the product ID: '.$barCodeExist->id);
                        }
                    
                    } else {
                        
                        $added_new_product_count++;
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
        
                        $distributor_price_key = 'store' . $stock->store_no . '_distributor_price';
                        $insertArr = [
                            'deleted' => 1,
                            'stock' => 0,
                            'parent_id' => 0,
                            'itemcode' => $itemCodeExist->itemcode,
                            'barcode' => $stock->barcode,
                            'product_name_en' => $itemCodeExist->product_name_en,
                            'product_name_ar' => $itemCodeExist->product_name_ar,
                            'product_image' => $product_image,
                            'product_image_url' => $itemCodeExist->product_image_url,
                            'unit' => $stock->packing,
                            'store' . $stock->store_no . '_distributor_price' => !empty($stock->rsp) ? trim($stock->rsp) : $itemCodeExist->$distributor_price_key,
                            'distributor_id' => $itemCodeExist->distributor_id,
                            'store' . $stock->store_no => !empty($stock->stock) ? trim($stock->stock) : 0,
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
                            'fk_company_id' => $stock->company_id
                        ];
        
                        if (!empty($stock->rsp) && is_numeric(trim($stock->rsp))) {
                            $price = calculatePriceFromFormula(trim($stock->rsp));
                            $insertArr['store' . $stock->store_no . '_price'] = $price[0];
                            $insertArr['margin'] = $price[1];
                        } else {
                            $insertArr['store' . $stock->store_no . '_price'] = $itemCodeExist->product_price;
                            $insertArr['margin'] = $itemCodeExist->margin;
                        }
        
                        $added_new_product_row = \App\Model\Product::create($insertArr);
                        if ($added_new_product_row) {
                            $added_new_product = 1;
                        } else {
                            \Log::info('Bulk Stock Update From Server: Adding new product failed for the product ID: '.$barCodeExist->id);
                        }
                    }
                }
        
                \App\Model\ProductStockFromCsv::find($stock->id)->update([
                    'checked' => $checked, 
                    'updated' => $updated, 
                    'added_new_product' => $added_new_product 
                ]);
        
            }
    
            \Log::info('Updated stock of batch ID: '.$this->key.' checked: '.$checked_count.' products_matched: '.$products_matched_count.', updated: '.$updated_count.' added_new_product: '.$added_new_product_count);

        }

    }
}
