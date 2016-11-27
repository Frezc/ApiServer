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

    public function checkEmployee($user_id) {
        return !!UserCompany::where('user_id', $user_id)->where('company_id', $this->id)->first();
    }

    public function makeSureAccess(User $user) {
        if ($user->isAdmin() || $this->checkEmployee($user->id)) {
            return true;
        }

        throw new MsgException('You have no access to this company.', 401);
    }

    public static function search($keywords) {
        $builder = Company::query()
            ->when($keywords, function ($query) use ($keywords) {
                $q_array = explode(" ", trim($keywords));
                foreach ($q_array as $qi) {
                    $query->orWhere('name', 'like', '%' . $qi . '%')
                        ->orWhere('description', 'like', '%' . $qi . '%')
                        ->orWhere('contact_person', 'like', '%' . $qi . '%');
                }
                return $query;
            });
        return $builder;
    }

}

