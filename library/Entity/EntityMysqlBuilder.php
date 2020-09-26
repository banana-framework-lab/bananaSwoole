<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/30
 * Time: 22:37
 */

namespace Library\Entity;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Query\Builder;
use Library\Container;

class EntityMysqlBuilder extends Builder
{
    /**
     * @var Manager $builderManager
     */
    public $builderManager;

    public function __construct()
    {
        $this->builderManager = Container::getMysqlPool()->get();
        parent::__construct($this->builderManager->getConnection());
    }

    public function __destruct()
    {
        Container::getMysqlPool()->back($this->builderManager);
    }

    /**
     * 返回查询构造器生成的SQL语句
     * @return string|string[]|null
     */
    public function getSql()
    {
        $bindings = $this->getBindings();
        return preg_replace_callback('/\?/', function ($match) use (&$bindings) {
            $binding = array_shift($bindings);
            if (is_numeric($binding)) {
                return $binding;
            } else if (is_string($binding)) {
                return empty($binding) ? "''" : "'{$binding}'";
            } else {
                return $binding;
            }
        }, $this->toSql());
    }
}