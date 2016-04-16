<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobEvaluate extends Model
{
    //

    protected $table = 'job_evaluate';

    protected $guarded = ['id','user_id','job_id','score','comment'];
}
