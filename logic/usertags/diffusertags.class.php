<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/24
 * Time: 17:07
 */

require_once(WORKROOT . '/lib/db/nosql/mongo.php');
require_once(WORKROOT . '/logic/usertags/tagscores.php');

class DiffUserTags implements LogicInterface
{
    private $_cfg;
    private $_mgo;
    private $_tagscores;

    public function __construct($cfg)
    {
        $this->_cfg = $cfg;
        $this->_mgo = new MongoWrapper($cfg['mongo_usertags']);
        $this->_tagscores = new TagScores($cfg);
        $this->_mgo->setDbName('usertags');
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($data = null)
    {
        //TODO
        if (isset($data) && isset($data['query']) && isset($data['query']['date']) && isset($data['query']['os'])) {
            //ios_usertags20151130, a_usertags20151130
            $this->_mgo->setColctName($data['query']['os'] . '_user');
            $filename = $this->_cfg['raw_path'] . '/' . $data['query']['os'] . '_usertags' . $data['query']['date'];
            if (file_exists($filename)) {
                $file = fopen($filename, "r");
                while (!feof($file)) {
                    $line = trim(fgets($file));
                    $file_tags = explode(" ", $line);
                    $imei = $file_tags[0];
                    $newtags = $this->getNewTags($file_tags);
                    $oldtags = $this->getOldTags($imei);
                    //这里能够产生addarr和remarr
                    $this->diffTags($newtags, $oldtags);
                    array_shift($file_tags);
                    $this->setNewTags($imei, $newtags, $this->_tagscores->getRawTags($file_tags));
                }
            } else {
                throw new Exception("$filename is not exists");
            }
        } else {
            throw new Exception('args is invaild');
        }
    }

    public function setNewTags($imei, $newtags, $wholetags)
    {
        if ($imei && is_array($newtags) && is_array($wholetags)) {
            $this->_mgo->update(
                array(
                    'imei' => $imei
                ),
                array(
                    'normalTags' => $newtags,
                    'wholeTags' => $wholetags,
                    'updatedAt' => new MongoDate(time())
                ),
                array(
                    'upsert' => true
                ));
        } else {
            Logger::logInfo('setNewTags faild: '.json_encode($newtags));
        }
    }

    public function diffTags($newtags, $oldtags)
    {
        $addArr = array_diff($newtags, $oldtags); //新增列表
        $remArr = array_diff($oldtags, $newtags); //删除列表

        //这里将比对出来的标签，封装成数据接口，发送给熊峰
    }

    public function getOldTags($imei)
    {
        if ($imei) {
            $data = $this->_mgo->findOne(array('imei' => $imei));
            if ($data && is_array($data)) {
                return $data['normalTags'];
            }
        } else {
            Logger::logWarn('imie[' . $imei . '] is not in mongo_usertags');
        }
        return array();
    }

    public function getNewTags($tags)
    {
        $tags = array_slice($tags, 1, $this->_cfg['oneuser_tagslimit']);
        return $this->_tagscores->resortbyWeight($tags);
    }
}