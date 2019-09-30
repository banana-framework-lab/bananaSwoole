<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 16:28
 */

namespace App\Api\Controller;

use App\Api\Logic\Chat as ChatLogic;
use App\Library\Service\PlatformService;
use App\Library\Virtual\Controller\AbstractController;

class Chat extends AbstractController
{
    /**
     * 获取发消息的秘钥
     * @return false|string
     */
    public function messageKey()
    {
        if (!PlatformService::instance()->judgePlatform($this->request['platform_id'], $this->request['platform_secret'])) {
            return $this->responseFailed(['msg' => '平台秘钥错误']);
        }

        // 记录会话列表
        $chatLogic = new ChatLogic();
        $result = $chatLogic->recordSessionList($this->request);
        if (!$result) {
            return $this->responseFailed(['msg' => '服务器繁忙，请稍后再试']);
        }

        // 计算密钥
        $key = $chatLogic->getSendMessageKey($this->request['platform_id'], $this->request['personal_uid'], $this->request['opponent_uid']);
        return $this->responseArray(['key' => $key]);
    }

    /**
     * 根据双方uid获取聊天记录
     * @return string
     */
    public function history()
    {
        $params = [
            'platformId' => $this->request['platform_id'],
            'personalUid' => $this->request['personal_uid'],
            'opponentUid' => $this->request['opponent_uid'],
            'maxId' => $this->request['max_id'],
            'page' => $this->request['page'],
            'limit' => $this->request['limit']
        ];

        $data = (new ChatLogic())->getChatHistory($params);
        return $this->responseArray($data);
    }

    /**
     * 根据uid判断用户是否在线
     * @return false|string
     */
    public function online()
    {
        $uid = $this->request['uid'];

        $isOnline = (new ChatLogic())->queryUserIsOnline($uid);

        return $this->responseArray(['isOnline' => $isOnline]);
    }

    /**
     * 根据uid获取用户的会话列表
     * @return false|string
     */
    public function sessionList()
    {
        $params = [
            'platformId' => $this->request['platform_id'],
            'uid' => $this->request['uid'],
            'page' => $this->request['page'],
            'limit' => $this->request['limit']
        ];

        $list = (new ChatLogic())->getSessionList($params);
        return $this->responseArray($list);
    }

    /**
     * 根据pid和oid和平台ip返回一个会话信息
     */
    public function getSessionInfo()
    {
        return $this->responseArray((new ChatLogic())->getSessionInfo($this->request));
    }

    /**
     * 修改会话列表的标题
     * @return false|string
     */
    public function sessionTitle()
    {
        $res = (new ChatLogic())->updateSessionTitle($this->request);
        return $this->autoResponse($res);
    }
}