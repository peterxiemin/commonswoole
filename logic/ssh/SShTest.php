<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/29
 * Time: 15:49
 */
namespace logic\ssh;
use logic\LogicInterface;
use lib\ssh\SShWrapper;
use CommonSwoole\CommonFunc;

class SShTest implements LogicInterface
{
    private $_cfg = null;
    private $_hec = null;

    public function __construct()
    {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_hec = new HandleExec($this->_cfg);
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($data = null)
    {
        $ssh = new SShWrapper($this->_cfg['ssh']);
        $ssh->setCmd('ls /tmp');
        $ssh->doExec($this->_hec, 'handleExecOutRet');
    }
}
