<?php
/**
 * Created by PhpStorm.
 * User: 国宝零号
 * Date: 2017/6/18
 * Time: 14:46
 */
 /**
  * 获取某些字段返回array
  *
  */
function sucesss($data)
{
    return response()->json($data);
}

function successList($data)
{
    return response()->json(['list' => $data->get(), 'total' => $data->count()]);
}

function checkTableData($model, $where,$clumn, $eqData)
{
    $result = $model->where($where)->first();
    if ($result[$clumn] == $eqData)
        return true;
    else
        return false;
}

function updateTable($model, $where, $data)
{
    $result = $model->where($where)->update($data);
    if ($result == true)
        return true;
    else
        return false;
}

/**
 * @param $tablename
 * @param $where
 * @param $data
 * @return array
 */
function getTableField($tablename,$where,$data){
     $result = DB::table($tablename)->where($where)->first($data);
     return (array)$result;
}

/**
 * @param $tablename
 * @param $where
 * @param $data  返回某个值
 * @return mixed|string
 */
function getTableClumnValue($tablename,$where,$data){
    $result = DB::table($tablename)->where($where)->value($data);
    return empty($result)?'':$result;
}

function inserTable($tablename,$data){
    $result = DB::table($tablename)->insert($data);
    if ($result)
        return true;
    else
        return false;

}

function getAvgScore($user_id){
    $avg = DB::table('job_evaluate')->where('pingjia_user_id',$user_id)->average('score');
    return $avg;
}