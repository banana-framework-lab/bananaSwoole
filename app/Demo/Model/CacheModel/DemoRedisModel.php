<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/15
 * Time: 19:52
 */

namespace App\Demo\Model\CacheModel;


use Library\Virtual\Model\CacheModel\AbstractRedisModel;

class DemoRedisModel extends AbstractRedisModel
{
    /**
     * 根据sessionId获取信息
     * 根据redis的key拿：例如：PHPREDIS_SESSION:nj0l7k148f51vbi1dd8g98
     * @return bool|string
     */
    public function getList()
    {
        $sessionInfo = $this->redis->get("wzw");
        return $sessionInfo;
    }
}

