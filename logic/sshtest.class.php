<?php

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 2015/11/29
 * Time: 15:49
 */

require_once(WORKROOT . '/lib/ssh/ssh.php');
require_once(WORKROOT . '/lib/db/nosql/Sharding/Data_Sharding.class.php');
require_once(WORKROOT . '/lib/db/nosql/Sharding/Storage_Route.class.php');
require_once(WORKROOT . '/lib/db/nosql/Sharding/Table_Hash.class.php');
require_once(WORKROOT . '/lib/db/nosql/mongosharding.php');
require_once(WORKROOT . '/lib/db/nosql/memcache.php');
require_once(WORKROOT . '/lib/db/nosql/php-bloomfilter/BloomFilter.php');

class HandleExec
{
    private $_storgeRouteInstance = NULL;
    private $_Storage_Mongo = NULL;
    private $_config = NULL;
    private $_Memc = NULL;
    private $_BF = NULL;

    public function __construct($config)
    {
        $this->_config = $config;
        $this->init();
    }

    public function init()
    {
        $this->_storgeRouteInstance = new storgeRoute($this->_config['imcp']['dbnode']['syq_sjs']);
        $this->_Storage_Mongo = new MongoWrapper2 ();
        $this->_Storage_Mongo->setDb('imcp');
        $this->_Storage_Mongo->setServerConfig($this->_storgeRouteInstance);
        $this->_Memc = new MemcacheWrapper($this->_config['memcache']);
        $this->_BF = new BloomFilter($this->_config['bloom']['bitsize'], $this->_config['bloom']['hashcount'], $this->_config['redis']);
    }

    public function __destruct()
    {

    }

    private function inBloom($key)
    {
        try {
            if ($this->_BF->has($key)) {
                return true;
            } else {
                $this->_BF->add($key);
                return false;
            }
        } catch (Exception $e) {
            Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
            throw new Exception($e->getMessage());
        }
    }

    public function handleExecOutRet($stream)
    {
        while (($line = fgets($stream, 4096)) !== false) {
            try {
                $arr = explode("\t", $line);
                if (is_array($arr) && count($arr) == 5) {
                    if ($arr[3] == "type:document" || $arr[3] == "type:documentslave") {
                        $key = substr($arr[2], 4);
                        $type = substr($arr[3], 5);
                        if ($this->inBloom($key)) {
//                            Logger::logInfo('in Bloom');
                            continue;
                        }
                        $val = $this->_Memc->get($key);
                        if ($val) {
                            $this->_handleEachLine(array('key' => $key, 'val' => $val, 'type' => $type));
                        }
                    }
                }
            } catch (Exception $e) {
                Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
            }
        }
    }

    private function _handleEachLine($data)
    {
        $key = trim($data ['key']);
        $val = trim($data ['val']);
        $type = trim($data ['type']);
        try {
            $c = isset ($this->_config['imcp']['collectionConfig'] [$type]) ? $this->_config['imcp']['collectionConfig'] [$type] : null;
            if ($key && $val && $type && is_array($c)) {
                $collection = $c ['params'] ['collection'];
                $hashTable = (isset ($c ['hashTable']) && $c ['hashTable']) ? true : false;
                $intId = (isset ($c ['intID']) && $c ['intID']) ? true : false;
                $keyPrefix = (isset ($c ['keyPrefix']) && $c ['keyPrefix']) ? $c ['keyPrefix'] : "";
                // 存值
                $result = $this->_Storage_Mongo->put($key, $val, $collection, $hashTable, $intId, $keyPrefix);
                if (!$result) {
                    throw new Exception('key: ' . $key . ' mongo put failed');
                }
            } else {
                throw new Exception('key or type or collection is invaild');
            }
        } catch (Exception $e) {
            throw new Exception("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]\n");
        }
    }
}

class SShTest implements LogicInterface
{
    private $_cfg = null;
    private $_hec = null;

    public function __construct($cfg)
    {
        $this->_cfg = $cfg['ssh'];
        $this->_hec = new HandleExec($cfg['hec']);
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($data = null)
    {
        if (isset($data) && isset($data['query']) && isset($data['query']['ip'])) {
            /* 日期格式外部curl严格控制， 如果不合法， 将无法查到日志 */
            if (isset($data['query']['ym']) && isset($data['query']['day']) && isset($data['query']['hm']) ) {
                $remote_ip = $data['query']['ip'];
                $this->_cfg['host'] = $remote_ip;
                $remote_ins = new SShWrapper($this->_cfg);
                $ym = $data['query']['ym'];
                $day = $data['query']['day'];
                $hm = $data['query']['hm'];
                $remote_ins->setCmd('zcat /data/logs/imcpagent/' . $ym . '/' . $day . '/imcpagent.log_' . "$ym$day$hm" . '.gz|grep \'type:document\'');
                $remote_ins->doExec($this->_hec, 'handleExecOutRet');
            } else {
                throw new Exception('ymdhis is invaild');
            }
        } else {
            throw new Exception('query ip is invaild');
        }
    }
}
