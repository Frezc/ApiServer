<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpectJob extends Model
{
    protected $table = 'expect_jobs';
    protected $guarded = ['id'];
    protected $hidden = ['updated_at'];

    public static function search($q) {
        $builder = ExpectJob::where('is_public', 1);

        if ($q) {
            $keywords = explode(" ", trim($q));
            foreach ($keywords as $keyword) {
                $builder->orWhere('expect_location', 'like', '%' . $keyword . '%')
                    ->orWhere('introduction', 'like', '%' . $keyword . '%');
            }
        }

        return $builder;
    }

    public function bindUserName() {
        $user = User::find($this->user_id);
        if ($user) {
            $this->user_name = $user->nickname;
        }
    }

    public function bindExpectTime() {
        $this->expect_time = ExpectTime::where('expect_job_id', $this->id)->get();
    }
}
