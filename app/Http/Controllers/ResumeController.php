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
        $this->middleware('user.access',['only' => ['delete', 'add', 'update']]);
        $this->middleware('log', ['only' => ['delete', 'add', 'update']]);
    }

    /*
     * [GET] users/{id}/resumes
     */
    public function get($id) {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user)  {
           $resumes= \DB::table('resumes')->where('id',$id)->first();
        }
        return response()->json($resumes);
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
        $resume->delete();
        return  'success';
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
            'birthday', 'contact', 'expect_location', 'sex']);
        $array['user_id'] = $user->id;
        Resume::create($array);
        return 'success';
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

    public function getOneAllResume(Request $request){
        $user = JWTAuth::parseToken()->authenticate();

        $resumes = \DB::table('resumes')->where('user_id',$user->id);
        $total = $resumes->count();
        return response()->json(['total' => $total, 'list' => $resumes->get()]);
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

    public function getOneResumeByUserId($id){
        if (JWTAuth::authenticate()){
        $resumes = new Resume;
        $resume = $resumes->where('user_id',$id)->first();
       return response()->json($resume);
        }
   }

}
