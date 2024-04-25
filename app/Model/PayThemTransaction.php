<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayThemTransaction extends Model
{
    use HasFactory;

    protected $table = 'paythem_transactions';
    protected $fillable = [
        "fk_order_id",
        "fk_order_product_id",
        "transaction_id",
        "transaction_voucher_quantity",
        "transaction_date",
        "transaction_value" ,
        "financial_id",
        "client_reference_id",
        "client_remaining_balance",
        "consignment_limit",
        "available_balance",
        "transaction_currency",
        "oem_id",
        "oem_name",
        "oem_brand_id",
        "oem_brand_name",
        "oem_brand_brand_product_format_id",
        "oem_brand_brand_product_format_desc",
        "oem_brand_brand_product_format_fields",
        "oem_product_id",
        "oem_product_name",
        "oem_product_sellprice",
        "oem_product_redemptioninstructions",
        "oem_product_vvssku",
        "oem_product_ean",
        "vouchers",
    ];
}
