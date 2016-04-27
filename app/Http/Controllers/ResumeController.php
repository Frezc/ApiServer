<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Resume;
use Storage;
use Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResumeController extends Controller
{
  public $default_photo = 'resume_photos\default';

  public function __construct()
  {
      $this->middleware('jwt.auth');
  }

  public function get(Request $request)
  {
      $this->validate($request, [
          'id' => 'integer'
      ]);

      $resume_id = $request->input('id');

      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id){
        // return $resumes->where('id', $resume_id)->get()->toArray();
        // 改为
        $rResumes = $resumes->where('id', $resume_id)->get();
        return response()->json($rResumes);
      } else {
        // return $resumes->get()->toArray();
        $rResumes = $resumes->get();
        return response()->json($rResumes);
      }
  }

  public function photo(Request $request)
  {
    $user = JWTAuth::parseToken()->authenticate();
    $resumes = $user->resumes();
    if ($resume_id = $request->query('id')){
      $resume = $resumes->where('id', $resume_id)->first();
      if ($resume != null){
        $file = storage_path('app/'.$resume->photo);
        return Response::download($file, 'photo');
      }
    }

    return $this->response->error('resume not found', 404);
  }

  public function delete(Request $request)
  {
      $user = JWTAuth::parseToken()->authenticate();
      $resumes = $user->resumes();
      if ($resume_id = $request->query('id')){
        $resume = $resumes->where('id', $resume_id)->first();
        if ($resume){
          if ($resume->photo != $this->default_photo){
            Storage::delete(
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

  public function add(Request $request)
  {
      $user = JWTAuth::parseToken()->authenticate();
      if (!($request->has('title')&&$request->has('name'))){
        return $this->response->errorBadRequest();
      }

      $array = $request->only(['title', 'name', 'school', 'introduction',
        'birthday', 'contact', 'expect_location']);
      $array['user_id'] = $user->id;
      $resume = Resume::create($array);

      if ($request->hasFile('photo') && $request->file('photo')->isValid()){
        Storage::put(
            'resume_photos/'.$resume->id,
            file_get_contents($request->file('photo')->getRealPath())
        );
        $resume->photo = 'resume_photos/'.$resume->id;
        $resume->save();
      }

      return response()->json($resume);
  }

  public function update(Request $request)
  {
      $user = JWTAuth::parseToken()->authenticate();
      try{
        $resume = $user->resumes()->findOrFail($request->query('id'));
      } catch (ModelNotFoundException $e){
        return $this->response->errorNotFound();
      }

      if ($request->hasFile('photo') && $request->file('photo')->isValid()){
        Storage::put(
            'resume_photos/'.$resume->id,
            file_get_contents($request->file('photo')->getRealPath())
        );
        $resume->photo = 'resume_photos/'.$resume->id;
        $resume->save();
      }

      $resume->update($request->only(['title', 'name', 'school', 'introduction',
        'birthday', 'contact', 'expect_location']));
      return response()->json($resume);
  }
}
