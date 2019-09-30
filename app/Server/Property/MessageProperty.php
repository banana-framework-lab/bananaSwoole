<?php
/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/23
 * Time: 10:41
 */

namespace App\Server\Property;

use App\Library\Virtual\Property\AbstractProperty;


class MessageProperty extends AbstractProperty
{
    /**
     * 发送者uuid
     * @var string $from_uuid
     */
    protected $from_uuid;

    /**
     * 发送者fd
     * @var int $from_fd
     */
    protected $from_fd;

    /**
     * 发送者的server_id
     * @var int $from_server_id
     */
    protected $from_server_id;

    /**
     * 接收者uuid
     * @var string $to_uuid
     */
    protected $to_uuid;

    /**
     * 接收者fd
     * @var int $to_fd
     */
    protected $to_fd;

    /**
     * 接受者的server_id
     * @var int $to_server_id
     */
    protected $to_server_id;

    /**
     * 平台id
     * @var int $platform_id
     */
    protected $platform_id;

    /**
     * 发送时间
     * @var int $send_time
     */
    protected $send_time;

    /**
     * 消息内容
     * @var string $message
     */
    protected $message;

    /**
     * 消息类型
     * @var int $type
     */
    protected $type;

//    /**
//     * 发送状态
//     * 0失败
//     * 1成功
//     * 2找不到fd
//     * @var int $pushStatus
//     */
//    protected $pushStatus = 0;

    /**
     * 玩家和玩家之间的聊天窗口
     * 由uuid1_uuid2组成，小的在前，大的在后
     * @var string $window_id
     */
    protected $window_id;

}
