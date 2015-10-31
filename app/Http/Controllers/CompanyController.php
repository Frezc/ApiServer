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
    if ($request->has('q')){
      $q = $request->query('q');
      $q_array = explode(" ", trim($q));
      return $q_array;

      // $results = Company::orwhere('name', 'like')
    } else {
      return $this->response->errorBadRequest();
    }
  }
}
