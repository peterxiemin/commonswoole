<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/29
 * Time: 14:49
 */
date_default_timezone_set("Asia/Shanghai");
$cfg = require_once('./config.php');
$workers = [];

/**
 * daemon($nochdir, $noclose);
 * $nochdir，为true表示不修改当前目录。默认false表示将当前目录切换到“/”
 * $noclose，默认false表示将标准输入和输出重定向到/dev/null
 */

if ($cfg && isset($cfg['debug']) && !$cfg['debug']) {
	swoole_process::daemon(1, 0);
}

/**
 * $function，子进程创建成功后要执行的函数
 * $redirect_stdin_stdout，重定向子进程的标准输入和输出。 启用此选项后，在进程内echo将不是打印屏幕，而是写入到管道。读取键盘输入将变为从管道中读取数据。 默认为阻塞读取。
 * $create_pipe，是否创建管道，启用$redirect_stdin_stdout后，此选项将忽略用户参数，强制为true 如果子进程内没有进程间通信，可以设置为false
 */


if ($cfg && isset($cfg['muti_process']) && is_array($cfg['muti_process'])) {
	foreach ($cfg['muti_process'] as $proc_cfg) {
		$process = new swoole_process(function (swoole_process $worker) use ($proc_cfg) {
			swoole_timer_tick($proc_cfg['ms'], function ($timer_id) use ($proc_cfg) {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $proc_cfg['url']);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($curl);
				curl_close($curl);
				logprint('info', trim($data));
			});
		},  false, false);
		$pid = $process->start();
		$workers[$pid] = $process;
	}

}
else {
	logprint('warn', 'cfg is invaild');
}


master_process($workers);

function logprint ($level, $msg) {
	global $cfg;
	if ($cfg['debug'] == true) {
		echo '['.date("Y/m/d H:i:s").'] ['.$level.'] ['.posix_getpid().'] ['.$msg.']'.PHP_EOL;
	}
	else {
		file_put_contents($cfg['logfile'], '['.date("Y/m/d H:i:s").'] ['.$level.'] ['.posix_getpid().'] ['.$msg.']'.PHP_EOL, FILE_APPEND|LOCK_EX);

	}
}

function master_process($workers)
{
	//监听子进程，如果停止，会再拉起来
	swoole_process::signal(SIGCHLD, function ($signo) use (&$workers) {
		while (1) {
			$ret = swoole_process::wait(false);
			if ($ret) {
				$pid = $ret['pid'];
				//这里实现一个自动拉起的能力
				$child_process = $workers[$pid];
				logprint('info', "Worker Exit, kill_signal={$ret['signal']} PID=" . $pid);
				$new_pid = $child_process->start();
				$workers[$new_pid] = $child_process;
				unset($workers[$pid]);
			} else {
				break;
			}
		}
	});


	//kill -10 结束全部程序
	swoole_process::signal(SIGUSR1, function ($signo) use (&$workers) {
		swoole_process::signal(SIGCHLD, null);
		foreach ($workers as $pid => $worker) {
			swoole_process::kill($pid);
		}
		//处理子进程，然后自己退出
		exit;
	});
}

