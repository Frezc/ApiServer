<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTime extends Model
{
    use SoftDeletes;

    protected $table = 'job_times';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at', 'job_id'];
    protected $dates = ['deleted_at'];
}
