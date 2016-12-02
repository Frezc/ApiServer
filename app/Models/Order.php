<?php

namespace App\Models;

use App\Exceptions\MsgException;
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
            return Company::getUserIds($this->recruiter_id);
        } else {
            return [$this->recruiter_id];
        }
    }

    public function makeSureAccess(User $user) {
        if ($this->applicant_id == $user->id) return true;
        if ($this->recruiter_type == 1 && $this->recruiter_id == $user->company_id) return true;
        if ($this->recruiter_type == 0 && $this->recruiter_id == $user->id) return true;
        if ($user->isAdmin()) return true;

        throw new MsgException('You have no access to this order.', 401);
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
