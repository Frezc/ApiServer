<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
// Route::resource('user', 'UserController');
// // Route::post('auth', 'AuthenticateController@authenticate');
// Route::post('auth', function(Request $request){
//   require 'auth';
// });

$api = app('Dingo\Api\Routing\Router');

$api -> version('v1',  function($api){
  //$api->get('users', 'App\Http\Controllers\AuthenticateController@index');
  $api->post('auth', 'App\Http\Controllers\AuthenticateController@authenticate');
  $api->get('refresh', 'App\Http\Controllers\AuthenticateController@refreshToken');
  $api->post('register', 'App\Http\Controllers\AuthenticateController@register');
  $api->post('user/update', 'App\Http\Controllers\UserController@update');
  $api->get('user/{id}', 'App\Http\Controllers\UserController@show');
  // $api->post('user', 'App\Http\Controllers\UserController@store');
  $api->get('resume', 'App\Http\Controllers\ResumeController@get');
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


Route::get('/', function () {
    return view('welcome');
});
