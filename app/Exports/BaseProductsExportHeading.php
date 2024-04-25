<?php

namespace App\Exports;

use App\Model\BaseProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseProductsExportHeading implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BaseProduct::where('id','=',0)->get();
    }

    public function headings(): array
    {
        return [
            'product_type',
            'parent_id',
            'fk_category_id',
            'fk_sub_category_id',
            'fk_brand_id',
            'product_name_en',
            'product_name_ar',
            'product_image_url',	
            'base_price',
            'unit',
            '_tags',
            'min_scale',
            'max_scale',
            'country_code',
            'country_icon'
        ];
    }
}
