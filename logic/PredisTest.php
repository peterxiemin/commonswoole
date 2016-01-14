<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/13
 * Time: 13:46
 */

namespace logic;

use lib\db\Predis\Client;
use commonswoole\CommonFunc;

class PredisTest implements LogicInterface {
    private $_cfg;
    private $_redis;
    public function __construct() {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_redis = new Client($this->_cfg['redis-cluster']);
    }

    public function __destruct() {

    }

    public function httpTaskProcess($r = null) {
        $this->_redis->set('HELLO', 'COMMONSWOOLE');
        return $this->_redis->get('HELLO');
    }

    public function workTaskProcess($data = null) {
        echo 'bbb';
    }

}