<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Job;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class JobController extends Controller
{

  public function get($id){
    try{
      return response()->json(Job::findOrFail($id));
    } catch (ModelNotFoundException $e){
      return $this->response->errorNotFound();
    }
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
      return $builder->get()->toArray();
    } else {
      return $this->response->errorBadRequest();
    }
  }
}
