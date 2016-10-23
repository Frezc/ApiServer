<?php

namespace App\Http\Controllers;

use App\Job;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Company;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Namshi\JOSE\JWS;

class CompanyController extends Controller
{
  public function get($id)
{
  return response()->json(Company::findOrFail($id));
}

  public function query(Request $request)
  {
  
      
    if ($request->has('q') && $request->has('limit')){//查询容器中是否含有q且里，limit返回资源标识符
      $q = $request->query('q'); 
      $q_array = explode(" ", trim($q));//以空格为标志分割q trim删除字符收尾的空白

      $builder = Company::query();//querybuilder对象，用于取出company表中所有元素
      //对数据库中名字、描述、人进行查找
      foreach($q_array as $qi){//这里的 $q_array是你要遍历的数组名，每次循环中，array_name数组的当前元素的值被赋给$value,并
        //且数组内部的下标向下移一步，也就是下次循环回得到下一个元素。
        $builder->where(function($query) use ($qi){
          $query->orWhere('name', 'like', '%'.$qi.'%')
                ->orWhere('description', 'like', '%'.$qi.'%')
                ->orWhere('contact_person', 'like', '%'.$qi.'%');
        });
      }

      //排列（对查询到的进行排列）
      $builder->orderBy(
        $request->input('orderby', 'id'),
        $request->input('direction', 'asc')
      );

      //分页
      if ($request->has('offset')){
        $builder->skip($request->input('offset'));
      }
      $builder->limit($request->input('limit'));
      return $builder->get()->toArray();
      // dd($builder->get());


    } else {


      return $this->response->errorBadRequest();
    }
  }
}
