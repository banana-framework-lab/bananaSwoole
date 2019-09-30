<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6 0006
 * Time: 11:41
 */

namespace App\Api\Logic;

use App\Library\Service\MessageKeyService;
use App\Api\Model\CacheModel\ApiRedis;
use App\Api\Model\DataBaseModel\Chat as ChatModel;
use App\Api\Model\DataBaseModel\SessionList as SessionListModel;
use App\Library\Entity\Model\DataBase\Mysql;

class Chat
{
    /**
     * 获取发送消息密钥
     * @param $platformId
     * @param $personalUid
     * @param $opponentUid
     * @return string
     */
    public function getSendMessageKey($platformId, $personalUid, $opponentUid)
    {
        return MessageKeyService::instance()->getMessageKey($platformId, $personalUid, $opponentUid);
    }

    /**
     * 查询聊天记录
     * @param array $params 查询条件
     * @return array
     */
    public function getChatHistory($params)
    {
        $personalUid = $params['personalUid'];
        $opponentUid = $params['opponentUid'];
        $maxId = $params['maxId'];
        $page = $params['page'];
        $limit = $params['limit'];
        $windowId = $this->calcWindowId($personalUid, $opponentUid);

        return (new ChatModel())->getChatHistoryByWindowId($windowId, $maxId, $page, $limit);
    }

    /**
     * 获取会话列表
     * @param $params
     * @return array
     */
    public function getSessionList($params)
    {
        //where条件
        $params['personal_uid'] = $params['uid'];

        //order条件
        $orderBy['create_time'] = 'desc';

        $sessionListModel = new SessionListModel();
        $searchCount = $sessionListModel->getCount($params);
        $searchResult = $sessionListModel->getList($params, $orderBy);
        // 计算最后一页的数量
        if ($searchCount <= $params['limit']) {
            $lastPage = 1;
        } else {
            $remainder = $searchCount % $params['limit'];
            if ($remainder <= 0) {
                $lastPage = $searchCount / $params['limit'];
            } else {
                $lastPage = intval($searchCount / $params['limit']) + 1;
            }
        }

        $resData = [
            'current_page' => (int)$params['page'],
            'last_page' => (int)$lastPage,
            'per_page' => (int)$params['limit'],
            'total' => (int)$searchCount,
            'data' => $searchResult
        ];
        return $resData;
    }

    /**
     * 获取一个session的信息
     * @param $condition
     * @return array
     */
    public function getSessionInfo($condition)
    {
        $sessionListModel = new SessionListModel();
        $sessionListModel->setListColumns('id,platform_id,personal_uid,opponent_uid,window_id,create_time,title');
        $sessionInfo = $sessionListModel->getFirst($condition) ?: [];
        return $sessionInfo;
    }

    /**
     * 修改会话列表的标题
     * @param $params
     * @return bool
     */
    public function updateSessionTitle($params)
    {
        $condition = [
            'platform_id' => $params['platform_id'],
            'personal_uid' => $params['personal_uid'],
            'opponent_uid' => $params['opponent_uid']
        ];
        $sessionListModel = new SessionListModel();
        if (!$sessionListModel->builder->where($condition)->first()) {
            return false;
        }

        return $sessionListModel->builder->where($condition)->update(['title' => $params['title']]);
    }

    /**
     * 根据uid判断用户是否在线
     * @param $uid
     * @return bool
     */
    public function queryUserIsOnline($uid)
    {
        $redisInstance = new ApiRedis();

        if ($redisInstance->redis->hExists($redisInstance->getUuidBindingFdKey(), $uid) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 记录会话列表
     * @param $params
     * @return bool
     */
    public function recordSessionList($params)
    {
        $platformId = $params['platform_id'];
        $personalUid = $params['personal_uid'];
        $personalName = $params['personal_name'];
        $opponentUid = $params['opponent_uid'];
        $opponentName = $params['opponent_name'];

        $windowId = $this->calcWindowId($params['personal_uid'], $params['opponent_uid']);
        $redisInstance = new ApiRedis();
        $isLog = $redisInstance->redis->hGet($redisInstance->getLogSessionLock($params['platform_id']), $windowId);
        if (!$isLog) {
            try {
                Mysql::connection()->beginTransaction();
                $sessionListModel = new SessionListModel();
                $isExist1 = $sessionListModel->builder->where([
                    'platform_id' => $params['platform_id'],
                    'personal_uid' => $params['personal_uid'],
                    'opponent_uid' => $params['opponent_uid'],
                ])->count();
                if (!$isExist1) {
                    $sessionListModel1 = new SessionListModel();
                    $sessionListModel1->platform_id = $platformId;
                    $sessionListModel1->personal_uid = $personalUid;
                    $sessionListModel1->opponent_uid = $opponentUid;
                    $sessionListModel1->window_id = $windowId;
                    $sessionListModel1->title = $opponentName;
                    $res1 = $sessionListModel1->save();
                } else {
                    $res1 = true;
                }

                $isExist2 = $sessionListModel->builder->where([
                    'platform_id' => $params['platform_id'],
                    'personal_uid' => $params['opponent_uid'],
                    'opponent_uid' => $params['personal_uid'],
                ])->count();
                if (!$isExist2) {
                    $sessionListModel2 = new SessionListModel();
                    $sessionListModel2->platform_id = $platformId;
                    $sessionListModel2->personal_uid = $opponentUid;
                    $sessionListModel2->opponent_uid = $personalUid;
                    $sessionListModel2->window_id = $windowId;
                    $sessionListModel2->title = $personalName;
                    $res2 = $sessionListModel2->save();
                } else {
                    $res2 = true;
                }
                if ($res1 && $res2) {
                    $redisInstance->redis->hSet($redisInstance->getLogSessionLock($params['platform_id']), $windowId, 1);
                    Mysql::connection()->commit();
                    return true;
                } else {
                    Mysql::connection()->rollback();
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 计算窗口句柄id
     * @param $personalUid
     * @param $opponentUid
     * @return string
     */
    private function calcWindowId($personalUid, $opponentUid)
    {
        if ((((int)$personalUid) < ((int)$opponentUid))) {
            $windowId = "{$personalUid}_{$opponentUid}";
        } else {
            $windowId = "{$opponentUid}_{$personalUid}";
        }

        return $windowId;
    }
}