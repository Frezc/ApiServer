<?php

//Route::get('users', 'AuthenticateController@index');
//Route::get('test', 'SmsController@test');
//Route::post('testEmail', 'EmailController@emailSend');
Route::post('resetPassword', 'SmsController@resetPassword');//重置密码
Route::post('bindPhone', 'SmsController@bindPhone');//手机绑定
Route::post('verifyEmail', 'EmailController@verifyEmail');//邮件验证
Route::post('bindEmail', 'EmailController@bindEmail');//邮箱绑定
Route::post('user/idCardVerify', 'UserController@idCardVerify');//身份验证

Route::get('self', 'UserController@self');
Route::post('users/{id}', 'UserController@update');//更新
Route::get('users/{id}', 'UserController@show');
// Route::post('user', 'UserController@store');
Route::get('users/{id}/resumes', 'ResumeController@get');//获取简历
//Route::get('resume/photo', 'ResumeController@photo');
Route::delete('users/{id}/resumes/{resumeId}', 'ResumeController@delete');//建立删除
Route::post('users/{id}/resumes', 'ResumeController@add');//添加简历
Route::post('users/{id}/resumes/{resumeId}', 'ResumeController@update');//更新简历
Route::get('users/{id}/orders', 'OrderController@get');//
//Route::post('avatar', 'AuthenticateController@updateAvatar');
Route::get('users/{id}/realNameApplies', 'UserController@getRealNameApplies');
Route::post('users/{id}/realNameApplies', 'UserController@createRealNameApplies');
Route::delete('users/{id}/realNameApplies/{rnaid}', 'UserController@deleteRealNameApply');
Route::get('users/{id}/logs', 'UserController@getLogs');

Route::get('jobs', 'JobController@query');
Route::get('job/apply', 'UserController@getJobApply');
Route::get('job/completed', 'UserController@getJobCompleted');
Route::get('jobs/{id}', 'JobController@get')->where('id', '[0-9]+');
Route::post('jobs/{id}/apply', 'JobController@apply');
Route::post('job/apply', 'UserController@postJobApply');
Route::get('job/evaluate', 'JobController@getJobEvaluate');
Route::post('job/evaluate', 'UserController@postJobEvaluate');
Route::post('expect_jobs', 'ExpectJobController@create');
Route::get('expect_jobs', 'ExpectJobController@query');
Route::post('expect_jobs/{id}/apply', 'ExpectJobController@apply');
Route::get('getAllJob', 'UserController@mainPage');

Route::get('companies', 'CompanyController@query');
Route::get('companies/{id}', 'CompanyController@get')->where('id', '[0-9]+');
Route::get('companies/apply', 'CompanyController@getApply');
Route::post('companies/apply', 'CompanyController@postApply');

Route::post('releaseJob','CompanyController@releaseJob');

Route::get('umsg', 'MessageController@getUpdate');
Route::get('messages', 'MessageController@get');
Route::get('notifications/{id}', 'MessageController@getNotification')->where('id', '[0-9]+');
Route::get('conversations', 'MessageController@getConversation');
Route::post('conversations', 'MessageController@postConversation');


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

// boss
Route::group(['namespace' => 'BOSS', 'middleware' => ['jwt.auth', 'role:admin']], function () {
    Route::get('users', 'UserController@query');
    Route::post('notifications', 'MessageController@postNotifications');
    Route::get('real_name_applies', 'UserController@getAllRealNameApplies');
    Route::get('notifications/history', 'MessageController@getHistory');
    Route::get('company_applies', 'UserController@getAllCompanyApplies');
});

Route::get('/', function () {
    return view('welcome');
});
