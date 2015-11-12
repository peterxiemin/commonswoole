<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 */

require_once('./lib/queue/kafka.php');
require_once('./lib/db/nosql/mongo.php');

class MessageQueue implements LogicInterface
{

    private $_config;
    private $_rdkfk;
    private $_mng;
    private $_userbase_cfg;
    private $_userbase_batch = array();
    private $_useraction_batch = array();

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_rdkfk = new KafkaWrapper($this->_config['kafka']);
        $this->_mng = new MongoWrapper($this->_config['mongo']);
        $this->_userbase_cfg = $this->_config['userinfo'];
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($id = 0)
    {
        $this->_rdkfk->setTopicName('userinfo1');
        $this->_rdkfk->setPartition($id);
        $this->_rdkfk->setOffset(RD_KAFKA_OFFSET_STORED);
        /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
        $this->_rdkfk->consumeStart($this);
    }

    public function callBack($msg)
    {
        $userbase = array();
        $useraction = array();
        $data = explode("\t", $msg);
        if (!$this->_checkMsgData($data)) {
            return;
        }
        for ($i = 0; $i < count($data); $i++) {
            $k = $this->_userbase_cfg['alldata'][$i];
            $v = $data[$i];
            if (array_key_exists($k, $this->_userbase_cfg['userbase'])) {
                if ($v && $v != "0" && $v != "null") {
                    $userbase[$k] = $v;
                }

            }
            if (array_key_exists($k, $this->_userbase_cfg['useraction'])) {
                if ($v && $v != "0" && $v != "null") {
                    $useraction[$k] = $v;
                }
            }
        }

        $this->_mng->setDb('usercenter');
        $this->_mng->setCollection('userbase');
        // 处理userbase;
        $this->_mng->batchUpdate($userbase);

        $today = Date('Y_m_d');
        $this->_mng->setCollection('useraction_' . $today);
        $this->_mng->batchUpdate($useraction);
    }

    /**
     * 这里对msg进行严格校验，不合法的，则进行删除
     */
    private function _checkMsgData($data)
    {
        if (count($data) !== 31) {
            echo "msg formate invaild, msg: [" . implode("\t", $data) . "] count: " . count($data) . "\n";
            return false;
        } else {
            return true;
        }
    }
}