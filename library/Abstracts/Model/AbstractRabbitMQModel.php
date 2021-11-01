<?php

namespace Library\Abstracts\Model;

use Closure;
use ErrorException;
use Library\Container;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

abstract class AbstractRabbitMQModel
{
    /**
     * 默认最长延迟时间为86400s  一天
     */
    const MAX_DEFAULT_DELAY_SECOND = 86400;

    /**
     * RabbitMq连接
     *
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * rabbit的vhost
     *
     * @var string
     */
    protected $vhost = '';

    /**
     * rabbit的queue
     *
     * @var string
     */
    protected $queue = '';

    /**
     * rabbit的exchange_name
     *
     * @var string
     */
    protected $exchange_name = '';


    /**
     * 消化消息队列的数据
     * @param Closure $digest_function
     * @throws ErrorException
     */
    public function digest(Closure $digest_function)
    {
        $this->checkConfig();

        $channel = $this->connection->channel();

        $channel->queue_declare($this->queue, false, true, false, false);

        $channel->basic_qos(null, 10, null);

        $channel->exchange_declare($this->exchange_name, 'direct', false, true, false);

        $channel->queue_bind($this->queue, $this->exchange_name);

        /**
         * @param AMQPMessage $message
         */
        $callback = function ($message) use ($digest_function) {

            /* @var AMQPChannel $channel */
            $channel = $message->get('channel');

            $message_data = unserialize($message->body);

            $digest_result = $digest_function($message_data);

            if ($digest_result) {
                $channel->basic_ack($message->get('delivery_tag'));
            }
        };

        $channel->basic_consume($this->queue, 'consumer', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * 生产数据到消息队列
     * @param string $message_data
     */
    public function produce(string $message_data)
    {
        $this->checkConfig();

        $channel = $this->connection->channel();

        $channel->queue_declare($this->queue, false, true, false, false);

        $channel->exchange_declare($this->exchange_name, 'direct', false, true, false);

        $channel->queue_bind($this->queue, $this->exchange_name, $this->queue);

        $message = new AMQPMessage(
            $message_data,
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $channel->basic_publish($message, $this->exchange_name, $this->queue);

        $channel->close();
    }

    /**
     * 生产延迟消息队列
     * @param array $message_data
     * @param int $ttl 单位：秒
     * @param int $max_delay_second 单位：秒
     * @param bool $need_time_suffix
     */
    public function delayProduce(array $message_data, int $ttl, int $max_delay_second = 0, $need_time_suffix = false)
    {
        $this->checkConfig();

        $channel = $this->connection->channel();

        $delay_tale = new AMQPTable();
        $delay_tale->set('x-dead-letter-exchange', "delay_{$this->exchange_name}");
        $delay_tale->set('x-dead-letter-routing-key', "delay_{$this->queue}");
        $delay_tale->set('x-message-ttl', ($max_delay_second > 0 ? $max_delay_second : self::MAX_DEFAULT_DELAY_SECOND) * 1000);

        $suffix = $need_time_suffix ? "_{$ttl}" : '';

        $channel->queue_declare("ttl_{$this->queue}{$suffix}", false, true, false, false, false, $delay_tale);
        // 这里是ttl队列，所以交换机这里要durable是false
        $channel->exchange_declare("ttl_{$this->exchange_name}{$suffix}", 'direct', false, false, false);
        $channel->queue_bind("ttl_{$this->queue}{$suffix}", "ttl_{$this->exchange_name}{$suffix}", "ttl_{$this->queue}{$suffix}");

        $channel->queue_declare("delay_{$this->queue}", false, true, false, false, false);
        $channel->exchange_declare("delay_{$this->exchange_name}", 'direct', false, true, false);
        $channel->queue_bind("delay_{$this->queue}", "delay_{$this->exchange_name}", "delay_{$this->queue}");

        // expiration 的单位是毫秒
        $message = new AMQPMessage(
            serialize($message_data),
            [
                'content_type' => 'text/plain',
                'expiration' => (int)$ttl * 1000,
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $channel->basic_publish($message, "ttl_{$this->exchange_name}{$suffix}", "ttl_{$this->queue}{$suffix}");

        $channel->close();
    }

    /**
     * 消化延迟消息队列的数据
     * @param Closure $digest_function
     * @throws ErrorException
     */
    public function delayDigest(Closure $digest_function)
    {
        $this->checkConfig();

        $channel = $this->connection->channel();

        $channel->queue_declare("delay_{$this->queue}", false, true, false, false);

        $channel->exchange_declare("delay_{$this->exchange_name}", 'direct', false, true, false);

        $channel->queue_bind("delay_{$this->queue}", "delay_{$this->exchange_name}");

        /**
         * @param AMQPMessage $message
         */
        $callback = function ($message) use ($digest_function) {

            /* @var AMQPChannel $channel */
            $channel = $message->delivery_info['channel'];

            $message_data = unserialize($message->body);

            $digest_result = $digest_function($message_data);

            if ($digest_result) {
                $channel->basic_ack($message->delivery_info['delivery_tag']);
            }
        };

        $channel->basic_consume("delay_{$this->queue}", 'consumer', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    private function checkConfig()
    {
        if (!$this->queue) {
            throw new LogicException('请先配置mq的queue');
        }
        if (!$this->exchange_name) {
            throw new LogicException('请先配置mq的exchange_name');
        }
    }

    /**
     * 初始化连接
     * AbstractSqlModel constructor.
     */
    public function __construct()
    {
        if (!$this->vhost) {
            throw new LogicException('请先配置mq的vhost');
        }
        $this->connection = Container::getRabbitMQPool()->get();
    }


    public function __destruct()
    {
        Container::getRabbitMQPool()->back($this->connection);
    }
}