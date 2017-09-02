<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Uploadfile;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Namshi\JOSE\JWT;
use Response;
use Storage;

class ResumeController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('log', ['only' => ['delete', 'add', 'update']]);
    }


    public function get(Request $request) {
        $resumes_id  = $request->get('resume_id');
        $user = JWTAuth::parseToken()->authenticate();
        if ($resumes_id == 0){
                $resumes= \DB::table('resumes')->where('user_id',$user->id)->first();
                $resumes->email = $user->email;
        }else{
            $resumes= \DB::table('resumes')->where('id',$resumes_id)->first();
            $user = User::find($resumes->user_id);
            $resumes->email = $user->email;
        }
        return response()->json($resumes);
    }

    public function update(Request $request) {
        $self = JWTAuth::parseToken()->authenticate();
        $user = User::findOrFail($self->id);
        $self->checkAccess($user->id);
        $this->validate($request, [
            'title' => 'string',
            'school' => 'string',
            'flag' => 'string',
            'photo' => 'exists:uploadfiles,path',
            'birthday' => 'string',
            'contact' => 'string',
            'expect_location' => 'string',
            'sex' => 'in:0,1'
        ]);
        $resume = $user->resumes();
        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($self);
            $uploadFile->replace($resume->photo);
        }
        $result =$resume->update(array_only($request->all(), ['title', 'school', 'introduction',
            'birthday', 'contact', 'expect_location', 'sex','weight','height','flag']));
        $user->email=$request->get('email');
        $user->save();
        return response()->json($result);
    }

    public function getAllResume(Request $request){
        $resume=\DB::table('resumes')->join();
        $resume->orderBy(
            $request->input('orderBytime', 'created_at'),
            $request->input('order', 'desc')
        );
        $total =$resume->count();
        if ($request->has('offset')) {
            $resume->skip($request->input('offset'));
        }
        $resume->limit($request->input('limit'));
        return response()->json(['list'=> $resume->get(), 'total'=>  $total]);
    }

}
