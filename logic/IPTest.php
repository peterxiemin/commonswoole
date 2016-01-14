<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/30
 * Time: 16:04
 */

namespace logic;
use commonswoole\CommonFunc;
use lib\iplib\IP;
use lib\log\Logger;

class IPTest implements LogicInterface {
    private $_cfg;
    public function __construct() {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
    }

    public function __destruct() {

    }

    public function workTaskProcess($data = null) {

    }

    public function httpTaskProcess($r = null) {
        $query_string = isset($r->server['query_string']) ? trim($r->server['query_string']) : null;
        if ($query_string) {
            $args = array();
            parse_str($query_string, $args);
            try {
                if (isset($args['ip'])) {
                    return IP::find($args['ip']);
                }
            } catch (\Exception $e) {
                Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
                throw new \Exception($e->getMessage());
            }
        }
        else {
            throw new \Exception('args is invaild');
        }
    }
}