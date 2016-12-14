<?php
/**
 * Created by PhpStorm.
 * User: Frezc
 * Date: 2016/12/3
 * Time: 21:59
 */

namespace App\Http\Controllers\BOSS;


use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JobController extends Controller {

    /*
     * [POST] jobs/{id}/restore
     */
    public function restore($id) {
        $job = Job::withTrashed()->findOrFail($id);
        $job->restore();
        return response()->json($job);
    }


    /*
     * [GET] jobs/{id}/time
     */
    public function getTime(Request $request, $id) {
        // 找到岗位，否则返回404
        $job = Job::findOrFail($id);
        $this->validate($request, [
            'expire' => 'in:0,1'
        ]);
        $expire = $request->input('expire', 0);
        $builder = JobTime::withTrashed()->where('job_id', $job->id);
        if (!$expire) {
            $builder->where('apply_end_at', '>', Carbon::now()->toDateString());
        }
        $builder->orderBy('apply_end_at', 'desc');
        return response()->json($builder->get());
    }
}