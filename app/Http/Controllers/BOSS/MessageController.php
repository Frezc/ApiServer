<?php

namespace App\Http\Controllers\BOSS;

use App\Http\Controllers\Controller;
use App\Jobs\PushNotifications;
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
}
