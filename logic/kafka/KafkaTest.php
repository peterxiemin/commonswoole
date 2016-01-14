<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/16
 * Time: 11:10
 */

namespace logic\kafka;
use commonswoole\CommonFunc;
use lib\queue\KafkaConsumerWrapper;
use lib\queue\KafkaProducerWrapper;
use logic\kafka;


class KafkaTest
{

    private $_cfg;
    private $_rdkc;
    private $_rdkp;
    private $_partition;
    private $_hmg;

    /**
     * @param $config
     */
    public function __construct()
    {
        $this->_cfg = CommonFunc::getLogicConf(__CLASS__);
        $this->_rdkc = new KafkaConsumerWrapper($this->_cfg['kafka']);
        $this->_rdkp = new KafkaProducerWrapper($this->_cfg['kafka']);
        $this->_hmg = new HandleMsg();
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
        if (!isset($data) || !isset($data['query']) || !isset($data['query']['type'])) {
            throw new \Exception('workTaskProcess params invaild');
        }

        $type = trim($data['query']['type']);
        if ($type === "producer") {
            $this->_rdkp->setTopicName('xmtest0003');
            $this->_rdkp->setPartition(RD_KAFKA_PARTITION_UA);
            $this->_rdkp->producer();
        } else if ($type === "consumer") {
            if (!isset($data['query']['partition'])) {
                throw new \Exception('query partition invaild');
            }
            $this->_partition = $data['query']['partition'];
            $this->_rdkc->setTopicName('xmtest0003');
            $this->_rdkc->setPartition($this->_partition);
            $this->_rdkc->setOffset(RD_KAFKA_OFFSET_END);
            /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
            $this->_rdkc->consumeStart($this->_hmg, 'handleMsg');
        } else
            throw new \Exception("type is neither producer nor consuer");
    }
}