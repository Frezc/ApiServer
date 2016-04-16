<?php
/**
 * Created by PhpStorm.
 * User: Umasou
 * Date: 2016/4/15
 * Time: 16:41
 */

namespace App\models;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companys';

    protected $fillable = ['id','name', 'url', 'address','logo','description','contact_person','contact'];

    function getId(){
        return $this->getAttribute('id');
    }
    function getName(){
        return $this->getAttribute('name');
    }
}