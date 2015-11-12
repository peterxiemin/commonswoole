<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:05
 */
class KafkaWrapper
{
    private $_rk;
    private $_topicname;
//    private $_kfk_prodct;
    private $_kfk;
//    private $_topic;
    private $_topicConf;
    private $_offset;
    private $_partition;

    const CONSUMER_CB = 'callBack';

    public function __construct($kfk_cfg)
    {
//        $this->_kfk_prodct = new RdKafka\Producer();
        $this->_rk = new RdKafka\Consumer();
        $this->_topicConf = new RdKafka\TopicConf();

        $this->initConfig($kfk_cfg);
    }

    public function __destruct()
    {

    }

    public function initConfig($kfk_cfg)
    {
        /* LOG_DEBUG 加入到配置文件中 */
        $this->_rk->setLogLevel($kfk_cfg['log_level']);
        $this->_rk->addBrokers(implode(',', $kfk_cfg['kafka_brokers']));

        /* 这里的配置也可以进行设置 */
        $this->_topicConf->set("auto.commit.interval.ms", 1e3);
        $this->_topicConf->set("offset.store.sync.interval.ms", 60e3);
    }

    public function setTopicName($topicname)
    {
        $this->_topicname = $topicname;
    }

    public function setOffset($offset)
    {
        $this->_offset = $offset;
    }

    public function setPartition($partition)
    {
        $this->_partition = $partition;
    }
//
//    public function setTopicConf() {
//
//    }

    private function _getTopic($topicname = null)
    {
        if ($topicname) {
            return $this->_rk->newTopic($topicname, $this->_topicConf);
        }

        if ($this->_topicname) {
            return $this->_rk->newTopic($this->_topicname, $this->_topicConf);
        }
        throw new Exception('topicname cat not be null');
    }

    public function consumeStart($obj, $partition = 0, $timeout = 1000)
    {
        $topic = $this->_getTopic($this->_topicname);
        /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
        $topic->consumeStart($this->_partition ? $this->_partition : 0, $this->_offset);

        while (true) {
            // The first argument is the partition (again).
            // The second argument is the timeout.
            $msg = $topic->consume($this->_partition ? $this->_partition : 0, $timeout);
            if ($msg->err) {
                echo $msg->errstr(), "\n";
                break;
            } else {
//                echo $msg->offset . '  pid: ' . posix_getpid() . ' ' . "\n";
                if (method_exists($obj, self::CONSUMER_CB)) {
//                    call_user_func_array(array($obj, 'callBack'), array($msg->payload));
                    $obj->callBack($msg->payload);
                }
                else {
                    throw new Exception('no callback in obj');
                }
            }
        }
    }

    /**
     * 获取kafka元信息
     * @param bool|false $alltopic
     * @param string $whichtopic
     * @param $timeout
     * @return mixed
     */
    public function printMetaData()
    {
        $metadata = $this->_kfk_consmr->metadata(false, $this->getTopic('topic2'), 1000);
        $topics = $metadata->getTopics();
        foreach ($topics as $topic) {
            var_dump($topic->getPartitions());
        }
    }
}