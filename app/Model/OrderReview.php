<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class OrderReview extends Model {

    use Sortable;

    protected $table = 'order_reviews';
    protected $fillable = [
        'fk_user_id',
        'fk_order_id',
        'products_quality',
        'delivery_experience',
        'overall_satisfaction',
        'review',
        'skip'
    ];
    public $sortable = ['id', 'rating', 'review'];

    public function getOrder() {
        return $this->belongsTo('App\Model\Order', 'fk_order_id');
    }

    public function getUser() {
        return $this->belongsTo('App\Model\User', 'fk_user_id');
    }

}
