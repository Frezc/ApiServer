<?php

namespace App\Http\Controllers;

use App\Jobs\PushNotifications;
use App\Models\Job;
use App\Models\JobEvaluate;
use App\Models\JobTime;
use App\Models\Message;
use App\Models\Order;
use App\Models\Resume;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use JWTAuth;

class JobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['apply']]);
        $this->middleware('log', ['only' => ['apply']]);
    }

    public function get($id) {
        $job = Job::findOrFail($id);
        $job->visited++;
        $job->save();
        $jobEva = JobEvaluate::where('job_id', $job->id);
        $job->number_evaluate = $jobEva->count();
        $job->average_score = $jobEva->avg('score');
        $job->time = JobTime::where('job_id', $job->id)->get();
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
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $q = $request->input('kw');
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);

        $builder = Job::query();

        if ($q) {
            $q_array = explode(" ", trim($q));

            foreach ($q_array as $qi) {
                $builder->orWhere('name', 'like', '%' . $qi . '%')
                        ->orWhere('description', 'like', '%' . $qi . '%')
                        ->orWhere('company_name', 'like', '%' . $qi . '%');
            }
        }

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

    public function apply(Request $request, $id) {
        $job = Job::findOrFail($id);

        $this->validate($request, [
            'job_time_id' => 'required|integer',
            'resume_id' => 'required|integer'
        ]);

        $jobTime = $job->jobTime()->findOrFail($request->input('job_time_id'));

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
            $to = UserCompany::getUserIds($job->company_id);
        }
        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER),
            $to,
            $self->nickname . ' 申请了岗位 ' . $job->name . '。'
        ));

        return response()->json($order);
    }
}
