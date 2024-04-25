<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Model\ProductStockFromCsv;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;

class ProcessBaseProductStockUpdateFromCsvToDb_Step2_V2 implements ShouldQueue
{

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $data;
    public $key;
    public $stock_per_set;
    public $id ;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $key, $stock_per_set, $store_id)
    {
        $this->data = $data;
        $this->key = $key;
        $this->stock_per_set = $stock_per_set;
        $this->id = $store_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Updating stock of batch ID: '.$this->key.'');

        $stocks = ProductStockFromCsv::where([
            'batch_id'=>$this->key
            ])->paginate($this->stock_per_set, ['*'], 'page', $this->data);

        $checked_count = 0;
        $matched_count = 0;
        $updated_count = 0;
        $added_new_product_count = 0;

        if (!$stocks) {
            \Log::info('No stocks found for the batch ID: '.$this->key.'');
        } else {
            foreach ($stocks as $stock) {

                \Log::info('Batch_id: '.$this->key.', Checking stock id: '.$stock->id.', with the itemcode '.$stock->itemcode.', with the barcode '.$stock->barcode);

                $checked = 1;
                $matched = 0;
                $base_product_store_id = 0;
                $base_product_id = 0;
                $updated = 0;
                $added_new_product = 0;
        
                $checked_count++;
                $itemcode = $stock->itemcode;
                $itemcode_without_0 = ltrim($stock->itemcode, '0');
                $barcode = $stock->barcode;
                $barcode_without_0 = ltrim($stock->barcode, '0');
        
                $selected_record = false;
                $itemCodeExist_without_0 = BaseProductStore::where(['itemcode'=>$itemcode_without_0, 'fk_store_id'=> $this->id, 'deleted' => 0])->first();
                if ($itemCodeExist_without_0) {
                    $selected_record = $itemCodeExist_without_0;
                } else {
                    $itemCodeExist = BaseProductStore::where(['itemcode'=>$itemcode, 'fk_store_id'=> $this->id, 'deleted' => 0])->first();
                    if ($itemCodeExist) {
                        $selected_record = $itemCodeExist;
                        $selected_record->update(['itemcode'=>$itemcode_without_0]);
                    }
                }
                        
                if($selected_record){

                    $matched=1;
                    $base_product_store_id = $selected_record->id;

                    $barcode_exists = false;

                    if ($selected_record->barcode==$barcode || $selected_record->barcode==$barcode_without_0) {
                        $barcode_exists = true;
                    }

                    if ($barcode_exists) {

                        $diff = $selected_record->product_price - (double)trim($stock->rsp);
                        if ($selected_record->allow_margin == 1 || ($selected_record->allow_margin == 0 && $diff < 0)) {
                            $priceArr = calculatePriceFromFormula((double)trim($stock->rsp), $selected_record->fk_store_id);
                            $insert_arr['margin'] = $priceArr[1];
                            $insert_arr['product_price'] = $priceArr[0];
                        } else { 
                            $profit = abs((double)trim($stock->rsp) - $selected_record->product_price);
                            $margin = number_format((($profit / (double)trim($stock->rsp)) * 100), 2);
                            $insert_arr['margin'] = $margin;
                            $insert_arr['product_price'] = $selected_record->product_price;
                        }
                        
                        $insert_arr['distributor_price'] = $stock->rsp;
                        $insert_arr['stock'] = $stock->stock;
                        $update_row = BaseProductStore::find($selected_record->id)->update($insert_arr);
                        
                        if ($update_row) {
                            $updated = 1;
                            // $update_base_product_res = update_base_product_store($selected_record->fk_product_id);
                            // if ($update_base_product_res) {
                            //     $update_base_product = $update_base_product_res->getData()->data;
                            //     if ($update_base_product && isset($update_base_product->fk_product_id)) {
                            //         $base_product_id = $update_base_product->fk_product_id;
                            //     }
                            // }
                            \Log::error('Bulk Stock Update From Server: Updating stock failed for the product store ID: '.$selected_record->id);
                        } else {
                            \Log::error('Bulk Stock Update From Server: Updating stock failed for the product store ID: '.$selected_record->id);
                        }

                    }else{

                        $latestStore = $selected_record;

                        $diff = $latestStore->product_price - (double)trim($stock->rsp);
                        if ($latestStore->allow_margin == 1 || ($latestStore->allow_margin == 0 && $diff < 0)) {
                            $priceArr = calculatePriceFromFormula((double)trim($stock->rsp), $latestStore->fk_store_id);
                            $insert_arr['margin'] = $priceArr[1];
                            $insert_arr['product_price'] = $priceArr[0];
                        } else { 
                            $profit = abs((double)trim($stock->rsp) - $latestStore->product_price);
                            $margin = number_format((($profit / (double)trim($stock->rsp)) * 100), 2);
                            $insert_arr['margin'] = $margin;
                            $insert_arr['product_price'] = $latestStore->product_price;
                        }
                        $insert_arr['itemcode'] = $latestStore->itemcode;
                        $insert_arr['barcode'] = $latestStore->barcode;
                        $insert_arr['unit'] = $latestStore->unit;
                        $insert_arr['other_names'] = $latestStore->other_names;
                        $insert_arr['distributor_price'] = $stock->rsp;
                        $insert_arr['stock'] = $stock->stock;
                        $insert_arr['allow_margin'] = $latestStore->allow_margin;
                        $insert_arr['fk_product_id'] = $latestStore->fk_product_id;
                        $insert_arr['fk_store_id'] = $latestStore->fk_store_id;
                        $insert_arr['is_active'] = 0;
                        
                        $added_new_product_row = BaseProductStock::create($insert_arr);
                        if ($added_new_product_row) {
                            $added_new_product = 1;
                        } else {
                            \Log::error('Bulk Stock Update From Server: Adding new product failed for the product barcode: '.$barcode);
                        }
                        
                    }
                }
                
                \App\Model\ProductStockFromCsv::find($stock->id)->update([
                    'checked' => $checked, 
                    'matched' => $matched, 
                    'base_product_store_id' => $base_product_store_id, 
                    'base_product_id' => $base_product_id,
                    'updated' => $updated, 
                    'added_new_product' => $added_new_product 
                ]);

            }
    
            \Log::info('Updated stock of batch ID: '.$this->key.' checked: '.$checked_count.' products_matched: '.$matched_count.', updated: '.$updated_count.' added_new_product: '.$added_new_product_count);

        }
        
    }
}
