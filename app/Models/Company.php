<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $table = 'companys';
    protected $guarded = ['id'];
    protected $hidden = ['business_license', 'updated_at'];

    public function jobs(){
      return $this->hasMany('App\Job');
    }

//    public function checkEmployee($user_id) {
//        return !!UserCompany::where('user_id', $user_id)->where('company_id', $this->id)->first();
//    }

    public function makeSureAccess(User $user) {
        if ($user->isAdmin() || $this->id == $user->company_id) {
            return true;
        }

        throw new MsgException('You have no access to this company.', 401);
    }

    public static function search($keywords) {

        $builder = Company::query()
            ->when($keywords, function ($query) use ($keywords) {
                $q_array = array_slice(explode(" ", trim($keywords)), 0, 3);
                foreach ($q_array as $qi) {
                    $query->where(function ($query) use ($qi) {
                        $query->where('name', 'like', '%' . $qi . '%')
                            ->orWhere('address', 'like', '%' . $qi . '%')
                            ->orWhere('contact', 'like', '%' . $qi . '%')
                            ->orWhere('contact_person', 'like', '%' . $qi . '%');
                    });
                }
                return $query;
            });
        return $builder;
    }

    public function users() {
        return $this->belongsToMany('App\Models\User', 'user_company', 'company_id', 'user_id');
    }

    public static function getUserIds($company_id) {
        return User::where('company_id', $company_id)
            ->get()
            ->map(function ($user) {
                return $user->id;
            })
            ->toArray();
    }
}

