<?php
/**
 * Created by PhpStorm.
 * User: 国宝零号
 * Date: 2017/8/31
 * Time: 18:44
 */

namespace App\Http\Controllers;



use App\Models\Company;
use App\Models\Job;
use App\Models\User;

use Mail ;
class MyService
{
    public static function checkIsCollect($uid, $jid)
    {
        $collect = \DB::table('job_collection')->where('user_id', $uid)
            ->where('job_id', $jid)
            ->count();
        if ($collect > 0)
            return true;
        else
            return false;
    }
    public function delete($tablename,$where){
        $result =  \DB::table($tablename)->where($where)->delete();
        if ($result == true)
            return true;
        else
            return false;
    }
    public function getList($tablename1,$tablename2,$one,$op,$two,$jointype='inner',$where,$select='*'){
        $result = \DB::table($tablename1)->join($tablename2,$one,$op,$two,$jointype)->where($where);
        $result1 = $result->select($select);
        return $result1;
    }
    public function sortPage($data,$page,$limit,$clum,$sort){
        $data->orderBy($clum,$sort);
        //分页
        $data->skip($page*$limit);
        $data->limit($limit);
        return $data;
    }
   public function emailSend($email,$blade,$data) {
    $email = '244774097@qq.com';
    Mail::send($blade, $data, function ($message) use ($email) {
        $message->to($email, 'dear')->subject('淘兼职邮箱验证');
    });
}

}