<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:05
 * Description: ��kafka��consumer�ķ�װ
 * ʵ���˻��������ѹ��ܣ� 1���ƶ�partion��offset�������ѣ� ����Ҫ�ֶ��޸ı��ص�offset�ļ�
 */

namespace lib\queue;
use lib\log\Logger;
class KafkaProducerWrapper
{
    private $_rkp;
    private $_topicConf = null;
    private $_partition = 0;

    /**
     * @param $kfk_cfg
     */
    public function __construct($kfk_cfg)
    {
        $this->_rkp = new \RdKafka\Producer();
        $this->initConfig($kfk_cfg);
    }

    /**
     * @param $kfk_cfg
     */
    public function initConfig($kfk_cfg)
    {
        $this->_rkp->setLogLevel(-1);
        $this->_rkp->addBrokers(implode(',', $kfk_cfg['kafka_brokers']));
    }

    /**
     * @param $topicname
     */
    public function setTopicName($topicname)
    {
        $this->_topicname = $topicname;
    }

    /**
     * @param $partition
     */
    public function setPartition($partition)
    {
        $this->_partition = $partition;
    }

    /**
     * @param null $topicname
     * @return mixed
     * @throws \Exception
     */
    private function _getTopic($topicname = null)
    {
        if ($topicname) {
            return $this->_rkp->newTopic($topicname, $this->_topicConf);
        }

        if ($this->_topicname) {
            return $this->_rkp->newTopic($this->_topicname);
        }
        throw new \Exception('topicname cat not be null');
    }


    /**
     * @throws \Exception
     */
    public function producer() {
        $topic = $this->_getTopic();
        $topic->produce($this->_partition, 0, "Message payload");
    }
}