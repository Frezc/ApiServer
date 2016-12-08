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
        $user = $this->getAuthenticatedUser();
        if ($user->isAdmin()) {
            $job = Job::withTrashed()->findOrFail($id);
        } else {
            $job = Job::findOrFail($id);
        }
        $job->visited++;
        $job->save();
        $job->bindTime();
        return response()->json($job);
    }

    /*
     * [POST] jobs
     */
    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required|string|between:1,250',
            'pay_way' => 'required|integer|in:1,2',
            'description' => 'string',
            'contact' => 'required|string|max:250',
            'contact_person' => 'required|string|max:16',
            'type' => 'required|exists:job_types,name',
            'city' => 'required|string',
            'address' => 'string',
            'by_company' => 'in:0,1'
        ]);

        $byCompany = $request->input('by_company');
        $self = JWTAuth::parseToken()->authenticate();

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

        $job = Job::create($params);

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
        $job = Job::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $job->checkAccess($self);
        $this->validate($request, [
            'time' => 'required|array',
            'time.*.number' => 'integer|min:0',
            'time.*.salary_type' => 'in:1,2',
            'time.*.salary' => 'integer|min:0',
            'time.*.apply_end_at' => 'date',
            'time.*.start_at' => 'required|date',
            'time.*.end_at' => 'required|date',
        ]);

        $time = $request->input('time');

        foreach ($time as $t) {
            $tf = array_only($t, ['number', 'salary_type', 'salary', 'apply_end_at', 'start_at', 'end_at']);
            if (!$tf['apply_end_at']) $tf['apply_end_at'] = $tf['start_at'];
            JobTime::create(array_merge($tf, ['job_id' => $job->id]));
        }

        $job->bindTime();
        return response()->json($job);
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
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'company_id' => 'integer',
            'user_id' => 'integer',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0',
            'exist' => 'integer|in:1,2',
            'type' => 'exists:job_types,name',
            'city' => 'string',
            'by_company' => 'in:0,1'
        ]);
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

        $builder = Job::search($q);

        $user_id && $builder->where('creator_id', $user_id);
        $company_id && $builder->where('company_id', $company_id);
        $type && $builder->where('type', $type);
        $city && $builder->where('city', $city);
        $by_company && $builder->whereNotNull('company_id');

        $total = $builder->count();

        //排列
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        // 判断是否为管理员，如果是则包括删除的数据
        if ($user = $this->getAuthenticatedUser()) {
            if ($user->isAdmin()) {
                if ($exist == 2) {
                    $builder->onlyTrashed();
                } else if (!$exist) {
                    $builder->withTrashed();
                }
            }
        }

        $jobs = $builder->get();
        
        foreach ($jobs as $job) {
            $job->number_evaluate = JobEvaluate::where('job_id', $job->id)->count();
            $job->average_score = JobEvaluate::where('job_id', $job->id)->avg('score');
        }
        return response()->json(['total' => $total, 'list' => $jobs]);
    }

    /*
     * [POST] jobs/{id}/apply
     */
    public function apply(Request $request, $id) {
        $job = Job::findOrFail($id);

        $this->validate($request, [
            'job_id' => 'required|integer',
            'resume_id' => 'required|integer'
        ]);

        $jobTime = JobTime::findOrFail($id);

        $resume = Resume::findOrFail($request->input('resume_id'));

        $self = JWTAuth::parseToken()->authenticate();

        $self->checkAccess($resume->user_id);
        $expectJob = $resume->convertToExpectJob();
        // create order
        $order = Order::create([
            'job_id' => $job->id,
            'job_name' => $job->name,
            'job_time_id' => $jobTime->id,
            'expect_job_id' => $expectJob->id,
            'applicant_id' => $resume->user_id,
            'applicant_name' => $self->nickname,
            'recruiter_type' => $job->company_id ? 1 : 0,
            'recruiter_id' => $job->company_id ? $job->company_id : $job->creator_id,
            'recruiter_name' => $job->company_id ? $job->company_name : $job->creator_name,
            'status' => 0,
            'applicant_check' => 1,
            'recruiter_check' => 0
        ]);

        $order->expect_job = $expectJob;
        $order->job_time = $jobTime;

        $to = $job->creator_id;
        if ($job->company_id) {
            $to = Company::getUserIds($job->company_id);
        }
        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER),
            $to,
            $self->nickname . ' 申请了岗位 ' . $job->name . '。'
        ));
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
