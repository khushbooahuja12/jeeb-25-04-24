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

class ProcessBaseProductStockUpdateFromCsvToDb_Step2 implements ShouldQueue
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
        
                $itemCodeExist = BaseProductStore::whereIn('itemcode', [$itemcode, $itemcode_without_0])
                ->where(['fk_store_id'=> $this->id, 'deleted' => 0])
                ->first();
                if($itemCodeExist){
                    $itemCodeExist->update(['itemcode'=>$itemcode_without_0]);
                    
                    $products_matched_count++;
                    $barCodeExist = BaseProductStore::where('itemcode','=', $itemCodeExist->itemcode)
                    ->whereIn('barcode', [$barcode, $barcode_without_0])
                    ->where(['fk_store_id' => $this->id, 'deleted' => 0])
                    ->first();
                    if ($barCodeExist) {
    
                        $updated_count++;
                        $distributor_price_key = 'store' . $stock->store_no . '_distributor_price';
                        
                        $diff = $barCodeExist->product_price - (double)trim($stock->rsp);
                        if ($barCodeExist->allow_margin == 1 || ($barCodeExist->allow_margin == 0 && $diff < 0)) {
                            $priceArr = calculatePriceFromFormula((double)trim($stock->rsp), $barCodeExist->fk_store_id);
                            $insert_arr['margin'] = $priceArr[1];
                            $insert_arr['product_price'] = $priceArr[0];
                        } else { 
                            $profit = abs((double)trim($stock->rsp) - $barCodeExist->product_price);
                            $margin = number_format((($profit / (double)trim($stock->rsp)) * 100), 2);
                            $insert_arr['margin'] = $margin;
                            $insert_arr['product_price'] = $barCodeExist->product_price;
                        }
                        
                        $insert_arr['distributor_price'] = $stock->rsp;
                        $insert_arr['stock'] = $stock->stock;
                        $update_row = BaseProductStore::find($barCodeExist->id)->update($insert_arr);
                        \Log::info('Adding stock step 2 for store '.$this->id.' with the itemcode '.$itemCodeExist->itemcode.' and barcode '.$barCodeExist->id.' with the price '.$insert_arr['product_price'].' the distriputer price '.$insert_arr['distributor_price']);
                        \Log::info($stock);

                        if ($update_row) {
                            $updated = 1;
                            $this->update_base_product($barCodeExist->fk_product_id);
                        } else {
                            \Log::info('Bulk Stock Update From Server: Updating stock failed for the product store ID: '.$barCodeExist->id);
                        }

                    }else{

                        $added_new_product_count++;
                        $latestStore = BaseProductStore::where(['itemcode'=>(int)trim($itemcode_without_0),'fk_store_id'=> $this->id])->orderBy('id','desc')->first();

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
                        $insert_arr['allow_margin'] = $latestStore->allow_margin;
                        $insert_arr['fk_product_id'] = $latestStore->fk_product_id;
                        $insert_arr['fk_store_id'] = $latestStore->fk_store_id;
                        $insert_arr['is_active'] = 0;
                        
                        $added_new_product_row = BaseProductStock::create($insert_arr);
                        if ($added_new_product_row) {
                            $added_new_product = 1;
                        } else {
                            \Log::info('Bulk Stock Update From Server: Adding new product failed for the product ID: '.$barCodeExist->id);
                        }
                    }
                }
                
                ProductStockFromCsv::find($stock->id)->update([
                    'checked' => $checked, 
                    'updated' => $updated, 
                    'added_new_product' => $added_new_product 
                ]);
        
            }
    
            \Log::info('Updated stock of batch ID: '.$this->key.' checked: '.$checked_count.' products_matched: '.$products_matched_count.', updated: '.$updated_count.' added_new_product: '.$added_new_product_count);

        }
        
    }
}
