<?php

namespace App\Components\Order\Models;

use App\Library\Model\Model;

class OrderDetail extends Model
{
    protected static $columns = [
        'id',
        'order_id',
        'product_type',
        'product_id',
        'product_extra',
        'status',
        'created_at',
        'updated_at',
    ];
}
