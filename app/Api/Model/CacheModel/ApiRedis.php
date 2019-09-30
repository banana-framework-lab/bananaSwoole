<?php

namespace App\Api\Model\CacheModel;

use App\Library\Virtual\Model\CacheModel\AbstractRedisModel;

class ApiRedis extends AbstractRedisModel
{
    /**
     * 获取平台id下的redis中uuid映射fd的key
     * @return string
     */
    public function getUuidBindingFdKey()
    {
        return "uuid_binding_fd_" . SERVER_ID . '';
    }

    /**
     * 获取平台id下的redis中fd映射uuid的key
     * @return string
     */
    public function getFdBindingUuidKey()
    {
        return "fd_binding_uuid_" . SERVER_ID . '';
    }

    /**
     * 获取记录会话列表锁
     * @param $platformId
     * @return string
     */
    public function getLogSessionLock($platformId)
    {
        return "log_session_lock_{$platformId}";
    }
}