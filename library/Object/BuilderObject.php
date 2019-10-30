<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/30
 * Time: 22:37
 */

namespace Library\Object;

/**
 * Class BuilderObject
 * @package Library\Object
 */
class BuilderObject
{
    /**
     * @var string $table
     */
    private $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }


}