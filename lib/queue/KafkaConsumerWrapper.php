<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:05
 * Description:
 *
 */
namespace lib\queue;
use lib\log\Logger;
class KafkaConsumerWrapper
{
    private $_rk;
    private $_topicname;
    private $_topicConf;
    private $_offset;
    private $_partition = 0;
    const CONSUMER_CB = 'handMessage';

    /**
     * @param $kfk_cfg
     */
    public function __construct($kfk_cfg)
    {
        $this->_rk = new \RdKafka\Consumer();
        $this->_topicConf = new \RdKafka\TopicConf();
        $this->initConfig($kfk_cfg);
    }

    /**
     * @param $kfk_cfg
     */
    public function initConfig($kfk_cfg)
    {

        $this->_rk->setLogLevel(-1);
        $this->_rk->addBrokers(implode(',', $kfk_cfg['kafka_brokers']));


        $this->_topicConf->set("auto.commit.interval.ms", 1e3);
        $this->_topicConf->set("offset.store.sync.interval.ms", 60e3);
        $this->_topicConf->set("offset.store.path", WORKROOT);
    }

    /**
     * @param $topicname
     */
    public function setTopicName($topicname)
    {
        $this->_topicname = $topicname;
    }

    /**
     * @param $offset
     */
    public function setOffset($offset)
    {
        $this->_offset = $offset;
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
     * @throws Exception
     */
    private function _getTopic($topicname = null)
    {
        if ($topicname) {
            return $this->_rk->newTopic($topicname, $this->_topicConf);
        }

        if ($this->_topicname) {
            return $this->_rk->newTopic($this->_topicname, $this->_topicConf);
        }
        throw new \Exception('topicname cat not be null');
    }


    public function consumeStart($obj, $cb)
    {
        $topic = $this->_getTopic($this->_topicname);
        /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
        try {
            $topic->consumeStart($this->_partition, $this->_offset);
            $counter = 60;
            while (true && $counter > 0) {
                $msg = $topic->consume($this->_partition, 1000);
                if ($msg == null || (isset($msg) && $msg->err)) {
                    Logger::logWarn('sleep 1s msg error: ');
                    $counter --;
                    sleep(1);
                    continue;
                } else {
                    if (method_exists($obj, $cb)) {
                        call_user_func_array(array($obj, $cb), array($msg));
                    } else {
                        throw new \Exception('no callback in obj');
                    }
                }
            }
        }
        catch(\RdKafka\Exception $e) {
            throw new \Exception('kafka error: '.$e->getMessage());
        }

    }

    public function printMetaData()
    {
        $metadata = $this->_kfk_consmr->metadata(false, $this->getTopic('topic2'), 1000);
        $topics = $metadata->getTopics();
        foreach ($topics as $topic) {
            var_dump($topic->getPartitions());
        }
    }
}