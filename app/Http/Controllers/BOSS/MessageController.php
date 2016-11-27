<?php

namespace App\Http\Controllers\BOSS;

use App\Http\Controllers\Controller;
use App\Jobs\PushNotifications;
use App\Models\Log;
use App\Models\Message;
use Illuminate\Http\Request;
use JWTAuth;

class MessageController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['postNotifications']]);
    }

    public function postNotifications(Request $request) {
        $this->validate($request, [
            'content' => 'required',
            'from' => 'in:1,2',
            'target' => 'array',
            'target.*' => 'integer'
        ]);

        $content = $request->input('content');
        $from = $request->input('from', 2);
        $target = $request->input('target');


        $this->dispatch(new PushNotifications(
            Message::getSender($from), $target, $content
        ));
        return 'success';
    }

    public function getHistory(Request $request) {
        $this->validate($request, [
            'off' => 'integer|min:0',
            'siz' => 'min:0|integer'
        ]);

        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);

        $builder = Log::where('method', 'POST')
            ->where('path', 'notifications');
        $total = $builder->count();
        $list = $builder
            ->skip($offset)
            ->limit($size)
            ->get()
            ->map(function ($item, $index) {
                $params = json_decode($item->params, true);
                $base = [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'user_name' => $item->user_name,
                    'created_at' => $item->created_at->toDateTimeString()
                ];
                return array_merge($base, $params);
            });
        return response()->json(['total' => $total, 'list' => $list]);
    }
}
