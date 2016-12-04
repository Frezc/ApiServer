<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract {
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'role_id', 'email_verified', 'updated_at'];


    protected $guarded = ['id', 'role_id'];

    public function resumes() {
        return Resume::where('user_id', $this->id);
//        return $this->hasMany('App\Resume');
    }

    public function jobApplies() {
        return $this->hasMany('App\Models\JobApply');
    }

    public function jobCompleteds() {
        return $this->hasMany('App\Models\JobCompleted');
    }

    public function companies() {
        return $this->belongsToMany('App\Models\Company', 'user_company', 'user_id', 'company_id');
    }

    public function bindRoleName() {
        $role = Role::find($this->role_id);
        if ($role)
            $this->role_name = $role->name;
    }

    /**
     * 检查某用户是否对其他用户有访问权
     * @param $owner_id 目标用户
     * @return bool
     * @throws MsgException
     */
    public function checkAccess($owner_id) {
        if ($this->id != $owner_id) {
            if ($this->isAdmin()) {
                return true;
            }
            throw new MsgException('You have no access to this user.', 401);
        }
        return true;
    }

    public function isAdmin() {
        $role = Role::find($this->role_id);
        if ($role && $role->name == 'admin') {
            return true;
        }
        return false;
    }

    public function getRealNameVerification() {
        return RealNameVerification::where('user_id', $this->id)->first();
    }

    public function checkNeedRealNameVerify() {
        $rmv = RealNameVerification::where('user_id', $this->id)->whereIn('is_examined', [0, 1])->first();
        if ($rmv) throw new MsgException('You needn\'t apply real name verification.', 400);
        return true;
    }

//    public function getCompanies() {
//        return UserCompany::where('user_id', $this->id)->get()->each(function ($item, $index) {
//            $item->setVisible(['company_id', 'company_name']);
//        });
//    }

    public static function search($kw) {
        $q_array = $kw ? array_slice(explode(" ", trim($kw)), 0, 3) : [];

        $builder = User::query();
        foreach ($q_array as $qi) {
            $builder->where(function ($query) use ($qi) {
                $query->where('nickname', 'like', '%' . $qi . '%')
                    ->orWhere('email', 'like', '%' . $qi . '%')
                    ->orWhere('phone', 'like', '%' . $qi . '%');
            });
        }

        return $builder;
    }
}
