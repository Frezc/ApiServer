<?php

namespace App\Http\Middleware;

use Closure;
use Validator;
use DB;
use Dingo\Api\Routing\Helpers;

class EmailMiddleware
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
          'email' => 'required|email|exists:email_verifications,email',
          'verification_code' => 'required|string'
      ]);

      if ($v->fails()){
          return $this->response->error($v->errors(), 400);
      }

      $veri = DB::table('email_verifications')->where('email', $request->input('email'))->first();

      if ($veri == null || $veri->token != $request->input('verification_code')){
        return $this->response->error('wrong code', 430);
      }

      if (abs(time() - strtotime($veri->send_at)) > 3600) {
        return $this->response->error('time exceed', 431);
      }

      $this->clearVerification($request->input('email'));

      return $next($request);
    }

    private function clearVerification($email){
      DB::delete('delete from email_verifications where email = ?', [$email]);
    }
}
