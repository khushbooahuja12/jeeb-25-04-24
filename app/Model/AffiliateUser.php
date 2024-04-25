<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class AffiliateUser extends Model
{

    use Sortable;

    protected $table = 'affiliate_users';
    protected $fillable = [
        'affiliate_code',
        'user_mobile',
        'user_ip',
        'user_registered',
        'otp_verified',
        'user_coupon',
        'user_coupon_used'
    ];
    public $sortable = ['id', 'affiliate_code', 'user_mobile', 'user_ip'];

    public function affiliate()
    {
        return $this->belongsTo('App\Model\Affiliate', 'code', 'affiliate_code');
    }

}
