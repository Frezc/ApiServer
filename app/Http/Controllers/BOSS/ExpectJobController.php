<?php
/**
 * Created by PhpStorm.
 * User: Frezc
 * Date: 2016/12/3
 * Time: 22:26
 */

namespace App\Http\Controllers\BOSS;


use App\Http\Controllers\Controller;
use App\Models\ExpectJob;

class ExpectJobController extends Controller {

    /*
     * [POST] expect_jobs/{id}/restore
     */
    public function restore($id) {
        $expectJob = ExpectJob::withTrashed()->findOrFail($id);
        $expectJob->restore();
        return response()->json($expectJob);
    }
}