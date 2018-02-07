<?php

namespace App\Components\User\Exceptions;

use App\Library\Exceptions\StandardError;

/**
 * Class AccountNotEnoughException
 *
 * Used when there is not enough money left to pay
 *
 * @package App\Components\User\Exceptions
 */
class AccountNotEnoughException extends StandardError
{
    public function __construct(int $userId, int $account, int $decrease)
    {
        parent::__construct("Account not enough. UserId: $userId, Account: $account, Decrease: $decrease");
    }

    public function getExceptionLiteral(): string
    {
        return "您的账户余额不足，请重试。";
    }
}
