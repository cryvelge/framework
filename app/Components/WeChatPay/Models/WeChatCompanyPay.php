<?php

namespace App\Components\WeChatPay\Models;

use App\Components\WeChatPay\Exceptions\CompanyPayException;
use Illuminate\Database\Eloquent\Model;
use EasyWeChat;
use Ramsey\Uuid\Uuid;
use Splunk;

/**
 * Class WeChatRedPack
 *
 * @property int $id
 * @property string $status
 * @property string $partner_trade_no
 * @property string $openid
 * @property string $check_name
 * @property string $re_user_name
 * @property string $amount
 * @property string $desc
 * @property string $spbill_create_ip
 * @property string $return_code
 * @property string $return_msg
 * @property string $result_code
 * @property string $err_code
 * @property string $err_code_des
 * @property string $payment_no
 * @property string $payment_time
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\WeChatPay\Models
 */
class WeChatCompanyPay extends Model
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
    const STATUS_SENDING = 'PROCESSING';

    /**
     * Status: sent
     */
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Status: failed
     */
    const STATUS_FAIL = 'FAIL';

    protected $fillable = [
        'status',
        'partner_trade_no',
        'openid',
        'check_name',
        're_user_name',
        'amount',
        'desc',
        'spbill_create_ip',
        'return_code',
        'return_msg',
        'result_code',
        'err_code',
        'err_code_des',
        'payment_no',
        'payment_time',
    ];

    /**
     * @param array $data
     * @return static
     */
    public static function create(array $data)
    {
        $data = array_merge([
            'status' => static::STATUS_INITIALIZED,
            'partner_trade_no' => Uuid::uuid4()->getHex(),
            'check_name' => 'NO_CHECK',
            're_user_name' => null,
            'spbill_create_ip' => EasyWeChat::config()['payment']['machine_ip'],
        ], $data);
        return parent::create($data);
    }

    /**
     * @return bool
     * @throws CompanyPayException
     */
    public function execute()
    {
        Splunk::log('wechat_company_pay_execute', [
            'id' => $this->id,
        ]);

        $data = [
            'partner_trade_no' => $this->partner_trade_no,
            'openid' => $this->openid,
            'check_name' => $this->check_name,
            'amount' => $this->amount,
            'desc' => $this->desc,
            'spbill_create_ip' => $this->spbill_create_ip,
        ];

        $result = EasyWeChat::merchant_pay()->send($data);

        $this->update($result->only([
            'return_code',
            'return_msg',
            'result_code',
            'err_code',
            'err_code_des',
            'payment_no',
            'payment_time',
        ]));

        if ($result->return_code == 'FAIL') {
            $this->update(['status' => static::STATUS_FAIL]);
            return false;
        }

        if ($result->result_code == 'FAIL') {
            if ($result->err_code == 'SYSTEMERROR') {
                $this->update(['status' => static::STATUS_PENDING_CHECK]);
            } else {
                $this->update(['status' => static::STATUS_FAIL]);
                return false;
            }
        } else {
            $this->update(['status' => static::STATUS_SENDING]);
        }

        return true;
    }

    public function checkStatus()
    {
        $result = EasyWeChat::merchant_pay()->query($this->partner_trade_no);
        $this->update(['status' => $result->status]);

        Splunk::log('wechat_company_pay_check_status', [
            'id' => $this->id,
            'partner_trade_no' => $this->partner_trade_no,
            'status' => $result->status
        ]);

        return $result->status;
    }
}
