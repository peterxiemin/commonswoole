<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:05
 * Description: 对kafka的consumer的封装
 * 实现了基本的消费功能： 1）制定partion和offset进行消费， 大需要手动修改本地的offset文件
 */

namespace lib\queue;

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
        /**
         * 初始化kafka类,需要安装rdkafka.so扩展
         */
        $this->_rkp = new RdKafka\Producer();
        $this->initConfig($kfk_cfg);
    }

    /**
     * @param $kfk_cfg
     */
    public function initConfig($kfk_cfg)
    {
        /* LOG_DEBUG 加入到配置文件中 */
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
     * @throws Exception
     */
    private function _getTopic($topicname = null)
    {
        if ($topicname) {
            return $this->_rkp->newTopic($topicname, $this->_topicConf);
        }

        if ($this->_topicname) {
            return $this->_rkp->newTopic($this->_topicname);
        }
        throw new Exception('topicname cat not be null');
    }

    /**
     * @param empty
     * @return empty
     * 设定好topic和partion，则可以进行生产
     */
    public function producer() {
        $topic = $this->_getTopic();
        $topic->produce($this->_partition, 0, "Message payload");
    }
}