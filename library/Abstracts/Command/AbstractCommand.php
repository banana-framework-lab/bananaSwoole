<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Abstracts\Command;

/**
 * Class AbstractCommand
 * @package Library\Abstracts\Command
 */
abstract class AbstractCommand
{
    /**
     * 执行脚本的方法,在构造函数后被调用
     */
    abstract public function execute();
}