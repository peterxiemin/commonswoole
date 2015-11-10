<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 10:53
 */

return array(
    'swoole' => array(
        'swoole_host' => '0.0.0.0',
        'swoole_port' => 8077,
        'max_request' => 50000,
//        'log_file'    => '/data/logs/imcpagent/imcpagent.log',
        'daemonize'   => false,
        'gzip'        => false
    ),
    'business' => array(
        'MessageQueue',
        'Test'
    )
);