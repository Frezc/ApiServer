<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCodeVerification extends Model
{
    protected $guarded = ['id'];
    protected $table='sms_code_verifications';
}
