<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/29
 * Time: 9:11
 */
namespace logic;
use commonswoole\CommonFunc;
use lib\db\MemcacheWrapper;

class MemcacheTest implements LogicInterface  {
    private $_memc;
    private $_cfg;
    public function __construct() {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function httpTaskProcess($r = null) {
        $this->_memc =  new MemcacheWrapper($this->_cfg['memcache']);
        $this->_memc->set('HELLO', 'COMMONSWOOLE', 0);
        return $this->_memc->get('HELLO');
    }

    public function workTaskProcess($data = null)
    {

    }
}