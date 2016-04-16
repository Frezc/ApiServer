<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEvaluate extends Model
{
    //
    protected $table = 'user_evaluate';

    protected $guarded = [
    'id',
    'user_id',
    'agents_id',
    'score',
    'comment'];

}
