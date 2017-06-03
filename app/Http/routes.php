<?php

/*
 * 账户相关
 */
Route::post('resetPassword', 'SmsController@resetPassword');//重置密码
Route::post('bindPhone', 'SmsController@bindPhone');//手机绑定
Route::post('authPhone', 'AuthenticateController@phoneAuth');//手机号码登录

Route::group(['middleware' => 'throttle:3'], function ($api) {
    Route::get('getSmsCode', 'SmsController@getSmsCode');
});
Route::group(['middleware' => 'throttle:3'], function ($api) {


    Route::post('authPhone', 'AuthenticateController@phoneAuth');//手机号码登录
    Route::get('refresh', 'AuthenticateController@refreshToken');//更新token
    Route::post('registerByPhone', 'SmsController@registerByPhone');//用户手机注册

});
/*
 * 订单相关
 */
Route::get('ordersDelete/{id}', 'OrderController@delete')->where('id', '[0-9]+');//删除某个订单
Route::get('ordersCancel/{id}', 'OrderController@close')->where('id', '[0-9]+');//关闭某个订单
/*
 * 用户相关
 */
Route::get('users/{id}', 'UserController@show');
Route::post('users/{id}', 'UserController@update');//更新用户信息
/*
 * 简历相关
 */
Route::get('users/{id}/resumes', 'ResumeController@get');//获取某份简历
Route::post('users/{id}/resumes/{resumeId}', 'ResumeController@update');//更新某份简历
Route::post('users/{id}/resumes/{resumeId}', 'ResumeController@delete');//删除谋一份简历
Route::get('users/getOneAllResume', 'ResumeController@getOneAllResume');//获取某人的全部简历
Route::post('users/{id}/resumes', 'ResumeController@add');//添加简历
/*
 * 工作相关
 */
Route::get('getAllJob', 'UserController@mainPage');//主页获取所有工作，可根据自己的简历筛选出相应的工作直接从后台推
Route::post('jobs/create', 'JobController@create');//创建工作
Route::post('jobs/update/{id}', 'JobController@update')->where('id', '[0-9]+');//对某一个的工作进行更新
Route::post('job/apply', 'UserController@postJobApply');//用户申请某一个工作
Route::get('jobs/{id}', 'JobController@get')->where('id', '[0-9]+');//获取某一个工作的详情
Route::get('jobs/close/{id}', 'JobController@closeJob')->where('id', '[0-9]+');//关闭某一个工作

/*
 * 邮件相关
 */
Route::post('companySendWorkMail/{id}', 'EmailController@companySendWorkMail')->where('id', '[0-9]+');//发送工作邀请邮箱
/*
 * 公司相关
 */

/*
 * 认证相关
 */
Route::get('users/{id}/realNameApplies', 'UserController@getRealNameApplies');//获取实名认证信息
Route::post('users/{id}/realNameApplies', 'UserController@createRealNameApplies');//创建实名认证信息
Route::delete('users/{id}/realNameApplies/{rnaid}', 'UserController@deleteRealNameApply');//取消实名认证
Route::post('verifyEmail', 'EmailController@verifyEmail');//邮件验证
Route::post('bindEmail', 'EmailController@bindEmail');//邮箱绑定







Route::get('self', 'UserController@self');

// Route::post('user', 'UserController@store');

//Route::get('resume/photo', 'ResumeController@photo');


Route::get('users/{id}/orders', 'OrderController@query');
Route::post('avatar', 'AuthenticateController@updateAvatar');


Route::get('users/{id}/logs', 'UserController@getLogs');
Route::get('userGetOrder','UserController@user_get_order');
Route::get('companyGetOrder','UserController@company_get_order');
Route::post('evaluates/{id}', 'UserController@updateEvaluate');

Route::get('jobsQuery', 'JobController@query');

Route::get('job/apply', 'UserController@getJobApply');         // use [GET] users/{id}/orders instead
Route::get('job/completed', 'UserController@getJobCompleted'); // use [GET] users/{id}/orders instead


Route::delete('jobs/{id}', 'JobController@delete')->where('id', '[0-9]+');
Route::post('jobs/{id}/time', 'JobController@addTime')->where('id', '[0-9]+');
Route::delete('jobs/{id}/time', 'JobController@closeTime')->where('id', '[0-9]+');
Route::post('jobs/{id}/apply', 'JobController@apply')->where('id', '[0-9]+');
Route::post('job/apply', 'UserController@postJobApply');       // use [POST] jobs/{id}/apply instead
Route::get('jobs/{id}/evaluate', 'JobController@getEvaluate');
Route::post('job/evaluate', 'UserController@postJobEvaluate'); // use [POST] orders/{id}/evaluate instead

Route::post('job/collect/{id}', 'JobController@collect')->where('id','[0-9]+');

Route::post('expect_jobs', 'ExpectJobController@create');
Route::get('expect_jobs', 'ExpectJobController@query');
Route::get('expect_jobs/{id}', 'ExpectJobController@get')->where('id', '[0-9]+');
Route::post('expect_jobs/{id}', 'ExpectJobController@update')->where('id', '[0-9]+');
Route::delete('expect_jobs/{id}', 'ExpectJobController@delete')->where('id', '[0-9]+');
Route::post('expect_jobs/{id}/apply', 'ExpectJobController@apply');

Route::get('getAlluser', 'UserController@getAlluser');
Route::get('companies', 'CompanyController@query');
Route::get('companies/{id}', 'CompanyController@get')->where('id', '[0-9]+');
Route::post('companies/{id}', 'CompanyController@update')->where('id', '[0-9]+');
Route::post('companies/{id}/users', 'CompanyController@addUser')->where('id', '[0-9]+');
Route::get('companies/apply', 'CompanyController@getApply');
Route::post('companies/apply', 'CompanyController@postApply');
Route::post('unlink_company', 'CompanyController@unlink');

Route::get('getResumes', 'ResumeController@getAllResume');


Route::get('orders/{id}', 'OrderController@get')->where('id', '[0-9]+');
Route::get('orders/{id}/evaluate', 'OrderController@getEvaluate')->where('id', '[0-9]+');
Route::post('orders/{id}/evaluate', 'OrderController@postEvaluate')->where('id', '[0-9]+');
Route::post('orders/{id}/check', 'OrderController@check')->where('id', '[0-9]+');
Route::post('orders/{id}/payment', 'OrderController@pay')->where('id', '[0-9]+');
Route::post('orders/{id}/completed', 'OrderController@completed')->where('id', '[0-9]+');

Route::get('orders/getOrderStatus', 'OrderController@getOrderStatus');

Route::get('umsg', 'MessageController@getUpdate');
Route::get('messages', 'MessageController@get');
Route::get('notifications/{id}', 'MessageController@getNotification')->where('id', '[0-9]+');
Route::get('conversations', 'MessageController@getConversation');
Route::post('conversations', 'MessageController@postConversation');
Route::post('feedbacks', 'MessageController@postFeedback');
Route::get('banners', 'DataController@getBanners');
Route::get('job_types', 'DataController@getJobTypes');
Route::post('reports', 'MessageController@createReport');
Route::get('orders/getCompanyOrderStatus', 'OrderController@getCompanyOrderStatus');
Route::post('upload/image', 'UploadController@uploadImage');


// 每分钟两次
Route::group(['middleware' => 'throttle:2'], function ($api) {

    Route::post('sendVerifyEmail', 'EmailController@sendVerifyEmail');
});

// boss
Route::group(['namespace' => 'BOSS', 'middleware' => ['jwt.auth', 'role:admin']], function () {
    Route::get('users', 'UserController@query');
    Route::post('notifications', 'MessageController@postNotifications');
    Route::get('real_name_applies', 'UserController@getAllRealNameApplies');
    Route::get('notifications/history', 'MessageController@getHistory');
    Route::get('company_applies', 'UserController@getAllCompanyApplies');
    Route::get('orders', 'UserController@getOrders');
    Route::get('feedbacks', 'MessageController@getFeedbacks');
    Route::post('data', 'DataController@setData');
    Route::post('feedbacks/{id}', 'MessageController@updateFeedback');
    Route::post('real_name_applies/{id}', 'UserController@updateRealNameApply')->where('id', '[0-9]+');
    Route::post('company_applies/{id}', 'UserController@updateCompanyApply')->where('id', '[0-9]+');
    Route::get('reports', 'MessageController@getReports');
    Route::post('reports/{id}', 'MessageController@updateReport');
    Route::post('users/{id}/role', 'UserController@updateRole');
    Route::post('jobs/{id}/restore', 'JobController@restore');
    Route::post('expect_jobs/{id}/restore', 'ExpectJobController@restore');
    Route::get('boss/umsg', 'MessageController@getUpdate');
    Route::get('evaluates', 'UserController@getEvaluates');
    Route::get('jobs/{id}/time', 'JobController@getTime')->where('id', '[0-9]+');
});

Route::get('/', function () {
    return view('welcome');
});
