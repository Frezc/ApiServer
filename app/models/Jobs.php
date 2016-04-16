<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;


class Jobs extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'jobs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['salary', 'description',
        'number','number_applied',
        'visited','time','name',
        'company_id','company_name',
        'active'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

}