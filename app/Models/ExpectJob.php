<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpectJob extends Model
{
    protected $table = 'expect_jobs';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];

    public static function search($q) {
        $builder = ExpectJob::where('is_public', 1);

        if ($q) {
            $keywords = array_slice(explode(" ", trim($q)), 0, 3);
            foreach ($keywords as $keyword) {
                $builder->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('school', 'like', '%' . $keyword . '%')
                        ->orWhere('expect_location', 'like', '%' . $keyword . '%');
                });
            }
        }

        return $builder;
    }

    public function bindExpectTime() {
        $this->expect_time = ExpectTime::where('expect_job_id', $this->id)->get();
    }

    public function makeSureAccess(User $user) {
        if ($this->user_id == $user->id || $user->isAdmin()) {
            return true;
        }

        throw new MsgException('You have no access to this job.', 401);
    }
}
