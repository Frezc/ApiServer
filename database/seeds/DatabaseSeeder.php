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
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    private $userNum = 30;
    private $companyNum = 10;
    private $jobNum = 50;
    private $jobTimeNum = 100;
    private $resumeNum = 10;
    private $jobCompletedNum = 60;
    private $jobApplyNum = 60;
    private $jobEvaluate = 20;
    private $userEvaluate = 20;
    private $expectJobNum = 50;
    private $expectTimeNum = 100;
    private $userCompanyNum = 20;
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
        // factory(App\Company::class,20)->create();
        // factory(App\Job::class,20)->create();
        // factory(App\User::class,20)->create();
        // factory(App\Resume::class,10)->create();
        // factory(App\JobCompleted::class,80)->create();
        // factory(App\JobApply::class,80)->create();
        // factory(App\JobEvaluate::class,20)->create();
        // DB::table('resumes')->update([
        //     'photo' => 'http://static.frezc.com/static/resume_photos/default'
        // ]);

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
            if ($faker->boolean) {
                $company = Company::find($index % $this->companyNum + 1);
            }

            User::create([
                'avatar' => Storage::url('images/test.jpg'),
                'email' => $faker->unique()->freeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'password' => Hash::make('secret'),
                'nickname' => $faker->name,
                'sign' => $faker->sentence(6, false),
                'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'location' => $faker->address,
                'sex' => $faker->numberBetween($min = 0, $max = 1),
                'email_verified' => $faker->numberBetween($min = 0, $max = 1),
                'company_id' => $company ? $company->id : null,
                'company_name' => $company ? $company->name : null
            ]);
        }

        foreach (range(1, $this->userCompanyNum) as $i) {
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $this->userNum));
            $company = Company::find($faker->numberBetween($min = 1, $max = $this->companyNum));
            UserCompany::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);
        }

        foreach (range(1, $this->jobNum) as $index) {
            $company = Company::findOrNew($faker->numberBetween($min = 1, $max = $this->companyNum));
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $this->userNum));
            $hasCom = $faker->boolean;
            Job::create([
                'salary_type' => $faker->numberBetween($min = 1, $max = 2),
                'salary' => '100',
                'description' => $faker->catchPhrase,
                'visited' => $faker->numberBetween($min = 0, $max = 1000),
                'name' => $faker->jobTitle,
                'company_id' => $hasCom ? $company->id : null,
                'company_name' => $hasCom ? $company->name : null,
                'creator_id' => $user->id,
                'creator_name' => $user->nickname,
                'contact' => $faker->phoneNumber,
                'active' => 1,
                'number_evaluate' => $faker->numberBetween($min = 1, $max = 100),
                'average_score' => 4.2
            ]);
        }

        foreach (range(1, $this->jobTimeNum) as $i) {
            $number = $faker->numberBetween($min = 1, $max = 100);
            $start_at = time() + 60 * 60 * 24 * $faker->numberBetween($min = 1, $max = 180);
            $st = $faker->numberBetween($min = 1, $max = 2);
            $salary = $st == 2 ? $faker->numberBetween($min = 1, $max = 1000) : 0;
            JobTime::create([
                'job_id' => $faker->numberBetween($min = 1, $max = $this->jobNum),
                'number' => $number,
                'number_applied' => $faker->numberBetween($min = 0, $max = $number),
                'salary_type' => $st,
                'salary' => $salary,
                'apply_end_at' => $faker->numberBetween($min = $start_at - 60 * 60 * 2, $max = $start_at),
                'start_at' => $start_at,
                'end_at' => $faker->numberBetween($min = $start_at, $max = $start_at + 60 * 60 * 3)
            ]);
        }

        foreach (range(1, $this->expectJobNum) as $index) {
            $user = User::findOrFail($faker->numberBetween($min = 1001, $max = 1000 + $this->userNum));
            ExpectJob::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'title' => $faker->catchPhrase,
                'name' => $faker->name,
                'photo' => Storage::url('images/test.jpg'),
                'school' => $faker->randomElement($array = array('杭州电子科技大学', '春田花花幼稚园', '断罪小学')),
                'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'sex' => $faker->numberBetween($min = 0, $max = 1),
                'expect_location' => $faker->address,
                'introduction' => $faker->sentence(4, false),
                'is_public' => 1
            ]);
        }

        foreach (range(1, $this->expectTimeNum) as $i) {
            $time = time() + 60 * 60 * 24 * $faker->numberBetween($min = 1, $max = 180);
            ExpectTime::create([
                'expect_job_id' => $faker->numberBetween($min = 1, $max = $this->expectJobNum),
                'year' => date('Y', $time),
                'month' => date('n', $time),
                'dayS' => date('j', $time),
                'dayE' => date('j', $time + 60 * 60 * 24 * 7),
//                'hourS' => 8,
//                'hourE' => 20,
//                'minuteS' => 30
            ]);
        }

        foreach (range(1, $this->resumeNum) as $index) {
            Resume::create([
                'user_id' => $faker->numberBetween($min = 1001, $max = 1000 + $this->userNum),
                'title' => $faker->jobTitle,
                'name' => $faker->name,
                'photo' => Storage::url('images/test.jpg'),
                'school' => $faker->randomElement($array = array('杭州电子科技大学', '春田花花幼稚园', '断罪小学')),
                'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'sex' => $faker->numberBetween($min = 0, $max = 1),
                'expect_location' => $faker->address,
                'introduction' => $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $this->orderNum) as $i) {
            $jobTime = JobTime::find($faker->numberBetween($min = 1, $max = $this->jobTimeNum));
            $job = Job::find($jobTime->job_id);
            $expectJob = ExpectJob::find($faker->numberBetween($min = 1, $max = $this->expectJobNum));

            $status = $faker->numberBetween($min = 0, $max = 3);
            $closeType = null;
            if ($status == 3) $closeType = $faker->numberBetween($min = 1, $max = 3);
            Order::create([
                'job_id' => $job->id,
                'job_name' => $job->name,
                'job_time_id' => $jobTime->id,
                'expect_job_id' => $expectJob->id,
                'applicant_id' => $expectJob->user_id,
                'applicant_name' => $expectJob->user_name,
                'recruiter_type' => $job->company_id ? 1 : 0,
                'recruiter_id' => $job->company_id ? $job->company_id : $job->creator_id,
                'recruiter_name' => $job->company_id ? $job->company_name : $job->creator_name,
                'status' => $status,
                'close_type' => $closeType,
                'applicant_check' => $status == 0 ? $faker->numberBetween($min = 0, $max = 1) : 1,
                'recruiter_check' => $status == 0
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

        foreach (range(1, $this->userEvaluate) as $index) {
            $order = Order::find($faker->numberBetween($min = 1, $max = $this->orderNum));
            if ($order->recruiter_type == 1) {
                $user = User::where('company_id', $order->recruiter_id)->first();
                $user_id = $user->id;
                $user_name = $user->nickname;
            } else {
                $user_id = $order->recruiter_id;
                $user_name = $order->recruiter_name;
            }

            UserEvaluate::create([
                'user_id' => $user_id,
                'user_name' => $user_name,
                'order_id' => $order->id,
                'target_id' => $order->applicant_id,
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
