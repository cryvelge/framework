<?php

namespace App\Components\MoneyTally;

use App\Components\MoneyTally\Models\MoneyTally;
use Illuminate\Support\Collection;
use Splunk;

class Manager
{
    const SOURCE_TYPE_SYSTEM = 'system';

    const SOURCE_TYPE_ACCOUNT = 'account';

    const SOURCE_TYPE_WECHAT = 'wechat';

    const LINK_TYPE_ORDER = 'order';

    const LINK_TYPE_CASH_BACK = 'cash_back';

    const LINK_TYPE_PROFIT = 'profit';

    const TYPE_ACCOUNT_PAY = 'account_pay';

    const TYPE_WECHAT_PAY = 'wechat_pay';

    const TYPE_ACCOUNT_PAY_CANCEL = 'account_pay_cancel';

    const TYPE_WECHAT_PAY_CANCEL = 'wechat_pay_cancel';

    const TYPE_WITHDRAW = 'withdraw';

    const TYPE_WITHDRAW_FAIL = 'withdraw_fail';

    /**
     * @param array $data
     * @return MoneyTally
     */
    public static function create(array $data)
    {
        Splunk::log('money_tally_create', $data);

        return MoneyTally::create([
            'user_id' => $data['user_id'],
            'money' => $data['money'],
            'from' => $data['from'],
            'to' => $data['to'],
            'link_type' => $data['link_type'] ?? null,
            'link_id' => $data['link_id'] ?? null,
            'operator' => $data['operator'],
            'remark' => $data['remark'] ?? null,
            'type' => $data['type'] ?? null,
        ]);
    }

    /**
     * @param array $query
     * @return Collection|null
     */
    public static function query($query) {
        if (count($query) == 0) {
            return null;
        }
        $ret = null;

        foreach (array_only($query, ['user_id', 'from', 'to', 'type']) as $key => $value) {
            if (is_null($ret)) {
                $ret = MoneyTally::where($key, $value);
            } else {
                $ret = $ret->where($key, $value);
            }
        }

        return $ret->get();
    }
}
