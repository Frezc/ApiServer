<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/15
 * Time: 23:19
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;

class JobCompleted extends Model
{
    protected $table = 'job_completed';


    protected $fillable = [
        'id',
        'user_id',
        'job_id',
        'resume_id',
        'description'];
    function getId(){
        return $this->getAttribute('id');
    }
    function getJobId(){
        return $this->getAttribute('job_id');
    }
    function getUserId(){
        return $this->getAttribute('user_id');
    }
}
