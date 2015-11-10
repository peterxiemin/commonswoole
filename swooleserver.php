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
            // 'reactor_num' => 1, // reactor thread num
            // 'worker_num' => 1, // worker process num
            // 'backlog' => 128, // listen backlog
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
        // TODO
    }

    /**
     *
     * @param unknown $server
     * @param unknown $worker_id
     */
    public function onWorkerStop($server, $worker_id)
    {
        echo "onWorkerStop\n";
    }

    /**
     *
     * @param unknown $request
     * @param unknown $response
     */
    public function onRequest($request, $response)
    {

//        var_dump($request->server);
//        $response->end('hello world!');
//        exit;

        $class = trim($request->server['path_info'], '//');
        if (! in_array($class, $this->swoole_cfg['business'])) {
            $info = 'your path_info '.$class.' not register';
            $response->end($info);
//            echo $info.PHP_EOL;
            return;
        }

        // 如果没有被定义
        if (! class_exists($class, false)) {
            if (! file_exists('./logic/'.strtolower($class).'.class.php')) {
                $info = $class.'class not exsits';
                $response->end($info);
//                echo $info.PHP_EOL;
                return;
            };
            require_once('./logic/logic.interface.php');
            require_once('./logic/'.strtolower($class).'.class.php');
        }
//        $class = ucfirst($class);
        $c = new $class;

        $json = $c->taskProcess($request);


        if ($this->swoole_cfg['swoole']['gzip'] === true) {
            $response->gzip(1);
            $response->header('Content-Encoding', 'gzip');
        }

        /* default */
        $response->header('Content-Type', 'application/json');
        $response->end($json);
    }
}
