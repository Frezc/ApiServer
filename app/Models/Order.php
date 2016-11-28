<?php

namespace App\Models;

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

    public function isOver() {
        return $this->status == 2 || $this->status == 3;
    }

    public function getRecruiterIds() {
        if ($this->recruiter_type == 1) {
            return UserCompany::getUserIds($this->recruiter_id);
        } else {
            return [$this->recruiter_id];
        }
    }

    public static function closeTypeText($type) {
        switch ($type) {
            case 1: return '应聘者';
            case 2: return '招聘者';
            case 3: return '管理员';
        }
        return '';
    }
}
