<?php

namespace App\Models;

use App\Exceptions\MsgException;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'receiver_id'];

    public static $WORK_HELPER = 1;
    public static $NOTI_HELPER = 2;

    public static function getSender($id) {
        return User::find($id);
    }

    public static function pushNotification($from, $to, $content) {
        $message = Message::firstOrNew([
            'sender_id' => $from->id,
            'receiver_id' => $to,
            'type' => 'notification'
        ]);
        $message->sender_name = $from->nickname;
        $message->sender_avatar = $from->avatar;
        $message->content = $content;
        $message->unread++;
        $message->save();

        Notification::create([
            'message_id' => $message->id,
            'content' => $content
        ]);
    }

    public static function pushNotificationToCampany($from, $company_id, $content) {
        UserCompany::where('company_id', $company_id)
            ->get()
            ->each(function ($uc) use ($from, $content) {
                Message::pushNotification($from, $uc->user_id, $content);
            });
    }

    public function checkAccess($user) {
        if ($this->receiver_id != $user->id) {
            throw new MsgException('You have no access to this message.', 401);
        }
        return true;
    }
}
