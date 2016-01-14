<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/13
 * Time: 10:43
 */
namespace logic\ssh;
use lib\log\Logger;

class HandleExec
{
    private $_config = NULL;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->init();
    }

    public function init()
    {

    }

    public function __destruct()
    {

    }

    public function handleExecOutRet($stream)
    {
        while (($line = fgets($stream, 4096)) !== false) {
            try {
                echo $line.PHP_EOL;
            } catch (\Exception $e) {
                Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
            }
        }
    }
}