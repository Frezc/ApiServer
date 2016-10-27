<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller {

    public function get($id) {
        return response()->json(Company::findOrFail($id));
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
