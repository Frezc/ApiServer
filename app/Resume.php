<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = 'resumes';

    protected $fillable = ['user_id', 'name'];
}
