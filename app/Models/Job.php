<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'tjz_jobs';
    protected $guarded = ['id'];

    public function jobTime() {
        return JobTime::where('job_id', $this->id);
    }

    public function checkAccess($user) {
        if ($this->creator_id == $user->id) {
            return true;
        }
        if ($this->company_id) {
            if (UserCompany::checkUC($user->id, $this->company_id)) {
                return true;
            }
        }

        throw new MsgException('You have no access to this job.', 401);
    }
}
