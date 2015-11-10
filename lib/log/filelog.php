<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 9:48
 */


class FileLog {
    private $filename;

    public function __construct($filename) {
        if (file_exists($filename)) {
            $this->filename = $filename;
        }
        else {
            throw new Exception("filename not error");
        }
    }

    public function __destruct() {

    }

    public function logDebug($msg) {
        file_put_contents($this->filename, $this->logformate('DEBUG', $msg), FILE_APPEND | LOCK_EX);
    }

    public function logInfo($msg) {
        file_put_contents($this->filename, $this->logformate('Info', $msg), FILE_APPEND | LOCK_EX);
    }

    public function logWarn($msg) {
        file_put_contents($this->filename, $this->logformate('Warn', $msg), FILE_APPEND | LOCK_EX);
    }

    public function logFatal($msg) {
        file_put_contents($this->filename, $this->logformate('Fatal', $msg), FILE_APPEND | LOCK_EX);
    }

    private function logformate($level, $msg) {
        $time = new Date("Y-m-d H:i:s");
        return " \"$time\"\t \"$level\"\t \"$msg\"";
    }
}