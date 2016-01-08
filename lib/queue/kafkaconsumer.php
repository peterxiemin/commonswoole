<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 19:05
 * Description: 对kafka的consumer的封装
 * 实现了基本的消费功能： 1）制定partion和offset进行消费， 大需要手动修改本地的offset文件
 */
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
        /**
         * 初始化kafka类,需要安装rdkafka.so扩展
         */
        $this->_rk = new RdKafka\Consumer();
        $this->_topicConf = new RdKafka\TopicConf();
        $this->initConfig($kfk_cfg);
    }

    /**
     * @param $kfk_cfg
     */
    public function initConfig($kfk_cfg)
    {
        /* LOG_DEBUG 加入到配置文件中 */
        $this->_rk->setLogLevel(-1);
        $this->_rk->addBrokers(implode(',', $kfk_cfg['kafka_brokers']));

        /* 这里的配置也可以进行设置 */
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
        throw new Exception('topicname cat not be null');
    }

    /**
     * 这里使用了一个类回调函数， 这样做很不方便， 没有对开发者进行约束
     * call_user_func_array函数会出现crash， 后面考虑优化
     * @param $obj
     * @throws Exception
     */
    public function consumeStart($obj, $cb)
    {
        $topic = $this->_getTopic($this->_topicname);
        /* RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED */
        try {
            $topic->consumeStart($this->_partition, $this->_offset);
            $counter = 60; //这里休息60s， 如果队列中还没有消息可读，就退出了
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
                        throw new Exception('no callback in obj');
                    }
                }
            }
        }
        catch(RdKafka\Exception $e) {
            throw new Exception('kafka error: '.$e->getMessage());
        }

    }

    /**
     * 打印kafka的相关信息，这个功能很有必要，本函数没有详细罗列查询方法，详见
     * https://github.com/arnaud-lb/php-rdkafka
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