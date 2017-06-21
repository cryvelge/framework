<?php

namespace App\Components\Payment\Models;

use App\Library\Model\Model;

class WeChatPayOrder extends Model
{
    protected static $columns = [
        'id',
        'order_id',
        'serial_number',
        'we_chat_serial_number',
    ];
}
