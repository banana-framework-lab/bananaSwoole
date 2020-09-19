<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 19:52
 */

namespace App\Demp\Model\CacheModel;

use Library\Virtual\Model\CacheModel\AbstractCoroutineRedisModel;

class SessionModel extends AbstractCoroutineRedisModel
{
    /**
     * @var string $sessionKeyHead session的redis的头字符串
     */
    private $sessionKeyHead = 'PHPREDIS_SESSION:';

    /**
     * 根据sessionId获取信息
     * 根据redis的key拿：例如：PHPREDIS_SESSION:nj0l7k148f51vbi1dd8g98
     * @param $sessionId
     * @return array
     */
    public function getSessionInfo(string $sessionId): array
    {
        $sessionInfo = unserialize($this->redis->get("{$this->sessionKeyHead}{$sessionId}"));
        if ($sessionInfo) {
            return $sessionInfo;
        } else {
            return [];
        }
    }

    /**
     * 设置redis数据
     * @param string $sessionId
     * @param array $sessionInfo
     */
    public function setSessionInfo(string $sessionId, array $sessionInfo)
    {
        $this->redis->set("{$this->sessionKeyHead}{$sessionId}", serialize($sessionInfo));
    }
}

