<?php

namespace App\Library\Service;

use DfaFilter\SensitiveHelper;
use App\Api\Model\CacheModel\ApiRedis;
use App\Api\Model\DataBaseModel\ForbiddenWord as ForbiddenWordModel;

class ForbidWordService
{
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    private $cache = null;

    private $handle = null;

    /**
     * 获取实例
     * @return null|static
     * @throws \DfaFilter\Exceptions\PdsBusinessException
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 构造函数
     * ForbidWordService constructor.
     * @throws \DfaFilter\Exceptions\PdsBusinessException
     */
    private function __construct()
    {
        $this->cache = new ApiRedis();

        // 获取屏蔽词数据
        $wordList = unserialize($this->cache->redis->get('forbidden_words'));
        if ($wordList === false) {
            $model = new ForbiddenWordModel();
            $model->setListColumns('content');
            $data = $model->getList()->toArray();
            $wordList = array_column($data, 'content');

            // 存进缓存
            $this->cache->redis->set('forbidden_words', serialize($wordList));
        }

        // 构建词库树
        $this->handle = SensitiveHelper::init()->setTree($wordList);
    }

    /**
     * 静止克隆
     */
    private function __clone()
    {

    }

    /**
     * 检测是否含有敏感词
     * @param $content
     * @return bool
     * @throws \DfaFilter\Exceptions\PdsSystemException
     */
    public function check($content)
    {
        return $this->handle->islegal($content);
    }

    /**
     * 敏感词替换
     * @param $content
     * @return mixed
     * @throws \DfaFilter\Exceptions\PdsBusinessException
     * @throws \DfaFilter\Exceptions\PdsSystemException
     */
    public function replace($content)
    {
        $filterContent = $this->handle->replace($content, '*', true);
        return $filterContent;
    }

    /**
     * 获取文字中的敏感词
     * @param $content
     * @param bool $onlyOne
     * @return array
     * @throws \DfaFilter\Exceptions\PdsSystemException
     */
    public function getForbiddenWord($content, $onlyOne = true)
    {
        if ($onlyOne) {
            return $this->handle->getBadWord($content, 1, 1);
        }

        return $this->handle->getBadWord($content);
    }
}