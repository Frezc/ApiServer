<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = ['id'];

    public function bindJobTime() {
        $jobTime = JobTime::find($this->job_time_id);
        $jobTime && $this->job_time = $jobTime;
    }

    public function bindExpectJob() {
        $expectJob = ExpectJob::find($this->expect_job_id);
        $expectJob && $this->expect_job = $expectJob;
    }
}
