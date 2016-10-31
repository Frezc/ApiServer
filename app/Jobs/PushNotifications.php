<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushNotifications extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $from;
    protected $to;
    protected $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from, $to, $content)
    {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (is_array($this->to)) {
            foreach ($this->to as $to) {
                Message::pushNotification($this->from, $to, $this->content);
            }
        } else {
            Message::pushNotification($this->from, $this->to, $this->content);
        }
    }
}
