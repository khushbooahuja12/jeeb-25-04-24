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
use Illuminate\Support\Facades\Bus;

class ProcessStock extends ProcessBaseProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $stock_id;
    protected $stock;

    // To match the base_products_store
    protected $matched_record;
    protected $selected_record;
    protected $itemcode;
    protected $barcode;
    protected $barcode_matched;

    // To change the base product
    protected $base_product;
    protected $update_base_product_row;
    protected $base_product_updated;
    
    // To create the new base product if not existing
    protected $added_new_product_row;

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

        // ----------------
        $this->matched_record = false;
        $this->selected_record = false;
        $this->itemcode = "";
        $this->barcode = "";
        $this->barcode_matched = false;
        
        // ----------------
        $this->base_product = false;
        $this->update_base_product_row = false;
        $this->base_product_updated = false;

        // ----------------
        $this->added_new_product_row = false;
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
        $this->stock->checked = 1;

        // Get matched product
        $this->itemcode = $this->stock->itemcode;
        $this->barcode = $this->stock->barcode;
        $this->store_id = $this->stock->store_no;
        $this->matched_record = $this->selectBaseProductStore($this->itemcode,$this->barcode,$this->store_id);
        $this->barcode_matched = false;

        if ($this->matched_record) {
            $this->selected_record = $this->matched_record['matched_record'];
            $this->barcode_matched = $this->matched_record['barcode_matched'];
            $this->stock->matched = 1;
        }

        // Process stock update if matched
        if ($this->selected_record) {

            $this->stock->base_product_store_id = $this->selected_record->base_product_store_id;
            $this->stock->base_product_id = $this->selected_record->base_product_id;

            if ($this->barcode_matched) {

                $this->diff = $this->selected_record->product_price - (double)trim($this->stock->rsp);
                if ($this->selected_record->allow_margin == 1 || ($this->selected_record->allow_margin == 0 && $this->diff < 0)) {
                    $this->priceArr = $this->calculatePriceFromFormula((double)trim($this->stock->rsp));
                    $this->insert_arr['margin'] = $this->priceArr[1];
                    $this->insert_arr['product_price'] = $this->priceArr[0];
                    $this->insert_arr['base_price'] = $this->priceArr[0];
                } else { 
                    $this->profit = abs((double)trim($this->stock->rsp) - $this->selected_record->product_price);
                    $this->margin = number_format((($this->profit / (double)trim($this->stock->rsp)) * 100), 2);
                    $this->insert_arr['margin'] = $this->margin;
                    $this->insert_arr['product_price'] = $this->selected_record->product_price;
                    $this->insert_arr['base_price'] = $this->selected_record->product_price;
                }
                
                $this->insert_arr['distributor_price'] = $this->stock->rsp;
                $this->insert_arr['stock'] = $this->stock->stock;
                $this->update_row = BaseProductStore::find($this->selected_record->id)->update($this->insert_arr);
                
                if ($this->update_row) {
                    $this->stock->updated = 1;
                    \Log::error('Bulk Stock Update From Server: Updating stock of base_product_store: '.$this->selected_record->id);

                    // Update base product
                    $this->update_base_product_row = $this->update_base_product($this->selected_record->fk_product_id);
                    if ($this->update_base_product_row) {
                        if ($this->update_base_product_row['status']) {
                            \Log::error('Bulk Stock Update From Server: Updating stock of base_product: '.$this->update_base_product_row['base_product']->id);
                        } else {
                            \Log::error('Bulk Stock Update From Server: Updating stock of base_product failed: '.$this->update_base_product_row['base_product']->id);
                        }
                    } else {
                        \Log::error('Bulk Stock Update From Server: Updating stock of base_product not found');
                    }
                } else {
                    \Log::error('Bulk Stock Update From Server: Updating stock of base_product_store failed: '.$this->selected_record->id);
                }

            } 
            // Add new base_product_stock if product is existing but barcode not matched
            elseif ($this->selected_record) {

                $this->diff = $this->selected_record->product_price - (double)trim($this->stock->rsp);
                if ($this->selected_record->allow_margin == 1 || ($this->selected_record->allow_margin == 0 && $this->diff < 0)) {
                    $this->priceArr = $this->calculatePriceFromFormula((double)trim($this->stock->rsp));
                    $this->insert_arr['margin'] = $this->priceArr[1];
                    $this->insert_arr['product_price'] = $this->priceArr[0];
                    $this->insert_arr['base_price'] = $this->priceArr[0];
                } else { 
                    $this->profit = abs((double)trim($this->stock->rsp) - $this->selected_record->product_price);
                    $this->margin = number_format((($this->profit / (double)trim($this->stock->rsp)) * 100), 2);
                    $this->insert_arr['margin'] = $this->margin;
                    $this->insert_arr['product_price'] = $this->selected_record->product_price;
                    $this->insert_arr['base_price'] = $this->selected_record->product_price;
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
                    $this->stock->added_new_product = 1;
                    \Log::error('Bulk Stock Update From Server: Adding new product for the product barcode: '.$this->barcode);
                } else {
                    \Log::error('Bulk Stock Update From Server: Adding new product for the product barcode failed: '.$this->barcode);
                }
                
            }
        }
        
        // Save the updated stock data back to the database
        $this->stock->save();
    }
    
}