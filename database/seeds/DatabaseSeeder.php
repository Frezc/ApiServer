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
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
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

        $userNum = 30;
        $companyNum = 10;
        $jobNum = 50;
        $jobTimeNum = 100;
        $resumeNum = 10;
        $jobCompletedNum = 60;
        $jobApplyNum = 60;
        $jobEvaluate = 20;
        $expectJobNum = 50;
        $expectTimeNum = 100;
        $userCompanyNum = 20;
        $orderNum = 100;
        $rnaNum = 30;
        $caNum = 25;

        $faker = Faker::create('zh_CN');

        foreach (range(3, $userNum) as $index) {
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
                'email_verified' => $faker->numberBetween($min = 0, $max = 1)
            ]);
        }

        foreach (range(1, $companyNum) as $index) {
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

        foreach (range(1, $userCompanyNum) as $i) {
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $userNum));
            $company = Company::find($faker->numberBetween($min = 1, $max = $companyNum));
            UserCompany::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);
        }

        foreach (range(1, $jobNum) as $index) {
            $company = Company::findOrNew($faker->numberBetween($min = 1, $max = $companyNum));
            $user = User::find($faker->numberBetween($min = 1001, $max = 1000 + $userNum));

            Job::create([
                'salary_type' => 1,
                'salary' => '100',
                'description' => $faker->catchPhrase,
                'visited' => $faker->numberBetween($min = 0, $max = 1000),
                'name' => $faker->jobTitle,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'creator_id' => $user->id,
                'creator_name' => $user->nickname,
                'active' => 1,
            ]);
        }

        foreach (range(1, $jobTimeNum) as $i) {
            $number = $faker->numberBetween($min = 1, $max = 100);
            $start_at = time() + 60 * 60 * 24 * $faker->numberBetween($min = 1, $max = 180);
            JobTime::create([
                'job_id' => $faker->numberBetween($min = 1, $max = $jobNum),
                'number' => $number,
                'number_applied' => $faker->numberBetween($min = 0, $max = $number),
                'start_at' => $start_at,
                'end_at' => $faker->numberBetween($min = $start_at, $max = $start_at + 60 * 60 * 3)
            ]);
        }

        foreach (range(1, $expectJobNum) as $index) {
            $user = User::findOrFail($faker->numberBetween($min = 1001, $max = 1000 + $userNum));
            ExpectJob::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
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

        foreach (range(1, $expectTimeNum) as $i) {
            $time = time() + 60 * 60 * 24 * $faker->numberBetween($min = 1, $max = 180);
            ExpectTime::create([
                'expect_job_id' => $faker->numberBetween($min = 1, $max = $expectJobNum),
                'year' => date('Y', $time),
                'month' => date('n', $time),
                'dayS' => date('j', $time),
                'dayE' => date('j', $time + 60 * 60 * 24 * 7),
                'hourS' => 8,
                'hourE' => 20,
                'minuteS' => 30
            ]);
        }

        foreach (range(1, $resumeNum) as $index) {
            Resume::create([
                'user_id' => $faker->numberBetween($min = 1001, $max = 1000 + $userNum),
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

        foreach (range(1, $orderNum) as $i) {
            $jobTime = JobTime::find($faker->numberBetween($min = 1, $max = $jobTimeNum));
            $job = Job::find($jobTime->job_id);
            $expectJob = ExpectJob::find($faker->numberBetween($min = 1, $max = $expectJobNum));

            $status = $faker->numberBetween($min = 0, $max = 3);
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
                'applicant_check' => $status == 0 ? $faker->numberBetween($min = 0, $max = 1) : 1,
                'recruiter_check' => $status == 0
            ]);
        }

        foreach (range(1, $jobCompletedNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $resumeNum));

            JobCompleted::create([
                'user_id' => $resume->user_id,
                'job_id' => $faker->numberBetween($min = 1, $max = $jobNum),
                'resume_id' => $resume->id,
                'description' => $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $jobApplyNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $resumeNum));

            JobApply::create([
                'user_id' => $resume->user_id,
                'job_id' => $faker->numberBetween($min = 1, $max = $jobNum),
                'resume_id' => $resume->id,
                'description' => $faker->sentence(4, false),
                'status' => $faker->numberBetween($min = 0, $max = 1),
            ]);
        }

        foreach (range(1, $jobEvaluate) as $index) {
            $jc = JobCompleted::findOrNew($faker->numberBetween($min = 1, $max = $jobCompletedNum));

            JobEvaluate::create([
                'user_id' => $jc->user_id,
                'job_id' => $jc->job_id,
                'comment' => $faker->catchPhrase,
                'score' => $faker->numberBetween($min = 0, $max = 5),
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
        foreach (range(2, $userNum) as $i) {
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

        foreach (range(1, $rnaNum) as $i) {
            $user = User::find(1000 + $i);
            \App\Models\RealNameVerification::create([
                'user_id' => $user->id,
                'user_name' => $user->nickname,
                'real_name' => $faker->name,
                'id_number' => $this->randomNumber(18),
                'verifi_pic' => Storage::url('images/test.jpg'),
                "message" => ""
            ]);
        }

        foreach (range(1, $caNum) as $i) {
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
                "business_license" => Storage::url('images/test.jpg'),
                "message" => "",
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
}
