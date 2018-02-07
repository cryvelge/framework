<?php

namespace App\Components\Payment\Models;

use App\Components\Payment\Manager;
use App\Components\Payment\Payable;
use App\Components\User\Manager as UserManager;
use App\Components\WeChatPay\Manager as WeChatPayManager;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;
use Splunk;

/**
 * Class Payment
 *
 * @property int $id
 * @property string $order_type
 * @property int $order_id
 * @property int $user_id
 * @property string $serial_number
 * @property string $type
 * @property int $price
 * @property string $status
 * @property array $parameters
 * @property string $fail_reason
 * @property string $pay_time
 * @property int $can_refund
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\Order\Models
 */
class Payment extends Model
{
    /**
     * Status: pending
     */
    const STATUS_PENDING = 'pending';

    /**
     * Status: finished
     */
    const STATUS_FINISHED = 'finished';

    /**
     * Status: canceled
     */
    const STATUS_CANCELED = 'canceled';

    /**
     * Status: failed
     */
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_type',
        'order_id',
        'user_id',
        'serial_number',
        'type',
        'price',
        'status',
        'parameters',
        'fail_reason',
        'pay_time',
        'can_refund',
    ];

    protected $casts = [
        'parameters' => 'array'
    ];

    /**
     * Execute payment
     */
    public function pay()
    {
        Splunk::log('payment_pay', [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
        ]);

        switch ($this->type) {
            case 'user_account':
                $this->initUserAccountPay();
                break;
            case 'wechat_pay':
                $this->initWeChatPay();
                break;
        }
    }

    /**
     * Cancel payment
     */
    public function cancel()
    {
        Splunk::log('payment_cancel', [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
        ]);

        if ($this->status !== static::STATUS_PENDING && $this->status !== static::STATUS_FINISHED) {
            return;
        }

        switch ($this->type) {
            case 'user_account':
                $this->cancelUserAccountPay();
                break;
            case 'wechat_pay':
                $this->cancelWeChatPay();
                break;
        }
    }

    /**
     * WeChat pay confirm
     * @throws \Exception
     */
    public function confirm()
    {
        Splunk::log('payment_confirm', [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
        ]);

        switch ($this->type) {
            case 'user_account':
                throw new \Exception('impossible');
                break;
            case 'wechat_pay':
                $payable = $this->getOrder();
                $this->status = static::STATUS_FINISHED;
                $this->pay_time = Carbon::now()->toDateTimeString();
                $this->save();
                MoneyTallyManager::create([
                    'type' => MoneyTallyManager::TYPE_WECHAT_PAY,
                    'user_id' => $this->user_id,
                    'money' => $this->price,
                    'from' => MoneyTallyManager::SOURCE_TYPE_WECHAT,
                    'to' => MoneyTallyManager::SOURCE_TYPE_SYSTEM,
                    'link_type' => $this->order_type,
                    'link_id' => $this->order_id,
                    'operator' => 'user',
                    'remark' => $payable->getName(),
                ]);
                $payable->onPaymentConfirmed();
                break;
        }
    }

    /**
     * WeChat pay fail
     * @throws \Exception
     */
    public function fail()
    {
        Splunk::log('payment_fail', [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
        ]);

        switch ($this->type) {
            case 'user_account':
                throw new \Exception('impossible');
                break;
            case 'wechat_pay':
                $payable = $this->getOrder();
                $this->status = static::STATUS_FAILED;
                $this->save();
                $payable->onPaymentConfirmed();
                break;
        }
    }

    /**
     * If payment is finished
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === static::STATUS_FINISHED;
    }

    /**
     * If payment is failed
     * @return bool
     */
    public function isFailed()
    {
        return $this->status === static::STATUS_FAILED;
    }

    /**
     * Get related order
     * @return Payable
     */
    public function getOrder()
    {
        $class = Manager::ORDER_TYPE[$this->order_type];
        return call_user_func([$class, 'find'], $this->order_id);
    }

    /**
     * Execute user account pay
     * @throws \Throwable
     */
    protected function initUserAccountPay()
    {
        try {
            $order = $this->getOrder();
            $user = UserManager::getById($this->user_id);
            $user->getAccount()->decrease(
                MoneyTallyManager::TYPE_ACCOUNT_PAY,
                $this->price,
                MoneyTallyManager::SOURCE_TYPE_SYSTEM,
                'system',
                $order->getName(),
                $this->order_type,
                $this->order_id
            );
            $this->status = static::STATUS_FINISHED;
            $this->save();
        } catch (AccountNotEnoughException $e) {
            $this->status = static::STATUS_FAILED;
            $this->save();
        } catch (\Throwable $e) {
            $this->status = static::STATUS_FAILED;
            $this->save();
            throw $e;
        }
    }

    /**
     * Initialize WeChat pay and get parameters for jssdk
     */
    protected function initWeChatPay()
    {
        $order = $this->getOrder();
        $wechatPay = WeChatPayManager::createPay([
            'payment_id' => $this->id,
            'name' => $order->getName(),
            'detail' => $order->getDetail(),
            'serial_number' => $this->serial_number,
            'price' => $this->price,
            'openid' => UserManager::getOpenId($this->user_id),
        ]);
        $this->parameters = $wechatPay->getParameters();
        $this->save();
    }

    /**
     * Cancel user account pay and return money if necessary
     */
    protected function cancelUserAccountPay()
    {
        if ($this->isFinished()) {
            $order = $this->getOrder();
            $user = UserManager::getById($this->user_id);
            $user->getAccount()->increase(
                MoneyTallyManager::TYPE_ACCOUNT_PAY_CANCEL,
                $this->price,
                MoneyTallyManager::SOURCE_TYPE_SYSTEM,
                'system',
                '订单取消: ' . $order->getName(),
                $this->order_type,
                $this->order_id
            );
        }
        $this->status = static::STATUS_CANCELED;
        $this->save();
    }

    /**
     * Cancel WeChat pay and refund if necessary
     */
    protected function cancelWeChatPay()
    {
        if ($this->isFinished()) {
            $order = $this->getOrder();
            MoneyTallyManager::create([
                'type' => MoneyTallyManager::TYPE_WECHAT_PAY_CANCEL,
                'user_id' => $this->user_id,
                'money' => $this->price,
                'from' => MoneyTallyManager::SOURCE_TYPE_SYSTEM,
                'to' => MoneyTallyManager::SOURCE_TYPE_WECHAT,
                'link_type' => $this->order_type,
                'link_id' => $this->order_id,
                'operator' => 'user',
                'remark' => '退款-' . $order->getName()
            ]);
            WeChatPayManager::createRefund($this->serial_number, '订单取消');
        }
        $this->status = static::STATUS_CANCELED;
        $this->save();
    }
}
