<?php

namespace App\Components\Storage\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class File
 *
 * @property int $id
 * @property string $type
 * @property string $key
 * @property string $store
 * @property string $created_at
 * @property string $updated_at
 *
 * @package App\Components\Storage\Models
 */
class File extends Model
{
    protected $fillable = [
        'type',
        'key',
        'store',
    ];
}
