<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobTime extends Model
{
    //
    protected $table = 'job_times';
    protected $guarded = ['id'];

    protected $hidden = ['updated_at'];
}
