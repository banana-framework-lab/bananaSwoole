<?php
/**
 * Redis的Model抽象类
 * User: zzh
 * Date: 2018/10/10
 * Time: 17:28
 */

namespace Library\Virtual\Model\CacheModel;

use Library\Container;

/**
 * Class AbstractRedisModel
 * @package Library\Abstract\Model\CacheModel
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