<?php

namespace App\Components\WeChatPay\Models;

use App\Components\WeChatPay\Exceptions\RedPackException;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use EasyWeChat;
use Splunk;

/**
 * Class WeChatRedPack
 *
 * @property int $id
 * @property string $status
 * @property string $mch_billno
 * @property string $send_name
 * @property string $re_openid
 * @property int $total_num
 * @property int $total_amount
 * @property string $wishing
 * @property string $act_name
 * @property string $remark
 * @property string $send_listid
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\WeChatPay\Models
 */
class WeChatRedPack extends Model
{
    /**
     * Status: initialized
     */
    const STATUS_INITIALIZED = 'initialized';

    /**
     * Status: pending check
     */
    const STATUS_PENDING_CHECK = 'pending_check';

    /**
     * Status: sending
     */
    const STATUS_SENDING = 'SENDING';

    /**
     * Status: sent
     */
    const STATUS_SENT = 'SENT';

    /**
     * Status: failed
     */
    const STATUS_FAILED = 'FAILED';

    /**
     * Status: received
     */
    const STATUS_RECEIVED = 'RECEIVED';

    /**
     * Status: refunding
     */
    const STATUS_REFUND_ING = 'REFUND_ING';

    /**
     * Status: refund
     */
    const STATUS_REFUND = 'REFUND';

    protected $fillable = [
        'status',
        'mch_billno',
        'send_name',
        're_openid',
        'total_num',
        'total_amount',
        'wishing',
        'act_name',
        'remark',
        'send_listid',
    ];

    /**
     * @param array $data
     * @return static
     */
    public static function create(array $data)
    {
        $data = array_merge([
            'status' => static::STATUS_INITIALIZED
        ], $data);
        $ret = parent::create($data);
        $mchId = EasyWeChat::config()['payment']['merchant_id'];
        $date = Carbon::today()->format('Ymd');
        $random = random_int(0, 9999999999);
        $ret->update([
            'mch_billno' => "{$mchId}{$date}{$random}"
        ]);
        return $ret;
    }

    /**
     * @return bool
     * @throws RedPackException
     */
    public function execute()
    {
        Splunk::log('wechat_redpack_execute', [
            'id' => $this->id,
        ]);

        $result = EasyWeChat::lucky_money()->sendNormal([
            'mch_billno'       => $this->mch_billno,
            'send_name'        => $this->send_name,
            're_openid'        => $this->re_openid,
            'total_num'        => $this->total_num,
            'total_amount'     => $this->total_amount,
            'wishing'          => $this->wishing,
            'act_name'         => $this->act_name,
            'remark'           => $this->remark,
        ]);

        $this->update($result->only([
            'return_code',
            'return_msg',
            'result_code',
            'err_code',
            'err_code_des',
            'send_listid',
        ]));

        if ($result->return_code == 'FAIL') {
            $this->update(['status' => static::STATUS_FAILED]);
            return false;
        }

        if ($result->result_code == 'FAIL') {
            if ($result->err_code == 'SYSTEMERROR' || $result->err_code == 'PROCESSING') {
                $this->update(['status' => static::STATUS_PENDING_CHECK]);
            } else {
                $this->update(['status' => static::STATUS_FAILED]);
                return false;
            }
        } else {
            $this->update(['status' => static::STATUS_SENDING]);
        }

        return true;
    }

    public function checkStatus()
    {
        $result = EasyWeChat::lucky_money()->query($this->mch_billno);
        $this->update([
            'status' => $result->status,
            'send_time' => $result->send_time ?? null,
            'refund_time' => $result->refund_time ?? null,
            'refund_amount' => $result->refund_amount ?? null,
            'rcv_time' => isset($result->hblist) ? $result->hblist['hbinfo']['rcv_time'] : null,
        ]);

        Splunk::log('wechat_redpack_check_status', [
            'id' => $this->id,
            'mch_billno' => $this->mch_billno,
            'status' => $result->status
        ]);
    }
}
