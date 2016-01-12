<?php

class BloomFilter
{

    private $_connection;
    private $_redis;
    private $_size;
    private $_hashCount;
    private $_key;

    const KEY_BLOOM = 'imcpsync001';


    public function __construct($size, $hash_count, $redis_cfg)
    {
        $this->_size = $size;
        $this->_hashCount = $hash_count;
        $this->_connection = array('host' => $redis_cfg['host'], 'port' => $redis_cfg['port']);
        $this->initRedis();
    }

    public function add($item)
    {
        $index = 0;
        $pipe = $this->_redis->pipeline();
        while ($index < $this->_hashCount) {
            $crc = $this->hash($item, $index);
            $pipe->setbit(self::KEY_BLOOM, $crc, 1);
            $index++;
        }
        $pipe->execute();
    }


    public function has($item)
    {
        $index = 0;
        $pipe = $this->_redis->pipeline();
        while ($index < $this->_hashCount) {
            $crc = $this->hash($item, $index);
            $pipe->getbit(self::KEY_BLOOM, $crc);
            $index++;
        }
        $result = $pipe->execute();
        return !in_array(0, $result);
    }


    private function hash($item, $index)
    {
        return abs(crc32(md5('m' . $index . $item))) % $this->_size;
    }


    private function initRedis()
    {
        $this->_redis = new Predis\Client($this->_connection);
    }
}

require_once __DIR__ . '/Predis.php';