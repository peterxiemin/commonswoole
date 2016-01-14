<?php

namespace lib\bloomfilter;
use lib\db\Predis\Client;

class BloomFilter
{

    private $_connection;
    private $_redis;
    private $_size;
    private $_hashCount;
    private $_key_bloom;


    public function __construct($size, $hash_count, $KEY_BLOOM, $redis_cfg)
    {
        $this->_size = $size;
        $this->_hashCount = $hash_count;
        $this->_key_bloom = $KEY_BLOOM;
        $this->_connection = array('host' => $redis_cfg['host'], 'port' => $redis_cfg['port']);
        $this->initRedis();
    }

    public function add($item)
    {
        $index = 0;
        $pipe = $this->_redis->pipeline();
        while ($index < $this->_hashCount) {
            $crc = $this->hash($item, $index);
            $pipe->setbit($this->_key_bloom, $crc, 1);
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
            $pipe->getbit($this->_key_bloom, $crc);
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
        $this->_redis = new Client($this->_connection);
    }
}
