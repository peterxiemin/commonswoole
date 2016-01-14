<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/22
 * Time: 10:19
 */

namespace logic;

use lib\db\MongoWrapper;
use commonswoole\CommonFunc;

class MongoTest
{
    private $_mgo;
    private $_cfg;

    public function __construct()
    {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_mgo = new MongoWrapper($this->_cfg['mongo-rep']);
        $this->_mgo->setDbName('test');
        $this->_mgo->setColctName('usertable');
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {
        $ret = $this->_mgo->findOne(array('_id' => 'user1468038131265307737'));
        return json_encode($ret, JSON_UNESCAPED_UNICODE);
    }

    public function workTaskProcess($data = null)
    {

    }

}
