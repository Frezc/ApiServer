<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/15
 * Time: 23:25
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;


class JobEvaluate extends Model
{
    protected $table = 'job_evaluate';


    protected $fillable = [
        'id',
        'user_id',
        'job_id',
        'score',
        'comment'];
}
function getId(){
    return $this->getAttribute('id');
}
function getJobId(){
    return $this->getAttribute('job_id');
}
function getUserId(){
    return $this->getAttribute('user_id');
}
function getScore(){
    return $this->getAttribute('score');
}
function getComment(){
    return $this->getAttribute('comment');
}