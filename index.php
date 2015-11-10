<?php
/**
 *
 */
//require('lib/log/filelog.php');
require('swooleserver.php');


//$log = new FileLog($cfg);

try {
    $sw = new swooleserver();
    $sw->onInit($cfg);
    $sw->onStart();
    $sw->onExit();
}
catch (Exception $e) {
    var_dump($e);
}