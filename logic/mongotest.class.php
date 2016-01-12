<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/22
 * Time: 10:19
 */

require_once(WORKROOT . '/lib/db/nosql/mongo.php');

class MongoTest {
    private $_mgo;
    private $_cfg;
    public function __construct($cfg) {
        $this->_cfg = $cfg;
        $this->_mgo = new MongoWrapper($cfg);
    }

    public function __destruct() {
    }

    public function httpTaskProcess($r = null) {
        
    }

    public function workTaskProcess($data = null) {
    }
}