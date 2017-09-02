<?php

use App\Models\Company;
use App\Models\ExpectJob;
use App\Models\ExpectTime;
use App\Models\Job;
use App\Models\JobApply;
use App\Models\JobCompleted;
use App\Models\JobEvaluate;
use App\Models\JobTime;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Resume;
use App\Models\Uploadfile;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\UserEvaluate;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    private $userNum = 30;
    private $companyNum = 10;
    private $jobNum = 50;
    private $jobTimeNum = 50;
    private $resumeNum = 30;
    private $jobCompletedNum = 60;
    private $jobApplyNum = 60;
    private $jobEvaluate = 20;
    private $userEvaluate = 20;
    private $expectJobNum = 50;
    private $expectTimeNum = 50;
    private $userCompanyNum = 10;
    private $orderNum = 100;
    private $rnaNum = 30;
    private $caNum = 25;
    private $feedbackNum = 30;
    private $reportNum = 30;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $this->call(PresetSeeder::class);

        $faker = Faker::create('zh_CN');

        foreach (range(1, $this->companyNum) as $index) {
            Company::create([
                'name' => $faker->unique()->company,
                'url' => $faker->url,
                'address' => $faker->address,
                'logo' => Storage::url('images/test.jpg'),
                'description' => $faker->catchPhrase,
                'contact_person' => $faker->name,
                'contact' => $faker->phoneNumber,
            ]);
        }

        foreach (range(3, $this->userNum) as $index) {
            $company = null;
            if ($index < 13) {
                $company = Company::find($index % $this->companyNum + 1);
            }else  $company=null;

            User::create([
                'avatar' => Storage::url('images/test.jpg'),
                'email' => $faker->unique()->freeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'password' => Hash::make('secret'),
                'nickname' => $faker->name,
                'email_verified' => $faker->numberBetween($min = 0, $max = 1),
                'company_id' => $company ? $company->id : null,
                'company_name' => $company ? $company->name : null,
                'role_id'=> $company ?  2 : 1,
                'money' => $faker->numberBetween($min = 0, $max = 100000)
            ]);

        }

        foreach (range(1, $this->userCompanyNum) as $i) {
            $user = User::find(1002+$i);
            UserCompany::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'company_id' => $user->company_id,
                'company_name' => $user->company_name,
            ]);
        }


        foreach (range(1, $this->jobNum) as $index) {
            $user = User::find($faker->numberBetween($min = 1003, $max = 1012));
            $company = Company::find($user->company_id);
            $type = \App\Models\JobType::find($faker->numberBetween($min = 1, $max = 24));
            Job::create([
                'salary_type' => $faker->randomElement($array = array('/天','/周','/小时','/月')),
                'salary' => rand(100,200),
                'description' => $faker->catchPhrase,
                'visited' => $faker->numberBetween($min = 0, $max = 1000),
                'name' =>$faker->randomElement($array = array('发传单', '家教', '餐厅服务员', 'KTV服务员','卫生打扫',
                    '送外卖', '保姆', '小孩接送', '高数辅导', '生物辅导', '物理辅导',
                    '英语辅导', '初中辅导', '化学辅导', '临时导游', '临时城管', '超市导购员')),
                'company_id' =>$company->id,
                'company_name' =>  $company->name,
                'creator_id' => $user->id,
                'creator_name' => $user->nickname,
                'salary_pay_way' => $faker->randomElement($array = array('周结算','月结算','天结算','小时结算')),
                'contact' => $faker->phoneNumber,
                'required_number' =>rand(2,10),
                'contact_person' => $faker->name,
                'pay_way' => $faker->randomElement($array = array('现金','支付宝','微信','钱包支付')),
                'active' => 1,
//                'number_evaluate' => $faker->numberBetween($min = 1, $max = 100),
//                'average_score' => 4.2,
                'type' => $type->name,
                'city' => $faker->city,
                'address' => $faker->address
            ]);
        }

        foreach (range(1, 50) as $i) {
            $start_at = Carbon::now()->addDays($faker->numberBetween($min = 1, $max = 180));
            JobTime::create([
                'job_id' => $i,
                'apply_end_at' => $start_at->subDays($faker->numberBetween($min = 0, $max = 2))->toDateTimeString(),
                'start_at' => $start_at->toDateTimeString(),
                'end_at' => $start_at->addDays($faker->numberBetween($min = 1, $max = 3))->toDateTimeString()
            ]);
        }


        foreach (range(1, 18) as $index) {
            $user = User::findOrFail(1012+$index);
            ExpectJob::create([
                'user_id' => $user->id,
                'type' => $faker->catchPhrase,
                'salary' => '50',
                'salary_type' => '/小时',
                'time' => '2015/8/9',
                'city' => $faker->city,
                'expect_location' => $faker->address,
            ]);
        }

        foreach (range(1, 18) as $i) {
            $start_at = Carbon::now()->addDays($faker->numberBetween($min = 1, $max = 180));
            ExpectTime::create([
                'expect_job_id' => $i,
                'start_at' => $start_at,
                'end_at' => (new Carbon($start_at))->addDays(3)->toDateString()
            ]);
        }

        foreach (range(1, $this->resumeNum) as $index) {
            Resume::create([
                'user_id' => 1000+$index,
                'title' => $faker->jobTitle,
                'name' => $faker->name,
                'photo' => Storage::url('images/test.jpg'),
                'school' => $faker->randomElement($array = array('杭州电子科技大学', '春田花花幼稚园', '断罪小学')),
                'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'sex' => $faker->numberBetween($min = 0, $max = 1),
                'city' => $faker->city,
                'weight' => 50,
                'flag' => 'php',
                'expect_location' => $faker->address,
                'introduction' => $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $this->orderNum) as $i) {
            $jobTime = JobTime::find($faker->numberBetween($min = 1, $max = 18));
             $user = User::find($faker->numberBetween($min=1010,$max=1027));
            $job = Job::find($jobTime->job_id);
            $status = $faker->numberBetween($min = 0, $max = 3);
            $closeType = null;
            if ($status == 3) $closeType = $faker->numberBetween($min = 1, $max = 3);
            Order::create([
                'job_id' => $job->id,
                'job_name' => $job->name,
                'job_time_id' => $jobTime->id,
                'pay_way' => $job->pay_way,
                'salary' => 100,
                'salary_type' => '元/小时',
                'applicant_id' => $user->id,
                'applicant_name' => $user->nickname,
                'recruiter_type' => $job->company_id ? 1 : 0,
                'recruiter_id' => $job->creator_id,
                'recruiter_name' => $job->creator_name,
                'status' => $status,
                'close_type' => $closeType,
                'applicant_check' => $status == 0 ? $faker->numberBetween($min = 0, $max = 1) : 1,
                'recruiter_check' => $status == 0 ? $faker->numberBetween($min = 0, $max = 1) : 1
            ]);
        }

        foreach (range(1, $this->jobCompletedNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $this->resumeNum));

            JobCompleted::create([
                'user_id' => $resume->user_id,
                'job_id' => $faker->numberBetween($min = 1, $max = $this->jobNum),
                'resume_id' => $resume->id,
                'description' => $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $this->jobApplyNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $this->resumeNum));

            JobApply::create([
                'user_id' => $resume->user_id,
                'job_id' => $faker->numberBetween($min = 1, $max = $this->jobNum),
                'resume_id' => $resume->id,
                'resume_id' => $resume->id,
                'description' => $faker->sentence(4, false),
                'status' => $faker->numberBetween($min = 0, $max = 1),
            ]);
        }

        foreach (range(1, $this->jobEvaluate) as $index) {
            $order = Order::find($faker->numberBetween($min = 1, $max = $this->orderNum));

            JobEvaluate::create([
                'user_id' => $order->applicant_id,
                'user_name' => $order->applicant_name,
                'order_id' => $order->id,
                'job_id' => $order->job_id,
                'comment' => $faker->catchPhrase,
                'score' => $faker->numberBetween($min = 1, $max = 5),
                'pictures' => Storage::url('images/test.jpg')
            ]);
        }


        Message::create([
            'sender_id' => 1,
            'sender_name' => '工作助手',
            'receiver_id' => 1001,
            'type' => 'notification',
            'content' => '简要消息',
            'unread' => 9
        ]);

        foreach (range(1, 9) as $i) {
            Notification::create([
                'message_id' => 1,
                'content' => $faker->realText($maxNbChars = 200)
            ]);
        }

        $admin = User::find(1001);
        foreach (range(2, $this->userNum) as $i) {
            $user = User::find(1000 + $i);
            Message::create([
                'sender_id' => $user->id,
                'sender_name' => $user->nickname,
                'receiver_id' => 1001,
                'type' => 'conversation',
                'content' => 'frezc：你好啊~',
                'unread' => 1
            ]);
            Message::create([
                'sender_id' => $admin->id,
                'sender_name' => $admin->nickname,
                'receiver_id' => $user->id,
                'type' => 'conversation',
                'content' => '你好啊~',
                'unread' => 1
            ]);
            \App\Models\Conversation::create([
                'conversation_id' => $admin->id . 'c' . $user->id,
                'sender_id' => $user->id,
                'sender_name' => $user->nickname,
                'content' => '你好呀！'
            ]);
            \App\Models\Conversation::create([
                'conversation_id' => $admin->id . 'c' . $user->id,
                'sender_id' => $admin->id,
                'sender_name' => $admin->nickname,
                'content' => '你好！'
            ]);
        }

        Uploadfile::create([
            'path' => Storage::url('images/test.jpg'),
            'uploader_id' => 1001
        ]);

        Uploadfile::create([
            'path' => Storage::url('images/test.jpg'),
            'uploader_id' => 1002
        ]);

        foreach (range(1, $this->rnaNum) as $i) {
            $user = User::find(1000 + $i);
            \App\Models\RealNameVerification::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'real_name' => $faker->name,
                'id_number' => $this->randomNumber(18),
                'verifi_pic' => Storage::url('images/test.jpg')
            ]);
        }

        foreach (range(1, $this->caNum) as $i) {
            $user = User::find(1000 + $i);
            \App\Models\CompanyApply::create([
                "user_id" => $user->id,
                "user_name" => $user->nickname,
                "name" => $faker->unique()->company,
                "url" => $faker->url,
                "address" => $faker->address,
                "logo" => Storage::url('images/test.jpg'),
                "description" => $faker->sentence(6, false),
                "contact_person" => $faker->name,
                "contact" => $faker->phoneNumber,
                "business_license" => Storage::url('images/test.jpg')
            ]);
        }

        foreach (range(1, $this->feedbackNum) as $i) {
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $this->userNum));
            $pic = Storage::url('images/test.jpg');
            \App\Models\Feedback::create([
                "user_id" => $user->id,
                "user_name" => $user->nickname,
                'content' => $faker->catchPhrase,
                'p1' => $pic,
                'p2' => $pic,
                'p3' => $pic,
                'p4' => $pic,
                'p5' => $pic,
                'type' => $faker->numberBetween($min = 1, $max = 4)
            ]);
        }

        foreach (range(1, $this->reportNum) as $i) {
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $this->userNum));
            $type = $faker->randomElement(['order', 'user', 'company', 'job', 'expect_job']);
            \App\Models\Report::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'content' => $faker->catchPhrase,
                'pictures' => Storage::url('images/test.jpg'),
                'target_type' => $type,
                'target_id' => $this->randomTypeId($faker, $type)
            ]);
        }
    }

    private function randomNumber($length) {
        $a = '';
        for ($i = 0; $i < $length; $i++) {
            $a .= mt_rand(0, 9);
        }
        return $a;
    }

    private function randomTypeId($faker, $type) {
        switch ($type) {
            case 'order':
                return $faker->numberBetween($min = 1, $max = $this->orderNum);
            case 'user':
                return $faker->numberBetween($min = 1001, $max = 1000 + $this->userNum);
            case 'company':
                return $faker->numberBetween($min = 1, $max = $this->companyNum);
            case 'job':
                return $faker->numberBetween($min = 1, $max = $this->jobNum);
            case 'expect_job':
                return $faker->numberBetween($min = 1, $max = $this->expectJobNum);
        }
    }
}
