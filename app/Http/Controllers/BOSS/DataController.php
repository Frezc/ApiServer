<?php

namespace App\Http\Controllers\BOSS;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Data;

class DataController extends Controller {

    public function __construct() {
        $this->middleware('log', ['only' => ['setData']]);
    }

    /*
     * [POST] data
     */
    public function setData(Request $request) {
        $this->validate($request, [
            'key' => 'required',
            'value' => 'required'
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        Data::updateOrCreate(['key' => $key], ['value' => $value]);
        return '保存成功';
    }
}