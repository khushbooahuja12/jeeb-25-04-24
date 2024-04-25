<?php

namespace App\Jobs;

use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;
use App\Model\PriceFormula;
use App\Model\ProductOfferOption;
use App\Model\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    // -------------------------------
    // Update base_product from store
    // -------------------------------
    protected function selectBaseProductStore($itemcode,$barcode,$store_id) {
        
        // Required variables for base_product_store
        $matched_records = false;
        $matched_record = false;
        $matched_record_ids = [];
        $itemcode_matched = false;
        $barcode_matched = false;
        $itemcode_without_0 = ltrim($itemcode, '0');
        $barcode_without_0 = ltrim($barcode, '0');

        // Perform processing or computations on store data
        $itemCodeExist_without_0 = BaseProductStore::where(['itemcode'=>$itemcode_without_0, 'fk_store_id'=>$store_id, 'deleted' => 0])->get();
        if ($itemCodeExist_without_0->count()) {
            $matched_records = $itemCodeExist_without_0;
        } else {
            $itemCodeExist = BaseProductStore::where(['itemcode'=>$itemcode, 'fk_store_id'=>$store_id, 'deleted' => 0])->get();
            if ($itemCodeExist->count()) {
                $matched_records = $itemCodeExist;
            }
        }

        // Check barcode match
        if($matched_records && $matched_records->count()){
            $itemcode_matched = true;
            foreach ($matched_records as $record) {
                $matched_record_ids[] = $record->fk_product_id;
                $record->update(['itemcode'=>$itemcode_without_0]);
                if ($record->barcode==$barcode || $record->barcode==$barcode_without_0) {
                    $matched_record = $record;
                    $barcode_matched = true;
                }
            }
        }

        return ['matched_record'=>$matched_record,'itemcode_matched'=>$itemcode_matched,'barcode_matched'=>$barcode_matched,'matched_record_ids'=>$matched_record_ids];

    }
    
    // -------------------------------
    // Update base_product
    // -------------------------------
    protected function update_base_product($product)
    {
        $total_base_broduct_stock = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->sum('stock');
        $base_product_store = BaseProductStore::where(['fk_product_id' => $product,['stock','>',0],'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->orderby('product_store_price','asc')->orderBy('margin', 'desc')->first();
        $status = false;
        $base_product = false;
        $update_arr = [];

        // If no stock from any store get a less price from 0 stock stores
        if(!$base_product_store){
            $base_product_store = BaseProductStore::where(['fk_product_id' => $product,'deleted'=> 0,'is_active'=> 1,'is_store_active'=> 1])->orderby('product_store_price','asc')->orderBy('margin', 'desc')->first();
        }
        
        if($base_product_store){

            $update_arr = [
                'fk_product_store_id' => $base_product_store->id,
                'fk_store_id' => $base_product_store->fk_store_id,
                'product_distributor_price' => $base_product_store->product_distributor_price,
                'product_store_price' => $base_product_store->product_store_price,
                'base_price' => $base_product_store->base_price,
                'product_store_stock' => $total_base_broduct_stock,
                'product_store_updated_at' => $base_product_store->product_store_updated_at,
                'itemcode' => $base_product_store->itemcode,
                'barcode' => $base_product_store->barcode,
                'allow_margin' => $base_product_store->allow_margin,
                'product_distributor_price_before_back_margin' => $base_product_store->product_distributor_price_before_back_margin,
                'fk_price_formula_id' => $base_product_store->fk_price_formula_id,
                'margin' => $base_product_store->margin,
                'back_margin' => $base_product_store->back_margin,
                'base_price_percentage' => $base_product_store->base_price_percentage,
                'discount_percentage' => $base_product_store->discount_percentage
            ];
        
        } else {
             
            $update_arr = [
                'product_store_stock' => 0,
                'product_store_updated_at' => date('Y-m-d H:i:s')
            ];
        
        }
        
        $base_product = BaseProduct::find($product);
        if($base_product && !empty($update_arr)){

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

    // -------------------------------
    // Update base_product store
    // -------------------------------
    protected function update_base_product_store($product)
    {
        $base_product = BaseProduct::find($product);

        $base_product_stores = BaseProductStore::where(['fk_product_id' => $product, 'deleted' => 0])->get();

        foreach ($base_product_stores as $key => $base_product_store) {

            $update_arr = [
                'product_type' => $base_product->product_type,
                'recipe_id' => $base_product->recipe_id,
                'recipe_variant_id' => $base_product->recipe_variant_id,
                'recipe_ingredient_id' => $base_product->recipe_ingredient_id,
                'parent_id' => $base_product->parent_id,
                'fk_category_id' => $base_product->fk_category_id,
                'fk_sub_category_id' => $base_product->fk_sub_category_id,
                'fk_brand_id' => $base_product->fk_brand_id,
                'product_name_en' => $base_product->product_name_en,
                'product_name_ar' => $base_product->product_name_ar,
                'product_image_url' => $base_product->product_image_url,
                'unit' => $base_product->unit,
                'min_scale' => $base_product->min_scale,
                'max_scale' => $base_product->max_scale,
                'fk_main_tag_id' => $base_product->fk_main_tag_id,
                'main_tags' => $base_product->main_tags,
                '_tags' => $base_product->_tags,
                'search_filters' => $base_product->search_filters,
                'custom_tag_bundle' => $base_product->custom_tag_bundle,
                'desc_en' => $base_product->desc_en,
                'desc_ar' => $base_product->desc_ar,
                'characteristics_en' => $base_product->characteristics_en,
                'characteristics_ar' => $base_product->characteristics_ar,
                'offers' => $base_product->offers,
                'fk_offer_option_id' => $base_product->fk_offer_option_id,
                'country_icon' => $base_product->country_icon,
                'country_code' => $base_product->country_code
            ];

            $update = BaseProductStore::find($base_product_store->id);
            $update->update($update_arr);
        }

        return true;
    }

    
    // -------------------------------
    // Get rounded number for price
    // -------------------------------
    protected function getRoundedOffNumber($number)
    {
        $reqNum = $number;
        $floor = floor($number);
        $decPart = $number - $floor;
    
        // Rounding decimal numbers
        $newDecPart = 0.00;
        if ($floor>=5) {
            if ($decPart>0.75) {
                $newDecPart = 0.99;
            }
            elseif ($decPart>0.50) {
                $newDecPart = 0.75;
            }
            elseif ($decPart>0.25) {
                $newDecPart = 0.50;
            }
            elseif ($decPart>0.00) {
                $newDecPart = 0.25;
            }
            $reqNum = $floor + $newDecPart;
        }
    
        return number_format($reqNum, 2);
    }
    
    // -------------------------------
    // Price calculation based on offers option and pricing formula
    // -------------------------------
    protected function calculateDistributorPrice($distributor_price_before_back_margin, $store_id)
    {

        $distributor_price = $distributor_price_before_back_margin;
        $back_margin = 0;
        // Back margin
        $store = Store::find($store_id);
        if ($store) {
            $back_margin = $store->back_margin;
            $distributor_price = $distributor_price * (100-$back_margin)/100;
        }
        return [$distributor_price,$back_margin];

    }
    protected function calculatePriceFromFormula($distributor_price, $offer_option_id=0, $brand_id=0, $sub_category_id=0, $store_id=0)
    {
    
        // Returning variables
        $selling_price = 0;
        $base_price = 0;
        $price_percentage = 0;
        $base_price_percentage = 0;
        $discount_percentage = 0;
        $price_formula_id = 0;
    
        // Price formula objects
        $price_formula = false;
        $offer_formula = false;
        $offer_option = false;
    
        // If product level offer option sent
        if($offer_option_id !=0){
    
            $offer_option = ProductOfferOption::find($offer_option_id);
    
        } else {
    
            // If brand, subcategory, store level offer option found
            $brand_test = $brand_id ? true : false;
            $sub_category_test = $sub_category_id ? true : false;
            $store_test = $store_id ? true : false;
            $global_test = true;
            $conditions = [
                [$brand_id, $sub_category_id, $store_id, $brand_test], // brand, subcategory, store combo
                [$brand_id, $sub_category_id, 0, $brand_test], // brand, subcategory combo
                [$brand_id, 0, 0, $brand_test], // brand
                [0, $sub_category_id, $store_id, $sub_category_test], // subcategory , store
                [0, $sub_category_id, 0, $sub_category_test], // subcategory
                [0, 0, $store_id, $store_test], // store
                [0, 0, 0, $global_test] // global store
            ];
    
            foreach ($conditions as $key => $condition) { 
    
                // Skip checking if no data sent
                if (!$condition[3]) {
                    continue;
                }
                // Check for available function
                $brand_id = $condition[0];
                $sub_category_id = $condition[1];
                $store_id = $condition[2];
    
                $query = PriceFormula::where('fk_offer_option_id','!=', 0);
                
                if ($brand_id !== null || $brand_id !==0) {
                    $query->where('fk_brand_id', '=', $brand_id);
                }
                if ($sub_category_id !== null || $sub_category_id !==0) {
                    $query->where('fk_subcategory_id', '=', $sub_category_id);
                }
                if ($store_id !== null || $store_id !==0) {
                    $query->where('fk_store_id', '=', $store_id);
                }
    
                $offer_formula = $query->first();
    
                if ($offer_formula !== null) {
                    $price_formula_id = $offer_formula->id;  
                    break; // Exit the loop if a match is found
                }
            }
            
            if($offer_formula){
    
                $offer_option = ProductOfferOption::find($offer_formula->fk_offer_option_id);
    
            } else {
                
                // If no offer option found, collect pricing formula
                foreach ($conditions as $condition) {
    
                    // Skip checking if no data sent
                    if (!$condition[3]) {
                        continue;
                    }
                    // Check for available function
                    $brand_id = $condition[0];
                    $sub_category_id = $condition[1];
                    $store_id = $condition[2];
        
                    $query = PriceFormula::whereRaw("x1 < $distributor_price AND x2 >= $distributor_price");
                    
                    if ($brand_id !== null || $brand_id !==0) {
                        $query->where('fk_brand_id', '=', $brand_id);
                    }
                    if ($sub_category_id !== null || $sub_category_id !==0) {
                        $query->where('fk_subcategory_id', '=', $sub_category_id);
                    }
                    if ($store_id !== null || $store_id !==0) {
                        $query->where('fk_store_id', '=', $store_id);
                    }
        
                    $price_formula = $query->first();
        
                    if ($price_formula !== null) {
                        $price_formula_id = $price_formula->id;        
                        break; // Exit the loop if a match is found
                    }
                }
                
            }
    
        }
    
        if ($offer_option) {
    
            // If offer option found, calculate base price and selling price
            $base_price = getRoundedOffNumber($distributor_price + ($distributor_price * ($offer_option->base_price_percentage / 100)));
            $selling_price = $base_price - ($base_price * ($offer_option->discount_percentage / 100));
            $selling_price  = getRoundedOffNumber($selling_price);
    
            $price_percentage = $distributor_price>0 ? (($selling_price - $distributor_price) / $distributor_price * 100) : 0;
            
            \Log::info('Price calculation with offer option: '.$offer_option->id.' for distributor_price:'.$distributor_price.' selling_price:'.$selling_price.', base_price:'.$base_price);
            
        } elseif ($price_formula) {
    
            // Calculate base price and selling price based on selected pricing formula
            $x1 = $price_formula->x1;
            $x2 = $price_formula->x2;
            $x3 = $price_formula->x3;
            $x4 = $price_formula->x4;
    
            if ($x4 == '0') { //condition in which distributor price is more than 200
                $price_percentage = $x3;
            } else {
                $numerator = ($distributor_price - $x1) * $price_formula->x3x4;
    
                $price_percentage = $price_formula->x2x1>0 ? ($price_formula->x3x4 - (($numerator) / $price_formula->x2x1)) + $x4 : $x4;
            }
    
            $selling_price = $distributor_price + ($distributor_price * $price_percentage / 100);
            $selling_price = getRoundedOffNumber($selling_price);
            $base_price = $selling_price;
            
            \Log::info('Price calculation for distributor_price:'.$distributor_price.', x1 '.$x1.' x2 '.$x2.' x3 '.$x3.' x4 '.$x4.' x3x4 '.$price_formula->x3x4.' x2x1 '.$price_formula->x2x1);
            
        } else {
            
            \Log::info('Price calculation formula not found for distributor_price:'.$distributor_price.', offer_option_id:'.$offer_option_id.', brand_id:'.$brand_id.', sub_category_id:'.$sub_category_id.', store_id:'.$store_id);
        
        }
    
        $selling_price = floatval(str_replace(',','',$selling_price));
        $base_price = floatval(str_replace(',','',$base_price));
        
        $base_price_percentage = $distributor_price>0 ? number_format(((($base_price-$distributor_price)/$distributor_price) * 100), 2) : 0;
        $discount_percentage = $base_price>0 ? number_format(((($base_price-$selling_price)/$base_price) * 100), 2) : 0;
        
        return [$selling_price, $price_percentage, $base_price, $base_price_percentage, $discount_percentage, $price_formula_id];
    
    }    

}