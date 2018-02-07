<?php

namespace App\Components\User\Models;

use App\Components\MoneyTally\Manager as MoneyTallyManager;
use App\Components\User\Exceptions\AccountNotEnoughException;
use App\Library\Lock;
use Cache;
use DB;
use Illuminate\Database\Eloquent\Model;
use Splunk;

/**
 * Class Account
 *
 * @property int $id
 * @property int $money
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\User\Models
 */
class Account extends Model
{
    const SOURCE_TYPE_SYSTEM = 'system';

    const SOURCE_TYPE_ACCOUNT = 'account';

    const SOURCE_TYPE_WECHAT = 'wechat';

    protected $fillable = [
        'id',
        'money',
    ];

    /**
     * Increase account money
     * @param string $type
     * @param int $count
     * @param string $from
     * @param string $operator
     * @param null|string $remark
     * @param null|string $linkType
     * @param int|null $linkId
     */
    public function increase(string $type, int $count, string $from, string $operator, ?string $remark = null, ?string $linkType = null, ?int $linkId = null)
    {
        Splunk::log('user_account_increase', [
            'id' => $this->id,
            'count' => $count,
            'from' => $from,
            'operator' => $operator,
            'remark' => $remark,
            'link_type' => $linkType,
            'link_id' => $linkId,
            'type' => 'money',
            'operation_type' => $type,
        ]);

        Cache::lock("user_account_modify_{$this->id}", 1)->block(1, function() use ($type, $from, $count, $operator, $remark, $linkType, $linkId) {
            $this->reload();

            DB::transaction(function() use ($type, $from, $count, $operator, $remark, $linkType, $linkId) {
                $this->lockForUpdate();
                MoneyTallyManager::create([
                    'user_id' => $this->id,
                    'money' => $count,
                    'from' => $from,
                    'to' => MoneyTallyManager::SOURCE_TYPE_ACCOUNT,
                    'operator' => $operator,
                    'remark' => $remark,
                    'link_type' => $linkType,
                    'link_id' => $linkId,
                    'type' => $type,
                ]);
                $this->update([
                    'money' => $this->money + $count
                ]);
            });
        });
    }

    /**
     * Decrease account money
     * @param string $type
     * @param int $count
     * @param string $to
     * @param string $operator
     * @param null|string $remark
     * @param null|string $linkType
     * @param int|null $linkId
     */
    public function decrease(string $type, int $count, string $to, string $operator, ?string $remark = null, ?string $linkType = null, ?int $linkId = null)
    {
        Splunk::log('user_account_decrease', [
            'id' => $this->id,
            'count' => $count,
            'to' => $to,
            'operator' => $operator,
            'remark' => $remark,
            'link_type' => $linkType,
            'link_id' => $linkId,
            'type' => 'money',
            'operation_type' => $type,
        ]);

        Lock::lock("user_account_modify_{$this->id}", 1, function() use ($type, $to, $count, $operator, $remark, $linkType, $linkId) {
            $this->reload();

            if ($this->money < $count) {
                throw new AccountNotEnoughException($this->id, $this->money, $count);
            }

            DB::transaction(function() use ($type, $to, $count, $operator, $remark, $linkType, $linkId) {
                $this->lockForUpdate();
                MoneyTallyManager::create([
                    'user_id' => $this->id,
                    'money' => $count,
                    'from' => MoneyTallyManager::SOURCE_TYPE_ACCOUNT,
                    'to' => $to,
                    'operator' => $operator,
                    'remark' => $remark,
                    'link_type' => $linkType,
                    'link_id' => $linkId,
                    'type' => $type,
                ]);
                $this->update([
                    'money' => $this->money - $count
                ]);
            });
        });
    }
}
