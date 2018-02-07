<?php

namespace App\Components\MoneyTally\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MoneyTally
 *
 * @property int $id
 * @property int $user_id
 * @property int $money
 * @property string $from
 * @property string $to
 * @property string $link_type
 * @property int $link_id
 * @property string $operator
 * @property string $remark
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\MoneyTally\Models
 */
class MoneyTally extends Model
{
    protected $fillable = [
        'user_id',
        'money',
        'from',
        'to',
        'link_type',
        'link_id',
        'operator',
        'remark',
        'type',
    ];
}
