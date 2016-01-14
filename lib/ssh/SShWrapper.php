<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/29
 * Time: 15:52
 */
namespace lib\ssh;

class SShWrapper
{
    private $_cmd;
    private $_ins;
    private $_cfg;

    public function __construct($cfg)
    {
        $this->_cfg = $cfg;
        $this->_init();
    }

    private function _init()
    {
        if (isset($this->_cfg['host']) && isset($this->_cfg['port']) && isset($this->_cfg['username']) && isset($this->_cfg['passwd'])) {
            $host = $this->_cfg['host'];
            $port = $this->_cfg['port'];
            $username = $this->_cfg['username'];
            $passwd = $this->_cfg['passwd'];
            $this->_ins = ssh2_connect($host, $port);
            ssh2_auth_password($this->_ins, $username, $passwd);
        }
        else {
            throw new \Exception('ssh cfg is invaild');
        }

    }

    public function __destruct()
    {

    }

    public function doExec($obj, $out_cb)
    {
        if (empty($this->_cmd)) {
            throw new \Exception('cmd is invaild');
        }
        $stream = ssh2_exec($this->_ins, $this->_cmd);
        stream_set_blocking($stream,true);
        call_user_func_array(array($obj, $out_cb), array($stream));
    }

    public function setCmd($cmd)
    {
        $this->_cmd = $cmd;
    }
}