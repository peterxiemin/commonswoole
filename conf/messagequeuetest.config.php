<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:09
 */

return array(
    'kafka' => array(
        'kafka_brokers' => array(
            '127.0.0.1',
            '127.0.0.2',
            '127.0.0.3'
        ),
        'log_level' => LOG_DEBUG,
        'zookeeper_host' => 'localhost',
        'zookeeper_port' => '2181'
    ),
    'mongo' => array(
        // 跨机房性能下降严重, insert最快， save稍慢， update最慢
        'mongourl' => 'mongodb://127.0.0.1:20000,127.0.0.2:20000,127.0.0.3:20000/', //600qps
        'option' => array(
            'socketTimeoutMS'=>100000,
            'w' => 1
        ),
        'db' => 'usercenter',
        'userbase' => 'userbase',
        'useraction' => 'useraction'
    ),
    'userinfo' => array(
        'alldata' => array(
            "date",
            "userkey",
            "login_time",
            "publish_id",
            "platform",
            "ua",
            "mos",
            "soft_version",
            "net",
            "is_update",
            "is_dau",
            "first_access_time",
            "hb",
            "page_aids",
            "page_count",
            "page_view_count",
            "share_aids",
            "share_count",
            "pushaccess_aids",
            "pushaccess_count",
            "openpush_aids",
            "openpush_count",
            "push_action",
            "except",
            "except_count",
            "life_time",
            "in_action",
            "in_count",
            "login",
            "login_count",
            "actions",
        ),
        'userbase' => array(
            "userkey" => null,
            "login_time" => null,
            "publish_id" => null,
            "platform" => null,
            "ua" => null,
        ),
        'useraction' => array(
            "mos" => null,//如果系统设计会发生改变
            "date" => null,
            "soft_version" => null,
            "net" => null,
            "userkey" => null,
            "is_dau" => null,
            "first_access_time" => null,
            "hb" => null,
            "page_aids" => null,
            "page_count" => null,
            "page_view_count" => null,
            "share_aids" => null,
            "share_count" => null,
            "pushaccess_aids" => null,
            "pushaccess_count" => null,
            "openpush_aids" => null,
            "openpush_count" => null,
            "push_action" => null,
            "except" => null,
            "except_count" => null,
            "life_time" => null,
            "in_action" => null,
            "in_count" => null,
            "login" => null,//todo
            "login_count" => null,
            "actions_count" => null,
            "actions" => null,
        )
    )
);