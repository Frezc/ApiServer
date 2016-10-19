<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Resume;
use Illuminate\Http\Request;
use JWTAuth;
use Response;
use Storage;
use App\Uploadfile;

class ResumeController extends Controller {
    public $default_photo = 'resume_photos\default';

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function get() {
        $user = JWTAuth::parseToken()->authenticate();
        $resumes = $user->resumes()->get();
        foreach ($resumes as $resume) {
            $resume->photo = asset(Storage::url($resume->photo));
        }
        return response()->json($resumes);
    }

    public function delete(Request $request) {
        $this->validate($request, [
            'id' => 'required|integer|exists:resumes,id'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $resume_id = $request->input('id');

        $resume = Resume::findOrFail($resume_id);
        if ($resume->user_id == $user->id) {
            if ($resume->photo) {
                $file = Uploadfile::where('path', $resume->photo)->first();
                $file->used--;
                $file->save();
            }
            $resume->delete();
            return 'deleted';
        } else {
            throw new MsgException('你没有权限删除该简历。', 401);
        }
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

    public function update(Request $request) {
        $this->validate($request, [
            'title' => 'string',
            'name' => 'string',
            'school' => 'string',
            'introduction' => 'string',
            'photo' => 'exists:uploadfiles,path',
            'birthday' => 'string',
            'contact' => 'string',
            'expect_location' => 'string',
            'id' => 'required|integer',
            'sex' => 'in:0,1'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        $resume = $user->resumes()->findOrFail($request->input('id'));

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
