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
    if ($request->has('q')){
      $q = $request->query('q');
      $q_array = explode(" ", trim($q));
      return $q_array;

      // $results = Job::orwhere('name', 'like')
    } else {
      return $this->response->errorBadRequest();
    }
  }
}
