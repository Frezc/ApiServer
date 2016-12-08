<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\Job;
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
        $this->middleware('log', ['only' => ['close', 'postEvaluate']]);
        $this->middleware('role:user', ['only' => ['close', 'postEvaluate']]);
    }

    /*
     * [GET] users/{id}/orders
     */
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
            });

        if ($role == 'applicant') {
            $builder->where('applicant_id', $user->id);
        } else {
            $builder->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('recruiter_type', 0)
                        ->where('recruiter_id', $user->id);
                });
                $user->company_id && $query->orWhere(function ($query) use ($user) {
                    $query->where('recruiter_type', 1)
                        ->where('recruiter_id', $user->company_id);
                });
            });
        }

        $total = $builder->count();

        $builder
            ->orderBy($orderby, $direction)
            ->skip($offset)
            ->limit($limit);
        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    /*
     * [DELETE] orders/{id}
     */
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

    /*
     * [GET] orders/{id}/evaluate
     */
    public function getEvaluate($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);

        $jobEvaluate = JobEvaluate::where('order_id', $order->id)->first();
        $userEvaluate = UserEvaluate::where('order_id', $order->id)->first();

        return response()->json(['job_evaluate' => $jobEvaluate, 'user_evaluate' => $userEvaluate]);
    }

    /*
     * [GET] orders/{id}
     */
    public function get($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);
        $order->bindExpectJob();

        return response()->json($order);
    }

    /*
     * [POSt] orders/{id}/evaluate
     */
    public function postEvaluate(Request $request, $id) {
        $order = Order::findOrFail($id);

        $this->validate($request, [
            'score' => 'required|integer|between:1,5',
            'comment' => 'string',
            'pictures' => 'string'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);

        if ($self->id == $order->applicant_id) {
            if (JobEvaluate::where('order_id', $order->id)->count() > 0) {
                throw new MsgException('Order has been evaluated.', 400);
            } else {
                JobEvaluate::create(array_merge(array_only($request->all(), ['score', 'comment', 'pictures']), [
                    'user_id' => $self->id,
                    'user_name' => $self->nickname,
                    'order_id' => $order->id,
                    'job_id' => $order->job_id
                ]));
                $job = Job::find($order->job_id);
                $job && $job->updateScore();
                return '评价成功';
            }
        }

        if ($order->isRecruiter($self)) {
            if (UserEvaluate::where('order_id', $order->id)->count() > 0) {
                throw new MsgException('Order has been evaluated.', 400);
            } else {
                UserEvaluate::create(array_merge(array_only($request->all(), ['score', 'comment', 'pictures']), [
                    'user_id' => $self->id,
                    'user_name' => $self->nickname,
                    'order_id' => $order->id,
                    'target_id' => $order->applicant_id
                ]));
                return '评价成功';
            }
        }

        throw new MsgException('You cannot evaluate this order', 400);
    }
}
