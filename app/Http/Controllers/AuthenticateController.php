<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use Storage;
use Validator;
use Hash;

class AuthenticateController extends Controller
{
  public function __construct()
  {
       $this->middleware('jwt.auth', ['except' => ['emailAuth', 'phoneAuth', 'refreshToken', 'register']]);
       $this->middleware('jwt.refresh', ['only' => ['refreshToken']]);
  }

  public function index()
  {
      $users = User::all();
      // return $users;
      // return $this->response->errorNotFound();
      return $user = JWTAuth::parseToken()->authenticate();
  }

  public function updateAvatar(Request $request){
    $user = JWTAuth::parseToken()->authenticate();
    if ($request->hasFile('avatar') && $request->file('avatar')->isValid()){
        // file_put_contents(public_path().'images/avatars/'.$user->id.'.png',
        //   file_get_contents($request->file('avatar')->getRealPath()));
        copy($request->file('avatar')->getRealPath(), public_path('images/avatars/'.$user->id));
        $avatar = '/images/avatars/'.$user->id;
        $user->avatar = $avatar;
        $user->save();
        return $avatar;
    } else {
        return $this->response->errorBadRequest();
    }
  }

  public function refreshToken(Request $request){
      return 'success';
  }

  public function emailAuth(Request $request)
  {
      $v = Validator::make($request->all(), [
          'email' => 'required|email',
          'password' => 'required'
      ]);

      if ($v->fails())
      {
          return $this->response->error($v->errors(), 400);
      }

      $user = User::where('email', $request->Input('email'))->first();
      if ($user != null){
          if ($user->email_verified == 0){
              return $this->response->error('email need to be verified.', 430);
          }
      }

      // grab credentials from the request
      $credentials = $request->only('email', 'password');

      try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              return $this->response->errorUnauthorized();
          }
      } catch (JWTException $e) {
          // something went wrong whilst attempting to encode the token
          return response()->json(['error' => 'could_not_create_token'], 500);
      }

      // all good so return the token

      return response()->json([
        'user' => User::where('email', $request->Input('email'))->first(),
        'token' => $token
      ]);
  }

  public function phoneAuth(Request $request)
  {
      $v = Validator::make($request->all(), [
          'phone' => 'required|regex:/[0-9]+/',
          'password' => 'required'
      ]);

      if ($v->fails())
      {
          return $this->response->error($v->errors(), 400);
      }

      // grab credentials from the request
      $credentials = $request->only('phone', 'password');

      try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              return response()->json(['error' => 'invalid_credentials'], 401);
          }
      } catch (JWTException $e) {
          // something went wrong whilst attempting to encode the token
          return response()->json(['error' => 'could_not_create_token'], 500);
      }

      // all good so return the token

      return response()->json([
        'user' => User::where('phone', $request->Input('phone'))->first(),
        'token' => $token
      ]);
  }

  public function register(Request $request){
    $v = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'password' => 'required|between:6,32',
        'nickname' => 'required|max:32'
    ]);

    if ($v->fails())
    {
        return $this->response->error($v->errors(), 400);
    }


    $user = new User;
    $user->email = $request->input('email');
    $user->nickname = $request->input('nickname');
    $user->password = Hash::make($request->input('password'));
    $user->save();

    //发邮件验证

    return 'success';
  }

  public function registerByPhone(Request $request){
    $v = Validator::make($request->all(), [
        'phone' => 'required|regex:/[0-9]+/|unique:users,phone',
        'password' => 'required|between:6,32',
        'nickname' => 'required|between:1,16',
        'verification_code' => 'required'
    ]);

    if ($v->fails())
    {
        return $this->response->error($v->errors(), 400);
    }


    //验证短信验证码

    $user = new User;
    $user->phone = $request->input('phone');
    $user->nickname = $request->input('nickname');
    $user->password = Hash::make($request->input('password'));
    $user->save();

    return 'success';
  }

  public function getSmsCode(Request $request){
    
  }
}
