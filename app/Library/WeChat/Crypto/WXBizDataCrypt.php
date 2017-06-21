<?php

namespace App\Library\WeChat\Crypto;

use Exception;

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */


class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     *
     * @return string 成功0，失败返回对应的错误码
     * @throws
     */
    public function decryptData($encryptedData, $iv)
    {
        if (strlen($this->sessionKey) != 24) {
            throw new Exception('Illegal Aes Key', ErrorCode::$IllegalAesKey);
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            throw new Exception('Illegal Iv', ErrorCode::$IllegalIv);
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);

        $decrypted = openssl_decrypt($aesCipher, 'aes-128-cbc', $aesKey, OPENSSL_RAW_DATA, $aesIV);
        $pkc_encoder = new PKCS7Encoder;
        $result = $pkc_encoder->decode($decrypted);

        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        return $result;
    }

}

