<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $table = 'companys';

    protected $guarded = ['id','name', 'url', 'address','logo','description','contact_person','contact'];

    public function jobs(){
      return $this->hasMany('App\Job');
    }
    function getId(){
        return $this->getAttribute('id');
    }
    function getName(){
        return $this->getAttribute('name');
    }
}
