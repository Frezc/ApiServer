<?php

namespace App\Http\Controllers;

use App\Resume;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $resumes = $user->resumes();
        return response()->json($resumes->get());
    }

    // refactor
    public function photo(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $resumes = $user->resumes();
        if ($resume_id = $request->query('id')) {
            $resume = $resumes->where('id', $resume_id)->first();
            if ($resume != null) {
                $file = storage_path('app/' . $resume->photo);
                return Response::download($file, 'photo');
            }
        }

        return $this->response->error('resume not found', 404);
    }

    // refactor
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
            // throw 401
        }
    }

    // refactor
    public function add(Request $request) {
        $this->validate($request, [
            'title' => 'required',
            'name' => 'required',
            'school' => 'string',
            'introduction' => 'string',
            'photo' => 'exists:uploadfiles,path',
            'birthday' => 'string',
            'contact' => 'string',
            'expect_location' => 'string'
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($user);
        }

        $array = $request->only(['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location']);
        $array['user_id'] = $user->id;
        $resume = Resume::create($array);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            Storage::put(
                'resume_photos/' . $resume->id,
                file_get_contents($request->file('photo')->getRealPath())
            );
            $resume->photo = 'resume_photos/' . $resume->id;
            $resume->save();
        }

        return response()->json($resume);
    }

    public function update(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        try {
            $resume = $user->resumes()->findOrFail($request->query('id'));
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound();
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            Storage::put(
                'resume_photos/' . $resume->id,
                file_get_contents($request->file('photo')->getRealPath())
            );
            $resume->photo = 'resume_photos/' . $resume->id;
            $resume->save();
        }

        $resume->update($request->only(['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location']));
        return response()->json($resume);
    }
}
