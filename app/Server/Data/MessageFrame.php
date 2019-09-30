<?php
/**
 * websocket请求参数
 * User: zzh
 * Date: 2019/08/22
 */

namespace App\Server\Data;

use App\Library\Service\MessageKeyService;

class MessageFrame
{
    const SIGN_KEY = 'jubaofangbananaandwzwsocketframework';

    /**
     * 初始化参数对象
     *
     * @param array $property
     */
    public function __construct($property = [])
    {
        foreach ($property as $property_name => $property_value) {
            if (property_exists($this, $property_name)) {
                $this->$property_name = $property_value;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function messageVerifySign($data)
    {
        $sign = strtoupper($data['sign']);
        unset($data['sign']);
        $signKey = MessageKeyService::instance()->getMessageKey($data['platform_id'], $data['from_uuid'], $data['to_uuid']);
        $mySign = strtoupper(md5("{$data['from_uuid']}{$data['message']}{$data['platform_id']}{$data['to_uuid']}{$signKey}"));
        var_dump($data);
        var_dump(http_build_query($data));
        var_dump($signKey);
        var_dump($mySign);
        var_dump($sign);

        if ($mySign !== $sign) {
            throw new \Exception('发消息的错误签名');
        }
    }
}