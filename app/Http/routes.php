<?php

/* dingo
$api = app('Dingo\Api\Routing\Router');

$api -> version('v1',  function($api){
  //$api->get('users', 'App\Http\Controllers\AuthenticateController@index');
  // $api->get('test', 'App\Http\Controllers\SmsController@test');
  // $api->post('testEmail', 'App\Http\Controllers\EmailController@emailSend');
  $api->post('auth', 'App\Http\Controllers\AuthenticateController@emailAuth');
  $api->post('authPhone', 'App\Http\Controllers\AuthenticateController@phoneAuth');
  $api->get('refresh', 'App\Http\Controllers\AuthenticateController@refreshToken');
  $api->post('register', 'App\Http\Controllers\AuthenticateController@register');
  $api->post('registerByPhone', 'App\Http\Controllers\SmsController@registerByPhone');
  $api->post('resetPassword', 'App\Http\Controllers\SmsController@resetPassword');
  $api->get('getSmsCode', 'App\Http\Controllers\SmsController@getSmsCode');
  $api->post('bindPhone', 'App\Http\Controllers\SmsController@bindPhone');
  $api->post('sendVerifyEmail', 'App\Http\Controllers\EmailController@sendVerifyEmail');
  $api->post('verifyEmail', 'App\Http\Controllers\EmailController@verifyEmail');
  $api->post('bindEmail', 'App\Http\Controllers\EmailController@bindEmail');

  $api->post('user/update', 'App\Http\Controllers\UserController@update');
  $api->get('user/{id}', 'App\Http\Controllers\UserController@show');
  // $api->post('user', 'App\Http\Controllers\UserController@store');
  $api->get('resume', 'App\Http\Controllers\ResumeController@get');
  $api->get('resume/photo', 'App\Http\Controllers\ResumeController@photo');
  $api->post('resume/delete', 'App\Http\Controllers\ResumeController@delete');
  $api->post('resume/add', 'App\Http\Controllers\ResumeController@add');
  $api->post('resume/update', 'App\Http\Controllers\ResumeController@update');
  $api->post('avatar', 'App\Http\Controllers\AuthenticateController@updateAvatar');
  $api->get('job/query', 'App\Http\Controllers\JobController@query');
  $api->get('job/apply', 'App\Http\Controllers\UserController@getJobApply');
  $api->get('job/completed', 'App\Http\Controllers\UserController@getJobCompleted');
  $api->get('job/{id}', 'App\Http\Controllers\JobController@get')->where('id', '[0-9]+');
  $api->post('job/apply', 'App\Http\Controllers\UserController@postJobApply');
  $api->get('job/evaluate', 'App\Http\Controllers\JobController@getJobEvaluate');
  $api->post('job/evaluate', 'App\Http\Controllers\UserController@postJobEvaluate');
  $api->get('company/query', 'App\Http\Controllers\CompanyController@query');
  $api->get('company/{id}', 'App\Http\Controllers\CompanyController@get')->where('id', '[0-9]+');
});
*/


//Route::get('users', 'AuthenticateController@index');
//Route::get('test', 'SmsController@test');
//Route::post('testEmail', 'EmailController@emailSend');
Route::post('resetPassword', 'SmsController@resetPassword');//重置密码
Route::post('bindPhone', 'SmsController@bindPhone');
Route::post('verifyEmail', 'EmailController@verifyEmail');
Route::post('bindEmail', 'EmailController@bindEmail');
Route::post('user/update', 'UserController@update');
Route::post('user/idCardVerify', 'UserController@idCardVerify');
Route::get('user/{id}', 'UserController@show');
// Route::post('user', 'UserController@store');
Route::get('resume', 'ResumeController@get');
Route::get('resume/photo', 'ResumeController@photo');
Route::post('resume/delete', 'ResumeController@delete');
Route::post('resume/add', 'ResumeController@add');
Route::post('resume/update', 'ResumeController@update');
Route::post('avatar', 'AuthenticateController@updateAvatar');
Route::get('job/query', 'JobController@query');
Route::get('job/apply', 'UserController@getJobApply');
Route::get('job/completed', 'UserController@getJobCompleted');
Route::get('job/{id}', 'JobController@get')->where('id', '[0-9]+');
Route::post('job/apply', 'UserController@postJobApply');
Route::get('job/evaluate', 'JobController@getJobEvaluate');
Route::post('job/evaluate', 'UserController@postJobEvaluate');
Route::get('company/query', 'CompanyController@query');
Route::get('company/{id}', 'CompanyController@get')->where('id', '[0-9]+');
Route::get('getAllJob','UserController@mainPage');
// 需要限制次数的请求
// 每分钟三次
Route::group(['middleware' => 'throttle:3'], function ($api) {
  Route::post('auth', 'AuthenticateController@emailAuth');
  Route::post('authPhone', 'AuthenticateController@phoneAuth');
  Route::get('refresh', 'AuthenticateController@refreshToken');

  Route::post('register', 'AuthenticateController@register');
  Route::post('registerByPhone', 'SmsController@registerByPhone');
});

// 每分钟一次
Route::group(['middleware' => 'throttle:1'], function ($api) {
  Route::get('getSmsCode', 'SmsController@getSmsCode');
});

// 每分钟两次
Route::group(['middleware' => 'throttle:2'], function ($api) {
  Route::get('getSmsCode', 'SmsController@getSmsCode');
  Route::post('sendVerifyEmail', 'EmailController@sendVerifyEmail');
});

Route::get('/', function () {
    return view('welcome');
});
