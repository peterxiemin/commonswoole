<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 9:48
 */
namespace lib\log;

class Logger {
    public static function logDebug($msg) {
        echo self::logformate('DEBUG', $msg);
    }

    public static function logInfo($msg) {
        echo self::logformate('INFO', $msg);
    }

    public static function logWarn($msg) {
        echo self::logformate('WARN', $msg);
    }

    public static function logFatal($msg) {
        echo self::logformate('FATAL', $msg);
    }

    public static function logformate($level, $msg) {
        $time = date("Y-m-d H:i:s");
        return " \"$time\"\t \"$level\"\t \"$msg\"".PHP_EOL;
    }
}
