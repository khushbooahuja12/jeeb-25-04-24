<?php

namespace App\Model;

use App\Jobs\ProcessCsvUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Kyslik\ColumnSortable\Sortable;

class ProductStockFromCsv extends Model
{

    use Sortable;

    protected $table = 'products_stock_from_csv';
    protected $fillable = [
        'itemcode',
        'barcode',
        'packing',
        'rsp',
        'stock',
        'batch_id',
        'product_name_en',
        'store_no',
        'company_id',
        'checked',
        'matched',
        'base_product_store_id',
        'base_product_id',
        'updated',
        'added_new_product',
    ];
}
