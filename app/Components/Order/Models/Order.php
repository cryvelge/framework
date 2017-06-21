<?php

namespace App\Components\Order\Models;

use App\Library\Model\Model;

class Order extends Model
{
    protected static $columns = [
        'id',
        'serial_number',
        'user_id',
        'status',
        'price',
        'remark',
        'created_at',
        'updated_at',
    ];

    public function pay()
    {
        //
    }
}
