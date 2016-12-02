<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'tjz_jobs';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];

    public function jobTime() {
        return JobTime::where('job_id', $this->id);
    }

    public function checkAccess(User $user) {
        if ($this->creator_id == $user->id || $user->isAdmin()) {
            return true;
        }
        if ($this->company_id) {
            if ($user->company_id == $this->company_id) {
                return true;
            }
        }

        throw new MsgException('You have no access to this job.', 401);
    }

    public static function search($keyword) {
        $builder = Job::query();
        if ($keyword) {
            $q_array = array_slice(explode(" ", trim($keyword)), 0, 3);

            foreach ($q_array as $qi) {
                $builder->where(function ($query) use ($qi) {
                    $query->where('name', 'like', '%' . $qi . '%')
                        ->orWhere('creator_name', 'like', '%' . $qi . '%')
                        ->orWhere('company_name', 'like', '%' . $qi . '%');
                });
            }
        }

        return $builder;
    }
}
