<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class News extends Model {

    use Sortable;

    protected $table = 'news';
    protected $fillable = [
        'title',
        'description',
        'image',
        'deleted'
    ];
    public $sortable = ['id', 'title', 'description'];

    public function getNewsImage() {
        return $this->belongsTo('App\Model\File', 'image');
    }

}
