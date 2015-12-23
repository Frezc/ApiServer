<?php

namespace App\Http\Middleware;

use Closure;
use Validator;
use Dingo\Api\Routing\Helpers;

class SmsMiddleware
{
    use Helpers;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $v = Validator::make($request->all(), [
          'phone' => 'required|regex:/[0-9]+/|unique:users,phone',
          'verification_code' => 'required|regex:/[0-9]+/'
      ]);

      if ($v->fails())
      {
        return $this->response->error($v->errors(), 400);
      }

      //验证短信验证码
      $curl = new Curl();
      $curl->setHeader('Content-Type', 'application/json');
      $curl->setHeader('X-LC-Id', env('SMS_APPID', ''));
      $curl->setHeader('X-LC-Key', env('SMS_APPKEY', ''));

      $curl->post('https://api.leancloud.cn/1.1/verifySmsCode/'.$request->input('verification_code').'?mobilePhoneNumber='.$request->input('phone'));
      // dd($curl->response);
      if (isset($curl->response->code)){
        return $this->response->error('无效的验证码', 400);
      }

      return $next($request);
    }
}
