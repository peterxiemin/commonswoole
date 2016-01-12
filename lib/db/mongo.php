<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 20:08
 * Description: mongo类封装
 */
class MongoWrapper
{
    private $_mgo;
    private $_db;
    private $_colct;
    private $_cfg;
    private $_batch_buff = array();
    const MAXBATCHNUM = 500;
    const MAXRETRIES = 3;

    /**
     * @param $cfg
     */

    public function __construct($cfg = array())
    {
        if ($cfg && is_array($cfg)) {
            $this->_cfg = $cfg;
            $this->_mgo = new MongoClient($this->_cfg['mongourl'], $this->_cfg['option']);
        } else {
            throw new Exception('cfg of mongo construct is invaild');
        }
    }

    /**
     * @param $db
     * @throws Exception
     */
    public function setDbName($db = null)
    {
        if ($db)
            $this->_db = $db;
        else {
            throw new Exception('db name cannot be null');
        }
    }

    /**
     * @param $collection
     * @throws Exception
     */
    public function setColctName($colct = null)
    {
        if ($colct)
            $this->_colct = $colct;
        else {
            throw new Exception('collection name cannot be null');
        }
    }

    /**
     * @return mixed
     */
    public function getDbName()
    {
        if ($this->_db) {
            return $this->_db;
        } else {
            throw new Exception('db name cannot be null');
        }
    }

    /**
     * @return mixed
     */
    public function getColctName()
    {
        if ($this->_colct) {
            return $this->_colct;
        } else {
            throw new Exception('collection name cannot be null');
        }
    }

    /**
     * 如果发现异常，进行重连
     * @return MongoCollection
     */
    private function _getColctObj()
    {
        try {
            if ($this->_db && $this->_colct) {
                return $this->_mgo->selectDB($this->_db)->selectCollection($this->_colct);
            } else {
                throw new Exception('db or collection cannot be null');
            }
        } catch (MongoException $e) {
            $counts = 0;
            for ($counts = 1; $counts <= self::MAXRETRIES; $counts++) {
                try {
                    $this->_mgo = new MongoClient($this->_cfg['mongourl'], $this->_cfg['option']);
                } catch (\Exception $e) {
                    Logger::logInfo('reconnect mongo server:[' . $this->_cfg['mongourl'] . ']');
                    continue;
                }
                return $this->_mgo->selectDB($this->_db)->selectCollection($this->_colct);
            }
        }
    }

    /**
     * @param $data
     */
    public function save($data = array())
    {
        if ($data && is_array($data)) {
            $c = $this->_getColctObj();
            try {
                $c->save($data);
                return true;
            } catch (MongoException $e) {
                Logger::logInfo('mongo in lib save failed, message [' . $e->getMessage() . '] code: [' . $e->getCode() . ']');
                return false;
            }
        } else {
            throw new Exception('data of is invaild');
        }
    }

    public function findOne($where = array()) {
        if (is_array($where)) {
            $c = $this->_getColctObj();
            try {
                return $c->findOne($where);
            }
            catch(MongoException $e) {
                Logger::logInfo('mongo in lib findOne failed, message [' . $e->getMessage() . '] code: [' . $e->getCode() . ']');
                return false;
            }
        }
        else {
            Logger::logWarn('findOne where is not array');
        }
    }

    /**
     * @param array $where
     * @param array $filter
     * @param null $obj
     * @param null $call_back
     * @return bool
     * @throws Exception
     */
    public function cmdByCurSor($where = array(), $filter = array(), $obj = NULL, $call_back = NULL)
    {
        if (is_array($where)) {
            $c = $this->_getColctObj();
            try {
                $cursor = $c->find($where);
                foreach ($filter as $opt => $val) {
                    switch ($opt) {
                        case "limit":
                            is_numeric($val) ? $cursor->limit($val) : NULL;
                            break;
                        default:
                            Logger::logInfo("opt is invaild");
                    }
                }
                foreach ($cursor as $doc) {
                    if (method_exists($obj, $call_back)) {
                        call_user_func_array(array($obj, $call_back), array($doc));
                    } else {
                        throw new Exception('call_back is invaild');
                    }
                }

            } catch (MongoException $e) {
                Logger::logInfo('mongo in lib save failed, message [' . $e->getMessage() . '] code: [' . $e->getCode() . ']');
                return false;
            }
        } else {
            throw new Exception('args of getcursor is invaild');
        }
    }

    /**
     * @param $criteria
     * @param $newobj
     * @param $options
     * @return bool
     * @throws Exception
     */
    public function update($criteria, $newobj, $options = array())
    {
        if (is_array($criteria) && is_array($newobj) && is_array($options)) {
            try {
                $c = $this->_getColctObj();
                return $c->update($criteria, $newobj, $options);
            } catch (MongoException $e) {
                Logger::logInfo('mongo in lib update failed, message [' . $e->getMessage() . '] code: [' . $e->getCode() . ']');
                return false;
            }
        } else {
            throw new Exception('date of update is invaild');
        }
    }

    /**
     * 批量插入， 目前不建议使用， 有未知错误
     * @param $data
     */
    public function batchInsert($data = array())
    {
        if ($data || is_array($data)) {
            try {
                $arr = &$this->_getBatchArr('insert');
                if (count($arr) > self::MAXBATCHNUM) {
                    $c = $this->_getColctObj();
                    $batch = new MongoInsertBatch($c, array('ordered' => false));
                    foreach ($arr as $doc) {
                        $batch->add((object)$doc);
                    }
                    $batch->execute();
                    $arr = array();
                } else {
                    array_push($arr, $data);
                }
            } catch (MongoException $e) {
                // 这里插入重复，不做特殊处理
                Logger::logInfo("error message: " . $e->getMessage() . "error code: [" . $e->getCode() . "]");
            }
        } else {
            throw new Exception('date of batchInsert is invaild');
        }
    }

    /**
     * @param $data
     */
    public function batchUpdate($data = array())
    {
        if ($data && is_array($data)) {
            try {
                $docs = &$this->_getBatchBuff('update');
                if (count($docs) > self::MAXBATCHNUM) {
                    $c = $this->_getColctObj();
                    $batch = new MongoUpdateBatch($c);
                    foreach ($docs as $doc) {
                        $batch->add((object)$doc);
                    }
                    $batch->execute();
                    $docs = array();
                } else {
                    if (isset($data['userkey'])) {
                        $update = array(
                            'q' => array('userkey' => $data['userkey']),
                            'u' => array('$set' => $data),
                            'multi' => false,
                            'upsert' => true,
                        );
                        array_push($docs, $update);
                    }
                }
            } catch (MongoException $e) {
                Logger::logInfo('mongo in lib batchupdate failed, message [' . $e->getMessage() . '] code: [' . $e->getCode() . ']');
                return false;
            }
        } else {
            Logger::logInfo('data of batchupdate is invaild');
            return false;
        }
    }

    /**
     * 获取待批量存储的文档
     * @param $opt
     * @return mixed
     */
    private function &_getBatchBuff($type)
    {
        $db = $this->getDbName();
        $colct_name = $this->_getColctObj();
        if ($type) {
            if (isset($this->_batch_buff[$type][$db][$colct_name])) {
                return $this->_batch_buff[$type][$db][$colct_name];
            } else {
                reuturn($this->_batch_buff[$type][$db][$colct_name] = array());
            }
        } else {
            throw new Exception('type of getbatchbuff is invaild');
        }
    }

    /**
     * @param $flag
     * @return mixed
     * @throws Exception
     */
    public function getLimitId($flag)
    {
        /* flag eq 1 get min flag eq -1 get max */
        if (!is_numeric($flag)) {
            throw new Exception('flag is not numeric');
        }
        $c = $this->_getColctObj();
        if ($c) {
            $cursor = $c->find(array(), array('_id' => 1))->sort(array('_id' => $flag))->limit(1);
            foreach ($cursor as $doc) {
                return $doc['_id'];
            }
        } else {
            throw new Exception('collection ins can not be null');
        }
    }
}