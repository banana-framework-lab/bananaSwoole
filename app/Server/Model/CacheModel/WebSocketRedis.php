<?php

namespace App\Server\Model\CacheModel;

use App\Library\Virtual\Model\CacheModel\AbstractRedisModel;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/22
 * Time: 16:40
 */
class WebSocketRedis extends AbstractRedisModel
{
    /**
     * 获取平台id下的redis中uuid映射fd的key
     * @return string
     */
    public function getUuidBindingFdKey()
    {
        return "uuid_binding_fd_".SERVER_ID.'';
    }

    /**
     * 获取平台id下的redis中fd映射uuid的key
     * @return string
     */
    public function getFdBindingUuidKey()
    {
        return "fd_binding_uuid_".SERVER_ID.'';
    }

    /**
     * 重启redis数据
     */
    public function restartFdData(){
        $this->redis->del("uuid_binding_fd_".SERVER_ID.'');
        $this->redis->del("fd_binding_uuid_".SERVER_ID.'');
    }
}