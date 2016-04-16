<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/16
 * Time: 17:07
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;

class UserEvaluate extends Model
{
    protected $table = 'user_evaluate';


    protected $fillable = [
        'id',
        'user_id',
        'agents_id',
        'score',
        'comment'];
    function getId(){
        return $this->getAttribute('id');
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
}