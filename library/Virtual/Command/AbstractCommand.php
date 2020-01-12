<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Virtual\Command;

/**
 * Class AbstractCommand
 * @package Library\Virtual\Command
 */
abstract class AbstractCommand
{
    /**
     * 执行脚本的方法,在构造函数时会被调用
     */
    abstract public function execute();
}