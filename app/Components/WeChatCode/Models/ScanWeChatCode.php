<?php

namespace App\Components\WeChatCode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ScanWeChatCode
 * @property int $id
 * @property int $user_id
 * @property int $we_chat_code_id
 * @property bool $subscribe
 * @property string $created_at
 * @package App\Components\WeChatCode\Models
 */
class ScanWeChatCode extends Model
{
    protected $fillable = [
        'user_id',
        'we_chat_code_id',
        'subscribe',
    ];
}
