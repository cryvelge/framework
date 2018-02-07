<?php

namespace App\Components\WeChatPay\Jobs;

use App\Components\Payment\Manager as PaymentManager;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeChatPayHandleJob implements ShouldQueue
{
    private $serialNumber;
    private $success;

    public function __construct($serialNumber, $success)
    {
        $this->serialNumber = $serialNumber;
        $this->success = $success;
    }

    public function handle()
    {
        PaymentManager::confirm($this->serialNumber, $this->success);
    }
}
