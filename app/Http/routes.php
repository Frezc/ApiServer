<?php

//Route::get('users', 'AuthenticateController@index');
//Route::get('test', 'SmsController@test');
//Route::post('testEmail', 'EmailController@emailSend');
Route::post('resetPassword', 'SmsController@resetPassword');
Route::post('bindPhone', 'SmsController@bindPhone');
Route::post('verifyEmail', 'EmailController@verifyEmail');
Route::post('bindEmail', 'EmailController@bindEmail');

Route::post('user/update', 'UserController@update');
Route::get('user/{id}', 'UserController@show');
// Route::post('user', 'UserController@store');
Route::get('resume', 'ResumeController@get');
Route::get('resume/photo', 'ResumeController@photo');
Route::post('resume/delete', 'ResumeController@delete');
Route::post('resume/add', 'ResumeController@add');
Route::post('resume/update', 'ResumeController@update');
//Route::post('avatar', 'AuthenticateController@updateAvatar');
Route::get('job/query', 'JobController@query');
Route::get('job/apply', 'UserController@getJobApply');
Route::get('job/completed', 'UserController@getJobCompleted');
Route::get('job/{id}', 'JobController@get')->where('id', '[0-9]+');
Route::post('job/apply', 'UserController@postJobApply');
Route::get('job/evaluate', 'JobController@getJobEvaluate');
Route::post('job/evaluate', 'UserController@postJobEvaluate');
Route::get('company/query', 'CompanyController@query');
Route::get('company/{id}', 'CompanyController@get')->where('id', '[0-9]+');

// 需要限制次数的请求
// 每分钟三次
Route::group(['middleware' => 'throttle:3'], function ($api) {
  Route::post('auth', 'AuthenticateController@emailAuth');
  Route::post('authPhone', 'AuthenticateController@phoneAuth');
  Route::get('refresh', 'AuthenticateController@refreshToken');

  Route::post('register', 'AuthenticateController@register');
  Route::post('registerByPhone', 'SmsController@registerByPhone');

});
  Route::post('upload/image', 'UploadController@uploadImage');

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
