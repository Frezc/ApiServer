<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Models\Job;
use App\Models\JobApply;
use App\Models\JobCompleted;
use App\Models\JobEvaluate;
use App\Models\Log;
use App\Models\Order;
use App\Models\RealNameVerification;
use App\Models\Resume;
use App\Models\Uploadfile;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class UserController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth', ['except' => ['show', 'mainPage','getAllResume']]);
        $this->middleware('log', ['only' => ['update', 'createRealNameApplies', 'deleteRealNameApply', 'updateEvaluate']]);
    }

    /*
     * [GET] self
     */
    public function self() {
        $self = JWTAuth::parseToken()->authenticate();
        $self->bindRoleName();
        $self->setHidden(['password']);
        return response()->json($self);
    }

    /*
     * [GET] users/{id}
     */
    public function show($id) {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
  public  function  user_get_order(Request $request){

         $user=JWTAuth::parseToken()->authenticate();
         $builder=Order::orderBy('created_at','desc')->get()->where('applicant_id',$user->id);
         return response()->json($builder);
}
    public  function  company_get_order(Request $request){
        $user=JWTAuth::parseToken()->authenticate();
        $builder=Order::orderBy('created_at','desc')->get()->where('recruiter_id',$user->id);
        return response()->json($builder);
    }

    public function mainPage(Request $request)
    {
        $builder = Job::query();
        $builder->orderBy(
            $request->input('orderBytime', 'created_at'),

            $request->input('order', 'desc')

        );
        $total =$builder->count();
        if ($request->has('offset')) {
            $builder->skip($request->input('offset'));
        }
        $builder->limit($request->input('limit'));
        return response()->json(['list'=> $builder->get(), 'total'=>  $total]);


}



    // refactor
    public function getJobApply(Request $request) {
        $this->validate($request, [
            'kw' => 'required',
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);

        $user = JWTAuth::parseToken()->authenticate();
        $builder = $user->jobApplies();


        if ($request->has('status')) {
            $status = $request->input('status');
            if (in_array($status, [0, 1, 2])) {
                $builder->where('status', $status);
            }
        }

        $total = $builder->count();

        //排列
        $builder->orderBy('created_at', $direction);

        //分页
        $builder->skip($offset);

        $builder->limit($limit);

        $job_applies = $builder->get();


        foreach ($job_applies as $job_apply) {

            $job_apply->job_name = Job::find($job_apply->job_id)->name;
            $job_apply->resume_name = Resume::find($job_apply->resume_id)->name;
        }

        return response()->json(['total' => $total, 'list' => $job_applies]);
    }


    // refactor
    public function getJobCompleted(Request $request) {
        $this->validate($request, [
            'kw' => 'required',
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);

        $user = JWTAuth::parseToken()->authenticate();
        $builder = $user->jobCompleteds();

        //筛选
        if ($request->has('is_evaluated')) {
            $evaluate = $request->input('is_evaluated');
            if ($evaluate == 0) {
                $builder->whereNull('job_evaluate_id');
            } else if ($evaluate == 1) {
                $builder->whereNotNull('job_evaluate_id');
            }
        }

        $total = $builder->count();

        //排列
        $builder->orderBy('created_at', $direction);

        //分页
        $builder->skip($offset);

        $builder->limit($limit);

        $job_completeds = $builder->get();

        //参数中添加job_name和resume_name
        foreach ($job_completeds as $job_completed) {

            // 会直接将对应的对象赋值给$job_apply
            // $job_apply->job_name = $job_apply->job->name;
            // $job_apply->resume_name = $job_apply->resume->name;

            $job_completed->job_name = Job::find($job_completed->job_id)->name;
            $job_completed->resume_name = Resume::find($job_completed->resume_id)->name;

            if ($job_completed->job_evaluate_id == null) {
                $job_completed->is_evaluated = 0;
            } else {
                $job_completed->is_evaluated = 1;
            }
        }

        return response()->json(['total' => $total, 'list' => $job_completeds]);
    }

    // refactor
    public function postJobApply(Request $request) {
        $this->validate($request, [
            'job_id' => 'required|integer',
            'resume_id' => 'required'
        ]);

        if (!$request->has('job_id') || !$request->has('resume_id')) {
            return $this->response->errorBadRequest();
        }

        $user = JWTAuth::parseToken()->authenticate();

        try {
            $resume = Resume::findOrFail($request->query('resume_id'));
            $job = Job::findOrFail($request->query('job_id'));
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound();
        }

        if ($resume->user_id != $user->id) {
            return $this->response->errorBadRequest();
        }

        $array = $request->except('token');
        $array['user_id'] = $user->id;
        $array['status'] = 0;
        $job_apply = JobApply::create($array);
        $job_apply->save();

        return 'Success';
    }

    // refactor
    public function postJobEvaluate(Request $request) {
        if (!$request->has('job_completed_id') || !$request->has('score')) {
            return $this->response->errorBadRequest();
        }
        $score = $request->input('score');
        if (!in_array($score, [0, 1, 2, 3, 4, 5])) {
            return $this->response->error('score is not avalid.', 400);
        }

        $user = JWTAuth::parseToken()->authenticate();

        try {
            $jobCompleted = JobCompleted::findOrFail($request->query('job_completed_id'));
        } catch (ModelNotFoundException $e) {
            return $this->response->errorNotFound();
        }

        if ($jobCompleted->jobEvaluated()) {
            return $this->response->error('Job has been evaluated.', 400);
        }

        if ($jobCompleted->user_id != $user->id) {
            return $this->response->error('Wrong job completed id.', 400);
        }

        $params = $request->only(['score', 'comment']);
        $params['job_id'] = $jobCompleted->job_id;
        $params['user_id'] = $user->id;
        $job_evaluate = JobEvaluate::create($params);

        if ($job_evaluate->save()) {
            $jobCompleted->job_evaluate_id = $job_evaluate->id;
            if ($jobCompleted->save()) {
                return 'success';
            }
        }

        return $this->response->errorInternal('evaluate save failed');
    }

    /*
     * [POST] users/{id}
     */
    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'nickname' => 'max:32',      // 昵称不能超过32位
            'sex' => 'in:0,1',           // 性别
            'sign' => 'string',          // 签名
            'birthday' => 'date_format:Y-m-d', // 生日
            'location' => 'string',            // 地址
            'avatar' => 'exists:uploadfiles,path' // 头像
        ]);
        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);
        $avatar = $request->input('avatar');
        if ($avatar) {
            $uploadFile = Uploadfile::where('path', $avatar)->first();
            // 当前用户对该文件是否有访问权限
            $uploadFile->makeSureAccess($self);
            $uploadFile->replace($user->avatar);
        }
        $user->update(array_only($request->all(),
            ['nickname', 'sex', 'sign', 'birthday', 'location', 'avatar']));
        return response()->json($user);
    }

    /*
     * [GET] users/{id}/realNameApplies
     */
    public function getRealNameApplies($id) {
//        $user = User::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
//        $self->checkAccess($user->id);
        $rnvs = RealNameVerification::where('user_id', $self->id)
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json($rnvs);
    }

    /*
     * [POST] users/{id}/realNameApplie
     */
    public function createRealNameApplies(Request $request, $id) {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'real_name' => 'required|string|max:16',
            'id_number' => 'required|string|max:24',
            'verifi_pic' => 'required|exists:uploadfiles,path'
        ]);

        $verifi_pic = $request->input('verifi_pic');

        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);
        $user->checkNeedRealNameVerify();

        $uploadFile = Uploadfile::where('path', $verifi_pic)->first();
        $uploadFile->makeSureAccess($self);
        $uploadFile->replace();

        $rnv = RealNameVerification::create([
            'user_id' => $user->id,
            'user_name' => $user->nickname,
            'real_name' => $request->input('real_name'),
            'id_number' => $request->input('id_number'),
            'verifi_pic' => $uploadFile->path,
        ]);

        return response()->json($rnv);
    }

    /*
     * [DELETE] users/{id}/realNameApplies/{rnaid}
     */
    public function deleteRealNameApply($id, $rnaid) {
        $user = User::findOrFail($id);
        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);
        $rnv = RealNameVerification::where('user_id', $user->id)->findOrFail($rnaid);
        if ($rnv->is_examined != 0) throw new MsgException('你只能取消未处理的申请。', 400);
        $rnv->is_examined = 3;
        $rnv->save();
        return response()->json($rnv);
    }

    /*
     * [GET] users/{id}/logs
     */
    public function getLogs(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'siz' => 'integer|min:0',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $self->checkAccess($user->id);

        $direction = $request->input('dir', 'desc');
        $offset = $request->input('off', 0);
        $limit = $request->input('siz', 20);

        $builder = Log::where('user_id', $user->id);

        $total = $builder->count();
        $builder->orderBy('id', $direction)
                ->skip($offset)
                ->limit($limit);
        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    /*
     * [POST] evaluates/{id}
     */
    public function updateEvaluate(Request $request, $id) {
        $evaluate = JobEvaluate::findOrFail($id);
        $this->validate($request, [
            'score' => 'required|integer|between:1,5',
            'comment' => 'string',
            'pictures' => 'string'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $evaluate->makeSureAccess($self);

        $evaluate->update(array_only($request->all(), ['score', 'comment', 'pictures']));
        return response()->json($evaluate);
    }
    public function getAllResume(Request $request){
        $resume=Resume::query();
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
