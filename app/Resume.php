<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = 'resumes';
    //在用Resume::create创建时不能填充的项
    protected $guarded = ['id'];

    public function convertToExpectJob($isPublic = 0) {
        $user = User::find($this->user_id);

        return ExpectJob::create(array_merge(
            array_only($this->toArray(),
                ['user_id', 'name', 'photo', 'school', 'birthday', 'contact', 'sex', 'expect_location', 'introduction']),
            ['is_public' => $isPublic, 'user_name' => $user->nickname]
        ));
    }
}
