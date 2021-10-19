<?php
/**
 * Redis的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Abstracts\Model;

use Library\Container;

/**
 * Class AbstractRedisModel
 * @package Library\Abstracts\Model\CacheModel
 */
abstract class AbstractRedisModel
{
    public $redis;

    public function __construct()
    {
        $this->redis = Container::getRedisPool()->get();
    }

    public function __destruct()
    {
        Container::getRedisPool()->back($this->redis);
    }
}