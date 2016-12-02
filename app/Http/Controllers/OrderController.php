<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\PushNotifications;
use App\Models\JobEvaluate;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\UserEvaluate;
use Illuminate\Http\Request;
use JWTAuth;

class OrderController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('log', ['only' => ['close']]);
    }

    public function query(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0',
            'status' => 'in:0,1,2,3',
            'applicant_check' => 'in:0,1',
            'recruiter_check' => 'in:0,1',
            'role' => 'in:applicant,recruiter'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);

        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'created_at');
        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $status = $request->input('status', -1);
        $applicant_check = $request->input('applicant_check', -1);
        $recruiter_check = $request->input('recruiter_check', -1);
        $role = $request->input('role', 'applicant');

        $builder = Order::query()
            ->when($status >= 0, function ($query) use ($status) {
                return $query->where('status', $status);
            })->when($applicant_check >= 0, function ($query) use ($applicant_check) {
                return $query->where('applicant_check', $applicant_check);
            })->when($recruiter_check >= 0, function ($query) use ($recruiter_check) {
                return $query->where('recruiter_check', $recruiter_check);
            })->when($role, function ($query) use ($role, $id) {
                return $query->where($role . '_id', $id);
            });
        $total = $builder->count();

        $builder
            ->orderBy($orderby, $direction)
            ->skip($offset)
            ->limit($limit);
        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    public function close($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();

        if ($order->applicant_id == $self->id) {
            $close_type = 1;
        } elseif ($order->recruiter_type == 0 && $order->recruiter_id == $self->id
            || $order->recruiter_type == 1 && $self->company_id == $order->recruiter_id) {
            $close_type = 2;
        } elseif ($self->isAdmin()) {
            $close_type = 3;
        } else {
            throw new MsgException('You have no access to this order.', 401);
        }

        if ($order->isOver()) {
            throw new MsgException('You cannot close this order.', 400);
        }

        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER), array_merge([$order->applicant_id], $order->getRecruiterIds()),
            '订单 ' . $order->id . ' 已被' . Order::closeTypeText($close_type) . '关闭。'
        ));

        $order->close_type = $close_type;
        $order->status = 3;
        $order->save();
        return response()->json($order);
    }

    public function getEvaluate($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);

        $jobEvaluate = JobEvaluate::where('order_id', $order->id)->first();
        $userEvaluate = UserEvaluate::where('order_id', $order->id)->first();

        return response()->json(['job_evaluate' => $jobEvaluate, 'user_evaluate' => $userEvaluate]);
    }

    public function get($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);
        $order->bindExpectJob();

        return response()->json($order);
    }
}
