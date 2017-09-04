<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Models\Job;
use App\Models\JobTime;
use App\Models\Order;
use App\Models\Resume;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\JobCollection;

class JobController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
        $this->middleware('jwt.auth', ['only' => ['apply', 'update', 'delete', 'create', 'addTime', 'collect', 'cancelCollect', 'getCollectList']]);
        $this->middleware('log', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
        $this->middleware('role:user', ['only' => ['apply', 'update', 'delete', 'create', 'addTime']]);
    }

    /*
     * [GET] jobs/{id}
     */
    public function get($id)
    {
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
        $job->iscollect = 0;
        if ($user)
            $job->iscollect = empty(getTableClumnValue('job_collection', ['user_id' => $user->id, 'job_id' => $id], 'id')) ? 0 : 1;
        // 绑定岗位的工作时间段
        $job->bindTime();
        // 返回json数据
        return response()->json($job);
    }

    public function mainPage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user) {
            $resume = Resume::query()->where('user_id', $user->id)->get(1);
            $q = $resume->flag;
        }
        $this->validate($request, [

            'limit' => 'integer|min:0', // 最多数量
            'orderby' => 'in:id,created_at,average_score', // 排序方式
            'company_id' => 'integer', // 发布者为企业时的id筛选
            'user_id' => 'integer',    // 发布者的id筛选
            'dir' => 'in:asc,desc',    // 排序方向
            'offset' => 'integer|min:0',  // 跳过多少数据
            'exist' => 'integer|in:1,2', // （管理员权限）是否显示删除项
            'job_type' => 'exists:job_types,name', // 岗位类型
            'city' => 'string',        // 所在城市
        ]);

        // 获取传入参数，或者设为默认值

        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'desc');
        $company_id = $request->input('company_id');
        $user_id = $request->input('user_id');
        $exist = $request->input('exist');
        $type = $request->input('job_type');
        $city = $request->input('city');
        // 关键字搜索
        $builder = Job::search($q);
        // 筛选
        $user_id && $builder->where('creator_id', $user_id);
        $company_id && $builder->where('company_id', $company_id);
        $type && $builder->where('job_type', $type);
        $city && $builder->where('city', $city);

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
        if ($request->has('offset'))
            $builder->skip($request->input('offset'));
        if ($request->has('limit'))
            $builder->limit($request->input('limit'));

        $builder->orderBy($orderby, $direction);

        $jobs = $builder->get();
        // 返回json数据
        return response()->json(['total' => $total, 'list' => $jobs]);
    }

    /*
     * [POST] jobs
     */
    public function create(Request $request)
    {

        $this->validate($request, [
            'name' => 'required|string|between:1,250', // 名称
            'salary' => 'required',                     //工资
            'salary_type' => 'required|in:1,2,3,4',               //工资单位
            'pay_way' => 'required|integer|in:1,2',    // 支付方式
            'description' => 'string',                 // 描述
            'contact' => 'required|max:16',    // 联系方式
            'contact_person' => 'required|string|max:16', // 联系人
            'type' => 'required|exists:job_types,name', // 岗位类型
            'city' => 'string',              // 城市
            'address' => 'required|string',                    // 地址
            'start_at' => 'required',
            'end_at' => 'required',
            'required_number' => 'required',        // 总人数
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        if ($self->role_id == 2) {
            // 筛选传入的参数
            $params = array_only($request->all(),
                ['name', 'salary', 'pay_way', 'salary_type', 'description', 'contact', 'contact_person', 'type', 'city', 'address', 'required_number']);
            $params['company_id'] = $self->company_id ? $self->company_id : 0;
            $params['company_name'] = $self->company_name ? $self->company_name : 0;
            $params['creator_id'] = $self->id;
            $params['creator_name'] = $self->nickname;
            $time = array_only($request->all(),
                ['start_at', 'end_at', 'apply_end_at']);
            $job = Job::create($params);
            $time['job_id'] = $job->id;
            $time['start_at'] = strtotime($time['start_at']);
            $time['end_at'] = strtotime($time['end_at']);
            $time['apply_end_at'] = strtotime($time['apply_end_at']);
            //工作时间插入时间表中
            JobTime::create($time);
            // 表中插入岗位
            // 返回创建成功的json数据
            return 'success';
        } else {
            return sucesss('你没有权限');
        }

    }

    /*
     * [POST] jobs/{id}
     */
    public function update(Request $request, $id)
    {
        $self = JWTAuth::parseToken()->authenticate();
        if ($self->isAdmin()) {
            $job = Job::withTrashed()->findOrFail($id);
        } else {
            $job = Job::findOrFail($id);
            $jobTime = JobTime::query()->where('job_id', $id)->first();
        }
        $this->validate($request, [
            'name' => 'string|between:1,250',
            'pay_way' => 'string',
            'salary_type' => 'string',
            'description' => 'string',
            'active' => 'integer|in:0,1',
            'contact' => 'string|max:250',
            'contact_person' => 'string|max:16',
            'type' => 'exists:job_types,name',
            'city' => 'string',
            'address' => 'string',
            'salary_pay_way' => 'string'
        ]);

        $job->checkAccess($self);
        $job->update(array_only($request->all(),
            ['name', 'salary', 'pay_way', 'salary_type', 'description', 'contact', 'contact_person', 'type', 'city', 'address', 'required_number']));
        $time = array_only($request->all(),
            ['start_at', 'end_at', 'apply_end_at']);
        $time['start_at'] = strtotime($time['start_at']);
        $time['end_at'] = strtotime($time['end_at']);
        $time['apply_end_at'] = strtotime($time['apply_end_at']);
        $jobTime->update(array_only($request->all(), ['start_at', 'end_at', 'apply_end_at']));
        return 'success';
    }

    /*
     *
     */
    public function closeJob(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
//        验证是不是自己发布的岗位
        $job = Job::findOrFail($id);
        if ($job->active == 0) {
            Throw new MsgException('you had close the job');
        }
        $job->checkAccess($user);
        $job->active = 0;
        $job->save();
        return sucesss('下架成功');
    }

    /*
     * [DELETE] jobs/{id}/time
     */
    public function closeTime(Request $request, $id)
    {
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
     * [GET] jobs
     */
    public function query(Request $request)
    {
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


    /**
     * @param Request $request
     * @param $id 工作的id
     * @return \Illuminate\Http\JsonResponse
     */
    public function collect(Request $request, $id)
    {
        $self = JWTAuth::parseToken()->authenticate();
        $user_id = $self->id;
        if (!$self->isUser()) {
            return response()->json('你不是用户');
        }
        if (MyService::checkIsCollect($user_id, $id)) {
            return sucesss('已经收藏');
        }
        $coll = new JobCollection;
        $coll->user_id = $user_id;
        $coll->job_id = $id;
        $resutl = $coll->save();
        if ($resutl == true) {
            return sucesss('收藏成功');
        } else {
            return sucesss('收藏失败');
        }
    }

    public function cancelCollect(Request $request)
    {
        $service = new MyService();
        $where = array_only($request->all(), array('user_id', 'job_id'));
        if (!MyService::checkIsCollect($where['user_id'], $where['job_id'])) {
            return sucesss('还没有收藏');
        }
        $result = $service->delete('job_collection', $where);
        if ($result === true) {
            return sucesss('取消成功');
        } else
            return sucesss('取消失败');
    }

    public function getCollectList(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $where['job_collection.user_id'] = $user->id;
        $select = ['job_collection.id', 'user_id', 'job_id', 'name', 'salary', 'salary_type', 'company_name', 'creator_id'];
        $servic = new MyService();
        $result = $servic->getList('job_collection', 'tjz_jobs', 'job_collection.job_id', '=', 'tjz_jobs.id', 'left', $where, $select);
        $result1 = $servic->sortPage($result, 0, 50, 'job_collection.created_at', 'asc');
        $total = $result1->count();
        $data = $result->get();
        foreach ($data as $key => $value){
            $data[$key]->start_at = getTableClumnValue('job_times',['job_id'=>$value->job_id],'start_at');
            $data[$key]->end_at =  getTableClumnValue('job_times',['job_id'=>$value->job_id],'end_at');
        }
        return response()->json(['list'=>$data,'total'=>$total]);
    }

    /*
     * [POST] jobs/{id}/apply
     * 需要修改验证有没有重复申请
     */

    public function apply(Request $request)
    {
        $status = 0;
        $data = array_only($request->all(), ['job_id', 'user_id', 'status']);

        $job = Job::findOrFail($data['job_id']);
        // 获取工作时间
        $jobTime = JobTime::where('job_id', $job->id)->first();
        $user = JWTAuth::parseToken()->authenticate();
        // 获取简历
        if (empty($data['user_id']) && $user->role_id == 1)//用户申请
            $self = $user;
        else {
            if ($user->role_id == 2 && $user->id == $job->creator_id) {
                $self = User::find($data['user_id']);
                $status = $data['status'];
            } else
                return sucesss('没有权限操作');
        }


        //验证有没有重复申请
        if (getLogNunber('orders', ['job_id' => $data['job_id'], 'applicant_id' => $self->id]) != 0) {
            return sucesss('你已经申请过这个工作');
        }
        // 创建订单
        $order = Order::create([
            'job_id' => $job->id,      // 岗位id
            'job_name' => $job->name,  // 岗位名称
            'salary' => $job->salary,  // 岗位薪资
            'salary_type' => $job->salary_type,  // 岗位薪资
            'job_time_id' => $jobTime->id, // 工作时间id
            'pay_way' => $job->pay_way,    // 支付方式 1 2
            'applicant_id' => $self->id, // 申请者Id
            'applicant_name' => $self->nickname, // 申请者名称
            'recruiter_type' => $job->company_id ? 1 : 0, // 招聘者类型
            'recruiter_id' => $job->creator_id,  // 招聘者id
            'recruiter_name' => $job->creator_name, // 招聘者名称
            'status' => $status,                    // 状态
            'applicant_check' => 0,           // 申请者是否确认
            'recruiter_check' => 0            // 招聘方是否确认
        ]);

        return 'success';
    }


    public function getJobList(Request $request)
    {

        $self = JWTAuth::parseToken()->authenticate();
        $where = array_except($request->all(), ['token']);
        if ($self->role_id == 2)
            $where['creator_id'] = $self->id;
        $data = getlist('tjz_jobs', $where);
        $data->orderBy('created_at');
        return successList($data);
    }

    public function delete($id)
    {
        $job = Job::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $job->checkAccess($self);

        $job->delete();
        return response()->json($job);
    }
}
