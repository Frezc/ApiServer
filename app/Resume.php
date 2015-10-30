<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = 'resumes';

    // protected $fillable = ['user_id', 'name'];
    //在用Resume::create创建时不能填充的项
    protected $guarded = ['photo', 'id'];
}
