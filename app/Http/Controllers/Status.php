<?php
/**
 * Created by PhpStorm.
 * User: 国宝零号
 * Date: 2017/9/2
 * Time: 14:51
 */

namespace App\Http\Controllers;


class Status
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 允许面试    // 订单状态 0：创建，1：允许面试，2：拒绝面试成功，3：面试成功 4 面试失败 5面成功并接受 6 工作完成先下付款 7工作完成已付款 8订单关闭(面试陈功以后)
     */
  static  $shengqing = 0;
  static  $yuxumianshi = 1;
  static  $jujuemianshi =2;
  static  $mianshichenggong =3;
  static  $mianshishibai =4;
  static  $jieshougongzuo =5;
  static  $gongzuowanchengxianxiafukuan=6;
  static  $gongzuowancheng=7;
    static  $guangbi=8;
}