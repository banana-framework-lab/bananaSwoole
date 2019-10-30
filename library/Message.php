<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/30
 * Time: 15:19
 */

namespace Library;

use Library\Entity\MessageQueue\EntityRabbit;
use Library\Entity\Swoole\EntitySwooleWebSocketSever;
use Library\Virtual\Object\AbstractMessageObject;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSwooleConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Message
 * @package Library
 */
class Message
{
    /**
     * 发送消息到消息队列
     * @param AbstractMessageObject $messageObject
     */
    public static function publishMessage(AbstractMessageObject $messageObject)
    {
        $connection = EntityRabbit::getInstance();

        $channel = $connection->channel();

        $queue = $messageObject->channel . "_exchange_" . Config::get('app.server_id');

        $channel->queue_declare($queue, false, true, false, false);

        $exchangeName = Config::get('app.is_server') ? Config::get('rabbit.server.message_exchange') : Config::get('rabbit.local.message_exchange');

        $channel->exchange_declare($exchangeName, 'direct', false, true, false);

        $channel->queue_bind($queue, $exchangeName, $queue);

        $message = new AMQPMessage(
            serialize($messageObject->toMessageData()),
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );
        $channel->basic_publish($message, $exchangeName, $queue);

        $channel->close();
    }

    /**
     * 消化消息队列的消息
     */
    public static function consumeMessage()
    {
        $channelList = Channel::getChannelList();
        foreach ($channelList as $key => $channel) {
            go(function () use ($channel) {
                $queue = (string)$channel . "_exchange_" . Config::get('app.server_id');
                $consumerTag = 'consumer';
                $rabbitConfig = $rabbitConfig = Config::get('app.is_server') ? Config::get('rabbit.server') : Config::get('rabbit.local');
                $connection = new AMQPSwooleConnection($rabbitConfig['host'],
                    $rabbitConfig['port'],
                    $rabbitConfig['user'],
                    $rabbitConfig['password'],
                    $rabbitConfig['vhost']
                );

                $exchangeName = Config::get('app.is_server') ? Config::get('rabbit.server.message_exchange') : Config::get('rabbit.local.message_exchange');

                $channel = $connection->channel();

                $channel->queue_declare($queue, false, true, false, false);

                $channel->exchange_declare($exchangeName, 'direct', false, true, false);

                $channel->queue_bind($queue, $exchangeName);

                /**
                 * @param \PhpAmqpLib\Message\AMQPMessage $message
                 */
                $callback = function ($message) {

                    /* @var AMQPChannel $channel */
                    $channel = $message->delivery_info['channel'];

                    $channel->basic_ack($message->delivery_info['delivery_tag']);
                    // 消息体
                    $messageBody = unserialize($message->body);

                    // push判断一下
                    if (EntitySwooleWebSocketSever::getInstance()->exist((int)($messageBody['toFd']))) {
                        EntitySwooleWebSocketSever::getInstance()->push(
                            $messageBody['toFd'],
                            json_encode($messageBody, JSON_UNESCAPED_UNICODE),
                            WEBSOCKET_OPCODE_TEXT
                        );
                    }
                };

                $channel->basic_consume($queue, $consumerTag, false, false, false, false, $callback);

                while (count($channel->callbacks)) {
                    $channel->wait();
                }
            });
        }
    }
}