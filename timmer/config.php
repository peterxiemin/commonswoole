<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/29
 * Time: 14:51
 */


return array(
	'debug' => false,
	'logfile' => './timer.log',
	'muti_process'=>array(
		array(
			'url' => 'http://127.0.0.1/HttpTest',
			'tick_cb' => 'process_timer',
			'ms' => 1000, //1秒
		),
		array(
			'url' => 'http://127.0.0.1/HttpTest',
			'tick_cb' => 'process_timer',
			'ms' => 2000, //1秒
		)
	),
);
