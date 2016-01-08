<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 10:53
 * Description: 服务的核心配置
 */

return array(
    /*
     * swoole的基础配置
     */
    'swoole' => array(
        'swoole_host' => '0.0.0.0',
        'swoole_port' => 7000,
        'worker_num' => 16, // 进程数
        'task_worker_num' => 16, //任务进程数
        'max_request' => 100000,
        'log_file' => '/data/logs/commonSwoole/commonSwoole.log',
        'debug' => true,
        'gzip' => false,
        'progname' => 'commonswoole'
    ),

    /**
     *业务配置，key: 类名称 value：process 表示多进程工作模式  http 表示http请求并相应的工作模式
     */
    'business' => array(
        'MongoTest' => array(
            'type' => array('process'),
            'online' => false
        ),
        'RedisTest' => array(
            'type' => array('http'),
            'online' => false
        ),
        'QueueTest' => array(
            'type' => array('process'),
            'online' => false
        ),
        'ProcessTest' => array(
            'type' => array('process'),
            'online' => false
        ),
        'HttpTest' => array(
            'type' => array('http'),
            'online' => false
        ),
        'SShTest' => array(
            'type' => array('process'),
            'online' => false
        ),
        'BloomTest'=>array(
            'type'=> array('http'),
            'online'=> false
        ),
        'IPTest'=>array(
            'type'=>array('http'),
            'online'=>false
        ),
        'MessageQueueTest'=>array(
            'type'=>array('process'),
            'online'=>false
        ),
        'DataTransTest' => array(
            'type' => array('process'),
            'online' => false
        ),
        'DiffUserTags' => array(
            'type' => array('process'),
            'online' => false
        ),
        'MemcacheTest' => array(
            'type' => array('process'),
            'online' => true
        )
    ),

    /**
     * 表示服务建构， 这里配置文件目录信息，意义不大， 后面考虑优化
     */
    'workpath' => array(
        'LOGIC' => WORKROOT . '/logic/',
        'CONFIG' => WORKROOT . '/conf/',
        'LIB' => WORKROOT . '/lib/'
    )
);