<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Ads extends Model {

    use Sortable;

    protected $table = 'ads';
    protected $fillable = [
        'name',
        'image',
        'redirect_type'
    ];
    public $sortable = ['id', 'name'];

    public function getAdsImage() {
        return $this->belongsTo('App\Model\File', 'image');
    }

}
