<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';
    protected $guarded = ['id'];

    public function jobTime() {
        return JobTime::where('job_id', $this->id);
    }
}
