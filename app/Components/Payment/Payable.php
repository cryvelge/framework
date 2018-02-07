<?php

namespace App\Components\Payment;

use App\Components\User\Exceptions\AccountNotEnoughException;
use App\Components\User\Manager as UserManager;
use App\Components\Payment\Manager as PaymentManager;
use App\Library\Lock;
use Illuminate\Support\Str;
use Splunk;

/**
 * Interface Payable
 * @package App\Components\Payment
 */
trait Payable
{
    /**
     * 订单状态: 等待处理
     */
    public static $statusInitialized = 'initialized';

    /**
     * 订单状态: 等待支付
     */
    public static $statusPending = 'pending';

    /**
     * 订单状态: 支付完成
     */
    public static $statusComplete = 'complete';

    /**
     * 订单状态: 订单关闭
     */
    public static $statusClosed = 'closed';

    /**
     * 订单状态: 订单退款
     */
    public static $statusRefund = 'refund';

    /**
     * Get possible payment detail
     * @param string $payType
     * @param bool $useBalance
     * @return array
     */
    public function getPaymentDetail(string $payType = 'wechat', bool $useBalance = true)
    {
        if ($useBalance) {
            $user = UserManager::getById($this->getUserId());
            $userBalance = $user->getAccount()->money;
            if ($userBalance >= $this->getPrice()) {
                return [
                    'user_account' => $this->getPrice()
                ];
            } else {
                return [
                    'user_account' => $userBalance,
                    "{$payType}_pay" => $this->getPrice() - $userBalance
                ];
            }
        } else {
            return [
                "{$payType}_pay" => $this->getPrice()
            ];
        }
    }

    /**
     * Create and execute order's payment
     * @param string $payType
     * @param bool $useBalance
     * @return array|bool
     */
    public function pay(string $payType = 'wechat', bool $useBalance = true)
    {
        return Lock::lock($this->getLockKey(), 5, function() use ($payType, $useBalance) {
            if ($this->getPrice() == 0) {
                $this->success();
                return true;
            }

            $this->closeExistedPayments();

            $paymentDetail = $this->getPaymentDetail($payType, $useBalance);
            $paymentUserAccount = null;
            $paymentWeChat = null;

            Splunk::log('payable_create', [
                'user_id' => $this->getUserId(),
                'type' => $this->getPayableType(),
                'id' => $this->getPayableId(),
                'user_account' => $paymentDetail['user_account'] ?? null,
                'wechat_pay' => $paymentDetail['wechat_pay'] ?? null,
            ]);

            if (isset($paymentDetail['user_account']) && $paymentDetail['user_account'] > 0) {
                $paymentUserAccount = PaymentManager::createPayment(
                    $this,
                    PaymentManager::TYPE_ACCOUNT,
                    $paymentDetail['user_account']
                );
            }

            if (isset($paymentDetail['wechat_pay'])) {
                $paymentWeChat = PaymentManager::createPayment(
                    $this,
                    PaymentManager::TYPE_WE_CHAT,
                    $paymentDetail['wechat_pay']
                );

                $paymentWeChat->pay();
                $this->setStatus(static::$statusPending);

                return $paymentWeChat->parameters;
            } elseif (isset($paymentDetail['user_account']) && $paymentDetail['user_account'] > 0) {
                $paymentUserAccount->pay();
                if ($paymentUserAccount->isFinished()) {
                    $this->success();
                    return true;
                } else {
                    $this->fail();
                    return false;
                }
            } else {
                $this->success();
                return true;
            }
        });
    }

    /**
     * Get payment status
     * @return bool
     */
    public function getPayStatus()
    {
        $payments = PaymentManager::getByOrder($this);

        if ($payments->contains->isFailed()) {
            return false;
        }

        if ($payments->every->isFinished()) {
            return true;
        }

        return null;
    }

    /**
     * Return payable type
     * @return string
     */
    public function getPayableType(): string
    {
        return Manager::ORDER_TYPE[static::class];
    }

    /**
     * Return payable id
     * @return int
     */
    abstract public function getPayableId(): int;

    /**
     * Return payable user id
     * @return int
     */
    abstract public function getUserId(): int;

    /**
     * Called on payment confirmed
     */
    public function onPaymentConfirmed(): void
    {
        Lock::lock($this->getLockKey(), 5, function() {
            $wechatPayment = PaymentManager::getByOrder($this, PaymentManager::TYPE_WE_CHAT);
            $accountPayment = PaymentManager::getByOrder($this, PaymentManager::TYPE_ACCOUNT);

            Splunk::log('payable_confirmed', [
                'user_id' => $this->getUserId(),
                'type' => $this->getPayableType(),
                'id' => $this->getPayableId(),
                'wechat_pay' => $wechatPayment->status
            ]);

            if ($wechatPayment->isFailed()) {
                $this->fail();
                return;
            }

            if ($wechatPayment->isFinished()) {
                if ($accountPayment) {
                    try {
                        $accountPayment->pay();
                    } catch (AccountNotEnoughException $e) {
                        $this->fail();
                        return;
                    }
                }
                $this->success();

                return;
            }
        });
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return string
     */
    abstract public function getDetail(): string;

    /**
     * @return int
     */
    abstract public function getPrice(): int;

    /**
     * called on payment succeeded
     */
    protected function success(): void
    {
        Splunk::log('payable_success', [
            'user_id' => $this->getUserId(),
            'type' => $this->getPayableType(),
            'id' => $this->getPayableId(),
        ]);
        $this->setStatus(static::$statusComplete);
    }

    /**
     * called on payment failed
     */
    protected function fail(): void
    {
        Splunk::log('payable_fail', [
            'user_id' => $this->getUserId(),
            'type' => $this->getPayableType(),
            'id' => $this->getPayableId(),
        ]);
        $this->setStatus(static::$statusClosed);
        $this->closeExistedPayments();
    }

    /**
     * Cancel order
     */
    public function cancel()
    {
        if (!$this->getPayStatus()) {
            Splunk::log('payable_cancel', [
                'user_id' => $this->getUserId(),
                'type' => $this->getPayableType(),
                'id' => $this->getPayableId(),
            ]);
            $this->fail();
        }
    }

    /**
     * Refund order
     */
    public function refund()
    {
        Splunk::log('payable_refund', [
            'user_id' => $this->getUserId(),
            'type' => $this->getPayableType(),
            'id' => $this->getPayableId(),
        ]);
        $this->setStatus(static::$statusRefund);
        $this->closeExistedPayments();
    }

    /**
     * Set payable status
     * @param string $status
     */
    abstract protected function setStatus(string $status): void;

    /**
     * Close all existed payments before create new ones
     */
    protected function closeExistedPayments()
    {
        Splunk::log('payable_close', [
            'user_id' => $this->getUserId(),
            'type' => $this->getPayableType(),
            'id' => $this->getPayableId(),
        ]);
        PaymentManager::cancelByOrder($this);
    }

    /**
     * Get key for lock
     * @return string
     */
    protected function getLockKey()
    {
        return Str::snake($this->getPayableType()) . "_pay_{$this->getUserId()}";
    }
}