<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 */

require_once(WORKROOT . '/lib/queue/kafkaconsumer.php');
require_once(WORKROOT . '/lib/db/nosql/mongo.php');

class MessageQueue implements LogicInterface
{
    private $_config;
    private $_kfkc;
    private $_hdmsg;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_hdmsg = new HandleMsg($config);
        $this->_kfkc = new KafkaConsumerWrapper($this->_config['kafka']);
    }

    /**
     * @param null $r
     */
    public function httpTaskProcess($r = null)
    {

    }

    /**
     * @param null $data
     * @throws Exception
     */
    public function workTaskProcess($data = null)
    {
        if (is_array($data) && is_array($data['query']) && isset($data['query']['patition']) && isset($data['query']['date'])) {
            $this->_collection_surfix = $data['query']['date'];
            $this->_kfkc->setTopicName('userdata_queue');
            $this->_kfkc->setPartition($data['query']['patition']);
            $this->_kfkc->setOffset(RD_KAFKA_OFFSET_STORED);
            /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
            $this->_kfkc->consumeStart($this->_hdmsg, 'handMessage');
        } else {
            throw new Exception('args not vaild');
        }
    }


}

/**
 * Class HandleMsg
 */
class HandleMsg
{
    private $_mng;
    private $_config;
    private $_userbase_cfg;
    private $_collection_surfix;

    /**
     * @param $cfg
     */
    public function __construct($cfg)
    {
        $this->_config = $cfg;
        $this->_mng = new MongoWrapper($this->_config['mongo']);
        $this->_userbase_cfg = $this->_config['userinfo'];
    }

    /**
     * @param $msg
     */
    public function handMessage($msg)
    {
        $userbase = array();
        $useraction = array();
        $data = explode("\t", $msg);
        if (!$this->_checkMsgData($data)) return;
        for ($i = 0; $i < count($data); $i++) {
            $k = $this->_userbase_cfg['alldata'][$i];
            $v = $data[$i];
            // dau            //if ($k == 'is_dau' && $v != 1) return;
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
        // userbase;
        if (count($userbase))
            $this->_mng->batchUpdate($userbase);

        $this->_collection_surfix = isset($useraction['date']) ? str_replace('-', '_', $useraction['date']) : $this->_collection_surfix;
        $this->_mng->setCollection('useraction_' . $this->_collection_surfix);

        if (count($useraction))
            $this->_mng->batchUpdate($useraction);
    }

    /**
     * @param $data
     * @return bool
     */
    private function _checkMsgData($data)
    {
        if (count($data) !== 31) {
            Logger::logWarn("msg formate invaild, msg: [" . implode("\t", $data) . "] count: " . count($data));
            return false;
        } else {
            return true;
        }
    }

}