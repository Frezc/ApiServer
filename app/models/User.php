<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;


class User extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['avatar', 'email','phone',
        'password','nickname','sign','birthday','location','sex',
        'company_id',
        'email_verified'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

}
