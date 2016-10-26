<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\ExpectTime;
use App\Job;
use App\Order;
use App\Resume;
use Illuminate\Http\Request;
use App\ExpectJob;
use JWTAuth;

class ExpectJobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function create(Request $request) {
        $this->validate($request, [
            'resume_id' => 'required|integer',
            'expect_times' => 'array',
            'expect_times.*.year' => 'required|integer|between:2016,2099',
            'expect_times.*.month' => 'required|integer|between:0,12',
            'expect_times.*.dayS' => 'required|integer|min:0',
            'expect_times.*.dayE' => 'integer|min:0',
            'expect_times.*.hourS' => 'integer|between:0,23',
            'expect_times.*.hourE' => 'integer|min:0,23'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $resume_id = $request->input('resume_id');
        $resume = Resume::findOrFail($resume_id);
        $expect_times = $request->input('expect_times', []);

        $self->checkAccess($resume->user_id);

        $expectJob = $resume->convertToExpectJob(1);

        foreach ($expect_times as $expect_time) {
            ExpectTime::create(array_merge($expect_time, [ 'expect_job_id' => $expectJob->id ]));
        }

        $expectJob->expect_time = $expect_times;

        return response()->json($expectJob);
    }

    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:created_at',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $builder = ExpectJob::search($request->input('kw'));
        $count = $builder->count();
        $builder->orderBy($request->input('orderby', 'created_at'), $request->input('dir', 'desc'));
        $builder->skip($request->input('off', 0));
        $builder->limit($request->input('siz', 20));
        $expectJobs = $builder->get();

        $expectJobs->each(function ($expectJob) {
            $expectJob->bindUserName();
            $expectJob->bindExpectTime();
        });

        return response()->json(['total' => $count, 'list' => $expectJobs]);
    }

    public function apply(Request $request, $id) {
        $expectJob = ExpectJob::findOrFail($id);

        $this->validate($request, [
            'job_id' => 'integer'
        ]);

        $job = Job::findOrFail($request->input('job_id'));
        $self = JWTAuth::parseToken()->authenticate();
        $job->checkAccess($self);

        $order = Order::create([
            'job_id' => $job->id,
            'job_name' => $job->name,
            'job_time_id' => null,
            'expect_job_id' => $expectJob->id,
            'applicant_id' => $expectJob->user_id,
            'applicant_name' => $expectJob->user_name,
            'recruiter_type' => $job->company_id ? 1 : 0,
            'recruiter_id' => $job->company_id ? $job->company_id : $job->creator_id,
            'recruiter_name' => $job->company_id ? $job->company_name : $job->creator_name,
            'status' => 0,
            'applicant_check' => 0,
            'recruiter_check' => 1
        ]);

        $order->expect_job = $expectJob;

        return response()->json($order);
    }
}
