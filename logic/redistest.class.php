<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/13
 * Time: 13:46
 */


require_once(WORKROOT . '/lib/db/nosql/redis.php');

class RedisTest implements LogicInterface {
    private $_cfg;
    private $_redis;
    public function __construct($cfg) {
        $this->_cfg = $cfg;
        $this->_redis = new RedisWrapper($cfg['redis-cluster']);
    }

    public function __destruct() {

    }

    public function httpTaskProcess($r = null) {
        $this->_redis->set('xiemin', 'haha');
        return $this->_redis->get('xiemin');
    }

    public function workTaskProcess($data = null) {
        echo 'bbb';
    }

}