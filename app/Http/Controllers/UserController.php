<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Job;
use App\Resume;
use App\JobApply;
use App\JobCompleted;
use App\JobEvaluate;
use JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['show']]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // refactor
    public function getJobApply(Request $request)
    {
        if (!$request->has('limit') || $request->input('limit') <= 0){
            return $this->response->errorBadRequest();
        }

        $user = JWTAuth::parseToken()->authenticate();
        $builder = $user->jobApplies();

        //筛选
        if ($request->has('status')){
            $status = $request->input('status');
            if (in_array($status, [0, 1, 2])){
                $builder->where('status', $status);
            }
        }

        //排列
        $builder->orderBy(
            'created_at',
            $request->input('direction', 'asc')
        );

        //分页
        if ($request->has('offset')){
            $builder->skip($request->input('offset'));
        }
        $builder->limit($request->input('limit'));

        $job_applies = $builder->get();

        //参数中添加job_name和resume_name
        foreach($job_applies as $job_apply){

            // 会直接将对应的对象赋值给$job_apply
            // $job_apply->job_name = $job_apply->job->name;
            // $job_apply->resume_name = $job_apply->resume->name;

            $job_apply->job_name = Job::find($job_apply->job_id)->name;
            $job_apply->resume_name = Resume::find($job_apply->resume_id)->name;
        }

        return $job_applies->toArray();
    }

    // refactor
    public function getJobCompleted(Request $request)
    {
        if (!$request->has('limit') || $request->input('limit') <= 0){
            return $this->response->errorBadRequest();
        }

        $user = JWTAuth::parseToken()->authenticate();
        $builder = $user->jobCompleteds();

        //筛选
        if ($request->has('is_evaluated')){
            $evaluate = $request->input('is_evaluated');
            if ($evaluate == 0){
                $builder->whereNull('job_evaluate_id');
            } else if ($evaluate == 1){
                $builder->whereNotNull('job_evaluate_id');
            }
        }

        //排列
        $builder->orderBy(
            'created_at',
            $request->input('direction', 'asc')
        );

        //分页
        if ($request->has('offset')){
            $builder->skip($request->input('offset'));
        }
        $builder->limit($request->input('limit'));

        $job_completeds = $builder->get();

        //参数中添加job_name和resume_name
        foreach($job_completeds as $job_completed){

            // 会直接将对应的对象赋值给$job_apply
            // $job_apply->job_name = $job_apply->job->name;
            // $job_apply->resume_name = $job_apply->resume->name;

            $job_completed->job_name = Job::find($job_completed->job_id)->name;
            $job_completed->resume_name = Resume::find($job_completed->resume_id)->name;

            if ($job_completed->job_evaluate_id == null){
                $job_completed->is_evaluated = 0;
            } else {
                $job_completed->is_evaluated = 1;
            }
        }

        return $job_completeds->toArray();
    }

    // refactor
    public function postJobApply(Request $request)
    {
        if (!$request->has('job_id') || !$request->has('resume_id')){
            return $this->response->errorBadRequest();
        }

        $user = JWTAuth::parseToken()->authenticate();

        try{
            $resume = Resume::findOrFail($request->query('resume_id'));
            $job = Job::findOrFail($request->query('job_id'));
        } catch (ModelNotFoundException $e){
            return $this->response->errorNotFound();
        }

        if ($resume->user_id != $user->id){
            return $this->response->errorBadRequest();
        }

        $array = $request->except('token');
        $array['user_id'] = $user->id;
        $array['status'] = 0;
        $job_apply = JobApply::create($array);
        $job_apply->save();

        return 'Success';
    }

    // refactor
    public function postJobEvaluate(Request $request)
    {
      if (!$request->has('job_completed_id') || !$request->has('score')){
        return $this->response->errorBadRequest();
      }
      $score = $request->input('score');
      if (!in_array($score, [0, 1, 2, 3, 4, 5])){
        return $this->response->error('score is not avalid.', 400);
      }

      $user = JWTAuth::parseToken()->authenticate();

      try{
        $jobCompleted = JobCompleted::findOrFail($request->query('job_completed_id'));
      } catch (ModelNotFoundException $e){
        return $this->response->errorNotFound();
      }

      if ($jobCompleted->jobEvaluated()){
        return $this->response->error('Job has been evaluated.', 400);
      }

      if ($jobCompleted->user_id != $user->id){
        return $this->response->error('Wrong job completed id.', 400);
      }

      $params = $request->only(['score', 'comment']);
      $params['job_id'] = $jobCompleted->job_id;
      $params['user_id'] = $user->id;
      $job_evaluate = JobEvaluate::create($params);

      if ($job_evaluate->save()){
        $jobCompleted->job_evaluate_id = $job_evaluate->id;
        if ($jobCompleted->save()){
          return 'success';
        }
      }

      return $this->response->errorInternal('evaluate save failed');
    }

    public function update(Request $request)
    {
      $this->validate($params,[
        'nickname' => 'max:32',
        'sex' => 'in:0,1',
        'birthday' => 'date_format:Y-m-d',
      ]);


      $user = JWTAuth::parseToken()->authenticate();

      $params = $request->only(['nickname', 'sex', 'sign', 'birthday',
        'location', 'phone']);
      
      // 修复会将值为null的项赋值进去的问题
      foreach ($params as $key => $value) {
        if ($value == null) {
          unset($params[$key]);
        }        
      }

      if (!$user->update($params)){
          throw new Exception('update fail.');
      }

      return response()->json($user);
    }
}
