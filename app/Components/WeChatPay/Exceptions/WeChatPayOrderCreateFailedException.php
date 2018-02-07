<?php

namespace App\Components\WeChatPay\Exceptions;

use App\Library\Exceptions\FatalError;

class WeChatPayOrderCreateFailedException extends FatalError
{
    public function __construct($ret)
    {
        if ($ret->return_code !== 'SUCCESS') {
            $errMsg = $ret->return_msg;
        } else {
            $errMsg = "{$ret->err_code}:{$ret->err_code_des}";
        }
        parent::__construct("WeChatPayOrderCreateFailed {$errMsg}");
    }

    public function getExceptionLiteral(): string
    {
        return '支付失败，请稍后重试或联系工作人员。';
    }
}
