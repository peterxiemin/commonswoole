<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/29
 * Time: 9:11
 */

require_once(WORKROOT . '/lib/db/nosql/memcache.php');
require_once(WORKROOT . '/lib/db/nosql/mongo.php');
class MemcacheTest implements LogicInterface  {
    private $_cfg;
    private $_memc;
    private $_mgo;
    public function __construct($cfg) {
        $this->_cfg = $cfg;
        $this->_mgo = new MongoWrapper($cfg['mongo']);
        $this->_memc = new MemcacheWrapper($cfg['memcache']);
        $this->_mgo->setDbName('test');
        $this->_mgo->setColctName('test');
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function httpTaskProcess($r = null) {

    }

    public function workTaskProcess($data = null) {
        if (is_array($data) && is_array($data['query']) && isset($data['query']['type'])) {
            switch ($data['query']['type']) {
                case 'memcache':
                    $count = 0;
                    while (1) {
                        $this->_memc->set($count, uniqid(), 3600);
                        $this->_memc->get($count);
                        if ($count % 10000 == 0) {
                            echo "memcache count: $count\n";
                        }
                        $count++;
                    }
                    break;
                case 'mongo':
                    $count = 0;
                    while (1) {
                        $this->_mgo->save(array('key'=>$count, 'val'=>uniqid()));
                        $this->_mgo->findOne(array('key'=>$count));
                        if ($count % 10000 == 0) {
                            echo "mongo count: $count\n";
                        }
                        $count++;
                    }
                    break;
                default:
                    throw new Exception('no type vaild');
            }
        }
    }
}