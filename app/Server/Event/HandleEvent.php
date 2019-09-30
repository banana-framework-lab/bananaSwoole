<?php

namespace App\Server\Event;

use App\Library\Virtual\Property\AbstractProperty;
use App\Server\Config\MessageType;
use App\Server\Model\CacheModel\WebSocketRedis;
use App\Server\Model\DataBaseModel\WebSocketMongo;
use App\Server\Property\MessageProperty;
use App\Server\WebsocketServer;
use Swoole\WebSocket\Server;

/**
 * handle的事件
 * User: zzh
 * Date: 2019/8/22
 * Time: 16:09
 */
class HandleEvent
{
    /**
     * @var null $cache
     */
    private $cache = null;

    /**
     * @var null $db
     */
    private $db = null;

    /**
     * HandleEvent constructor.
     */
    public function __construct()
    {
        $this->cache = new WebSocketRedis();
    }

    /**
     * 用户连接绑定数据
     * 把一个平台下用户的唯一标示uuid绑定fd
     * @param int $platformId
     * @param string $uuid
     * @param int $fd
     * @throws \Exception
     */
    public function uuidConnect($platformId, $uuid, $fd)
    {
        $bindingEvent = new BindingEvent();
        $uuidBindingInfo = $bindingEvent->uuidBindingInfo($platformId, $uuid);
        if ($uuidBindingInfo['canBinding']) {
            if (!$bindingEvent->uuidExistFd($uuidBindingInfo['list'], $fd)) {
                var_dump('success_binding');
                $bindingEvent->uuidBindingFd($uuidBindingInfo['list'], $platformId, $uuid, $fd);
                $bindingEvent->fdBindingUuid($platformId, $fd, $uuid);
            } else {
                var_dump('not_use_binding');
            }
        } else {
            var_dump('success_replace_binding');
            $this->uuidReplaceConnect($uuidBindingInfo['list'], $platformId, $uuid, $fd);
        }
    }

    /**
     * 1发送一条给第一个fd断开连接的信号
     * 2用户把原来的fd删除
     *
     * @param array $uuidBindingList
     * @param int $platformId
     * @param int $uuid
     * @param int $fd
     * @throws \Exception
     */
    private function uuidReplaceConnect($uuidBindingList, $platformId, $uuid, $fd)
    {
        $bindingEvent = new BindingEvent();
        $message = new MessageEvent();

        $messageBody = (new MessageProperty())->setProperty([
            'from_uuid' => '0',
            'from_fd' => '0',
            'from_server_id' => (int)SERVER_ID,
            'to_uuid' => "{$uuid}",
            'to_fd' => '' . (int)$uuidBindingList[0]['fd'] . '',
            'to_server_id' => "{$uuidBindingList[0]['server_id']}",
            'platform_id' => "{$uuidBindingList[0]['platform_id']}",
            'message' => '被迫下线通知',
            'send_time' => getMillisecond(),
            'type' => MessageType::CLOSE,
            'window_id' => "0_{$uuid}"
        ]);
        $message->publishMessage($messageBody, $uuidBindingList[0]['server_id']);

        $bindingEvent->uuidReplaceFd($uuidBindingList, $platformId, $uuid, $fd);
    }

    /**
     * 用户关闭连接取绑数据
     * 把一个平台下用户的唯一标示uuid绑定fd
     * 把一个平台下fd取绑uuid
     * @param int $fd
     */
    public function uuidClose($fd)
    {
        $bindingEvent = new BindingEvent();
        $uuidInfo = $bindingEvent->fdBindingInfo($fd);
        if ($uuidInfo) {
            $uuid = $uuidInfo['uuid'];
            $platformId = $uuidInfo['platform_id'];
            $uuidBindingList = $bindingEvent->uuidBindingInfo($platformId, $uuid);
            $bindingEvent->uuidUnbindingFd($uuidBindingList['list'], $uuid, $fd);
            $bindingEvent->fdUnBindingUuid($fd);
        }
    }

    /**
     * 发送消息插入队列
     * @param AbstractProperty $message
     * @throws \Exception
     */
    public function sendMessage($message)
    {
        $message = $message->toArray();

        //获取uid绑定数据
        var_dump('get_message_body', $message);
        $bindingEvent = new BindingEvent();
        $toUidInfo = $bindingEvent->uuidBindingInfo($message['platform_id'], $message['to_uuid']);
        $fromUidInfo = $bindingEvent->uuidBindingInfo($message['platform_id'], $message['from_uuid']);
        $toUidFdList = $toUidInfo['list'];
        $fromUidFdList = $fromUidInfo['list'];

        //组装必要数据
        $time = $message['send_time'];
        if ((((int)$message['from_uuid']) < ((int)$message['to_uuid']))) {
            $windowId = "{$message['from_uuid']}_{$message['to_uuid']}";
        } else {
            $windowId = "{$message['to_uuid']}_{$message['from_uuid']}";
        }

        //过滤message中有html注入脚本
        $message['message'] = checkData($message['message']);

        //把接受者消息推入队列
        var_dump('toUidList', $toUidFdList);

        //初始化消息对象(不管消息接受者是否存在，都直接存入数据库)
        $toMessageObject = (new MessageProperty())->setProperty([
            'from_uuid' => "{$message['from_uuid']}",
            'from_fd' => "{$message['from_fd']}",
            'from_server_id' => (int)SERVER_ID,
            'to_uuid' => "{$message['to_uuid']}",
            'to_fd' => "0",
            'to_server_id' => (int)SERVER_ID,
            'platform_id' => "0",
            'message' => "{$message['message']}",
            'send_time' => $time,
            'type' => MessageType::TO_MESSAGE,
            'window_id' => $windowId
        ]);

        foreach ($toUidFdList as $key => $value) {
            $toMessageObject = (new MessageProperty())->setProperty([
                'from_uuid' => "{$message['from_uuid']}",
                'from_fd' => "{$message['from_fd']}",
                'from_server_id' => (int)SERVER_ID,
                'to_uuid' => "{$message['to_uuid']}",
                'to_fd' => '' . (int)$value['fd'] . '',
                'to_server_id' => (int)$value['server_id'],
                'platform_id' => "{$value['platform_id']}",
                'message' => "{$message['message']}",
                'send_time' => $time,
                'type' => MessageType::TO_MESSAGE,
                'window_id' => $windowId
            ]);
            $messageEvent = new  MessageEvent();
            $messageEvent->publishMessage($toMessageObject, $value['server_id']);
        }

        //记录数据库
        $this->db = new WebSocketMongo();
        $this->db->insert($toMessageObject->toArray());


        //把发送者消息推入队列
        var_dump('fromUidList', $fromUidFdList);
        foreach ($fromUidFdList as $key => $value) {
            $fromMessageObject = (new MessageProperty())->setProperty([
                'from_uuid' => "0",
                'from_fd' => "0",
                'from_server_id' => (int)SERVER_ID,
                'to_uuid' => "{$message['from_uuid']}",
                'to_fd' => "{$value['fd']}",
                'to_server_id' => (int)$value['server_id'],
                'platform_id' => "{$value['platform_id']}",
                'message' => "{$message['message']}",
                'send_time' => $time,
                'type' => MessageType::FROM_MESSAGE,
                'window_id' => $windowId
            ]);
            $messageEvent = new  MessageEvent();
            $messageEvent->publishMessage($fromMessageObject, $value['server_id']);
        }
    }


    /**
     * 解析消息体
     * @param array $message
     * @param Server $server
     * @return bool
     */
    public function solveMessage($message, $server)
    {
        $toFd = $message['to_fd'];
//        $msg = $message['message'];
        $type = $message['type'];
        $sendTime = substr($message['send_time'], 0, 10);
        // 消息发送时间小于进程启动时间的消息，都丢掉
        if ($sendTime < WebsocketServer::$processStartTime) {
            return true;
        }
        var_dump('solve_message', $message);
        // push判断一下
        if (!$server->exist((int)($toFd))) {
            var_dump('not found fd');
            return true;
        }

        switch ($type) {
            case MessageType::CLOSE:
                $server->push($toFd, json_encode($message, JSON_UNESCAPED_UNICODE), WEBSOCKET_OPCODE_TEXT);
                $server->close($toFd);
                break;
            case MessageType::TO_MESSAGE:
                $pushResult = $server->push($toFd, json_encode($message, JSON_UNESCAPED_UNICODE), WEBSOCKET_OPCODE_TEXT);
                if ($pushResult) {
                    var_dump('success send');
                    return true;
                } else {
                    var_dump('fail send');
                    return true;
                }
                break;
            case MessageType::FROM_MESSAGE:
                $pushResult = $server->push($toFd, json_encode($message, JSON_UNESCAPED_UNICODE), WEBSOCKET_OPCODE_TEXT);
                if ($pushResult) {
                    var_dump('success send');
                    return true;
                } else {
                    var_dump('fail send');
                    return true;
                }
                break;
        }
        return true;
    }
}