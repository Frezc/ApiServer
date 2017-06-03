<?php

namespace App\Http\Controllers;

use App\Models\SmsCodeVerification;
use App\Models\User;
use Curl\Curl;
use Hash;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class SmsController extends Controller {
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['bindPhone']]);
        $this->middleware('sms', ['only' => [ 'bindPhone', 'resetPassword','']]);
    }

    /*
     * [GET] getSmsCode
     */
    public function  getSmsCode(Request $request){
        $this->validate($request,[
            'phone'=>'required |regex:/[0-9]+/|'
        ]);
        $phone=$request->input('phone');
        $smgcode=rand(99999,999999);
        DB::table('sms_code_verifications')->where('phone','like', '%' . $phone . '%')->delete();
        $ch=curl_init();
        $url='http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=C17975840&password=5b3d938c8f8a49e5e5cf7474f0ddd2a3';
        $url=$url.'&mobile='.$phone.'&content=您的验证码是：'.$smgcode.'。请不要把验证码泄露给其他人。';
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $xmlstr= curl_exec($ch);
        curl_close($ch);
        $xml= simplexml_load_string($xmlstr);
        $json=json_encode($xml);
        $json_Array=json_decode($json, true);
        if( $json_Array['code']==2){
            $smsVirification = new SmsCodeVerification();
            $smsVirification->phone=$phone;
            $smsVirification->code=$smgcode;
            $smsVirification->save();
            return 'success';
        }else{
            echo '发送失败';
        }
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
        $smscode=$request->input('code');
        $nickname = $request->input('nickname');
        $password = $request->input('password');
        $phone = $request->input('phone');
        $code=DB::table('sms_code_verifications')->where('phone', $phone )->value('code');
        if($smscode==$code){
            $user = new User;
            $user->phone = $phone;
            $user->role_id = 1;
            $user->nickname = $nickname;
            $user->password = Hash::make($password);
            $user->save();
            return 'success';
        }else{
            return '验证码不对';
        }
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
