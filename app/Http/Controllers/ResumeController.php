<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Uploadfile;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;
use Response;
use Storage;

class ResumeController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
        $this->middleware('user.access');
        $this->middleware('log', ['only' => ['delete', 'add', 'update']]);
    }

    /*
     * [GET] users/{id}/resumes
     */
    public function get() {
        $user = JWTAuth::parseToken()->authenticate();

        $builder = $user->resumes();
        $total = $builder->count();
        $resumes = $builder->get();
        return response()->json(['total' => $total, 'list' => $resumes]);
    }

    /*
     * [DELETE] users/{id}/resumes/{resumeId}
     */
    public function delete($userId, $resumeId) {
        $user = User::findOrFail($userId);
        $self = JWTAuth::parseToken()->authenticate();

        $self->checkAccess($user->id);
        $resume = $user->resumes()->findOrFail($resumeId);

        // 如果有简历有照片
        if ($resume->photo) {
            $file = Uploadfile::where('path', $resume->photo)->first();
            // 将该文件的使用次数减一并保存
            $file->used--;
            $file->save();
        }
        // 删除简历
        $resume->delete();
        // 返回删除的简历
        return response()->json($resume);
    }

    /*
     * [POST] users/{id}/resumes
     */
    public function add(Request $request, $id) {
        $user = User::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);
        $this->validate($request, [
            'title' => 'required',      // 标题
            'name' => 'required',       // 名字
            'school' => 'string',       // 学校
            'introduction' => 'string', // 介绍
            'photo' => 'exists:uploadfiles,path', // 照片
            'birthday' => 'string',     // 生日
            'contact' => 'string',      // 联系方式
            'expect_location' => 'string', // 联系人
            'sex' => 'in:0,1'           // 性别
        ]);

        // 如果带有照片
        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($self);
        }

        $array = array_only($request->all(), ['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location', 'photo', 'sex']);
        $array['user_id'] = $user->id;
        $resume = Resume::create($array);

        return response()->json($resume);
    }

    /*
     * [POST] users/{id}/resumes/{resumeId}
     */
    public function update(Request $request, $userId, $resumeId) {
        $user = User::findOrFail($userId);
        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);
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

        $resume = $user->resumes()->findOrFail($resumeId);

        $photo = $request->input('photo');
        if ($photo) {
            $uploadFile = Uploadfile::where('path', $photo)->first();
            $uploadFile->makeSureAccess($self);
            $uploadFile->replace($resume->photo);
        }

        $resume->update(array_only($request->all(), ['title', 'name', 'school', 'introduction',
            'birthday', 'contact', 'expect_location', 'photo', 'sex']));

        return response()->json($resume);
    }
}
