<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Models\JobTime;
use Illuminate\Http\Request;

class CompanyController extends Controller {

    function __construct(){
        $this->middleware('jwt.auth',['only'=>['releaseJob']]);
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
}
