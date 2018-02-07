<?php

namespace App\Components\Message\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Notification
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string $title 通知标题
 * @property array $body 通知主体内容
 * @property string|null $url 链接
 * @property string|null $pic 图片
 * @property string $type
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 *
 * @package App\Components\Message\Models
 */
class Notification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'url',
        'pic',
        'type',
        'status',
    ];

    protected $casts = [
        'body' => 'array',
    ];

    // 消息状态:未读消息
    const STATUS_PENDING = 'PENDING';
    // 消息状态:已读消息
    const STATUS_READED = 'READED';
    // 系统消息
    const TYPE_SYSTEM = 'SYSTEM';

    /**
     * 获得一条通知的内容
     *
     * @param int|array $notification_id
     *
     * @return
     */
    public static function getNotification($notification_id)
    {
        if (is_int($notification_id)) {
            return static::where('id', $notification_id)->first();
        } else {
            return static::whereIn('id', $notification_id)->get();
        }
    }

    /**
     * 获得通知的内容
     *
     * @param int $msg_id 消息id
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getNotificationByMsgId($msg_id)
    {
        if (is_int($msg_id)) {
            return static::where('msg_id', $msg_id)->first();
        } else {
            return static::whereIn('msg_id', $msg_id)->get();
        }
    }

    /**
     * 获得单一用户的所有通知
     *
     * @param int $user_id 用户id
     * @param string $status 状态
     * @param int|null $id id
     * @param int|null $count 数量
     */
    public static function getUserNotification($user_id, $status, $id = null, $count = null)
    {
        $builder = static::where('user_id', $user_id)
            ->where('status', $status);
        if ($id != null) {
            $builder->where('id', '<', $id);
        }
        if ($count != null) {
            $builder->limit($count);
        }
        return $builder
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * 读了一条通知
     */
    public function read()
    {
        $this->status = self::STATUS_READED;
        return $this->save();
    }

    /**
     *
     */
    public static function updateNotification($notification_ids, $update)
    {
        return static::whereIn('id', $notification_ids)->update($update);
    }

    /**
     * 读了多条通知
     *
     * @param array $notification_ids
     */
    public static function readNotification(array $notification_ids)
    {
        return static::whereIn('id', $notification_ids)->update([
            'status' => self::STATUS_READED,
        ]);
    }

    /**
     * 删除多条通知
     *
     * @param array $notification_ids
     */
    public static function destroyNotification(array $notification_ids)
    {
        return static::whereIn('id', $notification_ids)->update([
            'deleted_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * 查询某个用户当前有多少消息未读
     *
     * @param int $user_id 用户id
     *
     * @return int 返回一个数值
     */
    public static function pendingCount($user_id) {
        // count返回一个数值
        return static::where('user_id', $user_id)->where('status', self::STATUS_PENDING)->count();
    }
}