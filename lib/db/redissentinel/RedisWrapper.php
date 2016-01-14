<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/18
 * Time: 13:44
 */


namespace lib\db\redissentinel;

class RedisWrapper
{
    private $_cluster;
    private $_cfg;

    public function __construct($cfg)
    {
        $this->_cfg = $cfg;
        $this->init();
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    private function init()
    {
        /**
         * 如果redis使用集群并且加入sentinel做灾备切换，这里可以实现，使用下面的类， 可以实现自动切换，但效率会受到比较大的影响
         */
        $sentinel = new Credis_Sentinel(new Credis_Client($this->_cfg['sentinel_host'], $this->_cfg['sentinel_port']));
        $this->_cluster = $sentinel->getCluster($this->_cfg['master_name'], $this->_cfg['password']);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire
     */
    public function set($key, $value, $expire = 0)
    {
        $this->_cluster->set($key, $value, $expire);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->_cluster->get($key);
    }
}