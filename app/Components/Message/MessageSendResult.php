<?php

namespace App\Components\Message;

/**
 * Class MessageSendResult
 *
 * @property int $result 发送结果
 * @property \Exception $error 错误信息
 * @property mixed $response 响应结果
 *
 * @package App\Components\Message
 */
class MessageSendResult {

    //
    public $result = null;
    // 如果使用notifier发送时报错，将保存结果
    public $error = null;
    // 如果获取到从服务器的返回结果，那么就保存返回结果数据
    public $response = null;

    /**
     * 是否成功
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->result == 0;
    }

    /**
     * 获得结果
     *
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 获得错误信息
     * 
     * @return \Exception 错误信息
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获得响应信息
     *
     * @return mixed 响应信息
     */
    public function getResponse()
    {
        return $this->response;
    }
}