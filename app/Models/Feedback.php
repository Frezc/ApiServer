<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];
    protected $dates = [
        'created_at',
        'updated_at',
        'dealt_at'
    ];
}
