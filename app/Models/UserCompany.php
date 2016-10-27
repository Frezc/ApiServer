<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model
{
    protected $table = 'user_company';
    protected $guarded = ['id'];

    public static function checkUC($user_id, $company_id) {
        return !!UserCompany::where('user_id', $user_id)->where('company_id', $company_id)->first();
    }
}
