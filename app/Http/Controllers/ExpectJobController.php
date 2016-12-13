<?php

namespace App\Http\Controllers;

use App\Jobs\PushNotifications;
use App\Models\ExpectJob;
use App\Models\ExpectTime;
use App\Models\Job;
use App\Models\Message;
use App\Models\Order;
use App\Models\Resume;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;

class ExpectJobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('log', ['only' => ['create', 'apply', 'update', 'delete']]);
        $this->middleware('role:user', ['only' => ['create', 'apply', 'update', 'delete']]);
    }

    /*
     * [POST] expect_jobs
     */
    public function create(Request $request) {
        $this->validate($request, [
            'resume_id' => 'required|integer',  // 简历id
            'expect_times' => 'array',          // 期望时间
            'expect_times.*.start_at' => 'required|date', // 开始时间
            'expect_times.*.end_at' => 'required|date'    // 结束时间
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $resume_id = $request->input('resume_id');
        // 找到对应简历
        $resume = Resume::findOrFail($resume_id);
        $expect_times = $request->input('expect_times', []);
        // 验证权限
        $self->checkAccess($resume->user_id);
        // 将简历转为求职信息
        $expectJob = $resume->convertToExpectJob(1);
        // 将期望时间保存
        foreach ($expect_times as $expect_time) {
            ExpectTime::create(array_merge($expect_time, [ 'expect_job_id' => $expectJob->id ]));
        }

        // 返回生成的求职信息
        $expectJob->expect_time = $expect_times;
        return response()->json($expectJob);
    }

    /*
     * [GET] expect_jobs
     */
    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:created_at',
            'user_id' => 'integer',
            'time_s' => 'string|date',
            'time_e' => 'string|date',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0',
            'exist' => 'integer|in:1,2'
        ]);

        $user_id = $request->input('user_id');
        $exist = $request->input('exist');
        $time_s = $request->input('time_s');
        $time_e = $request->input('time_e');

        $builder = ExpectJob::search($request->input('kw'));

        if ($user_id) $builder->where('user_id', $user_id);
        $targetTime = ExpectTime::where('end_at', '>', Carbon::now()->toDateString());
        if ($time_s) {
            $targetTime->where(function ($query) use ($time_s) {
                $query->where('start_at', '<=', $time_s)
                    ->where('end_at', '>=', $time_s);
            });
            if ($time_e) {
                $targetTime->orWhere(function ($query) use ($time_e) {
                    $query->where('start_at', '<=', $time_e)
                        ->where('end_at', '>=', $time_e);
                })->orWhere(function ($query) use ($time_s, $time_e) {
                    $query->where('start_at', '>', $time_s)
                        ->where('end_at', '<', $time_e);
                });
            }
        }
        $target = $targetTime ->select('expect_job_id')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return $item->expect_job_id;
            });
        $builder->whereIn('id', $target);

        $count = $builder->count();
        $builder->orderBy($request->input('orderby', 'created_at'), $request->input('dir', 'desc'))
                ->skip($request->input('off', 0))
                ->limit($request->input('siz', 20));

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

        $expectJobs = $builder->get();

        $expectJobs->each(function ($expectJob) {
            $expectJob->bindExpectTime();
        });

        return response()->json(['total' => $count, 'list' => $expectJobs]);
    }

    /*
     * [POST] expect_jobs/{id}/apply
     */
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
            'pay_way' => $job->pay_way,
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

        $this->dispatch(new PushNotifications(
            Message::getSender(Message::$WORK_HELPER),
            $expectJob->user_id,
            $self->nickname . ' 为您发送了岗位邀请。'
        ));

        return response()->json($order);
    }

    /*
     * [GET] expect_jobs/{id}
     */
    public function get($id) {
        $user = $this->getAuthenticatedUser();
        if ($user && $user->isAdmin()) {
            $expectJob = ExpectJob::withTrashed()->findOrFail($id);
        } else {
            $expectJob = ExpectJob::findOrFail($id);
        }
        $expectJob->bindExpectTime();
        return response()->json($expectJob);
    }

    /*
     * [POST] expect_jobs/{id}
     */
    public function update(Request $request, $id) {
        $self = JWTAuth::parseToken()->authenticate();
        if ($self->isAdmin()) {
            $expectJob = ExpectJob::withTrashed()->findOrFail($id);
        } else {
            $expectJob = ExpectJob::findOrFail($id);
        }
        $this->validate($request, [
            'name' => 'string|between:1, 16',
            'photo' => 'exists:uploadfiles,path',
            'school' => 'string|max:250',
            'introduction' => 'string',
            'birthday' => 'date',
            'contact' => 'string',
            'sex' => 'in:0,1',
            'expect_location' => 'string'
        ]);

        $expectJob->makeSureAccess($self);

        $expectJob->update(array_only($request->all(),
            ['name', 'photo', 'school', 'introduction', 'birthday', 'contact', 'sex', 'expect_location']));
        return response()->json($expectJob);
    }

    /*
     * [POST] expect_jobs/{id}
     */
    public function delete($id) {
        $expectJob = ExpectJob::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $expectJob->makeSureAccess($self);
        $expectJob->delete();
        return response()->json($expectJob);
    }
}
