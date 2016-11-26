<?php

namespace App\Http\Controllers\BOSS;


use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Models\Data;

class DataController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['setData']]);
    }

    public function setData(Request $request) {
        $this->validate($request, [
            'key' => 'required',
            'data' => 'required'
        ]);

        $key = $request->input('key');
        $data = $request->input('data');

        Data::updateOrCreate(['key' => $key], ['data' => $data]);
        return '保存成功';
    }
}