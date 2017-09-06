<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\CloseOrderWhenNotPay;
use App\Jobs\PushNotifications;
use App\Models\Job;
use App\Models\JobApply;
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
use Illuminate\Support\Facades\DB;
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
        $status = $request->input('status');
        if ($status==3){

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

            $order->close_type = $close_type;
            $order->close_reason = $request->input('reason');
        // 向该订单的相关人员发送消息
        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER), array_merge([$order->applicant_id], $order->getRecruiterIds()),
            '订单 ' . $order->id . ' 已被' . Order::closeTypeText($close_type) . '关闭。'
          ));
        }
        // 保存订单
        $order->status = $status;
        $order->save();
        // 返回更新的订单
        return  'success';
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
//    /*
//     * [POST] orders/{id}/evaluate
//     */
//    public function postEvaluate(Request $request, $id) {
//        $order = Order::findOrFail($id);
//
//        $this->validate($request, [
//            'score' => 'required|integer|between:1,5',  // 分数
//            'comment' => 'string',                      // 评价
//            'pictures' => 'string'                      // 附图
//        ]);
//
//        $self = JWTAuth::parseToken()->authenticate();
//        $order->makeSureAccess($self);
//        // 订单是否已完成
//        if ($order->status != 2)
//            throw new MsgException('你只能评价已完成的订单', 400);
//        if ($self->id == $order->applicant_id) {
//            // 当前用户为求职者
//            if (JobEvaluate::where('order_id', $order->id)->count() > 0) {
//                throw new MsgException('订单已经被评价.', 400);
//            } else {
//                // 保存对岗位的评价
//                JobEvaluate::create(array_merge(array_only($request->all(),
//                    ['score', 'comment', 'pictures']), [
//                    'user_id' => $self->id,
//                    'user_name' => $self->nickname,
//                    'order_id' => $order->id,
//                    'job_id' => $order->job_id
//                ]));
//                $job = Job::find($order->job_id);
//                // 需要更新岗位表里的分数
//                $job && $job->updateScore();
//                return '评价成功';
//            }
//        }
//
//        if ($order->isRecruiter($self)) {
//            // 当前用户为招聘方的人
//            if (UserEvaluate::where('order_id', $order->id)->count() > 0) {
//                throw new MsgException('Order has been evaluated.', 400);
//            } else {
//                // 保存对用户的评价
//                UserEvaluate::create(array_merge(array_only($request->all(),
//                    ['score', 'comment', 'pictures']), [
//                    'user_id' => $self->id,
//                    'user_name' => $self->nickname,
//                    'order_id' => $order->id,
//                    'target_id' => $order->applicant_id
//                ]));
//                return '评价成功';
//            }
//        }
//        // 没有权限评价
//        throw new MsgException('You cannot evaluate this order', 400);
//    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 允许面试    // 订单状态 0：创建，1：允许面试，2：拒绝面试成功，3：面试成功 4 面试失败 5面成功并接受 6 工作完成先下付款 7工作完成已付款 8订单关闭(面试陈功以后)
     */
    public function orderHandle(Request $request)
    {
        $order = new Order();
        $service = new MyService();
        $self = JWTAuth::parseToken()->authenticate();
        $order_id = $request->get('order_id');
        $status = $request->get('status');
        $where = array('id' => $order_id);
        if (!checkTableData($order, $where, 'recruiter_id', $self->id)&&!checkTableData($order, $where, 'applicant_id', $self->id)) {
            return sucesss('没有权限操作这订单');
        }
        if (checkTableData($order, $where, 'status', $status)) {
            return sucesss('已经处理申请');
        }
        $result = updateTable($order, $where, ['status' => $status]);
        if ($result === false) {
            return sucesss('发送失败');
        }
        return sucesss('处理成功');
//        $data = getTableField($order, $where, ['applicant_id']);
//        //发送邮件
//        $email = getTableClumnValue('users', ['id' => getTableClumnValue('orders', $where, 'applicant_id')], 'email');
//        $data['phone'] = getTableClumnValue('users', ['id' => getTableClumnValue('orders', $where, 'recruiter_id')], 'phone');
//        $data['company_name'] = getTableClumnValue('tjz_job', ['id' => getTableClumnValue('orders', $where, 'job_id')], 'company_name');
//        $data['addr'] = getTableClumnValue('tjz_job', ['id' => getTableClumnValue('orders', $where, 'job_id')], 'address');
//        $data['job_name'] = getTableClumnValue('orders', $where, 'job_name');
//        $data['time'] = getTableClumnValue('job_times', ['id' => getTableClumnValue('order', $where, 'job_id')], 'start_at');
//        $data['$contact_person'] = getTableClumnValue('orders', $where, 'recruiter_name');
//        $service->emailSend($email, 'emails.interview', $data);
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
        if ($order->status == Status::$guangbi)
            throw new MsgException('订单已过期。', 400);
        // 线下支付
        if ($order->pay_way == 1){
            $order->status = Status::$gongzuowanchengxianxiafukuan;
            $order->save();
            return sucesss('你已在下线付款，请等待对方确认');
        }

        // 当前用户不是招聘方
        if ($order->recruiter_id != $self->id)
            return sucesss('你没有支付权限');

        if ($self->money > $order->salary) {
            // 用户余额足够
            $self->money -= $order->salary;
            $order->has_paid = 1;
            $order->status = Status::$gongzuowancheng;
            $money = getTableClumnValue('users',['id'=>$order->applicant_id],'money');
            updateTable('users',['id'=>$order->applicant_id],['money'=>$money+$order->salary]);
            $self->save();
            $order->save();
        } else {
            // 余额不足
            throw new MsgException('您没足够的余额支付该订单。', 400);
        }
        return sucesss('支付完成');
    }


    /*
     * 用户获取申请详情
     */
    public function UserGetOrderByStatus(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role_id == 1){
            $where =['applicant_id',$user->id];

        }if ($user->role_id == 2){
            $where =['recruiter_id',$user->id];
        }
        $orders = \DB::table('orders')->where($where)->where('status',$request->input('status'));
        $orders->orderBy('created_at','desc');
        $total = $orders->count();
        $data = $orders->get();
        foreach ($data as $key=>$value){
          $data[$key]->start_at = getTableClumnValue('job_times',['job_id'=>$value->job_id],'start_at');
          $data[$key]->end_at = getTableClumnValue('job_times',['job_id'=>$value->job_id],'end_at');
        }
        return response()->json(['list'=>$data,'total'=>$total]);
    }
    /*
     * 企业所有相应状态的岗位
     */
    public function getCompanyOrderStatus(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $status = $request->input('status');
        if ($status == 0){
            $jobs = \DB::table('orders')->join('tjz_jobs','tjz_jobs.id','=','orders.job_id')
                ->join('job_times','job_times.job_id','=','orders.job_id')
                ->join('users','users.id','=','orders.applicant_id')
                ->where('recruiter_id',$user->company_id)
                ->where('active',1);
        }
        if ($status == 1){
        $jobs = \DB::table('orders')->join('tjz_jobs','tjz_jobs.id','=','orders.job_id')
           ->join('job_times','job_times.job_id','=','orders.job_id')
           ->join('users','users.id','=','orders.applicant_id')
           ->where('recruiter_id',$user->company_id)
            ->where('active',1)
           ->where('status',$status);
       }
       if ($status == 3)
       {
           $jobs = \DB::table('orders')->join('tjz_jobs','tjz_jobs.id','=','orders.job_id')
               ->join('job_times','job_times.job_id','=','orders.job_id')
               ->join('users','users.id','=','orders.applicant_id')
               ->where('recruiter_id',$user->company_id)
               ->where('status',$status);
       }
        $jobs->select('orders.id','tjz_jobs.pay_way','applicant_id','orders.job_id','phone','job_name','orders.salary','address','start_at','end_at','apply_number','required_number','orders.salary_type');
        $jobs->orderBy('orders.created_at','desc');
        $total = $jobs->count();
        return response()->json(['list'=>$jobs->get(),'total'=>$total]);
    }
    /**
     * @param Request $request 用户评价
     */
    public function orderAppraisal(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $order_id = $request->get('order_id');
        $score = $request->get('score');
        $content = $request->get('content');
        $order = Order::findOrFail($order_id);

        $data['user_id'] = $user->id;
        $data['user_name'] = $user->nickname;
        $data['order_id'] = $order_id;
        $data['job_id'] = $order->job_id;
        $data['score'] = $score;
        $data['comment'] = $content;
        if ($order->status != Status::$gongzuowancheng){
            return sucesss('工作没有结束');
        }
        /**
         * 申请者评价
         */
        if ($user->id == $order->applicant_id) {
            $order->applicant_check = 1;
            $data['pingjia_user_id'] = $order->recruiter_id;
        }
        /**
         * 发布者评价
         */
        if ($user->id == $order->recruiter_id) {
            $order->recruiter_check = 1;
            $data['pingjia_user_id'] = $order->applicant_id;

        }

        if (inserTable('job_evaluate',$data)) {
            $order->save();
            return sucesss('评价成功');
        } else{
            return sucesss('评价失败');
        }

    }

    /**
     * @param Request $request  获取所有评价
     */

    public function getAppraisal(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $order_id = $request->get('order_id');
        $user_id = getTableClumnValue('orders',['id'=>$order_id],recruiter_id);
        if (empty($user_id)){
            $user_id = $user_id->id;
        }
        $content = \DB::table('job_evaluate')->where('pingjia_user_id',$user_id);
        $content->orderBy('created_at');
        $data = $content->get();
        $total = $content->count();
        return response()->json(['list'=>$data,'total'=>$total]);
    }

}
