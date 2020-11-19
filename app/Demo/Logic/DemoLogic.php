<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:15
 */

namespace App\Demo\Logic;

use App\Demo\Model\CacheModel\DemoRedisModel;
use App\Demo\Model\DatabaseModel\DemoMysqlModel;
use App\Demo\Model\MessageQueueModel\DemoRabbitMQModel;

class DemoLogic
{
    public function testMysql()
    {
        return (new DemoMysqlModel())->getList();

    }

    public function testRedis()
    {
        return (new DemoRedisModel())->getList();
    }

    public function testRabbitMQ()
    {
        return (new DemoRabbitMQModel())->pushMessage();
    }
}