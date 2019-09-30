<?php

namespace App\Server\Model\DataBaseModel;
use App\Library\Virtual\Model\DataBaseModel\AbstractMongoModel;

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
}