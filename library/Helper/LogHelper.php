<?php

namespace Library\Helper;

use Library\Config;
use Library\Entity\Swoole\EntitySwooleWebSever;
use Library\Object\Web\RouteObject;
use Library\Router;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Swoole\Coroutine;


/**
 * Class LogHelper
 * @package Library\Helper
 * @method static bool info(string $message = '', array $context = [], string $levelName = '')
 * @method static bool warning(string $message = '', array $context = [], string $levelName = '')
 * @method static bool error(string $message = '', array $context = [], string $levelName = '')
 * @method static bool success(string $message = '', array $context = [], string $levelName = '')
 */
class LogHelper
{
    private static $loggers;

    /**
     * 日志默认保存路径
     * @var string
     */
    private static $fileName = '';

    /**
     * 日志留存时间
     * @var int
     */
    private static $maxFiles = 0;

    /**
     * 日志等级
     * @var int
     */
    private static $level = Logger::DEBUG;

    /**
     * 文件读写权限分配
     * 0666 保证log日志文件可以被其他用户/进程读写
     * @var int
     */
    private static $filePermission = 0755;

    /**
     * monolog日志
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $logObject = ((Router::getRouteInstance())->getProject()) ?: null;

        self::$fileName = dirname(__FILE__) . '/../../app/' . $logObject . '/Runtime/logs/' . date('Ymd') . '/';

        $logger = self::createLogger($name);

        $message = empty($arguments[0]) ? '' : $arguments[0];
        $context = empty($arguments[1]) ? [] : $arguments[1];
        $levelName = empty($arguments[2]) ? $name : $arguments[2];

        $level = Logger::toMonologLevel($levelName);
        if (!is_int($level)) $level = Logger::INFO;

        return $logger->addRecord($level, $message, $context);
    }

    /**
     * 创建日志对象
     * @param $name
     * @return mixed
     */
    private static function createLogger($name)
    {
        if (empty(self::$loggers[$name])) {

            // 根据业务域名与方法名进行日志名称的确定
            $category = RequestHelper::server('server_name') ?: Config::get('app.server_name');
            // 日志文件目录
            $fileName = self::$fileName;
            // 日志保存时间
            $maxFiles = self::$maxFiles;
            // 日志等级
            $level = self::$level;
            // 权限
            $filePermission = self::$filePermission;
            // 创建日志
            $logger = new Logger($category);
            // 日志文件相关操作
            $handler = new RotatingFileHandler("{$fileName}{$name}.log", $maxFiles, $level, true, $filePermission);

            // 组装请求信息
            $requestInfo = [
                'ip' => RequestHelper::server('remote_addr'),
                'method' => RequestHelper::server('request_method'),
                'host' => RequestHelper::server('http_host'),
                'uri' => RequestHelper::server('request_uri')
            ];
            $template = "\r\n---------------------------------------------------------------\r\n[%datetime%] {$requestInfo['ip']} {$requestInfo['method']} {$requestInfo['host']}{$requestInfo['uri']}";
            $template .= "\r\n[%channel%][%level_name%][MESSAGE]: %message%";
            $template .= "\r\n[%channel%][%level_name%][CONTEXT]: %context%";
            $template .= "\r\n";

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

            // 日志格式
            $formatter = new LineFormatter($template, "Y-m-d H:i:s", true, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            self::$loggers[$name] = $logger;
        }
        return self::$loggers[$name];
    }
}