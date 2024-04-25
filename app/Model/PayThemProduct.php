<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayThemProduct extends Model
{
    use HasFactory;

    protected $table = 'paythem_products';
    protected $fillable = [
        "fk_product_id",
        "oem_id",
        "oem_name",
        "oem_brand_id",
        "oem_brand_name",
        "oem_brand_brand_product_format_id",
        "oem_brand_brand_product_format_desc",
        "oem_brand_brand_product_format_fields",
        "oem_product_id",
        "oem_product_name",
        "oem_product_vvssku",
        "oem_product_basecurrency",
        "oem_product_basecurrencysymbol",
        "oem_product_unitprice",
        "oem_product_sellprice",
        "oem_product_redemptioninstructions",
        "oem_product_imageurl",
        "oem_product_available"
    ];
}
