<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 17:55
 */

namespace Library;


use Library\Virtual\Command\AbstractCommand;
use swoole_process;

class Command
{
    /**
     * @var int $paramNumber
     */
    public $paramNumber;

    /**
     * @var array $paramData
     */
    public $paramData;

    /**
     * @var array $actionType
     */
    private $actionType = ['start', 'stop', 'reload', 'command'];

    /**
     * @var string $actionName
     */
    private $actionName;

    /**
     * @var string $serverName
     */
    private $serverName;

    /**
     * @var string $commandName
     */
    private $commandName;

    /**
     * Command constructor.
     * @param int $paramNumber
     * @param array $paramData
     */
    public function __construct(int $paramNumber, array $paramData)
    {
        $this->paramNumber = $paramNumber;
        $this->paramData = $paramData;
        $this->actionName = $this->paramData[1];
        $this->serverName = $this->paramData[2];
        $this->commandName = $this->paramData[3] ?? '';

        Config::instanceStart();
    }

    /**
     * cli的判断传参
     */
    public function cli()
    {
        if (!in_array($this->actionName, $this->actionType)) {
            echo "错误命令行为\n";
            return;
        }
        if ($this->paramData[1] == 'command') {
            $commandClass = "\\App\\{$this->serverName}\\Command\\{$this->commandName}Command";
            /* @var AbstractCommand $command */
            if (method_exists($commandClass, 'execute')) {
                $command = new $commandClass;
                $command->execute();
                return;
            } else {
                echo "找不到{$this->commandName}Command\n";
                return;
            }
        } else {
            switch ($this->actionName) {
                case 'start' :
                    $this->start();
                    break;
                case 'stop':
                    $this->stop();
                    break;
                case 'reload':
                    $this->reload();
                    break;
            }
            return;
        }
    }

    /**
     * 启动进程
     */
    private function start()
    {
        $filePath = dirname(__FILE__) . "/./../public/{$this->serverName}.php";
        if (file_exists($filePath)) {
            require $filePath;
        } else {
            echo "{$this->paramData[2]}服务不存在\n";
            return;
        }
    }

    /**
     * 退出进程
     */
    private function stop()
    {
        $filePath = dirname(__FILE__) . "/./../library/Runtime/CommandStack/$this->serverName";
        if (!file_exists($filePath)) {
            echo "{$this->serverName}服务不存在\n";
            return;
        }
        $pid = intval(file_get_contents($filePath));
        if (!swoole_process::kill($pid, 0)) {
            echo "{$pid}进程不存在\n";
            return;
        }
        swoole_process::kill($pid, 15);
        $time = time();
        while (true) {
            usleep(1000);
            if (!swoole_process::kill($pid, 0)) {
                if (is_file($filePath)) {
                    unlink($filePath);
                }
                echo "{$this->serverName}-{$pid}已经正常退出\n";
                return;
            } else {
                if (time() - $time > 5) {
                    echo "{$this->serverName}-{$pid}退出失败，请再试一遍\n";
                    return;
                }
            }
        }
        echo "{$this->serverName}-{$pid}退出失败";
        return;
    }

    /**
     * swoole所有进程重启
     */
    private function reload()
    {
        $filePath = dirname(__FILE__) . "/./../library/Runtime/CommandStack/$this->serverName";
        $pid = intval(file_get_contents($filePath));
        if (!swoole_process::kill($pid, 0)) {
            echo "{$pid}进程不存在\n";
        }
        $shell = "kill -USR1 $pid";
        exec($shell);
        echo "{$this->serverName}-{$pid}已经正常热重启\n";
        return;
    }
}