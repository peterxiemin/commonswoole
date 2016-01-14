<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/30
 * Time: 14:16
 */
namespace logic;

use commonswoole\CommonFunc;
use lib\bloomfilter\BloomFilter;
use lib\log\Logger;

class BloomTest implements LogicInterface
{
    private $_bf;
    private $_cfg;

    public function __construct()
    {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_bf = new BloomFilter($this->_cfg['bloom']['bitsize'], $this->_cfg['bloom']['hashcount'], $this->_cfg['key_bloom'], $this->_cfg['redis']);
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {
        $query_string = isset($r->server['query_string']) ? trim($r->server['query_string']) : null;
        if ($query_string) {
            $args = array();
            parse_str($query_string, $args);
            try {
                if ($this->_bf->has($args['q'])) {
                    return false;
                } else {
                    $this->_bf->add($args['q']);
                    return true;
                }
            } catch (\Exception $e) {
                Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception('args is invaild');
        }
    }

    public function workTaskProcess($data = null)
    {

    }
}