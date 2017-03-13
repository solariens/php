<?php

/**
 * 通用多进程框架
 * @since 2017-03-13
 */

class MultiProcessing {

    /**
     * 操作类型 file、xml
     * @var string
     */
    private $strType = null;

    /**
     * 对应的地址，如果type=file、对应地址则为对应的目录路径
     * @var string
     */
    private $strLoc = null;

    /**
     * 工作进程数
     * @var integer
     */
    private $intWorkerProcess = 4;

    /**
     * 保存loc列表的集合
     * @var array
     */
    private $arrLocList = array();

    /**
     * loc集合数
     * @var integer
     */
    private $intLocCount = 0;

    /**
     * 为每个工作进程分配任务数
     * @var integer
     */
    private $intWorkerComission = 0;

    /**
     * 记录每个工作进程ID，用于监控进程状态
     * @var array
     */
    private $arrPids = array();

    /**
     * 任务处理方法
     * @var string
     */
    private $funcDataHandler = null;

    /**
     * 主进程ID
     * @var integer
     */
    private $intMasterPid = 0;

    /**
     * 运行入口方法
     * @return
     */
    public function execute() {
        $this->initEnv();
        $this->setMasterPid();
        $this->forkWorker();
        $this->monitorWorker();
    }

    /**
     * 初始化工作环境，如：获取总工作量，为每个工作进程分配任务量
     * @return
     */
    private function initEnv() {
        switch($this->strType) {
            case 'file':
                $this->getFileList();
            break;
            case 'xml':
                //:todo
            break;
        }
        $this->intLocCount = count($this->arrLocList);
        $this->intWorkerComission = ceil($this->intLocCount / $this->intWorkerProcess);
    }

    /**
     * 监控工作进程状态
     * @return
     */
    private function monitorWorker() {
        if ($this->intMasterPid === posix_getpid()) {
            while (true) {
                $intPid = pcntl_wait($intStatus);
                $intStatus = pcntl_wexitstatus($intStatus);
                if ($intStatus === 100) {
                    unset($this->arrPids[$intPid]);
                }
                if (empty($this->arrPids)) {
                    break;
                }
            }
        }
    }

    /**
     * 设置主进程ID，便于后期监控工作进程运行状态
     */
    private function setMasterPid() {
        $this->intMasterPid = posix_getpid();
    }

    /**
     * fork工作进程
     * @return
     */
    private function forkWorker() {
        for ($intWorkerId=0; $intWorkerId<$this->intWorkerProcess; ++$intWorkerId) {
            $intPid = pcntl_fork();
            if ($intPid < 0) {
                die('生成工作进程失败');
            } elseif ($intPid > 0) {
                $this->arrPids[$intPid] = $intPid;
            } else {
                $this->runWorker($intWorkerId);
                exit(100);
            }
        }
    }

    /**
     * 工作进程数据处理
     * @param  interge $intWorkerId 工作进程ID
     * @return
     */
    private function runWorker($intWorkerId) {
        $intStart = $intWorkerId * $this->intWorkerComission;
        $arrLocList = array_slice($this->arrLocList, $intStart, $this->intWorkerComission);
        if (empty($arrLocList)) {
            return false;
        }
        foreach ($arrLocList as $key=>$val) {
            if (!empty($this->funcDataHandler)) {
                call_user_func($this->funcDataHandler, $val);
            } else {
                //:todo
            }
        }
    }

    /**
     * 从指定目录获取所有文件列表，非文件类型直接过滤掉
     * @return
     */
    private function getFileList() {
        $strExt = substr($this->strLoc, -1, 1);
        if ($strExt !== '/') {
            $this->strLoc .= '/';
        }
        if (!is_dir($this->strLoc)) {
            die('指定目录不存在，请正确设置');
        }
        $dir = opendir($this->strLoc);
        if ($dir === false) {
            die('打开对应目录失败');
        }
        while (($strFile = readdir($dir)) != false) {
            if ($strFile === '.' || $strFile === '..') {
                continue;
            }
            if (file_exists($this->strLoc . $strFile) && is_file($this->strLoc . $strFile)) {
                $this->arrLocList[] = $this->strLoc . $strFile;
            }
        }
        closedir($dir);
    }

    /**
     * 设置工作进程处理方法
     * @param string 回调方法名
     */
    public function setDataHandler($strFunc) {
        $this->funcDataHandler = $strFunc;
    }

    /**
     * 设置工作进程数
     * @param integer $intWorkerProcess 工作进程数
     */
    public function setWorkerProcess($intWorkerProcess) {
        $this->intWorkerProcess = $intWorkerProcess;
    }

    /**
     * 设置操作类型和对应的地址
     * @param string $strType 类型  如：file、json、xml
     * @param string $strLoc  对应地址  如：/home/work/
     */
    public function setTypeAndLoc($strType, $strLoc) {
        $this->strType = $strType;
        $this->strLoc = $strLoc;
    }
}

//使用方法如下
// $obj = new MultiProcessing();
// $obj->setTypeAndLoc('file', '/home/script/v2');
// $obj->setWorkerProcess(8);
// $obj->setDataHandler($strCallBack);
// $obj->execute();
