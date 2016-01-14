<?php
/**
 * 数据库配置文件示例
 */
return array(
	'DBLite' => array(
		'fetchStyle' => PDO::FETCH_ASSOC,
		'adapter' => 'Mysql',  //适配器模式，选择具体的数据库类型
		'defaultConnection' => 'master', //默认连接
	),
	'Mysql' => array(
		'maxRetries' => 2, //当数据库连接失败后，最大的重试次数
		'databases' => array(   //数据库实例
			'master' => array(   //支持配置多个数据库实例,实际连接时,随机选择一个实例进行连接
				array(
					'host' => '',
					'port' => '',
					'username' => '',
					'password' => '',
					'dbname' => '',
					'charset' => '',
					'prefix' => '', //数据表前缀,有统一前缀时,可以使用此配置,减小表名长度
				),
			),
		),
	),
);
