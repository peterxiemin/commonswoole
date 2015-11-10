<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 */

class MessageQueue implements LogicInterface {
//    public $logicname;
    public function __construct() {
//        $this->logicname = $logicname;
    }

    public function __destruct() {

    }

    public function taskProcess($r) {
        return json_encode(array('a'=>1, 'b'=>2));
    }
}