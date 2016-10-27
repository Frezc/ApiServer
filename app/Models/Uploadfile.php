<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Uploadfile extends Model
{
    protected $table = 'uploadfiles';
    protected $guarded = ['id'];

    public function makeSureAccess($user) {
        if ($this->uploader_id != $user->id) {
            /* throw */
            throw new MsgException('你没有权限访问此图片', 401);
        }
        return true;
    }
}
