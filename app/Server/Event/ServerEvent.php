<?php
/**
 * websocket Server事件回调
 * 因为swoole热重启机制，所以需要此类做个中转才能做到热重启
 * User: zzh
 * Date: 2019/08/22
 */

namespace App\Server\Event;

use App\Server\Data\MessageFrame;
use App\Server\Data\OpenRequest;
use App\Server\Config\MessageType;
use App\Server\Model\CacheModel\WebSocketRedis;
use App\Server\Property\MessageProperty;
use App\Server\WebsocketServer;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use App\Library\Service\ForbidWordService;

class ServerEvent
{

    /**
     * @var Server
     */
    private $server;

    private $cache;

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->cache = new WebSocketRedis();
    }


    /**
     * 启动进程时开启协程消费队列中的消息
     *
     * @param Server $server
     * @param int $workId
     */
    public function onWorkerStart(Server $server, $workId)
    {
        $this->cache->restartFdData();
        WebsocketServer::$processStartTime = time(); // 记录进程启动时间
        $messageEvent = new MessageEvent();
        $messageEvent->consumeMessage($server);
    }

    /**
     * open事件回调
     *
     * @param Server $server
     * @param Request $req
     * @return bool
     */
    public function onOpen(Server $server, Request $req)
    {
        // 请求参数对象
        $request = new OpenRequest($req->get);
        $request->setRequestHeader($req->header);

        // 黑名单、验签、验证参数完整性
        try {
            if (IS_SERVER) {
                $request->filterBlacklist($req);
                $request->verifySign($req->get);
                $request->verifyNecessary();
            }
        } catch (\Exception $e) {
            return $this->errorClose($req->fd, $e);
        }

        //进行用户连接的数据绑定
        $handleConnect = new HandleEvent();
        try {
            $handleConnect->uuidConnect($request->platformId, $request->uuid, $req->fd);
        } catch (\Exception $e) {
            $this->errorClose($req->fd, $e);
        }

        return true;
    }

    /**
     * 消息回调
     * @param Server $server
     * @param Frame $fr
     * @return bool
     */
    public function onMessage(Server $server, Frame $fr)
    {
        $data = json_decode($fr->data, true);
        if (!is_array($data)) {
            return $this->errorClose($fr->fd, (new \Exception('错误传入消息')));
        }
        try {
            if (IS_SERVER) {
                $frame = new MessageFrame($data);
                $frame->messageVerifySign($data);
            }

            // 屏蔽敏感词
            $data['message'] = ForbidWordService::instance()->replace($data['message']);

            $message = (new MessageProperty())->setProperty([
                'from_uuid' => $data['from_uuid'],
                'from_fd' => $fr->fd,
                'from_server_id' => SERVER_ID,
                'to_uuid' => $data['to_uuid'],
                'to_fd' => '',
                'to_server_id' => '',
                'platform_id' => $data['platform_id'],
                'message' => $data['message'],
                'send_time' => getMillisecond(),
                'type' => MessageType::TO_MESSAGE,
                'window_id' => ($data['from_uuid'] < $data['to_uuid']) ? "{$data['from_uuid']}_{$data['to_uuid']}" : "{$data['to_uuid']}_{$data['from_uuid']}"
            ]);
            $handleEvent = new HandleEvent();
            $handleEvent->sendMessage($message);
            return true;
        } catch (\Exception $e) {
            return $this->errorClose($fr->fd, $e);
        }
    }

    /**
     * 断开连接，清除绑定信息
     *
     * @param Server $server
     * @param int $fd
     */
    public function onClose($server, $fd)
    {
        $handleEvent = new  HandleEvent();
        $handleEvent->uuidClose($fd);
    }


    /**
     * 异步任务，做一些数据统计
     *
     * @param Server $server
     * @param        $taskId
     * @param        $fromId
     * @param array $data 二维数组，其中func是SessionStatistic对象里面的方法,param是数据参数
     */
    public function onTask(Server $server, $taskId, $fromId, $data)
    {

    }

    public function onFinish(Server $server, $taskId, $data)
    {
    }


    /**
     * 发送一条错误消息并且关闭连接
     *
     * @param integer $fd
     * @param \Exception $exception
     * @return bool
     */
    private function errorClose($fd, \Exception $exception)
    {
        $this->server->push(
            $fd,
            json_encode([
                'type' => MessageType::CLOSE,
                'msg' => $exception->getMessage(),
                'data' => []
            ], JSON_UNESCAPED_UNICODE),
            WEBSOCKET_OPCODE_TEXT
        );
        $this->server->close($fd);
        return false;
    }

}