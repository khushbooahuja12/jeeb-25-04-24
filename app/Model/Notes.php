<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notes extends Model {

    protected $table = 'notes';
    protected $fillable = [
        'fk_user_id',
        'title',
        'description',
    ];

    public function getNotesDetail() {
        return $this->hasMany('App\Model\NotesDetail', 'fk_notes_id', 'id');
    }

}
