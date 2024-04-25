<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';

    protected $fillable = [
        'invoice_id',
        'purchase_party',
        'purchase_by',
        'bill_image',
        'purchase_amount',
    ];

    public $timestamps = false;
}
