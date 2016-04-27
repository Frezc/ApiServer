<?php

namespace App\Http\Middleware;

use Closure;
use Validator;
use DB;

class EmailMiddleware
{
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

      if ($v->fails()) {
          return response()->json(['error' => $v->errors()], 400);
      }

      $veri = DB::table('email_verifications')->where('email', $request->input('email'))->first();

      if ($veri == null || $veri->token != $request->input('verification_code')){
        return response()->json(['error' => 'wrong code'], 430);
      }

      if (abs(time() - strtotime($veri->send_at)) > 3600) {
        return response()->json(['error' => 'time exceed'], 430);
      }

      $this->clearVerification($request->input('email'));

      return $next($request);
    }

    private function clearVerification($email){
      DB::delete('delete from email_verifications where email = ?', [$email]);
    }
}
