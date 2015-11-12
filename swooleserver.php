<?php

class SwooleServer
{
    private $serv;
    private $swoole_cfg;

    public function __construct()
    {
        $this->swoole_cfg = require('./conf/swoole.config.php');
        $this->configCheck();
    }

    public function __destruct()
    {
    }

    public function configCheck()
    {
        // 这里可以对配置进行严格检查
    }

    public function onInit()
    {
        $serv = new swoole_http_server ($this->swoole_cfg['swoole']['swoole_host'], $this->swoole_cfg['swoole']['swoole_port']);
        /**
         * 这里对swoole进程进行设置
         */
        $serv->set(array(
//            'reactor_num' => $this->swoole_cfg['swoole']['reactor_num'], // reactor thread num
//            'backlog' => $this->swoole_cfg['swoole']['backlog'], // listen backlog
            'worker_num' => $this->swoole_cfg['swoole']['worker_num'],
            'max_request' => $this->swoole_cfg['swoole']['max_request'],
            // 'dispatch_mode' => 1
            'log_file' => $this->swoole_cfg['swoole']['log_file'],
            'daemonize' => $this->swoole_cfg['swoole']['daemonize']
        ));
        $serv->on('WorkerStart', array(
            $this,
            onWorkerStart
        ));
        $serv->on('WorkerStop', array(
            $this,
            onWorkerStop
        ));
        $serv->on('Request', array(
            $this,
            onRequest
        ));
        $this->serv = $serv;
    }

    /**
     * @throws Exception
     */
    public function onStart()
    {
        if ($this->serv) {
            $this->serv->start();
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
        if ($this->swoole_cfg['business'] && is_array($this->swoole_cfg['business'])) {
            foreach ($this->swoole_cfg['business'] as $class => $type) {
                if ($type == 'process') {
                    $c = $this->_loadClass($class);
                    echo 'worker_id: '.$worker_id."\n";
                    $c->workTaskProcess($worker_id % $this->swoole_cfg['swoole']['worker_num']);
                    echo $class . " process finished\n";
                }
            }
        }
    }

    /**
     *
     * @param unknown $server
     * @param unknown $worker_id
     */
    public function onWorkerStop($server, $worker_id)
    {
        // TODO
    }

    /**
     *
     * @param unknown $request
     * @param unknown $response
     */
    public function onRequest($request, $response)
    {
        $class = trim($request->server['path_info'], '//');

        if ($this->swoole_cfg['business'] && is_array($this->swoole_cfg['business'])) {
            $flag = false;
            foreach ($this->swoole_cfg['business'] as $busness => $type) {
                if ($busness == $class && $type == 'http') {
                    $flag = true;
                    continue;
                }
            }
            if ($flag == false) {
                $info = 'your path_info ' . $class . ' not register';
                throw new Exception($info);
            }
        }

        $c = $this->_loadClass($class);
        $json = $c->httpTaskProcess($request);


        if ($this->swoole_cfg['swoole']['gzip'] === true) {
            $response->gzip(1);
            $response->header('Content-Encoding', 'gzip');
        }

        /* default */
        $response->header('Content-Type', 'application/json');
        $response->end($json);
    }

    private function _loadClass($class)
    {
        // 如果没有被定义
        if (!class_exists($class, false)) {
            if (!file_exists('./logic/' . strtolower($class) . '.class.php')) {
                $info = strtolower($class) . 'class not exsits';
                throw new Exception($info);
            };
            require_once('./logic/logic.interface.php');
            require_once('./logic/' . strtolower($class) . '.class.php');
        }

        if (!file_exists('./conf/' . strtolower($class) . '.config.php')) {
            $info = './conf/' . strtolower($class) . '.config.php not exsit';
            throw new Exception($info);
        }
        $bussness_cfg = require('./conf/' . strtolower($class) . '.config.php');
        return new $class($bussness_cfg);
    }

    private function hasRegister($class, $type)
    {
        // TODO
    }
}
