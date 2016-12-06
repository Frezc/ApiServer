<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class JobEvaluate extends Model
{
    //

    protected $table = 'job_evaluate';

    protected $guarded = ['id'];

    protected $hidden = ['updated_at', 'order_id'];

    public function makeSureAccess(User $user) {
        if ($this->user_id != $user->id) {
            throw new MsgException('You have no access to this evaluate.', 401);
        }

        return true;
    }
}
