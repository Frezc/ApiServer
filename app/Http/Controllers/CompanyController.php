<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompanyController extends Controller {

    public function get($id) {
        return response()->json(Company::findOrFail($id));
    }

    public function query(Request $request) {
        $this->validate($request, [
            'q' => 'required',
            'limit' => 'integer|min:0',
            'orderby' => 'in:id,created_at',
            'direction' => 'in:asc,desc',
            'offset' => 'integer|min:0'
        ]);

        $q = $request->input('q');
        $limit = $request->input('limit', 20);
        $orderby = $request->input('orderby', 'id');
        $direction = $request->input('direction', 'asc');
        $offset = $request->input('offset', 0);

        $q_array = explode(" ", trim($q));

        $builder = Company::query();
        foreach ($q_array as $qi) {
            $builder->where(function ($query) use ($qi) {
                $query->orWhere('name', 'like', '%' . $qi . '%')
                    ->orWhere('description', 'like', '%' . $qi . '%')
                    ->orWhere('contact_person', 'like', '%' . $qi . '%');
            });
        }

        //排列
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        // dd($builder->get());
        return response()->json($builder->get());
    }
}
