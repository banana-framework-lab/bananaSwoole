<?php
namespace  App\Api\Command;

use Library\Entity\Model\DataBase\EntityMysql;
use Library\Virtual\Command\AbstractCommand;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/26
 * Time: 14:01
 */
class TestCommand extends AbstractCommand
{
    /**
     * TestCommand constructor.
     * @throws \Throwable
     */
    public function __construct()
    {
        EntityMysql::instanceStart();
    }

    /**
     * 执行脚本的方法,在构造函数时会被调用
     */
    public function execute()
    {
        echo "Command执行成功\n";
    }
}
