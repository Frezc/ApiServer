<?php

namespace App\Http\Controllers;

use App\Models\User;
use Curl\Curl;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class SmsController extends Controller {
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['bindPhone']]);
        $this->middleware('sms', ['only' => ['getSmsCode', 'bindPhone', 'resetPassword']]);
    }

    /*
     * [GET] getSmsCode
     */
    public function getSmsCode(Request $request) {
        $this->validate($request, [
            'phone' => 'required|regex:/[0-9]+/'
        ]);

        $phoneNumber = $request->input('phone');

        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('X-LC-Id', env('SMS_APPID', ''));
        $curl->setHeader('X-LC-Key', env('SMS_APPKEY', ''));

        $body = json_encode([
            'mobilePhoneNumber' => $phoneNumber,
            'ttl' => 60
        ]);

        $curl->post('https://api.leancloud.cn/1.1/requestSmsCode', $body);

        return response()->json($curl->response);
    }

    /*
     * [POST] registerByPhone
     */
    public function registerByPhone(Request $request) {
        $this->validate($request, [
            'phone' => 'required|regex:/[0-9]+/|unique:users,phone',
            'password' => 'required|between:6,32',
            'nickname' => 'required|between:1,16'
        ]);

        $nickname = $request->input('nickname');
        $password = $request->input('password');
        $phone = $request->input('phone');

        $user = new User;
        $user->phone = $phone;
        $user->nickname = $nickname;
        $user->password = Hash::make($password);
        $user->save();

        return 'success';
    }

    /*
     * [POST] bindPhone
     */
    public function bindPhone(Request $request) {
        $this->validate($request, [
            'phone' => 'required|regex:/[0-9]+/|unique:users,phone'
        ]);

        $user = JWTAuth::parseToken()->authenticate();
        if ($user->phone != null) {
            return response()->json(['error' => 'phone has binded'], 400);
        } else {
            $user->phone = $request->input('phone');
            $user->save();
            return 'success';
        }
    }

    /*
     * [POST] resetPassword
     */
    public function resetPassword(Request $request) {
        $this->validate($request, [
            'password' => 'required|between:6,32'
        ]);

        $phone = $request->input('phone');
        $password = $request->input('password');

        $user = User::where('phone', $phone)->firstOrFail();
        $user->password = Hash::make($password);
        $user->save();

        return 'success';
    }

    public function test(Request $request) {
        $curl = new Curl();

        $curl->post('http://tjz.frezc.com/auth', [
            'email' => '504021398@qq.com',
            'password' => 'secret'
        ]);

        return response()->json($curl->response);
    }
}
