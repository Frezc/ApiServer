<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $table = 'companys';

    protected $fillable = ['name', 'url', 'address','logo','description','contact_person','contact'];
    protected $guarded = ['id'];

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
