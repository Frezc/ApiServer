<?php

namespace App\Http\Controllers;

use App\Jobs\PushNotifications;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobEvaluate;
use App\Models\JobTime;
use App\Models\Message;
use App\Models\Order;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

class JobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['apply']]);
        $this->middleware('log', ['only' => ['apply', 'update']]);
    }

    public function get($id) {
        $job = Job::findOrFail($id);
        $job->visited++;
        $job->save();
        $job->time = JobTime::where('job_id', $job->id)->get();
        return response()->json($job);
    }

    public function update(Request $request, $id) {
        $job = Job::findOrFail($id);
        $this->validate($request, [
            'name' => 'string|between:1,250',
            'pay_way' => 'integer|in:1,2',
            'salary_type' => 'integer|in:1,2',
            'description' => 'string',
            'active' => 'integer|in:0,1',
            'contact' => 'string|max:250'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $job->checkAccess($self);

        $job->update(array_only($request->all(), ['name', 'pay_way', 'salary_type', 'description', 'active', 'contact']));
        return response()->json($job);
    }

    public function getJobEvaluate(Request $request) {
        $this->validate($request, [
            'off' => 'integer|min:0',
            'siz' => 'required|min:0|integer',
            'job_id' => 'required'
        ]);

        // 第二个参数为默认值
        $offset = $request->input('off', 0);
        $job_id = $request->query('job_id');
        $limit = $request->input('siz');

        $builder = JobEvaluate::where('job_id', $job_id);

        $total = $builder->count();

        $evaluates = $builder->skip($offset)->limit($limit)->get();

        foreach ($evaluates as $evaluate) {
            $eva_user = User::find($evaluate->user_id);
            $evaluate->user_nickname = $eva_user->nickname;
            $evaluate->user_avatar = $eva_user->avatar;
            $evaluate->setHidden(['id', 'job_id']);
        }

        return response()->json(['total' => $total, 'list' => $evaluates]);
    }

    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'company_id' => 'integer',
            'user_id' => 'integer',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);
        $q = $request->input('kw');
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);
        $company_id = $request->input('company_id');
        $user_id = $request->input('user_id');

        $builder = Job::search($q);

        $user_id && $builder->where('creator_id', $user_id);
        $company_id && $builder->where('company_id', $company_id);

        $total = $builder->count();

        //排列
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        $jobs = $builder->get();
        
        foreach ($jobs as $job) {
            $job->number_evaluate = JobEvaluate::where('job_id', $job->id)->count();
            $job->average_score = JobEvaluate::where('job_id', $job->id)->avg('score');
        }
        return response()->json(['total' => $total, 'list' => $jobs]);
    }

    public function apply(Request $request,$id) {
        $job= Job::findOrFail($id);
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
}
