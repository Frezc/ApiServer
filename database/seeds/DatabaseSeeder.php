<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserTableSeeder::class);
        // DB::table('users')->insert([
        //     'nickname' => str_random(10),
        //     'email' => str_random(10).'@gmail.com',
        //     'password' => Hash::make('secret'),
        // ]);

        // DB::table('users')->update([
        //     'password' => Hash::make('secret')
        // ]);

        // DB::table('users')->update([
        //     'avatar' => 'http://static.frezc.com/static/avatars/default'
        // ]);
        //        factory(App\models\Company::class,20)->create();
        //        factory(App\models\Jobs::class,20)->create();
        //        factory(App\models\User::class,20)->create();
        //        factory(App\models\Resumes::class,10)->create();
        //        factory(App\models\JobCompleted::class,80)->create();
        //        factory(App\models\JobApply::class,80)->create();
                // factory(App\models\JobEvaluate::class,20)->create();
        DB::table('resumes')->update([
            'photo' => 'http://static.frezc.com/static/resume_photos/default'
        ]);

        Model::reguard();
    }
}
