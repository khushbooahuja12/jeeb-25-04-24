<?php

namespace App\Jobs;

use App\Model\BaseProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBaseProduct extends BaseProductHelper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    protected $key;
    protected $base_product_id;
    protected $value;
    
    /**
     * Create a new job instance.
     *
     * @param  int  $stockId
     * @return void
     */
    public function __construct(string $key, int $base_product_id, string $value)
    {
        $this->key = $key;
        $this->base_product_id = $base_product_id;
        $this->value = trim($value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get base product
        $base_product = BaseProduct::find($this->base_product_id);
        if ($base_product) {

            // Value formatting for tags
            if ($this->key=='_tags') {
                $tag_ids = explode(',',$this->value);
                // $_tags = $base_product->_tags;
                $_tags = '';
                foreach ($tag_ids as $tag_id) {

                    // Check tag already exist
                    $tag_id = trim($tag_id);
                    $product_tag_exist = \App\Model\ProductTag::find($tag_id);
                    if($product_tag_exist){

                        if (!str_contains($_tags, 'tag_'.$product_tag_exist->id.',')) { 
                            $_tags .= $_tags!='' && substr($_tags, -1, 1) != ',' ? ',' : '';
                            $_tags .= $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                        }
                        
                    }
                }
                $this->value = $_tags;
            }

            // Value formatting for main_tags
            if ( $this->key=='main_tags') {
                // Check tag already exist
                $product_tag_exist = \App\Model\ProductTag::find($this->value);
                if($product_tag_exist){
                    $base_product->update(['fk_main_tag_id'=>$this->value]);
                    $main_tags = $product_tag_exist->title_en.','.$product_tag_exist->title_ar;
                }
                $this->value = $main_tags;
            }

            $base_product->update([$this->key=>$this->value]);
            // Update base product
            $this->update_base_product_store($base_product->id);
            \Log::error('UpdateBaseProductSingleColumn_'.$this->key.': updated for '.$this->base_product_id.' as '.$this->value);
        } else {
            \Log::error('UpdateBaseProductSingleColumn_'.$this->key.': not found for '.$this->base_product_id.' as '.$this->value);
        }
    }
    
}