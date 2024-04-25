<?php

namespace App\Jobs;

use App\Model\ProductStockFromCsv;
use App\Model\BaseProduct;
use App\Model\BaseProductStore;
use App\Model\BaseProductStock;
use App\Model\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessBaseProductMarkOutOfStock;

class ProcessBaseProductStock extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $stock_id;
    protected $mark_out_of_stock;
    protected $stock;

    // To match the base_products_store
    protected $matched_record;
    protected $selected_record;
    protected $itemcode;
    protected $barcode;
    protected $itemcode_matched;
    protected $barcode_matched;
    protected $matched_record_ids;

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
    public function __construct(int $stock_id, bool $mark_out_of_stock=false)
    {
        $this->stock_id = $stock_id;
        $this->stock = false;
        $this->mark_out_of_stock = $mark_out_of_stock;

        // ----------------
        $this->matched_record = false;
        $this->selected_record = false;
        $this->itemcode = "";
        $this->barcode = "";
        $this->itemcode_matched = false;
        $this->barcode_matched = false;
        $this->matched_record_ids = false;
        
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

        if ($this->stock) {

            // Required variables
            $this->stock->checked = 1;

            // Get matched product
            $this->itemcode = $this->stock->itemcode;
            $this->barcode = $this->stock->barcode;
            $this->store_id = $this->stock->store_no;
            $this->batch_id = $this->stock->batch_id;
            $this->matched_record = $this->selectBaseProductStore($this->itemcode,$this->barcode,$this->store_id);

            if ($this->matched_record) {
                $this->selected_record = $this->matched_record['matched_record'];
                $this->itemcode_matched = $this->matched_record['itemcode_matched'];
                $this->barcode_matched = $this->matched_record['barcode_matched'];
                $this->matched_record_ids = $this->matched_record['matched_record_ids'];
                $this->stock->matched = 1;

                // Back margin calculation
                $this->priceDistributorArr = $this->calculateDistributorPrice($this->stock->rsp,$this->store_id);
                $this->insert_arr['product_distributor_price_before_back_margin'] = $this->stock->rsp;
                $this->product_distributor_price = $this->priceDistributorArr[0];
                $this->insert_arr['back_margin'] = $this->priceDistributorArr[1];
            }

            // Process stock update if matched
            if ($this->barcode_matched && $this->selected_record) {

                $this->stock->base_product_store_id = $this->selected_record->id;
                $this->stock->base_product_id = $this->selected_record->fk_product_id;
                $this->base_product = $this->stock->base_product_id ? BaseProduct::find($this->stock->base_product_id) : false;

                if ($this->base_product) {
                    
                    if ($this->selected_record->is_stock_update<2) {
                        $this->diff = $this->selected_record->product_store_price - (double)trim($this->product_distributor_price);
                        if ($this->selected_record->allow_margin == 1 || ($this->selected_record->allow_margin == 0 && $this->diff < 0)) {
                            $this->priceArr = $this->calculatePriceFromFormula((double)trim($this->product_distributor_price), $this->base_product->fk_offer_option_id, $this->base_product->fk_brand_id, $this->base_product->fk_sub_category_id, $this->selected_record->fk_store_id);
                            $this->insert_arr['margin'] = $this->priceArr[1];
                            $this->insert_arr['product_store_price'] = $this->priceArr[0];
                            $this->insert_arr['base_price'] = $this->priceArr[2];
                            $this->insert_arr['base_price_percentage'] = $this->priceArr[3];
                            $this->insert_arr['discount_percentage'] = $this->priceArr[4];
                            $this->insert_arr['fk_price_formula_id'] = $this->priceArr[5];
                        } else { 
                            $this->profit = abs((double)trim($this->product_distributor_price) - $this->selected_record->product_store_price);
                            $this->margin = number_format((($this->profit / (double)trim($this->product_distributor_price)) * 100), 2);
                            $this->insert_arr['margin'] = $this->margin;
                            $this->insert_arr['product_store_price'] = $this->selected_record->product_store_price;
                            $this->insert_arr['base_price'] = $this->selected_record->base_price;
                            $this->insert_arr['base_price_percentage'] = $this->product_distributor_price>0 ? number_format(((($this->insert_arr['base_price']-$this->product_distributor_price)/$this->product_distributor_price) * 100), 2) : 0;
                            $this->insert_arr['discount_percentage'] = $this->insert_arr['base_price']>0 ? number_format(((($this->insert_arr['base_price']-$this->selected_record->product_store_price)/$this->insert_arr['base_price']) * 100), 2) : 0;
                            $this->insert_arr['fk_price_formula_id'] = 0;
                        }
                        
                        $this->insert_arr['product_distributor_price'] = $this->product_distributor_price;
                        $this->insert_arr['stock'] = $this->stock->stock;
                        $this->insert_arr['is_stock_update'] = 1;
                        $this->update_row = BaseProductStore::find($this->selected_record->id)->update($this->insert_arr);
                        
                        if ($this->update_row) {
                            $this->stock->updated = 1;
                            \Log::info('Bulk Stock Update From Server: Updating stock of base_product_store: '.$this->selected_record->id);

                            // Update base product
                            $this->update_base_product_row = $this->update_base_product($this->selected_record->fk_product_id);
                            if ($this->update_base_product_row) {
                                if ($this->update_base_product_row['status']) {
                                    \Log::info('Bulk Stock Update From Server: Updating stock of base_product: '.$this->update_base_product_row['base_product']->id);
                                } else {
                                    \Log::info('Bulk Stock Update From Server: Updating stock of base_product failed: '.$this->update_base_product_row['base_product']->id);
                                }
                            } else {
                                \Log::info('Bulk Stock Update From Server: Updating stock of base_product not found');
                            }
                        } else {
                            \Log::info('Bulk Stock Update From Server: Updating stock of base_product_store failed: '.$this->selected_record->id);
                        }
                    } else {
                        \Log::info('Bulk Stock Update From Server: Updating stock not allowed for base_product_store: '.$this->selected_record->id);
                    }

                } else {
                    \Log::info('Bulk Stock Update From Server: Base product not found for base_product_store: '.$this->selected_record->id);
                }
            }
            // Add new base_product_stock if product is existing but barcode not matched
            else {

                // Required variables
                $this->insert_arr['fk_products_stock_from_csv_id'] = $this->stock->id;
                $this->insert_arr['itemcode'] = $this->stock->itemcode;
                $this->insert_arr['barcode'] = $this->stock->barcode;
                $this->insert_arr['unit'] = $this->stock->packing;
                $this->insert_arr['distributor_price'] = $this->stock->rsp;
                $this->insert_arr['stock'] = $this->stock->stock;
                $this->insert_arr['batch_id'] = $this->stock->batch_id;
                $this->insert_arr['product_name_en'] = $this->stock->product_name_en;
                $this->insert_arr['fk_store_id'] = $this->stock->store_no;
                $this->insert_arr['fk_company_id'] = $this->stock->company_id;
                $this->insert_arr['fk_product_id'] = 0;
                $this->insert_arr['status'] = 0;
                $this->insert_arr['matched_record_ids'] = '';

                // If itemcode found, record the IDs 
                if ($this->itemcode_matched && !empty($this->matched_record_ids)) {
                    $this->insert_arr['matched_record_ids'] = implode(',',$this->matched_record_ids);
                    $this->insert_arr['status'] = 1;
                }

                $this->base_product_stock = BaseProductStock::where([
                    'itemcode' => $this->stock->itemcode,
                    'barcode' => $this->stock->barcode,
                    'fk_store_id' => $this->stock->store_no
                ])->first();
                if (!$this->base_product_stock) {
                    $this->added_new_product_row = BaseProductStock::create($this->insert_arr);
                    if ($this->added_new_product_row) {
                        \Log::info('Bulk Stock Update From Server: Adding new product for the product barcode: '.$this->barcode);
                    } else {
                        \Log::info('Bulk Stock Update From Server: Adding new product for the product barcode failed: '.$this->barcode);
                    }
                } else {
                    $this->added_new_product_row = $this->base_product_stock->update($this->insert_arr);
                    if ($this->added_new_product_row) {
                        \Log::info('Bulk Stock Update From Server: Updating new product for the product barcode: '.$this->barcode);
                    } else {
                        \Log::info('Bulk Stock Update From Server: Updating new product for the product barcode failed: '.$this->barcode);
                    }
                }
                $this->stock->added_new_product = 1;
                
            } 
            
            // Save the updated stock data back to the database
            $this->stock->save();

            // -----------------------------------
            // Call for marking out of stock
            // -----------------------------------
            if ($this->mark_out_of_stock) {
                
                // How many records processed
                $all_stock_records_count = ProductStockFromCsv::where(['batch_id'=>$this->batch_id])->count();
                $processed_stock_records_count = ProductStockFromCsv::where(['batch_id'=>$this->batch_id,'checked'=>1])->count();
                $updated_stock_records_count = ProductStockFromCsv::where(['batch_id'=>$this->batch_id,'updated'=>1])->count();
                \Log::info('UpdateBaseProductMarkOutOfStock: All stocks-'.$all_stock_records_count.', processed stocks count-'.$processed_stock_records_count.', updated stocks count-'.$updated_stock_records_count);

                if ($all_stock_records_count==$processed_stock_records_count && $updated_stock_records_count>0) {
                    $perPage = 10000; // Number of items per page
                    $query = BaseProductStore::where([
                            'fk_store_id'=>$this->store_id,
                            'deleted'=>0,
                            'is_stock_update'=>0
                        ])->where('stock','!=',0)->select('id');
                    $paginator = $query->paginate($perPage);
                    $lastPage = $paginator->lastPage();

                    for ($i=1; $i <= $lastPage; $i++) { 
                        
                        $stocks = $query->paginate($perPage, ['*'], 'page', $i);
                        $this->base_product_stores = $stocks->map(function ($base_product_store) {
                            if ($base_product_store !== null && is_object($base_product_store)) {
                                return [
                                    'id' => $base_product_store->id
                                ];
                            }
                        })->toArray();
                        // Dispatch a sub-job for each product ID
                        Bus::batch(
                            collect($this->base_product_stores)->map(function ($base_product_store) {
                                return new ProcessBaseProductMarkOutOfStock($base_product_store['id']);
                            })
                        )->name('UpdateBaseProductMarkOutOfStock_StoreID:'.$this->store_id.'_batch:'.$this->batch_id.'_key:'.$i)->dispatch();
                        \Log::info('UpdateBaseProductMarkOutOfStock: Started id-'.$i);

                        // sleep(1);
                    }
                    
                } else {
                    \Log::info('UpdateBaseProductMarkOutOfStock: Update Not Completed Yet');
                }
            }
        } else {
            \Log::info('Bulk Stock Update From Server: stock record not found for ID: '.$this->stock_id);
        }

    }
    
}