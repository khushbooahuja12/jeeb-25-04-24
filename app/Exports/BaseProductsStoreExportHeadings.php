<?php

namespace App\Exports;

use App\Model\BaseProductStore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseProductsStoreExportHeadings implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BaseProductStore::where('id','=',0)->get();
    }

    public function headings(): array
    {
        return [
            'itemcode',
            'barcode',
            'allow_margin',
            'product_distributor_price_before_back_margin',
            'product_store_price',
            'base_price',
            'stock',
            'other_names',
            'is_active',
            'fk_product_id',
            'fk_store_id'
        ];
    }
}
