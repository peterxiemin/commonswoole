<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/29
 * Time: 9:12
 */


return array(
    'memcache' => array(
        'keyPrefix' => '',
        'exceptionDisabled' => false,
        'servers' => array(
            array(
                'host' => '127.0.0.1',
                'port' => 12122,
                'persistent' => true
            )
        )
    )
);