<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'tjz_logs';
    protected $guarded = ['id'];
    protected $hidden = ['user_id', 'user_name', 'updated_at'];
}
