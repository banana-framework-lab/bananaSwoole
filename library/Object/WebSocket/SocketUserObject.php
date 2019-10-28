<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/28
 * Time: 10:29
 */

namespace Library\Object\WebSocket;

/**
 * Class SocketUserObject
 * @package Library\Object\WebSocket
 */
class SocketUserObject
{
    /**
     * @var string 用户唯一标识
     */
    private $id;

    /**
     * @var string 用户所属app的id
     */
    private $appId;

    /**
     * @var int 用户的fd
     */
    private $fd;

    /**
     * @var string 用户的名字
     */
    private $username;

    /**
     * SocketUserObject constructor.
     * @param string $id
     * @param string $appId
     * @param string $username
     * @param int $fd
     */
    public function __construct(string $id, string $appId, string $username, int $fd)
    {
        $this->id = $id;
        $this->appId = $appId;
        $this->username = $username;
        $this->fd = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->id;
    }

    /**
     * @param string $AppId
     */
    public function setAppId(string $AppId)
    {
        $this->appId = $AppId;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->id;
    }

    /**
     * @param int $fd
     */
    public function setFd(int $fd)
    {
        $this->fd = $fd;
    }

    /**
     * socket用户对象转成数组
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'fd' => $this->fd,
            'appId' => $this->appId,
        ];
    }
}