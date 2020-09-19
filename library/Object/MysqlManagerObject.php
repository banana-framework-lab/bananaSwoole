<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/30
 * Time: 22:37
 */

namespace Library\Object;

use Illuminate\Database\Capsule\Manager;
use Library\Pool\MysqlClientPool;

/**
 * Class BuilderObject
 * @package Library\Object
 */
class MysqlManagerObject
{
    /**
     * @var Manager $manager
     */
    public $manager;

    /**
     * BuilderObject constructor.
     */
    public function __construct()
    {
        $this->manager = MysqlClientPool::get();
    }

    /**
     * BuilderObject destruct.
     */
    public function __destruct()
    {
        MysqlClientPool::back($this->manager);
    }
}