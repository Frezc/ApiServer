<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = 'resumes';
    //在用Resume::create创建时不能填充的项
    protected $guarded = ['id',
        'user_id',
        'title',
        'name',
        'photo',
        'school',
        'birthday',
        'contact',
        'sex',
        'expect_location',
        'introduction',];
    function getUserId(){
        return $this->getAttribute('user_id');
    }
    function getId(){
        return $this->getAttribute('id');
    }
}
