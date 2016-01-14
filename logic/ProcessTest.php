<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/29
 * Time: 8:33
 */

namespace logic;

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
        echo '############################'.PHP_EOL;
        echo '#####HELLO COMMONSWOOLE#####'.PHP_EOL;
        echo '############################'.PHP_EOL;
    }
}
