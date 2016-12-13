<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use JWTAuth;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthenticateController extends Controller {
    public function __construct() {
        $this->middleware('jwt.auth', ['except' => ['emailAuth', 'phoneAuth', 'refreshToken', 'register']]);
    }

    /*
     * [GET] refresh
     */
    public function refreshToken(Request $request) {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $token = $request->input('token');

        $newToken = JWTAuth::refresh($token);

        $user = JWTAuth::authenticate($newToken);
        $user->bindRoleName();
        return response()->json(['user' => $user, 'token' => $newToken]);
    }

    /*
     * [POST] auth
     */
    public function emailAuth(Request $request) {
        // 1.验证输入参数
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        // 2.获得输入的参数(如果要在这个方法里使用)
        $email = $request->input('email');
       
        // 3.处理逻辑
        // 验证email和password是否对应
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new AuthorizationException();
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // 登陆信息无误，确认email是否已经通过邮箱验证
        $user = User::where('email', $request->input('email'))->firstOrFail();
        if ($user != null) {
            if ($user->email_verified == 0) {
                return response()->json(['error' => 'email need to be verified.'], 430);
            }
        }

        $user->bindRoleName();

        // 4.登陆成功，返回json
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /*
     * [POST] authPhone
     */
    public function phoneAuth(Request $request) {
        $this->validate($request, [
            'phone' => 'required|regex:/[0-9]+/',
            'password' => 'required|string|min:6'
        ]);

        // grab credentials from the request
        $credentials = $request->only('phone', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new AuthorizationException();
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = User::where('phone', $request->input('phone'))->firstOrFail();
        $user->bindRoleName();

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /*
     * [POST] register
     */
    public function register(Request $request) {
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
