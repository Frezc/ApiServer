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
        return $this->hasMany('App\JobApply');
    }

    public function jobCompleteds() {
        return $this->hasMany('App\JobCompleted');
    }

    /**
     * 检查某用户是否对其他用户有访问权
     * @param $owner_id 目标用户
     * @return bool
     * @throws MsgException
     */
    public function checkAccess($owner_id) {
        if ($this->id != $owner_id) {
            $role = Role::find($this->role_id);
            if ($role && $role->name == 'admin') {
                return true;
            }
            throw new MsgException('You have no access to this user.', 401);
        }
        return true;
    }

    public function getRealNameVerification() {
        return RealNameVerification::where('user_id', $this->id)->first();
    }

    public function checkNeedRealNameVerify() {
        $rmv = RealNameVerification::where('user_id', $this->id)->whereIn('is_examined', [0, 1])->first();
        if ($rmv) throw new MsgException('You needn\'t apply real name verification.', 400);
        return true;
    }

    public function getCompanies() {
        return UserCompany::where('user_id', $this->id)->get()->each(function ($item, $index) {
            $item->setVisible(['company_id', 'company_name']);
        });
    }
}
