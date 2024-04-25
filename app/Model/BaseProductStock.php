<?php


namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Model\BaseProduct;

class BaseProductStock extends Model
{
    use Sortable;

    protected $table = 'base_products_stock';
    protected $fillable = [
        'fk_products_stock_from_csv_id',
        'itemcode',
        'barcode',
        'unit',
        'distributor_price',
        'stock',
        'batch_id',
        'product_name_en',
        'fk_store_id',
        'fk_company_id',
        'fk_product_id',
        'fk_product_store_id',
        'matched_record_ids',
        'status'
    ];
    
    public function baseProduct()
    {
        return $this->belongsTo('App\Model\BaseProduct', 'fk_product_id');
    }

    public function getStore()
    {
        return $this->belongsTo('App\Model\Store', 'fk_store_id');
    }

    public function storeBaseProducts()
    {
        $base_products = false;
        $new_product = BaseProductStock::find($this->id);
        if ($new_product) {
            $matched_record_ids = $new_product->matched_record_ids;
            $matched_record_ids = explode(',',$matched_record_ids);
            foreach ($matched_record_ids as $pid) {
                $base_products[] = BaseProduct::where('id',$pid)->where('deleted','=',0)->first();
            }
        }
        return $base_products;
    }

}
