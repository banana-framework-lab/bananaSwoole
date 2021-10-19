<?php


namespace Library\Server\Functions;


use Library\Container;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Swoole\Table;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class AutoReload
{
    /**
     * @var Table $reloadTable
     */
    private $reloadTable;

    /**
     * @var int $reloadTickId
     */
    public $reloadTickId;

    /**
     * @var bool $isFirstStart
     */
    private $isFirstStart = true;

    /**
     * @return Table
     */
    public function getReloadTable(): Table
    {
        return $this->reloadTable;
    }

    /**
     * @param Table $reloadTable
     */
    public function setReloadTable(Table $reloadTable): void
    {
        $this->reloadTable = $reloadTable;
    }

    /**
     * @param SwooleWebSocketServer $server
     */
    public function main($server)
    {
        // 读取需要热加载的路径
        $pathList = Container::getConfig()->get('reload.path_list', []);
        $isReload = false;
        $iNodeList = [];

        //判断文件更新或者新增
        foreach ($pathList as $pathKey => $pathValue) {
            $dirIterator = new RecursiveDirectoryIterator($pathValue);
            $iterator = new RecursiveIteratorIterator($dirIterator);

            /* @var SplFileInfo $fileValue */
            foreach ($iterator as $fileKey => $fileValue) {
                $ext = $fileValue->getExtension();
                if ($ext == 'php') {
                    $iNode = $fileValue->getInode();
                    $mTime = $fileValue->getMTime();
                    $iNodeList[] = $iNode;
                    if ($this->reloadTable->exist($iNode)) {
                        if ($this->reloadTable->get($iNode)['mTime'] != $mTime) {
                            $this->reloadTable->set($iNode, [
                                'mTime' => $mTime
                            ]);
                            $isReload = true;
                        }
                    } else {
                        $this->reloadTable->set($iNode, [
                            'mTime' => $mTime
                        ]);
                        $isReload = true;
                    }
                }
            }
        }

        //判断文件删除
        foreach ($this->reloadTable as $reloadKey => $reloadValue) {
            if (!in_array((int)$reloadKey, $iNodeList)) {
                $this->reloadTable->del($reloadKey);
                $isReload = true;
            }
        }

        if (!$this->isFirstStart) {
            if ($isReload) {
                $server->reload();
            }
        } else {
            $this->isFirstStart = false;
        }
    }
}