<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompanyController extends Controller
{
  public function get($id){
    try{
      return response()->json(Company::findOrFail($id));
    } catch (ModelNotFoundException $e){
      return $this->response->errorNotFound();
    }
  }

  public function query(Request $request){
    if ($request->has('q') && $request->has('limit')){
      $q = $request->query('q');
      $q_array = explode(" ", trim($q));

      $builder = Company::query();
      foreach($q_array as $qi){
        $builder->where(function($query) use ($qi){
          $query->orWhere('name', 'like', '%'.$qi.'%')
                ->orWhere('description', 'like', '%'.$qi.'%')
                ->orWhere('contact_person', 'like', '%'.$qi.'%');
        });
      }

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
