<?php

namespace App\Components\WeChatPay\Exceptions;

use App\Library\Exceptions\FatalError;

class RefundFailedException extends FatalError
{
    public function __construct($refund)
    {
        if ($refund->return_code !== 'SUCCESS') {
            $errMsg = $refund->return_msg;
        } else {
            $errMsg = "{$refund->err_code}:{$refund->err_code_des}";
        }
        parent::__construct("Refund failed, serial_number: {$refund->out_refund_no}, message: {$errMsg}");
    }

    public function getExceptionLiteral(): string
    {
        return '系统错误，请稍后重试或联系工作人员。';
    }
}
