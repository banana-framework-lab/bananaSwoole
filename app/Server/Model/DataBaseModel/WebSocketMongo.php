<?php

namespace App\Server\Model\DataBaseModel;
use Library\Virtual\Model\DataBaseModel\AbstractMongoModel;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/8/22
 * Time: 16:40
 */
class WebSocketMongo extends AbstractMongoModel
{
    /**
     * WebSocketMongo constructor.
     * @param string $dbName
     * @param string $collection
     */
    public function __construct($dbName = 'tanwange', $collection = 'twg_chat_content')
    {
        parent::__construct($dbName, $collection);
    }

    /**
     * 获取筛选器结果
     * @param array $filter 筛选条件
     * @return array $filter
     */
    protected function getFilter($filter = [])
    {
        // TODO: Implement getFilter() method.
    }

    /**
     * 获取选择器结果
     * @param array $options 选择条件
     * @return array $options
     */
    protected function getOptions($options = [])
    {
        // TODO: Implement getOptions() method.
    }
}