<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Resume;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Response;
use Storage;
use App\Uploadfile;

class ResumeController extends Controller {
    public $default_photo = 'resume_photos\default';

    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('user.access');
    }

    public function get() {
        $user = JWTAuth::parseToken()->authenticate();

        $builder = $user->resumes();
        $total = $builder->count();
        $resumes = $builder->get();
        foreach ($resumes as $resume) {
            $resume->photo = asset(Storage::url($resume->photo));
        }
        return response()->json(['total' => $total, 'list' => $resumes]);
    }

    public function delete($userId, $resumeId) {
        $user = JWTAuth::parseToken()->authenticate();

        $resume = $user->resumes()->findOrFail($resumeId);

        if ($resume->photo) {
            $file = Uploadfile::where('path', $resume->photo)->first();
            $file->used--;
            $file->save();
        }
        $resume->delete();
        return response()->json($resume);
    }

    public function add(Request $request) {
        $this->validate($request, [
            'title' => 'required',
            'name' => 'required',
            'school' => 'string',
            'introduction' => 'string',
            'photo' => 'exists:uploadfiles,path',
            'birthday' => 'string',
            'contact' => 'string',
            'expect_location' => 'string',
            'sex' => 'in:0,1'
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($user);
        }

        $array = array_only($request->all(), ['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location', 'photo', 'sex']);
        $array['user_id'] = $user->id;
        $resume = Resume::create($array);

        return response()->json($resume);
    }

    public function update(Request $request, $userId, $resumeId) {
        $this->validate($request, [
            'title' => 'string',
            'name' => 'string',
            'school' => 'string',
            'introduction' => 'string',
            'photo' => 'exists:uploadfiles,path',
            'birthday' => 'string',
            'contact' => 'string',
            'expect_location' => 'string',
            'sex' => 'in:0,1'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $resume = $user->resumes()->findOrFail($resumeId);

        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($user);
        }

        $resume->update(array_only($request->all(), ['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location', 'photo', 'sex']));

        return response()->json($resume);
    }
}
