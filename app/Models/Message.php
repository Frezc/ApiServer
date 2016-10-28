<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'receiver_id'];

    public function checkAccess($user) {
        if ($this->receiver_id != $user->id) {
            throw new MsgException('You have no access to this message.', 401);
        }
        return true;
    }
}
