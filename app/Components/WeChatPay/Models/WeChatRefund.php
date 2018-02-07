<?php

namespace App\Components\WeChatPay\Models;

use App\Components\WeChatPay\Exceptions\RefundFailedException;
use App\Library\Lock;
use Illuminate\Database\Eloquent\Model;
use EasyWeChat;
use ExceptionNotifier\Notifier;
use Splunk;

/**
 * Class WeChatRefund
 *
 * @property int $id
 * @property string $status
 * @property string $out_trade_no
 * @property string $transaction_id
 * @property string $out_refund_no
 * @property int $total_fee
 * @property int $refund_fee
 * @property string $refund_fee_type
 * @property string $refund_desc
 * @property string $refund_account
 * @property string $return_code
 * @property string $return_msg
 * @property string $result_code
 * @property string $err_code
 * @property string $err_code_des
 * @property string $refund_id
 * @property string $refund_recv_accout
 * @property string $refund_success_time
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\WeChatPay\Models
 */
class WeChatRefund extends Model
{
    /**
     * Status: initialized
     */
    const STATUS_INITIALIZED = 'INITIALIZE';

    /**
     * Status: processing
     */
    const STATUS_PROCESSING = 'PROCESSING';

    /**
     * Status: success
     */
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Status: change
     */
    const STATUS_CHANGE = 'CHANGE';

    /**
     * Status: close
     */
    const STATUS_REFUNDCLOSE = 'REFUNDCLOSE';

    /**
     * Status: fail
     */
    const STATUS_FAIL = 'FAIL';

    /**
     * Status: need retry
     */
    const STATUS_NEED_RETRY = 'NEED_RETRY';

    protected $fillable = [
        'status',
        'out_trade_no',
        'transaction_id',
        'out_refund_no',
        'total_fee',
        'refund_fee',
        'refund_fee_type',
        'refund_desc',
        'refund_account',
        'return_code',
        'return_msg',
        'result_code',
        'err_code',
        'err_code_des',
        'refund_id',
        'refund_recv_accout',
        'refund_success_time',
    ];

    /**
     * @return bool
     */
    public function execute()
    {
        Splunk::log('wechat_refund_execute', [
            'id' => $this->id,
        ]);

        $payment = EasyWeChat::payment();
        $result = $payment->refund(
            $this->out_trade_no,
            $this->out_refund_no,
            $this->total_fee,
            $this->refund_fee,
            null,
            'out_trade_no',
            $this->refund_account
        );

        $this->update($result->only([
            'return_code',
            'return_msg',
            'result_code',
            'err_code',
            'err_code_des',
            'refund_id',
        ]));

        if ($result->return_code == 'FAIL') {
            $this->update(['status' => static::STATUS_FAIL]);
            return false;
        }

        if ($result->result_code == 'FAIL') {
            if ($result->err_code == 'SYSTEMERROR' || $result->err_code == 'BIZERR_NEED_RETRY') {
                $this->update(['status' => static::STATUS_NEED_RETRY]);
            } else {
                $this->update(['status' => static::STATUS_FAIL]);
                Lock::lock('withdraw:restore_already_refund', 5, function() {
                    $pay = WeChatPay::where('serial_number', $this->out_trade_no)->first();
                    $pay->already_refund -= $this->refund_fee;
                    $pay->save();
                });
                return false;
            }
        } else {
            $this->update(['status' => static::STATUS_PROCESSING]);
        }

        return true;
    }

    /**
     * Check and update status
     */
    public function checkStatus()
    {
        $payment = EasyWeChat::payment();
        $result = $payment->queryRefundByRefundId($this->refund_id);
        $this->update(['status' => $result->refund_status_0]);

        Splunk::log('wechat_refund_check_status', [
            'id' => $this->id,
            'refund_id' => $this->refund_id,
            'status' => $result->refund_status_0
        ]);

        if ($result->refund_status_0 == 'SUCCESS') {
            $this->update([
                'refund_recv_accout' => $result->refund_recv_accout_0,
                'refund_success_time' => $result->refund_success_time_0,
            ]);
        } else if ($result->refund_status_0 == 'CHANGE') {
            Notifier::notify(new \Exception("Refund status CHANGE, id: {$this->refund_id}"));
        }
    }
}
