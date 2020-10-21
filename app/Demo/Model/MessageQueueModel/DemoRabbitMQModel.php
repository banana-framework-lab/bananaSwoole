<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */

namespace App\Demo\Model\MessageQueueModel;

use Library\Virtual\Model\MessageQueueModel\AbstractRabbitMQModel;


class DemoRabbitMQModel extends AbstractRabbitMQModel
{
    /**
     * AdminModel constructor.
     */
    public function __construct()
    {
        $this->vhost = 'test';
        $this->queue = 'test';
        $this->exchange_name = 'test';
        parent::__construct();
    }

    /**
     *
     */
    public function pushMessage()
    {
        $this->produce('test');
    }

    /**
     * @throws \ErrorException
     */
    public function digestMessage()
    {
        $this->digest(function ($message) {
            echo $message;
            return true;
        });
    }
}