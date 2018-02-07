<?php

namespace App\Components\WeChatPay\Exceptions;

use App\Library\Exceptions\FatalError;

/**
 * Class CompanyPayException
 *
 * Used if errors occurred when sending a company pay
 * @package App\Components\WeChatPay\Exceptions
 */
class CompanyPayException extends FatalError
{
    public function __construct($result)
    {
        if ($result->return_code !== 'SUCCESS') {
            $errMsg = $result->return_msg;
        } else {
            $errMsg = "{$result->err_code}:{$result->err_code_des}";
        }
        parent::__construct("Refund failed, serial_number: {$result->payment_no}, message: {$errMsg}");
    }

    public function getExceptionLiteral(): string
    {
        return '系统错误，请稍后重试或联系工作人员。';
    }
}
