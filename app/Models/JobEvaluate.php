<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobEvaluate extends Model
{
    //

    protected $table = 'job_evaluate';

    protected $guarded = ['id'];

    protected $hidden = ['updated_at', 'order_id'];
}
