<?php

namespace Library\Container\Instance;

use Library\Container;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Swoole\Coroutine;


/**
 * Class Log
 * @package Library\Container
 *
 * @method bool info(string $message = '', array $context = [], string $levelName = '', string $channel = '')
 * @method bool warning(string $message = '', array $context = [], string $levelName = '', string $channel = '')
 * @method bool task(string $message = '', array $context = [], string $levelName = '', string $channel = '')
 * @method bool error(string $message = '', array $context = [], string $levelName = '', string $channel = '')
 * @method bool success(string $message = '', array $context = [], string $levelName = '', string $channel = '')
 */
class Log
{
    private $loggers;

    /**
     * 日志留存时间
     * @var int
     */
    private $maxFiles = 0;

    /**
     * 日志等级
     * @var int
     */
    private $level = Logger::DEBUG;

    /**
     * 文件读写权限分配
     * 0666 保证log日志文件可以被其他用户/进程读写
     * @var int
     */
    private $filePermission = 0755;

    /**
     * monolog日志
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $logObject = Container::getRouter()->getRoute(
            Container::getServer()->getSwooleServer()->worker_id,
            Coroutine::getuid()
        )->getProject();
        $ymd = date('Ymd');
        if (!isset($arguments[3]) || $arguments[3] != '') {
            if ($logObject) {
                $fileName =  dirname(__FILE__) . "/../../app/{$logObject}/Runtime/logs/{$ymd}/";
            } else {
                $fileName = dirname(__FILE__) . "/../../runtime/Runtime/logs/{$ymd}/";
            }
        } else {
            $fileName = dirname(__FILE__) . "/../../app/{$arguments[3]}/Runtime/logs/{$ymd}/";
        }

        $logger = $this->createLogger($name, $fileName);

        $message = empty($arguments[0]) ? '' : $arguments[0];
        $context = empty($arguments[1]) ? [] : $arguments[1];
        $levelName = empty($arguments[2]) ? $name : $arguments[2];

        $level = Logger::toMonologLevel($levelName);
        if (!is_int($level)) $level = Logger::INFO;

        return $logger->addRecord($level, $message, $context);
    }

    /**
     * 创建日志对象
     * @param string $name
     * @param string $fileName
     * @return mixed
     */
    private function createLogger(string $name, string $fileName)
    {
        if (empty($this->loggers[$name])) {
            $workerId = Container::getServer()->getSwooleServer()->worker_id;
            $cId = Coroutine::getuid();
            $request = Container::getRequest()->getRequest($workerId, $cId);

            // 根据业务域名与方法名进行日志名称的确定
            $category = $request->server['server_name'];
            // 日志等级
            $level = $this->level;
            // 权限
            $filePermission = $this->filePermission;
            // 创建日志
            $logger = new Logger($category);
            // 日志文件相关操作
            $handler = new RotatingFileHandler("{$fileName}{$name}.log", $this->maxFiles, $level, true, $filePermission);

            // 组装请求信息
            $requestInfo = [
                'ip' => $request->server['remote_addr'],
                'host' => $request->server['http_host'],
                'uri' => $request->server['request_uri'],
                'method' => Container::getRouter()->getRoute($workerId, $cId)->getMethod()
            ];
            $template = "---------------------------------------------------------------";
            $template .= "\r\n[%datetime%] {$requestInfo['ip']}   {$requestInfo['method']}   {$requestInfo['host']}{$requestInfo['uri']}";
            $template .= "\r\n[%channel%][%level_name%][MESSAGE]: %message%";
            $template .= "\r\n[%channel%][%level_name%][CONTEXT]: %context%";

            // 组装跟踪栈信息
            // $backtrace数组第$idx元素是当前行，第$idx+1元素表示上一层，另外function、class需再往上取一个层次
            // PHP7 不会包含'call_user_func'与'call_user_func_array'，需减少一层
            $backtraceOffset = 0;
            if (version_compare(PCRE_VERSION, '7.0.0', '>=')) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $idx = 0 + $backtraceOffset;
            } else {
                $backtrace = debug_backtrace();
                $idx = 1 + $backtraceOffset;
            }

            $trace = basename($backtrace[$idx]['file']) . ":" . $backtrace[$idx]['line'];
            if (!empty($backtrace[$idx + 1]['function'])) {
                $trace .= '##';
                $trace .= $backtrace[$idx + 1]['function'];
            }
            $template .= "\r\n[%channel%][%level_name%][TRACE]: {$trace}";
            $template .= "\r\n";

            // 日志格式
            $formatter = new LineFormatter($template, "Y-m-d H:i:s", true, true);

            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            $this->loggers[$name] = $logger;
        }
        return $this->loggers[$name];
    }
}