<?php
/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/23
 * Time: 10:41
 */

namespace App\Api\Property;

use App\Library\Virtual\Property\AbstractProperty;


class SessionListProperty extends AbstractProperty
{
    /**
     * id
     * @var int $id
     */
    protected $id;

    /**
     * 平台id
     * @var int $platform_id
     */
    protected $platform_id;

    /**
     * 自己的uid
     * @var int $personal_uid
     */
    protected $personal_uid;

    /**
     * 对方的uid
     * @var string $opponent_uid
     */
    protected $opponent_uid;

    /**
     * 会话标题
     * @var string $title
     */
    protected $title;

    /**
     * 玩家和玩家之间的聊天窗口
     * 由uuid1_uuid2组成，小的在前，大的在后
     * @var string $window_id
     */
    protected $window_id;

}
