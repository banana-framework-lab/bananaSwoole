<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:26
 */

namespace Library\Abstracts\Task;
use Swoole\Server\Task;

/**
 * Class AbstractTask
 * @package Library\Abstracts\Task
 */
abstract class AbstractTask
{
    /**
     * task对象
     * @var Task $task
     */
    protected $task;

    /**
     * AbstractTask constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }
}