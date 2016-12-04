<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model {
    protected $table = 'user_company';
    protected $guarded = ['id'];

    public static function checkUC($user_id, $company_id) {
        return !!UserCompany::where('user_id', $user_id)->where('company_id', $company_id)->first();
    }

    public static function getUserIds($company_id) {
        return UserCompany::where('company_id', $company_id)
            ->get()
            ->map(function ($uc) {
                return $uc->user_id;
            })
            ->toArray();
    }
    public static function getCompanyId($user_id) {
        return UserCompany::where('user_id', $user_id)
<<<<<<< HEAD
        ->get()
        ->map(function ($uc) {
            return $uc->company_id;
        })
        ->toArray();
  }
=======
            ->get()
            ->map(function ($uc) {
            return $uc->company_id;
        })
            ->toArray();
    }
>>>>>>> 20e6a2f46d4ab5fcffccf349a64a69f2e940774a
}
