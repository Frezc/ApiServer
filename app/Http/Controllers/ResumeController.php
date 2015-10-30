<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Resume;
use Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResumeController extends Controller
{
  public $default_photo = 'http://static.frezc.com/static/resume_photos/default';

  public function __construct()
  {
    $this->middleware('jwt.auth');
  }

  public function get(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id = $request->query('id')){
        return $resumes->where('id', $resume_id)->get()->toArray();
      } else {
        return $resumes->get()->toArray();
      }
  }

  public function delete(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id = $request->query('id')){
        $resume = $resumes->where('id', $resume_id)->first();
        if ($resume){
          if ($resume->photo != $this->default_photo){
            Storage::disk('ftp')->delete(
                'resume_photos/'.$resume->id
            );
          }
          $resume->delete();
          return 'deleted';
        } else {
          return $this->response->error('resume not found', 404);
        }
      } else {
        return $this->response->errorBadRequest();
      }
  }

  public function add(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      if (!($request->has('title')&&$request->has('name'))){
        return $this->response->errorBadRequest();
      }

      $array = $request->except('token');
      $array['user_id'] = $user->id;
      $resume = Resume::create($array);

      if ($request->hasFile('photo') && $request->file('photo')->isValid()){
        Storage::disk('ftp')->put(
            'resume_photos/'.$resume->id,
            file_get_contents($request->file('photo')->getRealPath())
        );
        $resume->photo = 'http://static.frezc.com/static/resume_photos/'.$resume->id;
        $resume->save();
      }

      return response()->json($resume);
  }

  public function update(Request $request){
      $user = JWTAuth::parseToken()->authenticate();
      try{
        $resume = $user->resumes()->findOrFail($request->query('id'));
      } catch (ModelNotFoundException $e){
        return $this->response->errorNotFound();
      }

      if ($request->hasFile('photo') && $request->file('photo')->isValid()){
        Storage::disk('ftp')->put(
            'resume_photos/'.$resume->id,
            file_get_contents($request->file('photo')->getRealPath())
        );
        $resume->photo = 'http://static.frezc.com/static/resume_photos/'.$resume->id;
        $resume->save();
      }

      $resume->update($request->except('token'));
      return response()->json($resume);
  }
}
