<?php

namespace App\Components\WeChatPay\Models;

use App\Components\WeChatPay\Exceptions\WeChatPayOrderCreateFailedException;
use Illuminate\Database\Eloquent\Model;
use EasyWeChat;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use Splunk;

/**
 * Class WeChatPay
 *
 * @property int $id
 * @property string $name
 * @property string $detail
 * @property string $serial_number
 * @property int $price
 * @property bool $use_as_withdraw
 * @property bool $already_refund
 * @property int $prepay_id
 * @property bool $notified
 * @property bool $handled
 * @property string $return_code
 * @property string $return_msg
 * @property string $result_code
 * @property string $err_code
 * @property string $err_code_des
 * @property string $openid
 * @property bool $is_subscribe
 * @property string $trade_type
 * @property string $bank_type
 * @property int $total_fee
 * @property int $settlement_total_fee
 * @property string $fee_type
 * @property int $cash_fee
 * @property string $cash_fee_type
 * @property string $transaction_id
 * @property string $out_trade_no
 * @property string $attach
 * @property string $time_end
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\WeChatPay\Models
 */
class WeChatPay extends Model
{
    protected $fillable = [
        'name',
        'detail',
        'serial_number',
        'price',
        'use_as_withdraw',
        'already_refund',
        'prepay_id',
        'notified',
        'handled',
        'return_code',
        'return_msg',
        'result_code',
        'err_code',
        'err_code_des',
        'openid',
        'is_subscribe',
        'trade_type',
        'bank_type',
        'total_fee',
        'settlement_total_fee',
        'fee_type',
        'cash_fee',
        'cash_fee_type',
        'transaction_id',
        'out_trade_no',
        'attach',
        'time_end',
    ];

    /**
     * Get parameters for jssdk
     * @return array
     * @throws WeChatPayOrderCreateFailedException
     */
    public function getParameters()
    {
        Splunk::log('wechat_pay_execute', [
            'id' => $this->id,
        ]);

        $payment = EasyWeChat::payment();
        $order = new Order([
            'trade_type'       => 'JSAPI',
            'body'             => $this->name,
            'detail'           => $this->detail,
            'out_trade_no'     => $this->serial_number,
            'total_fee'        => $this->price,
            'openid'           => $this->openid,
        ]);
        $result = $payment->prepare($order);
        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $this->prepay_id = $result->prepay_id;
            $this->save();
            return $payment->configForJSSDKPayment($this->prepay_id);
        } else {
            throw new WeChatPayOrderCreateFailedException($result);
        }
    }

    /**
     * @param array $data
     */
    public function notify(array $data)
    {
        $this->fill(array_only($data, [
            'return_code', 'return_msg', 'result_code', 'err_code', 'err_code_des', 'openid', 'is_subscribe',
            'trade_type', 'bank_type', 'total_fee', 'settlement_total_fee', 'fee_type', 'cash_fee', 'cash_fee_type',
            'transaction_id', 'out_trade_no', 'attach', 'time_end'
        ]));
        $this->notified = true;
        $this->save();
    }

    public function checkStatus()
    {
        //
    }
}
