<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CloseOrderWhenNotPay extends Job implements ShouldQueue {
    use InteractsWithQueue, SerializesModels;

    protected $id;

    public function __construct($id) {
        $this->id = $id;
    }

    public function handle() {
        $order = Order::find($this->id);
        if ($order) {
            // 订单为在线支付，订单未关闭且未支付
            if ($order->pay_way == 2 &&
                $order->status != 3 &&
                $order->has_paid == 0) {
                // 关闭订单
                $order->status = 3;
                $order->close_type = 4;
                $order->close_reason = '未及时支付订单';
                $order->save();
            }
        }
    }
}
