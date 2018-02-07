<?php

namespace App\Components\WeChatPay\Exceptions;

use App\Library\Exceptions\StandardError;

class RefundOutOfLimitException extends StandardError
{
    public function __construct($serialNumber, $limit, $money)
    {
        parent::__construct("RefundOutOfLimit serial_number: {$serialNumber}, limit: {$limit}, money: {$money}");
    }

    public function getExceptionLiteral(): string
    {
        return '系统错误，请稍后重试或联系工作人员。';
    }
}
