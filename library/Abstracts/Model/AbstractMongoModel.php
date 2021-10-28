<?php
/**
 * MongoDB数据模型抽象类
 * User: zzh
 * Time: 17:27
 */

namespace Library\Abstracts\Model;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Manager;
use MongoDB\Operation\FindOneAndUpdate;

/**
 * Class AbstractMongoModel
 * @property Manager mongo
 * @package Library\Abstracts\Model\DataBaseModel
 */
abstract class AbstractMongoModel
{
    /**
     * 筛选器
     * @var array
     */
    protected $filter = [];

    /**
     * 选择器
     * @var array
     */
    protected $options = [];

    /**
     * 自增id数据集合
     * @var string
     */
    private $increaseCollection = '_increment';

    /**
     * 数据库名
     * @var string
     */
    private $dbName;

    /**
     * 集合名称
     * @var string
     */
    private $collectionName;

    /**
     * 集合
     * @var \MongoDB\Collection
     */
    private $collection;

    /**
     * AbstractMongoModel constructor.
     * @param $dbName
     * @param $collection
     */
    public function __construct($dbName, $collection)
    {
        $this->dbName = $dbName;
        $this->collection = $this->mongo->{$this->dbName}->$collection;
        $this->collectionName = $collection;
        $this->increaseCollection = "{$collection}_increment";
    }

    /**
     * @param $name
     * @return Client|Collection|\MongoDB\Database|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'mongo':
                return EntityMongo::getInstance();
            case 'db':
                return (EntityMongo::getInstance())->{$this->dbName};
            case 'collection':
                return $this->collection;
            default:
                return null;
        }
    }

    /**
     * 获取筛选器结果
     * @param array $filter 筛选条件
     * @return array $filter
     */
    abstract protected function getFilter($filter = []): array;

    /**
     * 获取选择器结果
     * @param array $options 选择条件
     * @return array $options
     */
    abstract protected function getOptions($options = []): array;

    /**
     * 获取默认的分页选择器
     * @param $page
     * @param $limit
     * @return array
     */
    protected function getDefaultPageOption($page, $limit): array
    {
        return [
            'skip' => $page > 0 ? (int)(($page - 1) * $limit) : 0,
            'limit' => (int)$limit,
        ];
    }

    /**
     * 根据条件获取文档
     * @param array $filter 筛选器
     * @param array $options 选择器
     * @return array
     */
    public function getList($filter = [], $options = []): array
    {
        $lastFilter = $this->getFilter($filter);
        $lastOptions = $this->getOptions($options) + ['projection' => ['_id' => 0]];
        return $this->collection->find($lastFilter, $lastOptions)->toArray();
    }

    /**
     * 根据条件统计文档数量
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function getCount($filter = [], $options = []): int
    {
        $lastFilter = $this->getFilter($filter);
        $lastOptions = $this->getOptions($options);
        unset($lastOptions['skip']);
        unset($lastOptions['limit']);
        return $this->collection->countDocuments($lastFilter, $lastOptions);
    }

    /**
     * 获取分页数据列表
     * @param array $filter
     * @param array $options
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function paginate($filter = [], $options = [], $page = 1, $limit = 10): array
    {
        // 格式化数据
        $page = (int)$page;
        $limit = (int)$limit;

        $options += $this->getDefaultPageOption($page, $limit);
        $searchCount = $this->getCount($filter, $options);
        $searchResult = $this->getList($filter, $options);

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
     * @return int
     */
    public function increaseId(): int
    {
        /**@var Collection $collection */
        $collection = $this->mongo->{$this->dbName}->{$this->increaseCollection};

        $update = ['$inc' => ["id" => 1]];
        $query = ['name' => $this->collectionName];
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
    public function addOne(array $document, $increaseId = true, $options = [])
    {
        if ($increaseId === true) {
            // 先获取自增id再插入
            $document['id'] = $this->increaseId();
        }
        $insertOneRes = $this->collection->insertOne($document, $options);
        if ($increaseId === true) {
            if ($insertOneRes->getInsertedId()) {
                return $document['id'];
            } else {
                return false;
            }
        } else {
            return $insertOneRes->getInsertedId();
        }
    }

    /**
     * 更新一条文档
     * @param array $filter
     * @param array $update
     * @param array $option
     * @return int|null
     */
    public function updateOne($filter, $update, $option = []): int
    {
        $updateRes = $this->collection->updateOne($filter, ['$set' => $update], $option);
        return (int)$updateRes->getModifiedCount();
    }
}