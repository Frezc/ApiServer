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
            'kw' => 'required',
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

        $q_array = explode(" ", trim($keywords));

        $builder = Company::query();
        foreach ($q_array as $qi) {
            $builder->where(function ($query) use ($qi) {
                $query->orWhere('name', 'like', '%' . $qi . '%')
                    ->orWhere('description', 'like', '%' . $qi . '%')
                    ->orWhere('contact_person', 'like', '%' . $qi . '%');
            });
        }

        $total = $builder->count();

        //排列
        $builder->orderBy($orderby, $direction);

        //分页
        $builder->skip($offset);
        $builder->limit($limit);

        return response()->json(['total' => $total, 'list' => $builder->get()]);
    }
}
