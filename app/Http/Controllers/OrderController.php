<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\CloseOrderWhenNotPay;
use App\Jobs\PushNotifications;
use App\Models\Job;
use App\Models\JobEvaluate;
use App\Models\JobTime;
use App\Models\Log;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\UserEvaluate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class OrderController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('log', ['only' => ['close', 'postEvaluate', 'check', 'pay', 'completed']]);
        $this->middleware('role:user', ['only' => ['close', 'postEvaluate', 'check', 'pay', 'completed']]);
    }

    /*
     * [GET] users/{id}/orders
     */
    public function query(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'siz' => 'integer|min:0',         // 一页数量
            'orderby' => 'in:id,created_at',  // 排序方式
            'dir' => 'in:asc,desc',           // 排序方向
            'off' => 'integer|min:0',         // 跳过多少项
            'status' => 'in:0,1,2,3',         // 状态筛选
            'applicant_check' => 'in:0,1',    // 求职者是否确认
            'recruiter_check' => 'in:0,1',    // 招聘方是否确认
            'role' => 'in:applicant,recruiter'// 该用户为求职者或招聘方
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        // 验证访问权限
        $self->checkAccess($user->id);

        // 获取参数，设置默认值
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'created_at');
        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $status = $request->input('status', -1);
        $applicant_check = $request->input('applicant_check', -1);
        $recruiter_check = $request->input('recruiter_check', -1);
        $role = $request->input('role', 'applicant');

        // 通过条件筛选
        $builder = Order::query()
            ->when($status >= 0,
                function ($query) use ($status) {
                    return $query->where('status', $status);
                }
            )->when($applicant_check >= 0,
                function ($query) use ($applicant_check) {
                    return $query->where('applicant_check', $applicant_check);
                }
            )->when($recruiter_check >= 0,
                function ($query) use ($recruiter_check) {
                    return $query->where('recruiter_check', $recruiter_check);
                }
            );

        if ($role == 'applicant') {
            // 作为求职者
            $builder->where('applicant_id', $user->id);
        } else {
            // 作为招聘方
            $builder->where(function ($query) use ($user) {
                // 得到自己的订单
                $query->where(function ($query) use ($user) {
                    $query->where('recruiter_type', 0)
                        ->where('recruiter_id', $user->id);
                });
                // 得到自己所在企业的订单
                $user->company_id && $query->orWhere(
                    function ($query) use ($user) {
                        $query->where('recruiter_type', 1)
                            ->where('recruiter_id', $user->company_id);
                    }
                );
            });
        }
        // 得到数量
        $total = $builder->count();
        // 排序、分页
        $builder
            ->orderBy($orderby, $direction)
            ->skip($offset)
            ->limit($limit);
        // 返回json
        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    /*
     * [DELETE] orders/{id}
     */
    public function close(Request $request, $id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();

        if ($order->applicant_id == $self->id) {
            // 当前用户为求职者时
            $close_type = 1;
        } elseif ($order->recruiter_type == 0 && $order->recruiter_id == $self->id
            || $order->recruiter_type == 1 && $self->company_id == $order->recruiter_id) {
            // 当前用户所属招聘方时
            $close_type = 2;
        } elseif ($self->isAdmin()) {
            // 当前用户为管理员时
            $close_type = 3;
        } else {
            // 其他用户没有权限关闭
            throw new MsgException('You have no access to this order.', 401);
        }

        if ($order->isOver()) {
            // 不能关闭已完成或已关闭的订单
            throw new MsgException('You cannot close this order.', 400);
        }

        if ($order->has_paid) {
            // 订单已支付，要将金额返还
            $log = Log::where('method', 'post')->where('orders/' . $order->id . '/payment')->first();
            if ($log) {
                $user = User::find($log->user_id);
                $jobTime = JobTime::find($order->job_time_id);
                $user->money += $jobTime->salary;
                $user->save();
            }
        }
        // 向该订单的相关人员发送消息
        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER), array_merge([$order->applicant_id], $order->getRecruiterIds()),
            '订单 ' . $order->id . ' 已被' . Order::closeTypeText($close_type) . '关闭。'
        ));
        // 保存订单
        $order->close_type = $close_type;
        $order->close_reason = $request->input('reason');
        $order->status = 3;
        $order->save();
        // 返回更新的订单
        return  'success';
        
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
     * [POST] orders/{id}/evaluate
     */
    public function postEvaluate(Request $request, $id) {
        $order = Order::findOrFail($id);

        $this->validate($request, [
            'score' => 'required|integer|between:1,5',  // 分数
            'comment' => 'string',                      // 评价
            'pictures' => 'string'                      // 附图
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);
        // 订单是否已完成
        if ($order->status != 2)
            throw new MsgException('你只能评价已完成的订单', 400);
        if ($self->id == $order->applicant_id) {
            // 当前用户为求职者
            if (JobEvaluate::where('order_id', $order->id)->count() > 0) {
                throw new MsgException('订单已经被评价.', 400);
            } else {
                // 保存对岗位的评价
                JobEvaluate::create(array_merge(array_only($request->all(),
                    ['score', 'comment', 'pictures']), [
                    'user_id' => $self->id,
                    'user_name' => $self->nickname,
                    'order_id' => $order->id,
                    'job_id' => $order->job_id
                ]));
                $job = Job::find($order->job_id);
                // 需要更新岗位表里的分数
                $job && $job->updateScore();
                return '评价成功';
            }
        }

        if ($order->isRecruiter($self)) {
            // 当前用户为招聘方的人
            if (UserEvaluate::where('order_id', $order->id)->count() > 0) {
                throw new MsgException('Order has been evaluated.', 400);
            } else {
                // 保存对用户的评价
                UserEvaluate::create(array_merge(array_only($request->all(),
                    ['score', 'comment', 'pictures']), [
                    'user_id' => $self->id,
                    'user_name' => $self->nickname,
                    'order_id' => $order->id,
                    'target_id' => $order->applicant_id
                ]));
                return '评价成功';
            }
        }
        // 没有权限评价
        throw new MsgException('You cannot evaluate this order', 400);
    }

    /*
     * [POST] orders/{id}/check
     */
    public function check(Request $request, $id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        // 检查权限
        $order->makeSureAccess($self);
        dd($order->isRecruiter($self));
        if ($order->applicant_id == $self->id && !$order->applicant_check) {
            // 当前用户为求职者且求职者未确认时，选择一个工作时间确认
            $jobTimeId = $request->input('job_time_id');
            $jobTime = JobTime::findOrFail($jobTimeId);
            if ($jobTime->job_id != $order->job_id)
                throw new MsgException('非法的job_time_id', 400);
            $order->job_time_id = $jobTime->id;
            $order->applicant_check = 1;
            $order->status = 1;
            $order->save();
            // 向对方发送已确认的通知
            $this->dispatch(new PushNotifications(Message::getSender(Message::$WORK_HELPER),
                $order->getRecruiterIds(), '对方已经确认了订单 ' . $order->id . '。'));
            // 一个小时内需要支付
            $job = (new CloseOrderWhenNotPay($order->id))->delay(60 * 60);
            $this->dispatch($job);
            return response()->json($order);
        } elseif ($order->isRecruiter($self) && !$order->recruiter_check) {
            // 当前用户所属招聘方且未确认
            $order->recruiter_check = 1;
            $order->status = 1;
            $order->save();
            // 向对方发送已确认的通知
            $this->dispatch(new PushNotifications(Message::getSender(Message::$WORK_HELPER),
                $order->applicant_id, '对方已经确认了订单 ' . $order->id . '。'));
            $job = (new CloseOrderWhenNotPay($order->id))->delay(60 * 60);
            $this->dispatch($job);
            return response()->json($order);
        }
        // 错误
        throw new MsgException('你已经确认过该订单了。', 400);
    }

    /*
     * [POST] orders/{id}/payment
     */
    public function pay($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);
        // 已支付
        if ($order->has_paid)
            throw new MsgException('您已经支付过了。', 400);
        // 已关闭
        if ($order->status == 3)
            throw new MsgException('订单已过期。', 400);
        // 线下支付
        if ($order->pay_way == 1)
            throw new MsgException('您无须支付该订单。', 400);
        // 当前用户不是招聘方
        if (!$order->isRecruiter($self))
            throw new MsgException('您无法支付该订单', 400);

        $jobTime = JobTime::findOrFail($order->job_time_id);
        if ($self->money > $jobTime->salary) {
            // 用户余额足够
            $self->money -= $jobTime->salary;
            $order->has_paid = 1;
            $self->save();
            $order->save();
        } else {
            // 余额不足
            throw new MsgException('您没足够的余额支付该订单。', 400);
        }
        return response()->json($order);
    }

    /*
     * [POST] orders/{id}/completed
     */
    public function completed($id) {
        $order = Order::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $order->makeSureAccess($self);

        if (!$order->isRecruiter($self))
            throw new MsgException('您没有权限操作该订单', 400);
        if ($order->pay_way == 2 && !$order->has_paid)
            throw new MsgException('您必须先支付该订单。', 400);
        if ($order->status != 1)
            throw new MsgException('您只能完成确认状态的订单。', 400);
        $jobTime = JobTime::findOrFail($order->job_time_id);
        if ($jobTime->end_at > Carbon::now()->toDateTimeString())
            throw new MsgException('您必须在工作时间结束后才能完成订单', 400);
        // 对于在线支付的订单，需要将钱转给求职者
        if ($order->has_paid) {
            $user = User::findOrFail($order->applicant_id);
            $user->money += $jobTime->salary;
            $user->save();
        }

        $order->status = 2;
        $order->save();
        return response()->json($order);
    }
    public function getOrderStatus(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $jobs = \DB::table('orders')->where('applicant_id',$user->id)->where('status',$request->input('status'));
        $jobs->orderBy('created_at','desc');
        $total = $jobs->count();
        return response()->json(['list'=>$jobs->get(),'total'=>$total]);
    }
}
