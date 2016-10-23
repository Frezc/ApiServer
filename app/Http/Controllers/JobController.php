<?php

namespace App\Http\Controllers;

use App\Job;
use App\JobEvaluate;
use App\JobTime;
use App\User;
use Illuminate\Http\Request;

class JobController extends Controller {

    public function get($id) {
        $job = Job::findOrFail($id);
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

        $evaluates = JobEvaluate::where('job_id', $job_id)
            ->skip($offset)->limit($limit)->get();

        foreach ($evaluates as $evaluate) {
            $eva_user = User::find($evaluate->user_id);
            $evaluate->user_nickname = $eva_user->nickname;
            $evaluate->user_avatar = $eva_user->avatar;
            $evaluate->setHidden(['id', 'job_id']);
        }

        return response()->json($evaluates);
    }

    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'required',
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

        $q_array = explode(" ", trim($q));

        $builder = Job::query();
        foreach ($q_array as $qi) {
            $builder->where(function ($query) use ($qi) {
                $query->orWhere('name', 'like', '%' . $qi . '%')
                    ->orWhere('description', 'like', '%' . $qi . '%')
                    ->orWhere('company_name', 'like', '%' . $qi . '%');
            });
        }

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
        return response()->json($jobs);
    }
}
