<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/15
 * Time: 23:49
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Resumes extends Model
{
    protected $table = 'resumes';


    protected $fillable = [
        'id',
        'user_id',
        'title',
        'name',
        'photo',
        'school',
        'birthday',
        'contact',
        'sex',
        'expect_location',
        'introduction',
        ];
    function getUserId(){
        return $this->getAttribute('user_id');
    }
    function getId(){
        return $this->getAttribute('id');
    }
}