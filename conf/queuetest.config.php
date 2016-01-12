<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/16
 * Time: 11:10
 */

return array(
    'mongo' => array(
        'mongourl' => 'mongodb://127.0.0.1:27017/', //600qps
        'option' => array('w' => 1)
    ),
    'kafka' => array(
        'kafka_brokers' => array(
            '127.0.0.1:9092',
            '127.0.0.2:9093',
            '127.0.0.3:9094'
        ),
        'log_level' => LOG_INFO,
        'zookeeper_host' => 'localhost',
        'zookeeper_port' => '2181'
    )
);