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

$factory->define(App\models\User::class, function ($faker) {
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
$factory->define(App\models\Company::class, function ($faker) {
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
$factory->define(App\models\JobApply::class, function ($faker) {
    $resume = \App\models\Resumes::findOrNew($faker->numberBetween($min = 1, $max = 35));
    while ($resume->getUserId()==null||$resume->getId()==null)
    {
        $resume = \App\models\Resumes::findOrNew($faker->numberBetween($min = 1, $max = 35));
    }
    return [
        'user_id' => $resume->getUserId() ,
        'job_id'  => $faker->numberBetween($min = 1, $max = 47),
        'resume_id'=> $resume->getId(),
        'description' =>  $faker->sentence(4,false),
        'status' => $faker->numberBetween($min = 0, $max = 1),

    ];
});
$factory->define(App\models\JobCompleted::class, function ($faker) {
    $resume = \App\models\Resumes::findOrNew($faker->numberBetween($min = 1, $max = 35));
    while ($resume->getUserId()==null||$resume->getId()==null)
    {
        $resume = \App\models\Resumes::findOrNew($faker->numberBetween($min = 1, $max = 35));
    }
    return [
        'user_id' => $resume->getUserId() ,
        'job_id'  => $faker->numberBetween($min = 1, $max = 47),
        'resume_id'=> $resume->getId(),
        'description' =>  $faker->sentence(4,false),

    ];
});
$factory->define(App\models\JobEvaluate::class, function ($faker) {
    $jc = \App\models\JobCompleted::findOrNew($faker->numberBetween($min = 1, $max = 100));
    while ($jc->getUserId()==null||$jc->getId()==null)
    {
        $jc = \App\models\Resumes::findOrNew($faker->numberBetween($min = 1, $max = 100));
    }
    return [
        'user_id' => $jc->getUserId(),
        'job_id'  => $jc->getJobId(),
        'comment' =>  $faker->catchPhrase,
        'score' => $faker->numberBetween($min = 0, $max = 5),
    ];
});
$factory->define(App\models\Jobs::class, function ($faker) {

    $company =\App\models\Company::findOrNew($faker->numberBetween($min = 0, $max = 39));
    //dd($company);
    while ($company->getId()==null)
    {
        echo 'failed once','<br>';
        $company =\App\models\Company::findOrNew($faker->numberBetween($min = 0, $max = 39));
    }
    return [
        'salary' => ($faker->numberBetween($min = 4, $max = 100)*10).'元/天',
        'description' => $faker->catchPhrase,
        'number' => $faker->numberBetween($min = 1, $max = 1000),
        'number_applied' => 0,
        'visited'=> $faker->numberBetween($min = 0, $max = 1000),
        'time'=> $faker->randomElement($array = array ('一年','一个月','六个月')),
        'name'=> $faker->jobTitle,
        'company_id'=> $company->getId(),
        'active'=> 1,
        'company_name'=> $company ->getName(),
    ];
});
$factory->define(App\models\Resumes::class, function ($faker) {
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