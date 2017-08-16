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
  function getKeyVel($model,$key){
  return  $model->values($key);
 }