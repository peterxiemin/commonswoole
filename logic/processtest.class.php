<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/29
 * Time: 8:33
 */
class ProcessTest implements LogicInterface
{
    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($data = null)
    {
        var_dump($data);
    }
}
