<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 17:55
 */

namespace Library;


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
     * @var string $rootFile ;
     */
    private $rootFile = 'bananaSwoole';

    /**
     * @var array $actionType
     */
    private $actionType = ['start', 'stop', 'reload', 'command'];

    /**
     * Command constructor.
     * @param int $paramNumber
     * @param array $paramData
     */
    public function __construct(int $paramNumber, array $paramData)
    {
        $this->paramNumber = $paramNumber;
        $this->paramData = $paramData;
    }

    /**
     * cli的判断传参
     */
    public function cli()
    {
        if ($this->rootFile != $this->paramData[0]) {
            echo "错误根脚本文件\n";
            exit;
        }

        if (!in_array($this->paramData[1], $this->actionType)) {
            echo "错误命令行为\n";
            exit;
        }

        if ($this->paramData[1] == 'command') {

        } else {
            switch ($this->paramData[1]) {
                case 'start' :
                    $serverName = $this->paramData[2];
                    $filePath = dirname(__FILE__) . "/./../public/{$serverName}.php";
                    $serverPidFile = fopen(dirname(__FILE__) . "/./../library/Runtime/CommandStack/$serverName", 'w+');
                    fwrite($serverPidFile, getmypid());
                    fclose($serverPidFile);
                    require $filePath;
                    break;
                case 'stop':
                    $serverName = $this->paramData[2];
                    $filePath = dirname(__FILE__) . "/./../library/Runtime/CommandStack/$serverName";
                    $serverPidFile = fopen($filePath, 'r');
                    $pid = fread($serverPidFile, filesize($filePath));
                    fclose($serverPidFile);
                    $shell = "kill -15 $pid";
                    exec($shell, $result, $status);
                    unlink($filePath);
                    break;
                case 'reload':
                    $serverName = $this->paramData[2];
                    $filePath = dirname(__FILE__) . "/./../library/Runtime/CommandStack/$serverName";
                    $serverPidFile = fopen($filePath, 'r');
                    $pid = fread($serverPidFile, filesize($filePath));
                    fclose($serverPidFile);
                    $shell = "kill -USR1 $pid";
                    exec($shell, $result, $status);
                    break;
            }
        }
    }
}