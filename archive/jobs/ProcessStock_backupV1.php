<?php

namespace App\Jobs;

use App\Model\ProductStockFromCsv;
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

class ProcessStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $stock_id;
    protected $stock;
    protected $checked;
    protected $matched;
    protected $base_product_store_id;
    protected $base_product_id;
    protected $updated;
    protected $added_new_product;

    // To match the base_products_store
    protected $itemcode;
    protected $itemcode_without_0;
    protected $barcode;
    protected $barcode_without_0;
    
    // To confirm the base_products_store is matched
    protected $selected_record;
    protected $itemCodeExist_without_0;
    protected $itemCodeExist;
    protected $barcodeExists;

    // To change the base_products_store data
    protected $diff;
    protected $priceArr;
    protected $insert_arr;
    protected $profit;
    protected $margin;
    protected $update_row;

    // To change the base product
    protected $base_product;
    protected $total_base_broduct_stock;
    protected $lowest_price_product_store;
    protected $update_arr;
    protected $update_base_product;
    
    // To create the new base product if not existing
    protected $added_new_product_row;

    // To pricing formula
    protected $distributor_price;
    protected $res;
    protected $x1;
    protected $x2;
    protected $x3;
    protected $x4;
    protected $pricePercentage;
    protected $numerator;
    protected $selling_price;
    protected $finalPrice;

    // To pricing formula
    protected $number;
    protected $floor;
    protected $decPart;
    protected $newDecPart;
    protected $reqNum;

    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(int $stock_id)
    {
        $this->stock_id = $stock_id;
        $this->stock = false;
        $this->checked = 1;
        $this->matched = 0;
        $this->base_product_store_id = 0;
        $this->base_product_id = 0;
        $this->updated = 0;
        $this->added_new_product = 0;

        // ----------------
        $this->itemcode = "";
        $this->itemcode_without_0 = "";
        $this->barcode = "";
        $this->barcode_without_0 ="";
        
        // ----------------
        $this->selected_record = false;
        $this->itemCodeExist_without_0 = false;
        $this->itemCodeExist = false;
        $this->barcodeExists = false;
        
        // ----------------
        $this->diff = 0;
        $this->priceArr = [];
        $this->insert_arr = [];
        $this->profit = 0;
        $this->margin = 0;
        $this->update_row = false;
        
        // ----------------
        $this->base_product = false;
        $this->total_base_broduct_stock = 0;
        $this->lowest_price_product_store = false;
        $this->update_arr = [];
        $this->update_base_product = false;

        // ----------------
        $this->added_new_product_row = false;

        // ----------------
        $this->distributor_price = 0;
        $this->res = false;
        $this->x1 = 0;
        $this->x2 = 0;
        $this->x3 = 0;
        $this->x4 = 0;
        $this->pricePercentage = 0;
        $this->numerator = 0;
        $this->selling_price = 0;
        $this->finalPrice = 0;

        // ----------------
        $this->number = 0;
        $this->floor = 0;
        $this->decPart = 0;
        $this->newDecPart = 0;
        $this->reqNum = 0;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch stock data from the database based on $this->stockId
        $this->stock = ProductStockFromCsv::find($this->stock_id);

        // Required variables
        $this->checked = 1;
        $this->matched = 0;
        $this->base_product_store_id = 0;
        $this->base_product_id = 0;
        $this->updated = 0;
        $this->added_new_product = 0;

        // Required variables for base_product_store
        $this->itemcode = $this->stock->itemcode;
        $this->itemcode_without_0 = ltrim($this->stock->itemcode, '0');
        $this->barcode = $this->stock->barcode;
        $this->barcode_without_0 = ltrim($this->stock->barcode, '0');

        // Perform processing or computations on store data
        $this->itemCodeExist_without_0 = BaseProductStore::where(['itemcode'=>$this->itemcode_without_0, 'fk_store_id'=>$this->stock->store_no, 'deleted' => 0])->first();
        if ($this->itemCodeExist_without_0) {
            $this->selected_record = $this->itemCodeExist_without_0;
        } else {
            $this->itemCodeExist = BaseProductStore::where(['itemcode'=>$this->itemcode, 'fk_store_id'=>$this->stock->store_no, 'deleted' => 0])->first();
            if ($this->itemCodeExist) {
                $this->selected_record = $this->itemCodeExist;
                $this->selected_record->update(['itemcode'=>$this->itemcode_without_0]);
            }
        }

        // If store id and itemcode matched
        if($this->selected_record){

            $this->matched=1;
            $this->base_product_store_id = $this->selected_record->id;

            $this->barcodeExists = false;

            if ($this->selected_record->barcode==$this->barcode || $this->selected_record->barcode==$this->barcode_without_0) {
                $this->barcodeExists = true;
            }

            if ($this->barcodeExists) {

                $this->diff = $this->selected_record->product_price - (double)trim($this->stock->rsp);
                if ($this->selected_record->allow_margin == 1 || ($this->selected_record->allow_margin == 0 && $this->diff < 0)) {
                    $this->distributor_price = (double)trim($this->stock->rsp);
                    $this->priceArr = $this->calculatePriceFromFormula();
                    $this->insert_arr['margin'] = $this->priceArr[1];
                    $this->insert_arr['product_price'] = $this->priceArr[0];
                } else { 
                    $this->profit = abs((double)trim($this->stock->rsp) - $this->selected_record->product_price);
                    $this->margin = number_format((($this->profit / (double)trim($this->stock->rsp)) * 100), 2);
                    $this->insert_arr['margin'] = $this->margin;
                    $this->insert_arr['product_price'] = $this->selected_record->product_price;
                }
                
                $this->insert_arr['distributor_price'] = $this->stock->rsp;
                $this->insert_arr['stock'] = $this->stock->stock;
                $this->update_row = BaseProductStore::find($this->selected_record->id)->update($this->insert_arr);
                
                if ($this->update_row) {
                    $this->updated = 1;
                    \Log::error('Bulk Stock Update From Server: Updating stock of base_product_store: '.$this->selected_record->id);

                    // Update base product accordingly
                    $this->base_product_id = $this->selected_record->fk_product_id;
                    $this->total_base_broduct_stock = BaseProductStore::where(['fk_product_id' => $this->base_product_id,'deleted'=> 0])->sum('stock');
                    $this->lowest_price_product_store = BaseProductStore::where(['fk_product_id' => $this->base_product_id,['stock','>',0],'deleted'=> 0])->orderby('product_price','asc')->first();
                    
                    if($this->lowest_price_product_store){

                        $this->update_arr = [
                            'fk_product_store_id' => $this->lowest_price_product_store->id,
                            'fk_store_id' => $this->lowest_price_product_store->fk_store_id,
                            'product_store_price' => $this->lowest_price_product_store->product_price,
                            'product_store_stock' => $this->total_base_broduct_stock,
                            'product_store_updated_at' => date('Y-m-d H:i:s'),
                        ];
                    
                    }else{

                        $this->update_arr = [
                            'fk_product_store_id' => null,
                            'fk_store_id' => 0,
                            'product_store_price' => 0.00,
                            'product_store_stock' => 0,
                            'product_store_updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }

                    $this->base_product = BaseProduct::find($this->base_product_id);
                    if($this->base_product){
                        $this->update_base_product = $this->base_product->update($this->update_arr);
                        if ($this->update_base_product) {
                            \Log::info('V3 Base product updated on stock update: '.$this->base_product_id);
                        } else {
                            \Log::info('V3 Base product updated on stock update failed: '.$this->base_product_id);
                        }
                    }else{
                        \Log::info('V3 Base product not found to update on stock update: '.$this->base_product_id);
                    }

                } else {
                    \Log::error('Bulk Stock Update From Server: Updating stock of base_product_store failed: '.$this->selected_record->id);
                }

            }else{

                $this->diff = $this->selected_record->product_price - (double)trim($this->stock->rsp);
                if ($this->selected_record->allow_margin == 1 || ($this->selected_record->allow_margin == 0 && $this->diff < 0)) {
                    $this->distributor_price = (double)trim($this->stock->rsp);
                    $this->priceArr = $this->calculatePriceFromFormula((double)trim($this->stock->rsp));
                    $this->insert_arr['margin'] = $this->priceArr[1];
                    $this->insert_arr['product_price'] = $this->priceArr[0];
                } else { 
                    $this->profit = abs((double)trim($this->stock->rsp) - $this->selected_record->product_price);
                    $this->margin = number_format((($this->profit / (double)trim($this->stock->rsp)) * 100), 2);
                    $this->insert_arr['margin'] = $this->margin;
                    $this->insert_arr['product_price'] = $this->selected_record->product_price;
                }
                $this->insert_arr['itemcode'] = $this->selected_record->itemcode;
                $this->insert_arr['barcode'] = $this->selected_record->barcode;
                $this->insert_arr['unit'] = $this->selected_record->unit;
                $this->insert_arr['other_names'] = $this->selected_record->other_names;
                $this->insert_arr['distributor_price'] = $this->stock->rsp;
                $this->insert_arr['stock'] = $this->stock->stock;
                $this->insert_arr['allow_margin'] = $this->selected_record->allow_margin;
                $this->insert_arr['fk_product_id'] = $this->selected_record->fk_product_id;
                $this->insert_arr['fk_store_id'] = $this->selected_record->fk_store_id;
                $this->insert_arr['is_active'] = 0;
                
                $this->added_new_product_row = BaseProductStock::create($this->insert_arr);
                if ($this->added_new_product_row) {
                    $this->added_new_product = 1;
                    \Log::error('Bulk Stock Update From Server: Adding new product for the product barcode: '.$this->barcode);
                } else {
                    \Log::error('Bulk Stock Update From Server: Adding new product for the product barcode failed: '.$this->barcode);
                }
                
            }
        }
        
        $this->stock->checked = $this->checked;
        $this->stock->matched = $this->matched;
        $this->stock->base_product_store_id = $this->base_product_store_id;
        $this->stock->base_product_id = $this->base_product_id;
        $this->stock->updated = $this->updated;
        $this->stock->added_new_product = $this->added_new_product;

        // Save the updated stock data back to the database
        $this->stock->save();
    }
    
    protected function calculatePriceFromFormula()
    {
        $this->res = PriceFormula::whereRaw("x1 < $this->distributor_price AND x2 >= $this->distributor_price")
            ->first();

        if ($this->res) {
            $this->x1 = $this->res->x1;
            $this->x2 = $this->res->x2;
            $this->x3 = $this->res->x3;
            $this->x4 = $this->res->x4;

            if ($this->x4 == '0') { //condition in which distributor price is more than 200
                $this->pricePercentage = $this->x3;
            } else {
                $this->numerator = ($this->distributor_price - $this->x1) * $this->res->x3x4;

                $this->pricePercentage = ($this->res->x3x4 - (($this->numerator) / $this->res->x2x1)) + $this->x4;
            }
            \Log::info('V3 Price calculation x1 '.$this->x1.' x2 '.$this->x2.' x3 '.$this->x3.' x4 '.$this->x4.' x3x4 '.$this->res->x3x4.' x2x1 '.$this->res->x2x1);
        
        } else {

            $this->pricePercentage = 0;
            \Log::info('V3 Price calculation pricePercentage is 0');

        }

        $this->selling_price = $this->distributor_price + ($this->distributor_price * $this->pricePercentage / 100);
        $this->number = $this->selling_price;
        $this->finalPrice = $this->getRoundedOffNumber();

        \Log::info('V3 Price calculation distribution price '.$this->distributor_price.', now final price '.$this->finalPrice.', now selling price '.$this->selling_price.'');
        
        return [$this->finalPrice, $this->pricePercentage];
    }

    protected function getRoundedOffNumber()
    {
        $this->floor = floor($this->number);
        $this->decPart = $this->number - $this->floor;
    
        // Removing unnecessorily adding 0.1 into the price based on the request from Prakash on 12/01/2022
        // $this->newDecPart = substr($this->decPart + 0.1, 0, 3);  
        
        $this->newDecPart = substr($this->decPart, 0, 3);
        $this->reqNum = number_format(($this->floor + $this->newDecPart), 2);
    
        return $this->reqNum;
    }
    
}