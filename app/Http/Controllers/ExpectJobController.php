<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\ExpectTime;
use App\Resume;
use Illuminate\Http\Request;
use App\ExpectJob;
use JWTAuth;

class ExpectJobController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth');
    }

    public function create(Request $request) {
        $this->validate($request, [
            'resume_id' => 'required|integer',
            'expect_times' => 'array',
            'expect_times.*.year' => 'required|integer|between:2016,2099',
            'expect_times.*.month' => 'required|integer|between:0,12',
            'expect_times.*.dayS' => 'required|integer|min:0',
            'expect_times.*.dayE' => 'integer|min:0',
            'expect_times.*.hourS' => 'integer|between:0,23',
            'expect_times.*.hourE' => 'integer|min:0,23'
        ]);

        $self = JWTAuth::parseToken()->authenticate();
        $resume_id = $request->input('resume_id');
        $resume = Resume::findOrFail($resume_id);
        $expect_times = $request->input('expect_times', []);

        $self->checkAccess($resume->user_id);

        $expectJob = $resume->convertToExpectJob(1);

        foreach ($expect_times as $expect_time) {
            ExpectTime::create(array_merge($expect_time, [ 'expect_job_id' => $expectJob->id ]));
        }

        $expectJob->expect_time = $expect_times;

        return response()->json($expectJob);
    }
}
