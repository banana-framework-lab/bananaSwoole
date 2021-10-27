<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Abstracts\Process;

/**
 * Class AbstractProcess
 * @package Library\Abstracts\Process
 */
abstract class AbstractProcess
{
    private $pid;

    private $pIndex;

    public $run;

    public $processNum;

    abstract public function main();

    public function __construct($processNum = 1)
    {
        $this->processNum = $processNum;
        pcntl_signal(SIGTERM, [$this, "handleSignal"], false);
    }

    public function handleSignal($signal)
    {
        switch ($signal) {
            case SIGTERM:
                $this->run = false;
                break;
        }
    }

    public function exec($pIndex)
    {
        $this->pid = posix_getpid();
        $this->pIndex = $pIndex;
        $this->run = true;
        $this->main();
    }
}