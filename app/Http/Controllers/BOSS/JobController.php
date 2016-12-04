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

class JobController extends Controller {

    /*
     * [POST] jobs/{id}/restore
     */
    public function restore($id) {
        $job = Job::withTrashed()->findOrFail($id);
        $job->restore();
        return response()->json($job);
    }
}