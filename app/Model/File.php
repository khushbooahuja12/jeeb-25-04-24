<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class File extends Model {

    protected $table = 'files';
    protected $fillable = [
        'file_path',
        'file_name',
        'file_ext',
    ];

}
