<?php

namespace App\Components\Message\Reader;

use App\Components\Message\Manager;
use App\Components\Message\Message;
use App\Components\Message\Models\Notification;

/**
 * 
 *
 * @package App\Components\Message\Reader
 */
class NotificationReader
{
    /**
     * 获得消息
     *
     * @param int|array $id notification的id
     *
     * @return Message|array 如果传入的是一个
     */
    public function get($id)
    {
        $is_array = is_array($id);
        if (!$is_array) {
            $id = [$id];
        }

        $notifications = Notification::getNotification($id);
        $id_map_notification = $notifications->mapWithKeys(function($notification) {
            return [
                $notification->id => $notification,
            ];
        });
        $messages = array_map(function($id) use($id_map_notification) {
            $notification = $id_map_notification[$id];
            $message = new Message([
                'notification_id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $notification->url,
                'pic' => $notification->pic,
            ]);
            $message->setId($notification->msg_id);
            $message->setUserId($notification->user_id);
            $message->setTopic('notification');
            return $message;
        }, $id);

        if ($is_array) {
            return $messages;
        } else {
            return $messages[0];
        }
    }

    /**
     * 获得用户未读消息
     *
     * @param int $user_id 用户id
     * @param int $id id
     * @param int $count 数量
     */
    public function getUserPendingNotification($user_id, $id = null, $count = null) {
        return Notification::getUserNotification($user_id, Notification::STATUS_PENDING, $id, $count);
    }

    /**
     * 获得用户已读消息
     *
     * @param int $user_id 用户id
     * @param int $id id
     * @param int $count 数量
     */
    public function getUserReadedNotification($user_id, $id, $count = null) {
        return Notification::getUserNotification($user_id, Notification::STATUS_READED, $id, $count);
    }

    /**
     *
     * @param int|array $id notification的id
     * @param array $update 更新数据
     */
    public function update($id, $update)
    {
        if (is_int($id)) {
            return Notification::updateNotification([$id], $update);
        } else if (is_array($id)) { // array
            return Notification::updateNotification($id, $update);
        } else {
            // 类型错误Exception
        }
    }

    /**
     * 读过通知
     *
     * @param int|array $id notification的id
     */
    public function read($id)
    {
        if (is_int($id)) {
            return Notification::readNotification([$id]);
        } else if (is_array($id)) { // array
            return Notification::readNotification($id);
        } else {
            // 类型错误Exception
        }
    }

    /**
     * 删除通知
     *
     * @param int|array $id notification的id
     */
    public function remove($id)
    {
        if (is_int($id)) {
            return Notification::destroyNotification([$id]);
        } else if (is_array($id)) { // array
            return Notification::destroyNotification($id);
        } else {
            // 类型错误Exception
        }
    }

    /**
     * 获得未读消息条数
     *
     * @param int|array $id notification的id
     */
    public function pendingCount($userId) {
        return Notification::pendingCount($userId);
    }
}