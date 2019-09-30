<?php

namespace App\Server\Event;

use App\Library\Virtual\Property\AbstractProperty;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSwooleConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Swoole\WebSocket\Server;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/23
 * Time: 9:27
 */
class MessageEvent
{
    const EXCHANGE = 'webSocket_message_exchange';

    const QUEUE_HEADER = 'webSocket_tanwange_queue_';

    const SERVER_ID = SERVER_ID;

    /**
     * 发送消息到消息队列
     *
     * @param AbstractProperty $messageBody
     */
    public function publishMessage($messageBody, $serverId)
    {
        $messageBody = $messageBody->toArray();
        var_dump('message_content', $messageBody);
        $rabbitConfig = IS_SERVER ? RABBITMQ_CONFIG['server'] : RABBITMQ_CONFIG['local'];
        $connection = new AMQPStreamConnection(
            $rabbitConfig['host'],
            $rabbitConfig['port'],
            $rabbitConfig['user'],
            $rabbitConfig['password'],
            $rabbitConfig['vhost']
        );
        $channel = $connection->channel();

        //根据serverId进行创造队列
        $queue = (self::QUEUE_HEADER) . $serverId;
        // 队列名称名称，需要带上服务器ID 并且这里routingKey和queue名称保持一致
        $channel->queue_declare($queue, false, true, false, false);
        $channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
        $channel->queue_bind($queue, self::EXCHANGE, $queue);
        $message = new AMQPMessage(
            json_encode($messageBody,JSON_UNESCAPED_UNICODE),
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );
        $channel->basic_publish($message, self::EXCHANGE, $queue);
        $channel->close();
        $connection->close();
    }

    /***
     * 消化消息队列的信息
     *
     * @param Server $server
     */
    public function consumeMessage($server)
    {
        go(function () use ($server) {
            $queue = self::QUEUE_HEADER . self::SERVER_ID;
            $consumerTag = 'consumer';
            $rabbitConfig = IS_SERVER ? RABBITMQ_CONFIG['server'] : RABBITMQ_CONFIG['local'];
            $connection = new AMQPSwooleConnection($rabbitConfig['host'],
                $rabbitConfig['port'],
                $rabbitConfig['user'],
                $rabbitConfig['password'],
                $rabbitConfig['vhost']
            );
            $channel = $connection->channel();
            $channel->queue_declare($queue, false, true, false, false);
            $channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
            $channel->queue_bind($queue, self::EXCHANGE);

            var_dump("start_wait_{$queue}_consume");
            /**
             * @param \PhpAmqpLib\Message\AMQPMessage $message
             */
            $callback = function ($message) use ($server) {
                var_dump('do_consume');
                // 消息确认，不管后面运行结果如何都确认
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                // 消息体
                $messageBody = json_decode($message->body, true);
                $handleEvent = new HandleEvent();
                $handleEvent->solveMessage($messageBody, $server);

            };

            $channel->basic_consume($queue, $consumerTag, false, false, false, false, $callback);
            while (count($channel->callbacks)) {
                $channel->wait();
            }
        });
    }
}