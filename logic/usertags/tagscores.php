<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/25
 * Time: 8:56
 */

require_once(WORKROOT . '/lib/db/nosql/redis.php');

class TagScores
{
    private $_cfg;
    // 中间变量， 完成任务后，需要清理
    private $_tsmap;
    private $_out;
    private $_rawtags;


    public function __construct($cfg)
    {
        $this->_cfg = $cfg;
        $this->_redis = new RedisWrapper($cfg['redis']);
    }

    public function setTsMap($tagdict)
    {
        foreach ($tagdict as $chname => $data) {
            if (in_array($data['alias'], $this->_rawtags)) {
                $this->_tsmap[$data['alias']]['manual_score'] = $data['manual_score'];
            }
        }
    }

    public function setRawTags($rawtags) {
        foreach ($rawtags as $tag) {
            if (array_key_exists($tag, $this->_cfg[tagDict]) && isset($this->_cfg[tagDict][$tag]['alias'])) {
                $this->_rawtags[] = $this->_cfg[tagDict][$tag]['alias'];
            }
        }
    }

    public function getRawTags($rawtags) {
        $new_tags = array();
        foreach ($rawtags as $tag) {
            if (array_key_exists($tag, $this->_cfg[tagDict]) && isset($this->_cfg[tagDict][$tag]['alias'])) {
                $new_tags[] = $this->_cfg[tagDict][$tag]['alias'];
            }
        }
        return $new_tags;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function resortbyWeight($rawtags)
    {
        if (is_array($rawtags)) {
            $this->setRawTags($rawtags);
            $this->setTsMap($this->_cfg['tagDict']);
            for ($i = 0, $j = count($this->_rawtags); $i < count($this->_rawtags) && $j > 0; $i++, $j--) {
                $this->setOrderScore($this->_rawtags[$i], $j);
                $this->setManualScore($this->_rawtags[$i]);
                $this->setFeedBackScore($this->_rawtags[$i]);
                $this->setWeightAvgScore($this->_rawtags[$i]);
            }
            arsort($this->_out);
            $ret = array_keys($this->_out);
            $this->cleanVar();
            return $ret;
        }
    }

    public function setWeightAvgScore($tag)
    {
        if (isset($this->_tsmap[$tag])) {
            $this->_out[$tag] = ($this->_tsmap[$tag]['order_score'] * $this->_cfg['factor_weight']['order_score'] +
                    $this->_tsmap[$tag]['manual_score'] * $this->_cfg['factor_weight']['manual_score'] +
                    $this->_tsmap[$tag]['feedback_score'] * $this->_cfg['factor_weight']['feedback_score']) /
                ($this->_cfg['factor_weight']['feedback_score'] + $this->_cfg['factor_weight']['feedback_score'] + $this->_cfg['factor_weight']['feedback_score']);
        }
    }

    public function cleanVar() {
        $this->emptyRawTags();
        $this->emptyTsMap();
        $this->emptyOut();
    }

    public function emptyOut() {
        unset($this->_out);
    }

    public function emptyTsMap()
    {
        unset($this->_tsmap);
    }

    public function emptyRawTags() {
        unset($this->_rawtags);
    }

    public function   setOrderScore($tag, $order)
    {
        if (isset($this->_tsmap[$tag])) {
            $this->_tsmap[$tag]['order_score'] = $order * (100 / count($this->_rawtags));
        }
    }

    public function setManualScore($tag)
    {
        //这部分已经预先设置好了
    }

    public function setFeedBackScore($tag)
    {
        //这里还没有开发， 不过可以先把接口准备好
        if (isset($this->_tsmap[$tag])) {
            $this->_tsmap[$tag]['feedback_score'] = 0;
        }
    }
}