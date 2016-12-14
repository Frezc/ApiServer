<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'tjz_jobs';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];

    protected $dates = ['deleted_at'];

    public function jobTime() {
        return JobTime::where('job_id', $this->id);
    }

    public function bindTime() {
        $this->time = JobTime::where('job_id', $this->id)
            ->where('apply_end_at', '>', Carbon::now()->toDateTimeString())
            ->orderBy('id', 'desc')
            ->get();
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
            // 最多只允许3个关键字
            $q_array = array_slice(explode(" ", trim($keyword)), 0, 3);

            foreach ($q_array as $qi) {
                // 搜索岗位名称、创建者名称、企业名称、城市和地址
                $builder->where(function ($query) use ($qi) {
                    $query->where('name', 'like', '%' . $qi . '%')
                        ->orWhere('creator_name', 'like', '%' . $qi . '%')
                        ->orWhere('company_name', 'like', '%' . $qi . '%')
                        ->orWhere('city', 'like', '%' . $qi . '%')
                        ->orWhere('address', 'like', '%' . $qi . '%');
                });
            }
        }

        return $builder;
    }

    public function updateScore() {
        $builder = JobEvaluate::where('job_id', $this->id);
        $this->update([
            'number_evaluate' => $builder->count(),
            'average_score' => $builder->avg('score')
        ]);
    }
}
