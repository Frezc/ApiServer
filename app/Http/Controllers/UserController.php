<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Job;
use App\Resume;
use App\JobApply;
use JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['show']]);
    }

    public function show($id)
    {
        try{
          $user = User::findOrFail($id);
        } catch (ModelNotFoundException $e){
          return $this->response->errorNotFound();
        }
        return response()->json($user);
    }
  
    public function getJobApply(Request $request){
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
    
    public function postJobApply(Request $request){
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
}
