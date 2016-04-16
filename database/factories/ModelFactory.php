<?php
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function ($faker) {
    return [
        'avatar' => '/images/avatars/default',
        'email' => $faker->unique()->freeEmail,
        'phone' => $faker->unique()->phoneNumber,
        'password' => Hash::make('secret'),
        'nickname' => $faker->name,
        'sign' => $faker->sentence(6,false),
        'birthday' => $faker->date($format = 'Y-m-d', $max = 'now'),
        'location'=> $faker->address,
        'sex'=> $faker->numberBetween($min = 0, $max = 1),
        'email_verified'=> $faker->numberBetween($min = 0, $max = 1)

    ];
});
$factory->define(App\Company::class, function ($faker) {
    return [
        'name' => $faker->unique()->company,
        'url'  => $faker->url,
        'address'=> $faker->address,
        'logo' =>  $faker->url,
        'description' => $faker->catchPhrase,
        'contact_person' => $faker->name,
        'contact' => $faker->phoneNumber,

    ];
});
$factory->define(App\JobApply::class, function ($faker) {
    $resume = \App\Resume::findOrNew($faker->numberBetween($min = 1, $max = 35));
    while (!$resume->user_id||!$resume->id)
    {
        $resume = \App\Resume::findOrNew($faker->numberBetween($min = 1, $max = 35));
    }
    return [
        'user_id' => $resume->user_id ,
        'job_id'  => $faker->numberBetween($min = 1, $max = 47),
        'resume_id'=> $resume->id,
        'description' =>  $faker->sentence(4,false),
        'status' => $faker->numberBetween($min = 0, $max = 1),

    ];
});
$factory->define(App\JobCompleted::class, function ($faker) {
    $resume = \App\Resume::findOrNew($faker->numberBetween($min = 1, $max = 35));
    while (!$resume->user_id||!$resume->id)
    {
        $resume = \App\Resume::findOrNew($faker->numberBetween($min = 1, $max = 35));
    }
    return [
        'user_id' => $resume->user_id ,
        'job_id'  => $faker->numberBetween($min = 1, $max = 47),
        'resume_id'=> $resume->id,
        'description' =>  $faker->sentence(4,false),

    ];
});
$factory->define(App\JobEvaluate::class, function ($faker) {
    $jc = \App\JobCompleted::findOrNew($faker->numberBetween($min = 1, $max = 100));
    while (!$jc->user_id)
    {
        $jc = \App\Resume::findOrNew($faker->numberBetween($min = 1, $max = 100));
    }
    return [
        'user_id' => $jc->user_id,
        'job_id'  => $jc->job_id,
        'comment' =>  $faker->catchPhrase,
        'score' => $faker->numberBetween($min = 0, $max = 5),
    ];
});
$factory->define(App\Job::class, function ($faker) {

    $company =\App\Company::findOrNew($faker->numberBetween($min = 0, $max = 39));
    //dd($company);
    while ($company->id==null)
    {
        echo 'failed once','<br>';
        $company =\App\Company::findOrNew($faker->numberBetween($min = 0, $max = 39));
    }
    return [
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
    ];
});
$factory->define(App\Resume::class, function ($faker) {
    return [
        'user_id' => $faker->numberBetween($min = 0, $max = 12),
        'title'  => $faker->jobTitle,
        'name'=> $faker->name,
        'photo' =>  '/images/avatars/default',
        'school' => $faker->randomElement($array = array ('杭州电子科技大学','春田花花幼稚园','断罪小学')),
        'birthday' =>  $faker->date($format = 'Y-m-d', $max = 'now'),
        'sex' => $faker->numberBetween($min = 0, $max = 1),
        'expect_location'=> $faker->address,
        'introduction'=> $faker->sentence(4,false),
    ];
});
