<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 13:17
 */

class Test implements LogicInterface{
    public function taskProcess($r) {
        return json_encode(array('a'=>1));
    }
}