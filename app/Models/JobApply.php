<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApply extends Model
{
    //
    protected $table = 'job_apply';

    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function job(){
        return $this->belongsTo('App\Job');
    }

    public function resume(){
        return $this->belongsTo('App\Resume');
    }

}
