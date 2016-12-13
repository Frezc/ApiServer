<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Message;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushNotifications extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    protected $from;
    protected $to;
    protected $content;

    public function __construct($from, $to, $content) {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
    }

    public function handle() {
        if ($this->to) {
            if (is_array($this->to)) {
                // 发送对象是数组
                foreach ($this->to as $to) {
                    Message::pushNotification(
                        $this->from, $to, $this->content);
                }
            } else {
                // 发送对象是单个用户
                Message::pushNotification(
                    $this->from, $this->to, $this->content);
            }
        } else {
            // 没有发送对象，默认向所有人发送
            $userCount = User::where('id', '>', 1000)->count();
            for ($i = 1001; $i <= 1000 + $userCount; $i++) {
                Message::pushNotification(
                    $this->from, $i, $this->content);
            }
        }
    }
}
