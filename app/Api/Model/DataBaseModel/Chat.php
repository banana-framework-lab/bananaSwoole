<?php

namespace App\Api\Model\DataBaseModel;

use App\Library\Virtual\Model\DataBaseModel\AbstractMongoModel;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 20:05
 */
class Chat extends AbstractMongoModel
{
    /**
     * Chat constructor.
     */
    public function __construct()
    {
        parent::__construct('tanwange', 'twg_chat_content');
    }

    /**
     * 根据窗口id返回聊天历史记录
     * @param string $windowId
     * @param int $maxId
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getChatHistoryByWindowId($windowId, $maxId = 0, $page = 1, $limit = 10)
    {
        $condition = [
            'window_id' => $windowId
        ];
        if ($maxId) {
            $condition['id'] = ['$lte' => (int)$maxId];
        }

        return $this->filter($condition)->paginate($page, $limit);
    }
}