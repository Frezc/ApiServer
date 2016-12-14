<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\PushNotifications;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobEvaluate;
use App\Models\JobTime;
use App\Models\Message;
use App\Models\Order;
use App\Models\Resume;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class JobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
        $this->middleware('log', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
        $this->middleware('role:user', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
    }

    /*
     * [GET] jobs/{id}
     */
    public function get($id) {
        // 得到当前登录的用户
        $user = $this->getAuthenticatedUser();
        // 判断是否为管理员
        if ($user && $user->isAdmin()) {
            // 如果是管理员可以得到已删除的岗位
            $job = Job::withTrashed()->findOrFail($id);
        } else {
            // 如果不是的话只能得到未删除的岗位
            $job = Job::findOrFail($id);
        }
        // 访问次数+1
        $job->visited++;
        $job->save();
        // 绑定岗位的工作时间段
        $job->bindTime();
        // 返回json数据
        return response()->json($job);
    }

    /*
     * [POST] jobs
     */
    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required|string|between:1,250', // 名称
            'pay_way' => 'required|integer|in:1,2',    // 支付方式
            'description' => 'string',                 // 描述
            'contact' => 'required|string|max:250',    // 联系方式
            'contact_person' => 'required|string|max:16', // 联系人
            'type' => 'required|exists:job_types,name', // 岗位类型
            'city' => 'required|string',              // 城市
            'address' => 'string',                    // 地址
            'by_company' => 'in:0,1'                  // 是否由企业发布
        ]);

        $byCompany = $request->input('by_company');
        $self = JWTAuth::parseToken()->authenticate();

        // 筛选传入的参数
        $params = array_only($request->all(),
            ['name', 'pay_way', 'description', 'contact', 'contact_person', 'type', 'city', 'address']);
        if ($byCompany) {
            if ($self->company_id) {
                $params['company_id'] = $self->company_id;
                $params['company_name'] = $self->company_name;
            } else {
                throw new MsgException('There is no company belongs to you.', 400);
            }
        }

        // 表中插入岗位
        $job = Job::create($params);
        // 返回创建成功的json数据
        return response()->json($job);
    }

    /*
     * [POST] jobs/{id}
     */
    public function update(Request $request, $id) {
        $self = JWTAuth::parseToken()->authenticate();
        if ($self->isAdmin()) {
            $job = Job::withTrashed()->findOrFail($id);
        } else {
            $job = Job::findOrFail($id);
        }
        $this->validate($request, [
            'name' => 'string|between:1,250',
            'pay_way' => 'integer|in:1,2',
            'salary_type' => 'integer|in:1,2',
            'description' => 'string',
            'active' => 'integer|in:0,1',
            'contact' => 'string|max:250',
            'contact_person' => 'string|max:16',
            'type' => 'exists:job_types,name',
            'city' => 'string',
            'address' => 'string'
        ]);

        $job->checkAccess($self);

        $job->update(array_only($request->all(), ['name', 'pay_way', 'salary_type', 'description', 'active', 'contact', 'contact_person', 'type', 'city', 'address']));
        return response()->json($job);
    }

    /*
     * [POST] jobs/{id}/time
     */
    public function addTime(Request $request, $id) {
        // 找到岗位，否则返回404
        $job = Job::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        // 检查当前用户是否有修改权限
        $job->checkAccess($self);
        // json格式
        $this->validate($request, [
            'time' => 'required|array',         // 时间数组
            'time.*.number' => 'integer|min:0', // 人数
            'time.*.salary_type' => 'in:1,2',   // 工资类型
            'time.*.salary' => 'integer|min:0', // 工资
            'time.*.apply_end_at' => 'date',    // 申请结束时间
            'time.*.start_at' => 'required|date', // 开始时间
            'time.*.end_at' => 'required|date',  // 结束时间
        ]);

        $time = $request->input('time');

        foreach ($time as $t) {
            $tf = array_only($t,
                ['number', 'salary_type', 'salary', 'apply_end_at', 'start_at', 'end_at']);
            // 申请结束时间默认等于开始时间
            if (!$tf['apply_end_at']) $tf['apply_end_at'] = $tf['start_at'];
            // 保存到数据库
            JobTime::create(array_merge($tf, ['job_id' => $job->id]));
        }

        // 绑定岗位的时间
//        $job->bindTime();
        $jobTime = JobTime::withTrashed()->where('job_id', $job->id)->orderBy('apply_end_at', 'desc')->get();
        return response()->json($jobTime);
    }

    /*
     * [DELETE] jobs/{id}/time
     */
    public function closeTime(Request $request, $id) {
        // 找到岗位，否则返回404
        $job = Job::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        // 检查当前用户是否有修改权限
        $job->checkAccess($self);
        // json格式
        $this->validate($request, [
            'time' => 'required|string'         // 用逗号隔开
        ]);

        $time = $request->input('time');
        $timeArr = explode(',', $time);
        JobTime::where('job_id', $job->id)->whereIn('id', $timeArr)->delete();
        // 绑定岗位的时间
//        $job->bindTime();
        $jobTime = JobTime::withTrashed()->where('job_id', $job->id)->orderBy('apply_end_at', 'desc')->get();
        return response()->json($jobTime);
    }


    /*
     * [GET] jobs/{id}/evaluate
     */
    public function getEvaluate(Request $request, $id) {
        $job = Job::findOrFail($id);
        $this->validate($request, [
            'off' => 'integer|min:0',
            'siz' => 'min:0|integer'
        ]);

        // 第二个参数为默认值
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);

        $builder = JobEvaluate::where('job_id', $job->id);

        $total = $builder->count();

        $evaluates = $builder
            ->skip($offset)
            ->limit($limit)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($evaluates as $evaluate) {
            $evaluate->makeHidden('job_id');
        }

        return response()->json(['total' => $total, 'list' => $evaluates]);
    }

    /*
     * [GET] jobs
     */
    public function query(Request $request) {
        // 传入参数验证
        $this->validate($request, [
            'kw' => 'string', // 关键字
            'siz' => 'integer|min:0', // 最多数量
            'orderby' => 'in:id,created_at,average_score', // 排序方式
            'company_id' => 'integer', // 发布者为企业时的id筛选
            'user_id' => 'integer',    // 发布者的id筛选
            'time_s' => 'string|date', // 时间筛选
            'time_e' => 'string|date', // 时间段筛选（结束时间）
            'dir' => 'in:asc,desc',    // 排序方向
            'off' => 'integer|min:0',  // 跳过多少数据
            'exist' => 'integer|in:1,2', // （管理员权限）是否显示删除项
            'type' => 'exists:job_types,name', // 岗位类型
            'city' => 'string',        // 所在城市
            'by_company' => 'in:0,1'   // 是否由企业发布
        ]);

        // 获取传入参数，或者设为默认值
        $q = $request->input('kw');
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $company_id = $request->input('company_id');
        $user_id = $request->input('user_id');
        $exist = $request->input('exist');
        $type = $request->input('type');
        $city = $request->input('city');
        $by_company = $request->input('by_company');
        $time_s = $request->input('time_s');
        $time_e = $request->input('time_e');

        // 关键字搜索
        $builder = Job::search($q);
        // 筛选
        $user_id && $builder->where('creator_id', $user_id);
        $company_id && $builder->where('company_id', $company_id);
        $type && $builder->where('type', $type);
        $city && $builder->where('city', $city);
        $by_company && $builder->whereNotNull('company_id');
        // 筛选时间
        // 申请结束的时间必须比当前晚
        $targetTime = JobTime::where('apply_end_at', '>', Carbon::now()->toDateTimeString());
        if ($time_s) {
            // 选择包括用户选定时间的job time
            $targetTime->where(function ($query) use ($time_s) {
                $query->where('start_at', '<=', $time_s)
                    ->where('end_at', '>=', $time_s);
            });
            // 如果用户选择的是一段时间
            if ($time_e) {
                // 交叉或包含的情况
                $targetTime->orWhere(function ($query) use ($time_e) {
                    $query->where('start_at', '<=', $time_e)
                        ->where('end_at', '>=', $time_e);
                })->orWhere(function ($query) use ($time_s, $time_e) {
                    $query->where('start_at', '>', $time_s)
                        ->where('end_at', '<', $time_e);
                });
            }
        }
        // 得到对应的job id
        $target = $targetTime->select('job_id')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return $item->job_id;
            });
        $builder->whereIn('id', $target);

        // 判断是否为管理员，如果是则包括删除的数据
        if ($user = $this->getAuthenticatedUser()) {
            // 是否为管理员
            if ($user->isAdmin()) {
                // 显示数据类型【只显示删除的、只显示存在的、全部显示】
                if ($exist == 2) {
                    $builder->onlyTrashed();
                } else if (!$exist) {
                    $builder->withTrashed();
                }
            }
        }

        // 得到数量
        $total = $builder->count();

        // 排序以及分页
        $builder->orderBy($orderby, $direction)
                ->skip($offset)
                ->limit($limit);

        $jobs = $builder->get();
        // 返回json数据
        return response()->json(['total' => $total, 'list' => $jobs]);
    }

    /*
     * [POST] jobs/{id}/apply
     */
    public function apply(Request $request, $id) {
        $job = Job::findOrFail($id);

        $this->validate($request, [
            'job_time_id' => 'required|integer',  // 工作时间的id
            'resume_id' => 'required|integer'     // 简历id
        ]);
        // 获取工作时间
        $jobTime = JobTime::where('job_id', $job->id)
            ->findOrFail($request->input('job_time_id'));
        // 获取简历
        $resume = Resume::findOrFail($request->input('resume_id'));

        $self = JWTAuth::parseToken()->authenticate();
        // 验证权限
        $self->checkAccess($resume->user_id);
        // 将简历转为求职资料
        $expectJob = $resume->convertToExpectJob();
        // 创建订单
        $order = Order::create([
            'job_id' => $job->id,      // 岗位id
            'job_name' => $job->name,  // 岗位名称
            'job_time_id' => $jobTime->id, // 工作时间id
            'pay_way' => $job->pay_way,    // 支付方式
            'expect_job_id' => $expectJob->id, // 求职资料id
            'applicant_id' => $resume->user_id, // 申请者Id
            'applicant_name' => $self->nickname, // 申请者名称
            'recruiter_type' => $job->company_id ? 1 : 0, // 招聘者类型
            'recruiter_id' => $job->company_id ?
                $job->company_id : $job->creator_id,  // 招聘者id
            'recruiter_name' => $job->company_id ?
                $job->company_name : $job->creator_name, // 招聘者名称
            'status' => 0,                    // 状态
            'applicant_check' => 1,           // 申请者是否确认
            'recruiter_check' => 0            // 招聘方是否确认
        ]);

        $order->expect_job = $expectJob;
        $order->job_time = $jobTime;

        // 得到招聘方的id，如果是企业的话会得到企业下的所有人id
        $to = $job->creator_id;
        if ($job->company_id) {
            $to = Company::getUserIds($job->company_id);
        }
        // 发送消息
        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER),
            $to,
            $self->nickname . ' 申请了岗位 ' . $job->name . '。'
        ));
        // 返回创建的订单信息
        return response()->json($order);
    }

    /*
     * [DELETE] jobs/{id}
     */
    public function delete($id) {
        $job = Job::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $job->checkAccess($self);

        $job->delete();
        return response()->json($job);
    }
}
