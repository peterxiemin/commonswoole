<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 * Description: 框架的核心封装类
 */


namespace CommonSwoole;

class SSWrapper
{
	private $_serv;
	private $_swoole_cfg = array();

	public function __construct()
	{
		$this->_swoole_cfg = CommonFunc::getLogicConf(__CLASS__);
	}

	public function __destruct()
	{

	}

	private function _getSwooleCoreCfg()
	{
		if (!$this->_swoole_cfg) {
			throw new \Exception('_swoole_cfg is invaild');
		}

		$core_cfg = array();
		/* 默认会以cpu数为准 */
		if (isset($this->_swoole_cfg['swoole']['worker_num'])) {
			$core_cfg['worker_num'] = $this->_swoole_cfg['swoole']['worker_num'];
		}

		/* 下面强制设置 */
		$core_cfg['task_worker_num'] = isset($this->_swoole_cfg['swoole']['task_worker_num']) ? $this->_swoole_cfg['swoole']['task_worker_num'] : 8;
		$core_cfg['max_request'] = isset($this->_swoole_cfg['swoole']['max_request']) ? $this->_swoole_cfg['swoole']['max_request'] : 100000;

		/* log_file和debug绑定设置 */
		$core_cfg['daemonize'] = isset($this->_swoole_cfg['swoole']['debug']) ? !$this->_swoole_cfg['swoole']['debug'] : false;
		if ($core_cfg['daemonize']) {
			if (isset($this->_swoole_cfg['swoole']['log_file'])) {
				if (!file_exists($this->_swoole_cfg['swoole']['log_file'])) {
					throw new Exception('log_file not exsits');
				}
				$core_cfg['log_file'] = $this->_swoole_cfg['swoole']['log_file'];
			} else {
				$core_cfg['log_file'] = "/dev/null";
			}
		}
		return $core_cfg;
	}

	public function onInit()
	{
		$host = isset($this->_swoole_cfg['swoole']['swoole_host']) ? $this->_swoole_cfg['swoole']['swoole_host'] : '0.0.0.0';
		$port = isset($this->_swoole_cfg['swoole']['swoole_port']) ? $this->_swoole_cfg['swoole']['swoole_port'] : 80;
		$serv = new \swoole_http_server ($host, $port);
		/**
		 * 这里对swoole进程进行设置
		 */
		$serv->set($this->_getSwooleCoreCfg());
		$serv->on('WorkerStart', array(
			$this,
			'onWorkerStart'
		));
		$serv->on('WorkerStop', array(
			$this,
			'onWorkerStop'
		));
		$serv->on('Request', array(
			$this,
			'onRequest'
		));
		$serv->on('Task', array(
			$this,
			'onTask'
		));
		$serv->on('Finish', array(
			$this,
			'onFinish'
		));
		$this->_serv = $serv;
	}

	/**
	 * @throws Exception
	 */
	public function onStart()
	{
		if ($this->_serv) {
			$this->_serv->start();
		} else {
			throw new Exception('swoole instance is null');
		}
	}

	public function onExit()
	{
		// TODO
	}

	/**
	 * 启动进程的时候调用此函数
	 * @param unknown $server
	 * @param unknown $worker_id
	 */
	public function onWorkerStart($server, $worker_id)
	{
		global $argv;
		if ($worker_id >= $server->setting['worker_num']) {
			swoole_set_process_name("php {$argv[0]} {$this->_swoole_cfg['swoole']['progname']} task worker");
		} else {
			swoole_set_process_name("php {$argv[0]} {$this->_swoole_cfg['swoole']['progname']} event worker");
		}
	}

	/**
	 * @param unknown $server
	 * @param unknown $worker_id
	 */
	public function onWorkerStop($server, $worker_id)
	{
		// TODO
	}

	/**
	 * @param $serv
	 * @param $task_id
	 * @param $from_id
	 * @param $data
	 */
	public function onTask($serv, $task_id, $from_id, $data)
	{
		try {
			lib\log\Logger::logInfo("pid: [" . posix_getpid() . "] task_id[" . $task_id . "] from_id: [" . $from_id . "] start");
			if ($data && is_array($data)) {
				if (isset($data['c_arr']) && isset($data['args'])) {
					$c_arr = $data['c_arr'];
					$args = $data['args'];
					$c = $this->_loadClass($c_arr);
					$c->workTaskProcess($args);
				}
			}
		} catch (Exception $e) {
			lib\log\Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
		}
		$this->_serv->finish("finished");
	}

	public function onFinish($serv, $task_id, $data)
	{
		lib\log\Logger::logInfo("tasking_num: [" . $serv->stats()['tasking_num'] . "] pid: [" . posix_getpid() . "] task_id: [" . $task_id . "] message: [" . $data . "]");
	}

	/**
	 * @param $request
	 * @param $response
	 * @throws Exception
	 */
	public function onRequest($request, $response)
	{
		/* 下面操作性能出现了比较大的下降 */
		try {
			$msg = "";
			$code = 0;
			$query = array();
			if (!isset($request->server['path_info'])) {
				throw new Exception('path_info of request->server is invaild');
			}
			$c_arr = $this->getCname($request->server['path_info']);
			if (!isset($request->server['query_string'])) {
				$query = array();
			} else {
				parse_str($request->server['query_string'], $query);
			}
			if ($this->_getRegisterType($c_arr['cname']) ==  'http') {
				$c = $this->_loadClass($c_arr);
				$ret = $c->httpTaskProcess($request);
				if ($this->_swoole_cfg['swoole']['gzip'] === true) {
					$response->gzip(1);
					$response->header('Content-Encoding', 'gzip');
				}
				/* default */
				$response->header('Content-Type', 'application/json');
				$response->header('Content-Type', 'text/html; charset=utf-8');
				/* 这里msg的类型发生了变化， 不好的编程风格 */
				is_string($ret) ? $msg .= $ret : $msg = $ret;
				$code = 1;
			} elseif ($this->_getRegisterType($c_arr['cname']) == 'process') {
				$this->_serv->task(array('c_arr' => $c_arr, 'args' => array('query' => $query)));
				$msg .= " pid: [" . posix_getpid() . "] finished";
				$code = 1;
			} else {
				$msg = $c_arr['cname'] . " not be register";
				$code = 0;
			}
			$response->end(json_encode(array('code' => $code, 'msg' => $msg), JSON_UNESCAPED_UNICODE));
		} catch (Exception $e) {
			$msg = "throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]\n";
			lib\log\Logger::logWarn($msg . "" . $e->getTraceAsString());
			$response->end(json_encode(array('code' => 0, 'msg' => $msg)));
		}
	}

	/**
	 * @param $class
	 * @return mixed
	 * @throws Exception
	 */
	private function _loadClass($c_arr)
	{
		//加载类文件
		if ($c_arr && is_array($c_arr) && isset($c_arr['path_info']) && isset($c_arr['cname'])) {
			$cname = $c_arr['cname'];
			$path_info = $c_arr['path_info'];
			if (!class_exists($cname, false)) {
				require_once($this->_swoole_cfg['workpath']['LOGIC'] . 'logic.interface.php');
				if (file_exists($this->_swoole_cfg['workpath']['LOGIC'] . strtolower($cname) . '.class.php')) {
					require_once($this->_swoole_cfg['workpath']['LOGIC'] . strtolower($cname) . '.class.php');
				} else if ($this->_swoole_cfg['workpath']['LOGIC'] . $path_info . strtolower($cname) . '.class.php') {
					//这里支持单个业务放入文件夹中
					require_once($this->_swoole_cfg['workpath']['LOGIC'] . $path_info . strtolower($cname) . '.class.php');
				} else {
					$info = strtolower($cname) . ' class not exsits';
					throw new Exception($info);
				}
			}
		} else {
			throw new Exception('cname or path_info is not set');
		}


		//加载配置文件,目前只支持单一配置文件
		if (!file_exists($this->_swoole_cfg['workpath']['CONFIG'] . "" . strtolower($cname) . '.config.php')) {
			$info = $this->_swoole_cfg['workpath']['CONFIG'] . strtolower($cname) . '.config.php not exsit';
			throw new Exception($info);
		}
		$bussness_cfg = require($this->_swoole_cfg['workpath']['CONFIG'] . strtolower($cname) . '.config.php');
		return new $cname($bussness_cfg);
	}

	/**
	 * @param $class
	 * @param $type
	 */
	private function _getRegisterType($class)
	{
		if (is_array($this->_swoole_cfg['business']) && isset($this->_swoole_cfg['business'][$class]))
		{
			$conf = $this->_swoole_cfg['business'][$class];
			if ($conf['online'] == true && in_array($conf['type'], array('process', 'http')))
			{
				return $conf['type'];
			}
		}
		return false;
	}

	/**
	 * @param $path_info
	 * @return array
	 */
	public function getCname($path_info)
	{
		preg_match('/(.*\/)(.*)/', $path_info, $match);
		if (is_array($match) && count($match) == 3) {
			return array('path_info' => $match[1], 'cname' => $match[2]);
		}
		throw new Exception('pathinfo is invaild');
	}
}
