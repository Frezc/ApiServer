<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyApply;
use App\Models\Job;
use App\Models\JobTime;
use App\Models\Uploadfile;
use App\Models\UserCompany;
use App\Models\User;
use Illuminate\Http\Request;
use JWTAuth;

class CompanyController extends Controller {

    function __construct(){

        $this->middleware('jwt.auth',['only'=>['releaseJob', 'getApply', 'postApply', 'update']]);
        $this->middleware('log', ['only' => ['postApply', 'update', 'releaseJob']]);
        $this->middleware('role:user', ['only' => ['releaseJob', 'postApply', 'update']]);
    }

    /*
     * [GET] companies/{id}
     */
    public function get($id) {
        return response()->json(Company::findOrFail($id));
    }

    /*
     * [GET] releaseJob
     */
    public function releaseJob(Request $request){

        $this->validate($request, [
            'name'=>'string',
            'pay_way'=>'integer|min:0|max:2',
            'salary_type'=>'integer|min:0|max:2',
            'salary'=>'integer|min:0',
            'demanded_number'=>'integer|min:0'
        ]);
          $job=new Job;
          $jobstime =new JobTime;
          $job->name=$request->input('name');
          $job->description=$request->input('description');
          $job->pay_way=$request->input('pay_way');
          $job->salary=$request->input('salary');
          $job->salary_type=$request->input('salary_type');
          $user=JWTAuth::parseToken()->authenticate();
          $user_id=$user->id;
          $job->creator_id=$user_id;
//          $user_company=UserCompany::getCompanyId($user_id);
          $company_id=$user->company_id;
          $company=Company::find($company_id);
          $job->company_id=$company_id;
          $job->company_name=$company->name;
          $job->creator_name=$company->contact_person;
          $job->contact=$company->contact;
          $job->job_type=$request->input('job_type');
          $jobstime->start_at=$request->input('start_at');
          $jobstime->end_at=$request->input('end_at') ;
          $jobstime->number=$request->input('demanded_number');
          $job->save();
          $jobstime->job_id=$job->id;
          $jobstime->save();
          return  response()->json($job);
    }

    /*
     * [GET] companies
     */
    public function query(Request $request) {
        $this->validate($request, [
            'kw' => 'string',
            'siz' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'dir' => 'in:asc,desc',
            'off' => 'integer|min:0'
        ]);

        $keywords = $request->input('kw');
        $limit = $request->input('siz', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('dir', 'asc');
        $offset = $request->input('off', 0);

        $builder = Company::search($keywords);

        $total = $builder->count();

        //排列
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }

    /*
     * [GET] companies/apply
     */
    public function getApply(Request $request) {
        $this->validate($request, [
            'siz' => 'integer|min:0',
            'off' => 'integer|min:0'
        ]);

        $offset = $request->input('off', 0);
        $size = $request->input('siz', 20);
        $self = JWTAuth::parseToken()->authenticate();

        $builder = CompanyApply::where('user_id', $self->id)
            ->orderBy('updated_at', 'desc');
        $total = $builder->count();
        $list = $builder->skip($offset)->limit($size)->get();

        return response()->json(['total' => $total, 'list' => $list]);
    }

    /*
     * [POST] companies/apply
     */
    public function postApply(Request $request) {
        $this->validate($request, [
            'name' => 'required|between:1,50',
            'url' => 'string',
            'address' => 'required|string',
            'logo' => 'exists:uploadfiles,path',
            'description' => 'string',
            'contact_person' => 'required|max:16',
            'contact' => 'required|max:50',
            'business_license' => 'required|exists:uploadfiles,path'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $logo = $request->input('logo');
        if ($logo) {
            $uploadFile = Uploadfile::where('path', $logo)->first();
            $uploadFile->makeSureAccess($self);
        }
        $business_license = $request->input('business_license');
        $uploadFile = Uploadfile::where('path', $business_license)->first();
        $uploadFile->makeSureAccess($self);

        $params = array_only($request->all(), ['name', 'url', 'address', 'logo',
            'description', 'contact_person', 'contact', 'business_license']);
        $params['user_id'] = $self->id;
        $params['user_name'] = $self->nickname;
        $params['status'] = 1;
        $companyApply = CompanyApply::create($params);

        return response()->json($companyApply);
    }

    /*
     * [POST] companies/{id}
     */
    public function update(Request $request, $id) {
        $company = Company::findOrFail($id);
        $this->validate($request, [
            'url' => 'string',
            'address' => 'string',
            'logo' => 'exists:uploadfiles,path',
            'description' => 'string',
            'contact_person' => 'max:16',
            'contact' => 'max:50'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $company->makeSureAccess($self);

        $company->update(array_only($request->all(), ['url', 'address', 'logo', 'description', 'contact_person', 'contact']));
        return response()->json($company);
    }
}
