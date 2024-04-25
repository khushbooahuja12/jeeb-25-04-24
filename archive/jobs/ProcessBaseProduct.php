<?php

namespace App\Jobs;

use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;
use App\Model\PriceFormula;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected function selectBaseProductStore($itemcode,$barcode,$store_id) {
        
        // Required variables for base_product_store
        $matched_record = false;
        $barcode_matched = false;
        $itemcode_without_0 = ltrim($itemcode, '0');
        $barcode_without_0 = ltrim($barcode, '0');

        // Perform processing or computations on store data
        $itemCodeExist_without_0 = BaseProductStore::where(['itemcode'=>$itemcode_without_0, 'fk_store_id'=>$store_id, 'deleted' => 0])->first();
        if ($itemCodeExist_without_0) {
            $matched_record = $itemCodeExist_without_0;
        } else {
            $itemCodeExist = BaseProductStore::where(['itemcode'=>$itemcode, 'fk_store_id'=>$store_id, 'deleted' => 0])->first();
            if ($itemCodeExist) {
                $matched_record = $itemCodeExist;
                $matched_record->update(['itemcode'=>$itemcode_without_0]);
            }
        }

        // Check barcode match
        if($matched_record){
            if ($matched_record->barcode==$barcode || $matched_record->barcode==$barcode_without_0) {
                $barcode_matched = true;
            }
        }

        return ['matched_record'=>$matched_record,'barcode_matched'=>$barcode_matched];

    }
    
    protected function update_base_product($product)
    {
        $total_base_broduct_stock = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0])->sum('stock');
        $base_product_store = BaseProductStore::where(['fk_product_id' => $product,['stock','>',0],'deleted'=> 0])->orderby('product_price','asc')->first();
        $status = false;
        $base_product = false;

        if($base_product_store){

            $update_arr = [
                'fk_product_store_id' => $base_product_store->id,
                'fk_store_id' => $base_product_store->fk_store_id,
                'product_distributor_price' => $base_product_store->distributor_price,
                'product_store_price' => $base_product_store->product_price,
                'base_price' => $base_product_store->base_price,
                'product_store_stock' => $total_base_broduct_stock,
                'product_store_updated_at' => date('Y-m-d H:i:s'),
            ];
        
        }else{

            $update_arr = [
                'product_store_stock' => 0,
                'product_store_updated_at' => date('Y-m-d H:i:s')
            ];

        }

        $base_product = BaseProduct::find($product);
        if($base_product){

            $update = $base_product->update($update_arr);
            if($update){
                $base_product = $base_product->refresh();
                $status = true;
            }else{
                $status = false;
            }
            
        }else{

            $status = false;

        }

        return ['status' => $status, 'base_product' => $base_product];
    }

    protected function calculatePriceFromFormula($distributor_price)
    {
        $res = PriceFormula::whereRaw("x1 < $distributor_price AND x2 >= $distributor_price")
            ->first();

        if ($res) {
            $x1 = $res->x1;
            $x2 = $res->x2;
            $x3 = $res->x3;
            $x4 = $res->x4;

            if ($x4 == '0') { //condition in which distributor price is more than 200
                $pricePercentage = $x3;
            } else {
                $numerator = ($distributor_price - $x1) * $res->x3x4;

                $pricePercentage = ($res->x3x4 - (($numerator) / $res->x2x1)) + $x4;
            }
            \Log::info('V3 Price calculation x1 '.$x1.' x2 '.$x2.' x3 '.$x3.' x4 '.$x4.' x3x4 '.$res->x3x4.' x2x1 '.$res->x2x1);
        
        } else {
            $pricePercentage = 0;
            \Log::info('V3 Price calculation pricePercentage is 0');
        
        }

        $selling_price = $distributor_price + ($distributor_price * $pricePercentage / 100);
        $finalPrice = $this->getRoundedOffNumber($selling_price);

        \Log::info('V3 Price calculation distribution price '.$distributor_price.', now final price '.$finalPrice.', now selling price '.$selling_price.'');
        
        return [$finalPrice, $pricePercentage];
    }

    protected function getRoundedOffNumber($number)
    {
        $floor = floor($number);
        $decPart = $number - $floor;
    
        // Removing unnecessorily adding 0.1 into the price based on the request from Prakash on 12/01/2022
        // $newDecPart = substr($decPart + 0.1, 0, 3);  
        
        $newDecPart = substr($decPart, 0, 3);
        $reqNum = number_format(($floor + $newDecPart), 2);
    
        return $reqNum;
    }
    
}