<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 17:55
 */

namespace Library;

use Library\Abstracts\Command\AbstractCommand;
use Library\Abstracts\Process\AbstractProcess;
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
    private $actionType = ['server', 'command', 'process', 'shell'];

    /**
     * @var string $actionName
     */
    private $actionName;

    /**
     * @var string $serverActionName
     */
    private $serverActionName;

    /**
     * @var $projectName
     */
    private $projectName;

    /**
     * @var string $commandName
     */
    private $commandName;

    /**
     * @var string $processName
     */
    private $processName;

    /**
     * @var $serverName
     */
    private $serverName;

    /**
     * Command constructor.
     * @param int $paramNumber
     * @param array $paramData
     */
    public function __construct(int $paramNumber, array $paramData)
    {
        $this->paramNumber = $paramNumber;
        $this->paramData = $paramData;
        $this->actionName = $this->paramData[1] ?? '';
        $this->serverActionName = $this->paramData[2] ?? '';
        $this->projectName = ucfirst($this->paramData[3] ?? '');
        $this->commandName = ucfirst($this->paramData[4] ?? '');
        $this->processName = ucfirst($this->paramData[4] ?? '');
        $this->serverName = $this->paramData[3] ?? '';

        Container::loadCommonFile();
        Container::setConfig();
        Container::getConfig()->initConfig();
    }

    /**
     * cli的判断传参
     */
    public function cli()
    {
        if (!in_array($this->actionName, $this->actionType)) {
            echo "错误命令行为" . PHP_EOL;
            return;
        }

        switch ($this->actionName) {
            case 'command':
                $commandClass = "\\App\\$this->projectName\\Command\\{$this->commandName}Command";
                /* @var AbstractCommand $command */
                if (method_exists($commandClass, 'execute')) {
                    $command = new $commandClass();
                    $command->execute();
                } else {
                    echo "找不到{$commandClass}" . PHP_EOL;
                }
                break;
            case 'process':
                switch ($this->serverActionName) {
                    case 'start':
                        $phpSrc = trim(exec('which php'));
                        $processNum = exec("ps -ef | grep 'php bananaSwoole shell start' | grep '$this->projectName $this->processName' | grep -v \"grep\" | wc -l");
                        if ((int)$processNum <= 0) {
                            $logDir = dirname(__FILE__) . "/../log/$this->projectName/Process/";
                            if (!is_dir($logDir)) {
                                mkdir($logDir, 0755, true);
                            }
                            $logDir .= "{$this->processName}Process.log";
                            echo shell_exec("$phpSrc bananaSwoole shell start $this->projectName $this->processName >> {$logDir}" . PHP_EOL);
                        } else {
                            echo "bananaSwoole process start $this->projectName $this->processName 已经启动,数量为$processNum" . PHP_EOL;
                        }
                        break;
                    case 'kill':
                        $processNum = exec("ps -ef | grep 'php bananaSwoole shell start' | grep '$this->projectName $this->processName' | grep -v \"grep\" | wc -l");
                        if ((int)$processNum > 0) {
                            ob_start();
                            passthru("ps -ef | grep 'php bananaSwoole shell start' | grep '$this->projectName $this->processName' | grep -v \"grep\" | awk '{print $2}'");
                            $processIdList = ob_get_clean();
                            exec('kill -9 ' . implode(' ', explode("\n", trim($processIdList))));
                            echo "已清理 bananaSwoole process start $this->projectName $this->processName 进程数为:$processNum" . PHP_EOL;
                        } else {
                            echo "bananaSwoole process start $this->projectName $this->processName 无启动进程" . PHP_EOL;
                        }
                        break;
                    default:
                        echo "{$this->serverActionName}是错误命令行为." . PHP_EOL;
                        break;
                }
                break;
            case 'shell':
                $processClass = "\\App\\$this->projectName\\Process\\{$this->processName}Process";
                /* @var AbstractProcess $command */
                if (method_exists($processClass, 'main')) {
                    (new Process(new $processClass()))->main();
                } else {
                    echo "找不到{$processClass}" . PHP_EOL;
                }
                break;
            case 'server':
                switch ($this->serverActionName) {
                    case 'start' :
                        $this->start();
                        break;
                    case 'stop':
                        $this->stop();
                        break;
                    case 'reload':
                        $this->reload();
                        break;
                    default:
                        echo "{$this->serverActionName}是错误命令行为..." . PHP_EOL;
                        break;
                }
                break;
        }
    }

    /**
     * 启动进程
     */
    private function start()
    {
        $processNum = exec("ps -ef | grep 'php bananaSwoole server start' | grep '$this->serverName' | grep -v \"grep\" | wc -l");
        if ((int)$processNum <= 0) {
            $filePath = dirname(__FILE__) . "/../public/$this->serverName.php";
            if (file_exists($filePath)) {

                $pidFilePath = dirname(__FILE__) . "/../runtime/Server/";

                if (!file_exists($pidFilePath)) {
                    mkdir($pidFilePath, 755, true);
                }

                $pidFilePath .= ".$this->serverName";
                $pidFile = fopen($pidFilePath, "w");
                fwrite($pidFile, posix_getpid());
                fclose($pidFile);

                require $filePath;
            } else {
                echo "{$this->serverName}服务不存在" . PHP_EOL;
            }
        } else {
            echo "bananaSwoole server start serverName 已经启动" . PHP_EOL;
        }
    }

    /**
     * 退出进程
     */
    private function stop()
    {
        $filePath = dirname(__FILE__) . "/../runtime/Server/.$this->serverName";

        if (!file_exists($filePath)) {
            echo "无记录进程信息" . PHP_EOL;
            return;
        }

        $pid = intval(file_get_contents($filePath));
        if (!swoole_process::kill($pid, 0)) {
            echo "{$pid}进程不存在" . PHP_EOL;
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
                echo "{$this->serverName}-{$pid}已经正常退出" . PHP_EOL;
                return;
            } else if (time() - $time > 5) {
                echo "{$this->serverName}-{$pid}退出失败，请再试一遍" . PHP_EOL;
                return;
            }
        }
    }

    /**
     * swoole所有进程重启
     */
    private function reload()
    {
        $filePath = dirname(__FILE__) . "/../runtime/Server/.$this->serverName";

        if (!file_exists($filePath)) {
            echo "无记录进程信息" . PHP_EOL;
            return;
        }

        $pid = intval(file_get_contents($filePath));
        if (!swoole_process::kill($pid, 0)) {
            echo "{$pid}进程不存在" . PHP_EOL;
        }
        $shell = "kill -USR1 $pid";
        exec($shell);
        echo "{$this->serverName}-{$pid}已经正常热重启" . PHP_EOL;
    }
}