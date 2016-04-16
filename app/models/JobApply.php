<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/15
 * Time: 23:15
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;



class JobApply extends Model
{
    protected $table = 'job_apply';


    protected $fillable = ['user_id', 'job_id','resume_id',
        'description','status'];


}