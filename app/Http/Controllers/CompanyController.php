<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyApply;
use App\Models\Job;
use App\Models\JobTime;
use App\Models\Uploadfile;
use Illuminate\Http\Request;
use JWTAuth;

class CompanyController extends Controller {

    function __construct(){
        $this->middleware('jwt.auth',['only'=>['releaseJob', 'getApply', 'postApply', 'update']]);
    }

    public function get($id) {
        return response()->json(Company::findOrFail($id));
    }

    public function releaseJob(Request $request){

        $this->validate($request, [
            'name'=>'string',
            'pay_way'=>'integer|min:0|max:2',
            'salary_type'=>'integer|min:0|max:2',
            'salary'=>'integer|min:0',
            'demanded_number'=>'integer|min:0'
        ]);
          $job=new Job;
          $job->name=$request->input('name');
          $job->description=$request->input('description');
          $job->pay_way=$request->input('pay_way');
          $job->salary=$request->input('salary');
          $job->company_name=$request->input('company_name');
          $job->creator_id=$request->input('creator_id');
          $job->company_id=$request->input('company_id');
          $job->creator_name=$request->input('creator_name');

          $job->contact_number=$request->input('contact_number');
          $job->job_type=$request->input('job_type');
          $job->salary_time=$request->input('salary_time');
          $jobstime =new JobTime;
          $jobstime->start_at=$request->input('start_at');
          $jobstime->end_at=$request->input('end-at');
          $jobstime->number=$request->input('demanded_number');
          $jobstime->save();
          $job->save();
          return 'success';
    }


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
