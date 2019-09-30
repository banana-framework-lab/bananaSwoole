<?php

namespace App\Server\Event;

use App\Server\Model\CacheModel\WebSocketRedis;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/22
 * Time: 16:28
 */
class BindingEvent
{
    /**
     *
     * @var null $cache
     */
    private $cache = null;

    /**
     * 同一个uuid可绑定数量
     * @var int $userConnectNumber
     */
    private $uuidConnectNumber = 3;

    /**
     * HandleEvent constructor.
     */
    public function __construct()
    {
        $this->cache = new WebSocketRedis();
    }

    /**
     * 根据fd获取server_id
     * @param array $uuidBindingList
     * @param int $fd
     * @return mixed
     */
    public function getServerIdByFd($uuidBindingList, $fd)
    {
        echo json_encode(array_column($uuidBindingList, 'server_id', 'fd')) . ' ';
        echo $fd;
        return array_column($uuidBindingList, 'server_id', 'fd')[$fd];
    }

    /**
     * 用户uuid绑定fd的信息
     * @param $platformId
     * @param $uuid
     * @return array
     */
    public function uuidBindingInfo($platformId, $uuid)
    {
        $fdList = unserialize($this->cache->redis->hGet($this->cache->getUuidBindingFdKey(), $uuid)) ?: [];
        $fdNumber = count($fdList);
        return [
            'count' => $fdNumber,
            'platformId' => $platformId,
            'canBinding' => $fdNumber < $this->uuidConnectNumber,
            'list' => $fdList
        ];
    }

    /**
     * 用户uuid绑定Fd
     * @param array $uuidBindingList
     * @param int $platformId
     * @param string $uuid
     * @param int $fd
     */
    public function uuidBindingFd($uuidBindingList, $platformId, $uuid, $fd)
    {
        $uuidBindingList[] = $this->makeUuidBindingFdInfo($fd, $platformId);
        $this->cache->redis->hSet($this->cache->getUuidBindingFdKey(), $uuid, serialize($uuidBindingList));
    }

    /**
     * 用户uuid取绑Fd
     * @param array $uuidBindingList
     * @param string $uuid
     * @param int $fd
     */
    public function uuidUnbindingFd($uuidBindingList, $uuid, $fd)
    {
        $uuidBindingList = array_filter($uuidBindingList, function ($value) use ($fd) {
            return $value['fd'] != $fd;
        });
        if (count($uuidBindingList) > 0) {
            $this->cache->redis->hSet($this->cache->getUuidBindingFdKey(), $uuid, serialize($uuidBindingList));
        } else {
            $this->cache->redis->hDel($this->cache->getUuidBindingFdKey(), $uuid);
        }
    }

    /**
     * 用户uuid逼下线第一个fd
     * @param $uuidBindingList
     * @param $platformId
     * @param $uuid
     * @param $fd
     */
    public function uuidReplaceFd($uuidBindingList, $platformId, $uuid, $fd)
    {
        array_shift($uuidBindingList);
        $uuidBindingList[] = $this->makeUuidBindingFdInfo($fd, $platformId);
        $this->cache->redis->hSet($this->cache->getUuidBindingFdKey(), $uuid, serialize($uuidBindingList));
    }

    /**
     * @param $uuidBindingList
     * @param $fd
     * @return bool
     */
    public function uuidExistFd($uuidBindingList, $fd)
    {
        $fdList = array_column($uuidBindingList, 'fd');
        var_dump('already binding fd', $fdList);
        return in_array($fd, $fdList);
    }

    /**
     * 生产uuid绑定到fd的信息
     * @param int $fd
     * @param int $platformId
     * @return array
     */
    private function makeUuidBindingFdInfo($fd, $platformId)
    {
        return [
            'fd' => $fd,
            'server_id' => SERVER_ID,
            'platform_id' => $platformId
        ];
    }

    /**
     * 生产fd绑定到uuid的信息
     * @param $platformId
     * @param $uuid
     * @return array
     */
    private function makeFdBindingUuidInfo($platformId, $uuid)
    {
        return [
            'platform_id' => $platformId,
            'uuid' => $uuid,
        ];
    }

    /**
     * 用户fd绑定fd的信息
     * @param $fd
     * @return array
     */
    public function fdBindingInfo($fd)
    {
        $uuidInfo = unserialize($this->cache->redis->hGet($this->cache->getFdBindingUuidKey(), $fd)) ?: [];
        return $uuidInfo;
    }

    /**
     * fd绑定uuid
     * @param $platformId
     * @param $fd
     * @param $uuid
     */
    public function fdBindingUuid($platformId, $fd, $uuid)
    {
        $bindingInfo = $this->makeFdBindingUuidInfo($platformId, $uuid);
        $this->cache->redis->hSet($this->cache->getFdBindingUuidKey(), $fd, serialize($bindingInfo));
    }

    /**
     * fd接触绑定uuid
     * @param $fd
     */
    public function fdUnBindingUuid($fd)
    {
        $this->cache->redis->hDel($this->cache->getFdBindingUuidKey(), $fd);
    }
}