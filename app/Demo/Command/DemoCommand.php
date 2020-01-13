<?php
namespace  App\Demo\Command;

use Library\Virtual\Command\AbstractCommand;
use Throwable;

class DemoCommand extends AbstractCommand
{
    /**
     * TestCommand constructor.
     * @throws Throwable
     */
    public function __construct()
    {
        // todo执行一些命令任务需要的初始化
    }

    /**
     * 执行脚本的方法,在构造函数时会被调用
     */
    public function execute()
    {
        echo "DemoCommand执行成功\n";
    }
}
