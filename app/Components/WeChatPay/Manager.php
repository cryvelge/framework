<?php

namespace App\Components\WeChatPay;

use App\Components\WeChatPay\Exceptions\RefundOutOfLimitException;
use App\Components\WeChatPay\Jobs\WeChatPayHandleJob;
use App\Components\WeChatPay\Models\WeChatCompanyPay;
use App\Components\WeChatPay\Models\WeChatPay;
use App\Components\WeChatPay\Models\WeChatRedPack;
use App\Components\WeChatPay\Models\WeChatRefund;
use App\Library\Lock;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Splunk;

/**
 * Class Manager
 * @package App\Components\WeChatPay
 */
class Manager
{
    /**
     * @param array $data
     * @return WeChatPay
     */
    public static function createPay(array $data)
    {
        Splunk::log('wechat_pay_create', $data);

        $pay = WeChatPay::create([
            'name' => $data['name'],
            'detail' => $data['detail'],
            'serial_number' => $data['serial_number'],
            'price' => $data['price'],
            'use_as_withdraw' => $data['use_as_withdraw'] ?? true,
            'handled' => false,
            'openid' => $data['openid'],
        ]);
        return $pay;
    }

    /**
     * @param string $serialNumber
     * @param string $reason
     * @param int|null $money
     * @param int|null $refundMoney
     * @return WeChatRefund
     */
    public static function createRefund(string $serialNumber, string $reason, ?int $money = null, ?int $refundMoney = null)
    {
        Splunk::log('wechat_refund_create', [
            'serial_number' => $serialNumber,
            'reason' => $reason,
            'money' => $money
        ]);

        return Lock::lock("wechat_refund_{$serialNumber}", 2, function() use ($serialNumber, $reason, $money, $refundMoney) {
            $pay = WeChatPay::where('serial_number', $serialNumber)->first();
            if (!is_null($money) && $pay->price - $pay->already_refund < $money) {
                throw new RefundOutOfLimitException($serialNumber, $pay->price - $pay->already_refund, $money);
            }

            if (is_null($money)) {
                $money = $pay->price - $pay->already_refund;
            }

            if (is_null($refundMoney)) {
                $refundMoney = $money;
            }

            $refund = WeChatRefund::create([
                'out_trade_no' => $pay->out_trade_no,
                'transaction_id' => $pay->transaction_id,
                'out_refund_no' => Uuid::uuid4()->getHex(),
                'total_fee' => $pay->total_fee,
                'refund_fee' => $money,
                'refund_fee_type' => 'CNY',
                'refund_desc' => $reason,
                'refund_account' => config('wechat.payment.refund_account', 'REFUND_SOURCE_UNSETTLED_FUNDS'),
                'status' => WeChatRefund::STATUS_INITIALIZED
            ]);

            $pay->update([
                'already_refund' => $pay->already_refund + $refundMoney
            ]);

            return $refund;
        });
    }

    /**
     * @param array $data
     * @return WeChatRedPack
     */
    public static function createRedPack(array $data)
    {
        Splunk::log('wechat_redpack_create', $data);

        $data = [
            'send_name' => $data['name'],
            're_openid' => $data['openid'],
            'total_num' => 1,
            'total_amount' => $data['money'],
            'wishing' => $data['wishing'],
            'act_name' => $data['act'],
            'remark' => $data['remark'],
        ];
        $redpack = WeChatRedPack::create($data);
        return $redpack;
    }

    /**
     * @param array $data
     * @return WeChatCompanyPay
     */
    public static function createCompanyPay(array $data)
    {
        Splunk::log('wechat_company_pay_create', $data);

        $data = [
            'openid' => $data['openid'],
            'amount' => $data['amount'],
            'desc' => $data['desc']
        ];
        $pay = WeChatCompanyPay::create($data);
        return $pay;
    }

    /**
     * @param array $data
     * @param bool $success
     */
    public static function notifyWeChatPay(array $data, bool $success)
    {
        Splunk::log('wechat_pay_notify', $data);

        Lock::lock("wechat_pay_confirm_{$data['out_trade_no']}", 2, function() use ($data, $success) {
            $pay = WeChatPay::where('serial_number', $data['out_trade_no'])->first();

            if ($pay && !$pay->notified) {
                $pay->notify($data);
                dispatch(new WeChatPayHandleJob($data['out_trade_no'], $success));
            }
        });
    }

    /**
     * @param string $openId
     * @return \Illuminate\Support\Collection
     */
    public static function getRefundablePayments(string $openId)
    {
        return WeChatPay::where('openid', $openId)
            ->whereColumn('already_refund', '<', 'price')
            ->where('result_code', 'SUCCESS')
            ->where('time_end', '>', Carbon::now()->subYear()->toDateTimeString())
            ->orderBy('time_end', 'asc')
            ->get();
    }

    public static function getInstanceById($type, $id)
    {
        switch ($type) {
            case 'payment':
                return WeChatPay::where('id', $id)->first();
            case 'refund':
                return WeChatRefund::where('id', $id)->first();
            case 'red_pack':
                return WeChatRedPack::where('id', $id)->first();
            case 'company_pay':
                return WeChatCompanyPay::where('id', $id)->first();
        }
    }
}
