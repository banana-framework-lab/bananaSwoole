<?php


namespace App\Index\Server;


use Library\Abstracts\Server\AbstractSwooleServer;
use Library\Container;
use Library\Exception\LogicException;
use Swoole\Coroutine;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server\Task;
use Swoole\WebSocket\Frame as SwooleSocketFrame;
use Swoole\WebSocket\Server as SwooleSocketServer;
use Throwable;

class IndexServer extends AbstractSwooleServer
{

    /**
     * @inheritDoc
     */
    public function start(SwooleSocketServer $server, int $workerId): bool
    {
        // TODO: Implement start() method.
        return true;
    }

    /**
     * @inheritDoc
     */
    public function open(SwooleSocketServer $server, SwooleRequest $request)
    {
        // TODO: Implement open() method.
    }

    /**
     * @inheritDoc
     */
    public function message(SwooleSocketServer $server, SwooleSocketFrame $frame)
    {
        // TODO: Implement message() method.
    }

    /**
     * @inheritDoc
     */
    public function close(SwooleSocketServer $server, int $fd)
    {
        // TODO: Implement close() method.
    }

    /**
     * @inheritDoc
     */
    public function request(SwooleRequest $request, SwooleResponse $response)
    {
        // 标识此次fd为http请求;
        $this->bindTable->set($request->fd, ['http' => 1]);

        /* @var RouteObject $routeObject */
        $routeObject = Container::getRouter()->controllerRouter($request->server['request_uri']);

        // 初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

        // 初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $rawContentData = json_decode($request->rawContent(), true) ?: [];
        $requestData = array_merge($getData, $postData, $rawContentData);

        // 初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;

            /* @var AbstractMiddleWare $middleWare */
            if (method_exists($middleClass, $methodName)) {
                $middleWare = new $middleClass($requestData);
                $middleWare->$methodName();
                $requestData = $middleWare->takeMiddleData();
            }

        } catch (LogicException $webE) {
            $response->end(json_encode([
                'status' => $webE->getStatus(),
                'code' => $webE->getCode(),
                'message' => $webE->getMessage()
            ]));
            return;
        } catch (Throwable $e) {
            $response->end(json_encode([
                'status' => -1,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]));
            return;
        }

        // 初始化控制器
        try {
            if (class_exists($controllerClass)) {
                /* @var AbstractController $controller */
                $controller = new $controllerClass($requestData);
                if (method_exists($controller, $methodName)) {
                    $returnData = $controller->$methodName();
                    if (!empty($returnData)) {
                        $response->end(json_encode($returnData));
                    }
                } else {
                    $response->status(403);
                    $response->end();
                    return;
                }
            } else {
                $response->status(404);
                $response->end();
                return;
            }
        } catch (Throwable $e) {
            if (Container::getConfig()->get('app.debug', true)) {
                if ($e->getCode() != C_EXIT_CODE) {
                    $response->status(200);
                    $response->end($e->getMessage() . "\n" . $e->getTraceAsString());
                } else {
                    $response->status(200);
                    $workerId = Container::getSwooleServer()->worker_id;
                    $cId = Coroutine::getCid();
                    $response->end(Container::getResponse()->dumpFlush($workerId, $cId));
                }
            } else {
                $response->status(500);
                $response->end();
            }
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function task(SwooleSocketServer $server, Task $task)
    {
        // TODO: Implement task() method.
    }

    /**
     * @inheritDoc
     */
    public function exit(SwooleSocketServer $server, int $workerId)
    {
        // TODO: Implement exit() method.
    }
}