<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 * Description: 程序入口，所以的throw异常如果没有被业务捕获，将在这里进行捕获
 */

define('WORKROOT', __DIR__);
date_default_timezone_set("Asia/Shanghai");

//自动加载
require_once(WORKROOT . '/commonswoole/CommonFunc.php');
//加载全局函数
require_once(WORKROOT . '/lib/Think/Common/functions.php');

spl_autoload_register('\CommonSwoole\commonfunc::autoload');
try {
    $sw = new commonswoole\SSWrapper();
    $sw->onInit();
    $sw->onStart();
    $sw->onExit();
} catch (\Exception $e) {
    lib\log\Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
    //打印栈信息
    lib\log\Logger::logWarn($e->getTraceAsString());
}
