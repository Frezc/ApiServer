<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PresetSeeder extends Seeder
{
    /**
     * 这里放数据库中的一些预设值
     * 无论是生产环境还是测试环境都必要的
     */
    public function run()
    {
        Role::create([
            'name' => 'user',
            'mode' => '1',
            'public' => 1
        ]);

        Role::create([
            'name' => 'admin',
            'mode' => '11',
            'public' => 1,
            'admin' => 1
        ]);

        User::create([
            'avatar' => null,
            'email' => 'work-helper@tjz.com',
            'password' => Hash::make('secret'),
            'nickname' => '工作助手',
            'sign' => '关于工作的消息会第一时间通知哦。',
            'email_verified' => 1,
        ]);

        User::create([
            'avatar' => null,
            'email' => 'notification-helper@tjz.com',
            'password' => Hash::make('secret'),
            'nickname' => '通知助手',
            'email_verified' => 1,
        ]);

        User::create([
            'id' => 1001,
            'avatar' => null,
            'email' => '504021398@qq.com',
            'phone' => '15988166495',
            'password' => Hash::make('secret'),
            'nickname' => 'frezc',
            'sign' => 'You can contact me.',
            'birthday' => '1995-02-14',
            'location' => '杭电',
            'sex' => 0,
            'email_verified' => 1,
            'role_id' => 2
        ]);

        User::create([
            'avatar' => null,
            'email' => 'admin@tjz.com',
            'phone' => '18888888888',
            'password' => Hash::make('secret'),
            'nickname' => 'admin',
            'sign' => '我是管理员',
            'birthday' => '1999-09-09',
            'location' => 'no pos',
            'sex' => 1,
            'email_verified' => 1,
            'role_id' => 2
        ]);
    }
}
