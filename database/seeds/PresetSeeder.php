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
            'name' => 'company',
            'mode' => '2',
            'public' => 1
        ]);
        Role::create([
            'name' => 'admin',
            'mode' => '3',
            'public' => 1,
            'admin' => 1
        ]);

        Role::create([
            'name' => 'banned',
            'mode' => '2'
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
            'email' => '244774907@qq.com',
            'phone' => '17777777777',
            'password' => Hash::make('secret'),
            'nickname' => 'frezc',
            'sign' => 'You can contact me.',
            'birthday' => '1995-02-14',
            'location' => '杭电',
            'sex' => 0,
            'email_verified' => 1,
            'role_id' => 2,
            'money' => 99999999
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
            'role_id' => 2,
            'money' => 99999999
        ]);

        \App\Models\Data::create([
            'key' => 'banners',
            'value' => json_encode([
                Storage::url('images/__banner1.jpg'),
                Storage::url('images/__banner2.jpg'),
                Storage::url('images/__banner3.jpg'),
                Storage::url('images/__banner4.jpg')])
        ]);

        \App\Models\JobType::insert([
            ['name' => '传单派发'],
            ['name' => '促销导购'],
            ['name' => '话务客服'],
            ['name' => '礼仪模特'],
            ['name' => '家教助教'],
            ['name' => '服务员'],
            ['name' => '问卷调查'],
            ['name' => '审核录入'],
            ['name' => '地推拉访'],
            ['name' => '打包分拣'],
            ['name' => '展会协助'],
            ['name' => '充场'],
            ['name' => '安保'],
            ['name' => '送餐员'],
            ['name' => '演出'],
            ['name' => '翻译'],
            ['name' => '校园代理'],
            ['name' => '技师技工'],
            ['name' => '美容美发'],
            ['name' => '餐饮工'],
            ['name' => '兼职司机'],
            ['name' => '义工'],
            ['name' => '在线兼职'],
            ['name' => '其他']
        ]);
    }
}
