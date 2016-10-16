<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Uploadfile extends Model
{
    protected $table = 'uploadfiles';
    protected $guarded = ['id'];

    public function makeSureAccess($user) {
        if ($this->uploader_id != $user->id) {
            /* throw */
        }
        return true;
    }
}
