<?php
/**
 * MongoDB数据模型抽象类
 * User: zzh
 * Time: 17:27
 */

namespace App\Library\Virtual\Model\DataBaseModel;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Manager;
use MongoDB\Operation\FindOneAndUpdate;

/**
 * @property Manager mongo
 */
abstract class AbstractMongoModel
{
    /**
     * 筛选器
     * @var array
     */
    protected $filter = [];

    /**
     * 自增id数据集合
     * @var string
     */
    protected $increaseCollection = 'twg_chat_increment';

    /**
     * @param $name
     * @return Client|Manager
     */
    public function __get($name)
    {
        if ($name === 'mongo') {
            return $this->getMongodb('local');
        }
        return null;
    }

    /**
     * 数据库名
     * @var string
     */
    protected $db;

    /**
     * 集合名称
     * @var string
     */
    protected $collectionName;

    /**
     * 集合
     * @var \MongoDB\Collection
     */
    protected $collection;

    /**
     * AbstractMongoModel constructor.
     * @param $dbName
     * @param $collection
     */
    public function __construct($dbName, $collection)
    {
        $this->db = $dbName;
        $this->collection = $this->mongo->{$this->db}->$collection;
        $this->collectionName = $collection;
    }

    /**
     * 获取MongoDB实例
     * @param $name
     * @return Manager
     */
    private function getMongodb($name = 'local')
    {
        static $mongodbServer = [];
        if (isset($mongodbServer[$name])) {
            return $mongodbServer[$name];
        }
        if (IS_SERVER) {
            $mongoConf = MONGO_LIST['server'];
        } else {
            $mongoConf = MONGO_LIST[$name];
        }
        $uri = $mongoConf['url'];
        $mongodbServer[$name] = new Client($uri);
        return $mongodbServer[$name];
    }

    /**
     * 设置筛选器
     * @param array $condition
     * @return AbstractMongoModel
     */
    public function filter($condition = [])
    {
        $this->filter = $condition;
        return $this;
    }

    /**
     * 获取筛选器
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * 获取分页数据列表
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function paginate($page = 1, $limit = 10)
    {
        // 格式化数据
        $page = (int)$page;
        $limit = (int)$limit;

        $searchCount = $this->count($this->getFilter(), []);
        $searchResult = $this->find($this->getFilter(), [], $page, $limit);

        // 计算最后一页的数量
        if ($searchCount <= $limit) {
            $lastPage = 1;
        } else {
            $remainder = $searchCount % $limit;
            if ($remainder <= 0) {
                $lastPage = $searchCount / $limit;
            } else {
                $lastPage = intval($searchCount / $limit) + 1;
            }
        }

        $resData = [
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $limit,
            'total' => $searchCount,
            'data' => $searchResult
        ];
        return $resData;
    }

    /**
     * 获取当前集合的自增ID
     * @param string $collectionName
     * @return int
     */
    public function increaseId($collectionName = null)
    {
        /**
         * @var Collection $collection
         */
        $collection = $this->mongo->{$this->db}->{$this->increaseCollection};

        $update = ['$inc' => ["id" => 1]];
        $query = ['name' => $collectionName ?: $this->collectionName];
        $option = [
            'upsert' => true,
            'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER
        ];

        $res = $collection->findOneAndUpdate($query, $update, $option);
        return (int)$res->id;
    }

    /**
     * 插入一条文档
     * @param array $document 需要插入的文档，一个数组
     * @param bool $increaseId 是否需要插入自增ID，默认为true
     * @param array $options Command options，具体要参考官方文档（一般不需要用到）
     * @return mixed
     */
    public function insert(array $document, $increaseId = true, $options = [])
    {
        if ($increaseId === true) {
            // 先获取自增id再插入
            $document['id'] = $this->increaseId($this->collectionName);
        }

        $insert_one_res = $this->collection->insertOne($document, $options);
        if ($increaseId === true) {
            if ($insert_one_res->getInsertedId()) {
                return $document['id'];
            } else {
                return false;
            }
        } else {
            return $insert_one_res->getInsertedId();
        }
    }

    /**
     * 更新一条文档
     * @param array $filter
     * @param array $update
     * @param array $option
     * @return int|null
     */
    public function update($filter, $update, $option = [])
    {
        $updateRes = $this->collection->updateOne($filter, ['$set' => $update], $option);
        return $updateRes->getModifiedCount();
    }

    /**
     * 根据条件统计文档数量
     * @param array $filter
     * @param array $options
     * @return mixed
     */
    public function count($filter = [], $options = [])
    {
        return $this->collection->countDocuments($filter, $options);
    }

    /**
     * 根据条件查询文档
     * @param array $filter
     * @param array $options
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function find($filter = [], $options = [], $page = 1, $limit = 10)
    {
        $options += [
            'projection' => [
                '_id' => 0,
            ],
            'sort' => ['id' => -1],
            'skip' => ($page - 1) * $limit,
            'limit' => $limit,
        ];
        return $this->collection->find($filter, $options)->toArray();
    }
}