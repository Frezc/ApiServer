<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Job;
use App\User;
use App\JobEvaluate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class JobController extends Controller
{

  public function get($id){


    try{
      $job = Job::findOrFail($id);
      $job->number_evaluate = JobEvaluate::where('job_id', $job->id)->count();
      $job->average_score = JobEvaluate::where('job_id', $job->id)->avg('score');
      return response()->json($job);
    } catch (ModelNotFoundException $e){
      return $this->response->errorNotFound();
    }
  }

  public function getJobEvaluate(Request $request){
    if (!$request->has('limit') || $request->query('limit') <= 0 || !$request->has('job_id')){
      return $this->response->errorBadRequest();
    }

    $evaluates = JobEvaluate::where('job_id', $request->query('job_id'))->get();
    foreach($evaluates as $evaluate){
      $eva_user = User::find($evaluate->user_id);
      $evaluate->user_nickname = $eva_user->nickname;
      $evaluate->user_avatar = $eva_user->avatar;
      $evaluate->setHidden(['id', 'job_id']);
    }

    return $evaluates->toArray();
  }

  public function query(Request $request){
    if ($request->has('q') && $request->has('limit') && $request->query('limit') > 0){
      $q = $request->query('q');
      $q_array = explode(" ", trim($q));

      $builder = Job::query();
      foreach($q_array as $qi){
        $builder->where(function($query) use ($qi){
          $query->orWhere('name', 'like', '%'.$qi.'%')
                ->orWhere('description', 'like', '%'.$qi.'%')
                ->orWhere('company_name', 'like', '%'.$qi.'%');
        });
      }

      //筛选

      //排列
      $builder->orderBy(
        $request->input('orderby', 'id'),
        $request->input('direction', 'asc')
      );

      //分页
      if ($request->has('offset')){
        $builder->skip($request->input('offset'));
      }
      $builder->limit($request->input('limit'));

      // dd($builder->get());
      $jobs = $builder->get();
      foreach($jobs as $job){
        $job->number_evaluate = JobEvaluate::where('job_id', $job->id)->count();
        $job->average_score = JobEvaluate::where('job_id', $job->id)->avg('score');
      }
      return $jobs->toArray();
    } else {
      return $this->response->errorBadRequest();
    }
  }
}
