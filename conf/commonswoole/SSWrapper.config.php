<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 10:53
 * Description: ����ĺ�������
 */

return array(
    /*
     * swoole�Ļ�������
     */
    'swoole' => array(
        'swoole_host' => '0.0.0.0',
        'swoole_port' => 10001,
        'worker_num' => 16, // ������
        'task_worker_num' => 16, //���������
        'max_request' => 100000,
        'log_file' => '/data/logs/commonSwoole/commonSwoole.log',
        'debug' => true,
        'gzip' => false,
        'progname' => 'commonswoole'
    ),

    /**
     *ҵ�����ã�key: ������ value��process ��ʾ����̹���ģʽ  http ��ʾhttp������Ӧ�Ĺ���ģʽ
     */
    'business' => array(
        'MongoTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'RedisSentinelTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'PredisTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'KafkaTest' => array(
            'type' => 'process',
            'online' => true
        ),
        'ProcessTest' => array(
            'type' => 'process',
            'online' => true
        ),
        'HttpTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'SShTest' => array(
            'type' => 'process',
            'online' => true
        ),
        'BloomTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'IPTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'MemcacheTest' => array(
            'type' => 'http',
            'online' => true
        ),
        'MysqlTest' => array(
            'type' => 'http',
            'online' => true,
        ),
        'ThinkTest' => array(
            'type' => 'http',
            'online' => true,
        )
    ),

    /**
     * ��ʾ���񽨹��� ���������ļ�Ŀ¼��Ϣ�����岻�� ���濼���Ż�
     */
    'workpath' => array(
        'LOGIC' => WORKROOT . '/logic',
        'CONFIG' => WORKROOT . '/conf/',
        'LIB' => WORKROOT . '/lib/'
    )
);
