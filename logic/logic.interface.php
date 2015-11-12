<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:50
 */

interface LogicInterface {
    public function httpTaskProcess($r = null);
    public function workTaskProcess($id = 0);
}