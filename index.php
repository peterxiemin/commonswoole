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

$cfg_file = WORKROOT . '/conf/swoole.config.php';
if (!file_exists($cfg_file)) {
    throw new Exception($cfg_file . 'not exsits');
}
$cfg = require_once($cfg_file);

$sw_file = WORKROOT . '/swooleserver.php';
if (!file_exists($sw_file)) {
    throw new Exception($sw_file . 'not exsits');
}

/* 全局日志 */
$log_file = WORKROOT . '/lib/log/logger.php';
if (!file_exists($log_file)) {
    throw new Exception($log_file . ' not exsits');
}

try {
    require_once($sw_file);
    require_once($log_file);
    $sw = new swooleserver($cfg);
    $sw->onInit();
    $sw->onStart();
    $sw->onExit();
} catch (Exception $e) {
    Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
    Logger::logWarn($e->getTraceAsString());
}