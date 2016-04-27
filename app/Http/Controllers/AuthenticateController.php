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
use Illuminate\Auth\Access\AuthorizationException;

class AuthenticateController extends Controller
{
  public function __construct()
  {
      $this->middleware('jwt.auth', ['except' => ['emailAuth', 'phoneAuth', 'refreshToken', 'register']]);
  }

  public function index()
  {
      $users = User::all();
      return $users;
  }

  // 以后实现
  // 对比
  /*
  public function updateAvatar(Request $request) {
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
  */
  public function updateAvatar(Request $request) {
      $this->validate($request, [
          'avatar' => 'required|image'
      ]);
      $user = JWTAuth::parseToken()->authenticate();
      // file_put_contents(public_path().'images/avatars/'.$user->id.'.png',
      //   file_get_contents($request->file('avatar')->getRealPath()));
      
      // todo
      copy($request->file('avatar')->getRealPath(), public_path('images/avatars/'.$user->id));
      $avatar = '/images/avatars/'.$user->id;
      $user->avatar = $avatar;
      $user->save();
      return $avatar;
  }

  public function refreshToken(Request $request){
      $this->validate($request, [
          'token' => 'required'
      ]);

      $token = $request->input('token');

      $newToken = JWTAuth::refresh($token);
      $user = JWTAuth::authenticate($newToken);

      return response()->json(['user' => $user, 'token' => $newToken]);
  }

  public function emailAuth(Request $request)
  {
      // 1.验证输入参数
      $this->validate($request, [
          'email' => 'required|email',
          'password' => 'required'
      ]);

      // 2.获得输入的参数(如果要在这个方法里使用)
      $email = $request->input('email');

      // 3.处理逻辑
      // 验证email和password是否对应
      $credentials = $request->only('email', 'password');

      try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              throw new AuthorizationException();
          }
      } catch (JWTException $e) {
          // something went wrong whilst attempting to encode the token
          return response()->json(['error' => 'could_not_create_token'], 500);
      }

      // 登陆信息无误，确认email是否已经通过邮箱验证
      $user = User::where('email', $request->input('email'))->firstOrFail();
      if ($user != null){
          if ($user->email_verified == 0){
              return reponse()->json(['error' => 'email need to be verified.'], 430);
          }
      }

      // 4.登陆成功，返回json
      return response()->json([
        'user' => $user,
        'token' => $token
      ]);
  }

  public function phoneAuth(Request $request)
  {
      $this->validate($request, [
          'phone' => 'required|regex:/[0-9]+/',
          'password' => 'required'
      ]);

      // grab credentials from the request
      $credentials = $request->only('phone', 'password');

      try {
          // attempt to verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              throw new AuthorizationException();
          }
      } catch (JWTException $e) {
          // something went wrong whilst attempting to encode the token
          return response()->json(['error' => 'could_not_create_token'], 500);
      }

      return response()->json([
        'user' => User::where('phone', $request->input('phone'))->firstOrFail(),
        'token' => $token
      ]);
  }

  public function register(Request $request){
      $this->validate($request, [
          'email' => 'required|email|unique:users,email',
          'password' => 'required|between:6,32',
          'nickname' => 'required|max:32'
      ]);

      $user = new User;
      $user->email = $request->input('email');
      $user->nickname = $request->input('nickname');
      $user->password = Hash::make($request->input('password'));
      $user->save();

      //todo 发邮件验证

      return 'success';
  }
}
