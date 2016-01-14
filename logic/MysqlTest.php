<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/22
 * Time: 10:19
 */

namespace logic;

use lib\db\dblite\adapter\Mysql;
use commonswoole\CommonFunc;

class MysqlTest
{
    private $_msql;
    private $_cfg;

    public function __construct()
    {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_msql = new Mysql($this->_cfg);
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {
        return 'HELLO COMMONSWOOLE';
    }

    public function workTaskProcess($data = null)
    {

    }

}
