<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/13
 * Time: 21:37
 */

namespace  logic\kafka;

class HandleMsg {
    public function __construct()
    {
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
    public function _handleMsg($arg) {
        echo $arg.PHP_EOL;
    }
}

