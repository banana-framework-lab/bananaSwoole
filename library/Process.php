<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 17:55
 */

namespace Library;

use Library\Abstracts\Process\AbstractProcess;

class Process
{
    private $pid;

    public $run = false;

    public $childProcessPids;

    /**
     * @var AbstractProcess $childProcess
     */
    public $childProcess;

    public function __construct(AbstractProcess $childProcess)
    {
        $this->childProcess = $childProcess;
    }

    public function main()
    {
        $status = 0;

        $this->pid = daemonize();

        pcntl_async_signals(true);

        $this->run = true;

        pcntl_sigprocmask(SIG_BLOCK, [SIGTERM]);

        for ($i = 1; $i <= $this->childProcess->processNum; $i++) {
            $pid = $this->spawnProcess($i);
            $this->childProcessPids[] = $pid;
        }

        pcntl_signal(SIGTERM, array(&$this, "handleSignal"), false);
        pcntl_signal(SIGCHLD, array(&$this, "handleSignal"), false);

        pcntl_sigprocmask(SIG_UNBLOCK, [SIGTERM]);

        $ret = [];
        while ($this->childProcessPids) {
            $pid = pcntl_wait($status, 0);

            if ($pid === -1) {
                continue;
            }

            echo date('Y-m-d H:i:s') . " 父进程 pid:{$this->pid} 子进程退出 pid:$pid" . PHP_EOL;

            $idx = array_search($pid, $this->childProcessPids);
            $ret[$idx] = $status;

            if (!$this->run) {
                unset($this->childProcessPids[$idx]);
                continue;
            }
            $newPid = $this->spawnProcess(++$idx);

            echo date('Y-m-d H:i:s') . " 父进程 pid:{$this->pid} 新建子进程 pid:$newPid" . PHP_EOL;

            $this->childProcessPids[$idx] = $newPid;
        }
        return $ret;
    }

    /**
     * @param $signal
     */
    public function handleSignal($signal)
    {
        switch ($signal) {
            case SIGTERM:
                foreach ($this->childProcessPids as $pid) {
                    posix_kill($pid, SIGTERM);
                }
                $this->run = false;
                break;
        }
    }

    /**
     * @param $processIndex
     * @return int
     */
    protected function spawnProcess($processIndex)
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            echo "父进程 pid:{$this->pid} 子进程启动失败." . PHP_EOL;
            exit;
        }
        if ($pid === 0) {
            pcntl_sigprocmask(SIG_UNBLOCK, [SIGTERM]);
            $this->childProcess->exec($processIndex);
            exit;
        }
        return $pid;
    }
}