<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 16:29
 */

namespace Library\Virtual\Event;

use Library\Object\WebSocket\SocketGetDataObject;
use Library\Object\WebSocket\SocketUserObject;

abstract class AbstractEvent
{
    /**
     * @var SocketGetDataObject $getData
     */
    public $getData;

    /**
     * @var SocketUserObject $userData
     */
    public $userData;

    /**
     * AbstractEvent constructor.
     * @param SocketGetDataObject $getData
     * @param SocketUserObject $userData
     */
    public function __construct(SocketGetDataObject $getData, SocketUserObject $userData)
    {
        $this->getData = $getData;
        $this->userData = $userData;
    }

    /**
     * @param SocketUserObject $userData
     */
    abstract public function open(SocketUserObject $userData);

    /**
     * @return mixed
     */
    abstract public function message();

    /**
     * @return mixed
     */
    abstract public function close();
}