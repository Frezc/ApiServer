<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobCompleted extends Model
{
    //
    protected $table = 'job_completed';
    protected $hidden = ['job_evaluate_id', 'user_evaluate_id'];

    protected $guarded = ['id'];

    public function jobEvaluated(){
      return $this->job_evaluate_id != null;
    }

    public function userEvaluated(){
      return $this->user_evaluate_id != null;
    }
}
