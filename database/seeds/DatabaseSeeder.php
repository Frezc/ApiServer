<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Faker\Factory as Faker;
use App\Company;
use App\Job;
use App\User;
use App\Resume;
use App\JobCompleted;
use App\JobApply;
use App\JobEvaluate;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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

        $userNum = 30;
        $companyNum = 10;
        $jobNum = 50;
        $resumeNum = 10;
        $jobCompletedNum = 60;
        $jobApplyNum = 60;
        $jobEvaluate = 20;

        $faker = Faker::create('zh_CN');
        User::create([
            'avatar' => '/images/avatars/default',
            'email' => '504021398@qq.com',
            'phone' => $faker->unique()->phoneNumber,
            'password' => Hash::make('secret'),
            'nickname' => $faker->name,
            'sign' => $faker->sentence(6,false),
            'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
            'location'=> $faker->address,
            'sex'=> $faker->numberBetween($min = 0, $max = 1),
            'email_verified'=> 1
        ]);
        foreach (range(1, $userNum - 1) as $index) {
            User::create([
                'avatar' => null,
                'email' => $faker->unique()->freeEmail,
                'phone' => $faker->unique()->phoneNumber,
                'password' => Hash::make('secret'),
                'nickname' => $faker->name,
                'sign' => $faker->sentence(6, false),
                'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'location'=> $faker->address,
                'sex'=> $faker->numberBetween($min = 0, $max = 1),
                'email_verified'=> $faker->numberBetween($min = 0, $max = 1)
            ]);
        }

        foreach (range(1, $companyNum) as $index) {
            Company::create([
                'name' => $faker->unique()->company,
                'url'  => $faker->url,
                'address'=> $faker->address,
                'logo' => null,
                'description' => $faker->catchPhrase,
                'contact_person' => $faker->name,
                'contact' => $faker->phoneNumber,
            ]);
        }

        foreach (range(1, $jobNum) as $index) {
            $company = Company::findOrNew($faker->numberBetween($min = 1, $max = $companyNum));

            Job::create([
                'salary' => ($faker->numberBetween($min = 4, $max = 100)*10).'元/天',
                'description' => $faker->catchPhrase,
                'number' => $faker->numberBetween($min = 1, $max = 1000),
                'number_applied' => 0,
                'visited'=> $faker->numberBetween($min = 0, $max = 1000),
                'time'=> $faker->randomElement($array = array ('一年','一个月','六个月')),
                'name'=> $faker->jobTitle,
                'company_id'=> $company->id,
                'active'=> 1,
                'company_name'=> $company ->name,
            ]);
        }

        foreach (range(1, $resumeNum) as $index) {
            Resume::create([
                'user_id' => $faker->numberBetween($min = 1, $max = $userNum),
                'title'  => $faker->jobTitle,
                'name'=> $faker->name,
                'photo' => null,
                'school' => $faker->randomElement($array = array ('杭州电子科技大学','春田花花幼稚园','断罪小学')),
                'birthday' =>  $faker->date($format = 'Y-m-d', $max = 'now'),
                'sex' => $faker->numberBetween($min = 0, $max = 1),
                'expect_location'=> $faker->address,
                'introduction'=> $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $jobCompletedNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $resumeNum));

            JobCompleted::create([
                'user_id' => $resume->user_id ,
                'job_id'  => $faker->numberBetween($min = 1, $max = $jobNum),
                'resume_id'=> $resume->id,
                'description' =>  $faker->sentence(4, false),
            ]);
        }

        foreach (range(1, $jobApplyNum) as $index) {
            $resume = Resume::findOrNew($faker->numberBetween($min = 1, $max = $resumeNum));

            JobApply::create([
                'user_id' => $resume->user_id ,
                'job_id'  => $faker->numberBetween($min = 1, $max = $jobNum),
                'resume_id'=> $resume->id,
                'description' =>  $faker->sentence(4, false),
                'status' => $faker->numberBetween($min = 0, $max = 1),
            ]);
        }

        foreach (range(1, $jobEvaluate) as $index) {
            $jc = JobCompleted::findOrNew($faker->numberBetween($min = 1, $max = $jobCompletedNum));

            JobEvaluate::create([
                'user_id' => $jc->user_id,
                'job_id'  => $jc->job_id,
                'comment' =>  $faker->catchPhrase,
                'score' => $faker->numberBetween($min = 0, $max = 5),
            ]);
        }
    }
}
