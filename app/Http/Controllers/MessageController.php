<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

class MessageController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function getUpdate() {
        $self = JWTAuth::parseToken()->authenticate();
        $count = Message::where('receiver_id', $self->id)->sum('unread');
        return response()->json(['messages_count' => $count]);
    }

    public function get(Request $request) {
        $this->validate($request, [
            'off' => 'integer',
            'siz' => 'integer'
        ]);

        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);
        $self = JWTAuth::parseToken()->authenticate();
        $builder = Message::where('receiver_id', $self->id);
        $total = $builder->count();
        $list = $builder
            ->orderBy('updated_at', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();
        return response()->json(['total' => $total, 'list' => $list]);
    }

    public function getNotification(Request $request, $id) {
        $message = Message::findOrFail($id);

        $this->validate($request, [
            'off' => 'integer',
            'siz' => 'integer'
        ]);

        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);
        $self = JWTAuth::parseToken()->authenticate();
        $message->checkAccess($self);
        $message->unread = 0;
        $message->save();

        $builder = Notification::where('message_id', $message->id);
        $total = $builder->count();
        $list = $builder
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();
        return response()->json(['total' => $total, 'list' => $list]);
    }

    public function getConversation(Request $request) {
        $this->validate($request, [
            'target_id' => 'required|integer',
            'off' => 'integer',
            'siz' => 'integer'
        ]);

        $targetId = $request->input('target_id');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);
        $self = JWTAuth::parseToken()->authenticate();

        $message = Message::where('receiver_id', $self->id)
            ->where('sender_id', $targetId)->first();
        $message->unread = 0;
        $message->save();

        $conversationId = $this->getConversationId($message->sender_id, $message->receiver_id);
        $builder = Conversation::where('conversation_id', $conversationId);
        $total = $builder->count();
        $list = $builder
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get();
        return response()->json(['total' => $total, 'list' => $list]);
    }

    public function postConversation(Request $request) {
        $this->validate($request, [
            'receiver_id' => 'required|integer',
            'content' => 'required|string'
        ]);

        $receiver_id = $request->input('receiver_id');

        $receiver = User::findOrFail($receiver_id);

        $content = $request->input('content');
        $self = JWTAuth::parseToken()->authenticate();

        $conversation = Conversation::create([
            'conversation_id' => $this->getConversationId($self->id, $receiver->id),
            'sender_id' => $self->id,
            'sender_name' => $self->nickname,
            'sender_avatar' => $self->avatar,
            'content' => $content
        ]);

        $message = Message::firstOrNew([
            'receiver_id' => $receiver->id,
            'sender_id' => $self->id,
            'type' => 'conversation'
        ]);
        $message->sender_name = $self->nickname;
        $message->sender_avatar = $self->avatar;
        $message->content = $self->nickname . '：' . $content;
        $message->unread = $message->unread + 1;
        $message->save();

        $message = Message::firstOrNew([
            'receiver_id' => $self->id,
            'sender_id' => $receiver->id,
            'type' => 'conversation'
        ]);
        $message->sender_name = $receiver->nickname;
        $message->sender_avatar = $receiver->avatar;
        $message->content = $content;
        $message->save();

        return response()->json($conversation);
    }

    private function getConversationId($id1, $id2) {
        return min($id1, $id2) . 'c' . max($id1, $id2);
    }
}
