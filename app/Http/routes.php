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

Route::get('/', function () {
    return view('welcome');
});

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
  $api->get('user/{id}', 'App\Http\Controllers\UserController@show');
  // $api->post('user', 'App\Http\Controllers\UserController@store');
  $api->get('resume', 'App\Http\Controllers\ResumeController@get');
  $api->post('resume/delete', 'App\Http\Controllers\ResumeController@delete');
  $api->post('resume/add', 'App\Http\Controllers\ResumeController@add');
  $api->post('resume/update', 'App\Http\Controllers\ResumeController@update');
  $api->post('avatar', 'App\Http\Controllers\AuthenticateController@updateAvatar');
  $api->get('job/query', 'App\Http\Controllers\JobController@query');
  $api->get('job/apply', 'App\Http\Controllers\UserController@getJobApply');
  $api->get('job/{id}', 'App\Http\Controllers\JobController@get');
  $api->post('job/apply', 'App\Http\Controllers\UserController@postJobApply');
  $api->get('company/query', 'App\Http\Controllers\CompanyController@query');
  $api->get('company/{id}', 'App\Http\Controllers\CompanyController@get');
});
