<?php

namespace App\Http\Controllers;

use App\Exceptions\MsgException;
use App\Jobs\Job;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Mail;
use Validator;


class EmailController extends Controller {

    public function __construct() {
        $this->middleware('jwt.auth', ['only' => ['bindEmail']]);
//        $this->middleware('email', ['only' => ['verifyEmail', 'bindEmail']]);
     //   $this->middleware('log', ['only' => ['sendVerifyEmail', 'verifyEmail', 'bindEmail']]);
    }

    /*
     * [POST] sendVerifyEmail
     */
    public function sendVerifyEmail(Request $request) {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $email = $request->input('email');
        $verify = DB::table('email_verifications')->where('email', $email)->get();

        if (count($verify) > 0) {
            // 			如果表中以存在该email对应的项，则先删除
            $this->clearVerification($email);
        }

        $token = $this->generateToken($email);
        $send_time = time();
        $send_at = strftime('%Y-%m-%d %X', $send_time);
        $url='http://localhost/waibao/public/verifyEmail?email='.$email.'&verification_code='.$token;
        // 		一个小时后过期
        $avalible_before = strftime('%Y-%m-%d %X', $send_time + 3600);

        // 		发送邮件
        Mail::send('emails.verification',
            ['token' => $url, 'avalible_before' => $avalible_before],
            function ($message) use ($email) {
                $message->to($email, 'dear')->subject('淘兼职邮箱验证');
            }
        );

        // 		将发送的验证码和过期时间保存到email_verifications表中
        DB::insert('insert into email_verifications (email, token, send_at) values (?,?,?)',
            [$email, $token, $send_at]);

        return 'success';
    }

    /*
     * [POST] verifyEmail
     */
    public function verifyEmail(Request $request) {
        /* validate at middleware */
        $email = $request->input('email');
        $code = $request->input('verification_code');

        $verify = DB::table('verification_code')->where('email',$email)->get();
      if($verify->code ==  $code){
        $user = User::where('email', $request->input('email'))->firstOrFail();
        $user->email_verified = 1;
        $user->save();
      }
        return 'success';
    }

    /*
     * [POST] bindEmail
     */
    public function bindEmail(Request $request) {
        /* validate at middleware */
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->email != null) {
            throw new MsgException('email has binded', 400);
        } else {
            $user->email = $request->input('email');
            $user->email_verified = 1;
            $user->save();
            return 'success';
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return string
     */
    public function companySendWorkMail(Request $request,$id){
        $user = User::findOrFail($id);
//        $email = $user->email;
        $email = '244774097@qq.com';
        $job_id=$request->input("job_id");
        $job = \App\Models\Job::find($job_id);
        $company_user= JWTAuth::parseToken()->authenticate();
        $company = Company::findOrfail($company_user->company_id);
        $data['job_name'] = $job->name;
        $data['contact_person'] = $job->contact_person;
        $data['phone'] = $job->contact;
        $data['company_name'] = $company->name;
        $data['addr']  = $company->address;
        $data['time'] = date('y-m-d',time());
       $flag = Mail::send('emails.work',$data,function ($msg)use($email){

           $msg->to($email)->subject('淘兼职邀请邮箱');
       });

       if ($flag){
           return 'sucess';
       }else
       {
           return 'fail';
       }


   }

    /**
     * @param $email
     * @return string
     */
    private function generateToken($email) {
        // 		return Hash::make($email.date('Ymd').str_random(16));
        return str_random(6);
    }

    /**
     * @param $email
     */
    private function clearVerification($email) {
        DB::delete('delete from email_verifications where email = ?', [$email]);
    }

    /**    *  测试用方法    */
    public function emailSend(Request $request) {
        $order = new Order();
        $where =array('id'=>1);
        $email = getTableClumnValue('users',['id'=>getTableClumnValue('orders',$where,'applicant_id')],'email');
        echo $email;
    }
}
