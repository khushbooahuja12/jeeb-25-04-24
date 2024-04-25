<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Affiliate extends Model
{

    use Sortable;

    protected $table = 'affiliates';
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'code',
        'qr_code_image_url',
        'status'
    ];
    public $sortable = ['id', 'name', 'email', 'status'];

    public function userMobiles()
    {
        return $this->hasMany('App\Model\AffiliateUser', 'affiliate_code', 'code');
    }

    public function userRegisteredMobiles()
    {
        return $this->userMobiles()->where(['user_registered'=>1,'otp_verified'=>1]);
    }

}
