<?php

namespace App\Components\User\Models;

use App\Library\Model\Model;

class User extends Model
{
    protected static $columns = [
        'id',
        'serial_number',
        'union_id',
        'name',
        'mobile',
        'nickname',
        'avatar',
        'gender',
        'country',
        'province',
        'city',
        'address',
        'created_at',
        'updated_at',
    ];
    protected static $serializeColumns = [];
    protected static $useSoftDelete = false;

    public static function register($userInfo)
    {
        return static::create($userInfo);
    }

    public static function findByUnionId($unionId)
    {
        return static::where('union_id', $unionId)->first;
    }
}
