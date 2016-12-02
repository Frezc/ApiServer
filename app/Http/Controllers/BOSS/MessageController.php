<?php

namespace App\Http\Controllers\BOSS;

use App\Http\Controllers\Controller;
use App\Jobs\PushNotifications;
use App\Models\Feedback;
use App\Models\Log;
use App\Models\Message;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class MessageController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['postNotifications', 'updateFeedback', 'updateReport']]);
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

    public function getFeedbacks(Request $request) {
        $this->validate($request, [
            'off' => 'integer|min:0',
            'siz' => 'min:0|integer',
            'status' => 'in:1,2,3',
            'type' => 'in:1,2,3'
        ]);

        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $status = $request->input('status');
        $type = $request->input('type');

        $builder = Feedback::query();
        $status && $builder->where('status', $status);
        $type && $builder->where('type', $type);

        $total = $builder->count();
        $list = $builder
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($size)
            ->get();
        return response()->json(['total' => $total, 'list' => $list]);
    }

    public function updateFeedback(Request $request, $id) {
        $fb = Feedback::findOrFail($id);
        $this->validate($request, [
            'type' => 'in:1,2,3',
            'status' => 'in:1,2,3',
            'message' => 'string'
        ]);

        $fb->update(array_only($request->all(), ['type', 'status', 'message']));

        $status = $request->input('status');
        if ($status == 2) {
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $fb->user_id, '反馈已被处理！感谢您对本产品的支持。'));
        }
        if ($status == 2 || $status == 3) {
            $fb->dealt_at = Carbon::now();
            $fb->save();
        }
        return response()->json($fb);
    }

    public function getReports(Request $request) {
        $this->validate($request, [
            'target_type' => 'in:order,user,company,job,expect_job',
            'status' => 'in:1,2,3',
            'off' => 'integer|min:0',
            'siz' => 'min:0|integer'
        ]);

        $type = $request->input('target_type');
        $status = $request->input('status');
        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);

        $builder = Report::query();
        $status && $builder->where('status', $status);
        $type && $builder->where('target_type', $type);

        $total = $builder->count();
        $list = $builder
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->limit($size)
            ->get();
        return response()->json(['total' => $total, 'list' => $list]);
    }

    public function updateReport(Request $request, $id) {
        $report = Report::findOrFail($id);
        $this->validate($request, [
            'status' => 'in:1,2,3',
            'message' => 'string'
        ]);

        $report->update(array_only($request->all(), ['status', 'message']));
        $status = $request->input('status');
        if ($status == 2) {
            $this->dispatch(new PushNotifications(
                Message::getSender(Message::$NOTI_HELPER), $report->user_id, '举报已被处理！感谢您对本产品的支持。'));
        }
        if ($status == 2 || $status == 3) {
            $report->dealt_at = Carbon::now();
            $report->save();
        }
        return response()->json($report);
    }
}
