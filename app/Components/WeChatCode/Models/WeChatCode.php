<?php

namespace App\Components\WeChatCode\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WeChatCode
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $handler
 * @property string $description
 * @property string $scene
 * @property array $data
 * @property string $ticket
 * @property string $url
 * @property string $created_at
 * @property string $expire_at
 * @package App\Components\QrCode\Models
 */
class WeChatCode extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'handler',
        'description',
        'scene',
        'data',
        'ticket',
        'url',
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function getImageUrl()
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $this->ticket;
    }
}
