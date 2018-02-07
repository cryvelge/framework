<?php

namespace App\Components\WeChatPay\Exceptions;

use App\Library\Exceptions\FatalError;

/**
 * Class RedPackException
 *
 * Used if errors except SYSTEMERROR and PROCESSING occurred when sending a red pack
 * @package App\Components\WeChatPay\Exceptions
 */
class RedPackException extends FatalError
{
    public function __construct($result)
    {
        if ($result->return_code !== 'SUCCESS') {
            $errMsg = $result->return_msg;
        } else {
            $errMsg = "{$result->err_code}:{$result->err_code_des}";
        }
        parent::__construct("Refund failed, serial_number: {$result->mch_billno}, message: {$errMsg}");
    }

    public function getExceptionLiteral(): string
    {
        return '系统错误，请稍后重试或联系工作人员。';
    }
}
