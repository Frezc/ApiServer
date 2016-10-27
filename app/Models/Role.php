<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    //在用Resume::create创建时不能填充的项
    protected $guarded = ['id'];
}
