<?php

/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 20:08
 */

/**
 * Class MongoWrapper
 * 考虑是否需要加入try catch
 */
class MongoWrapper
{
    private $_mng;
    private $_db;
    private $_collection;

    private $_batchup_arr = array();
    const MAXBATCHUPNUM = 500;

    private $_batchin_arr = array();

    public function __construct($cfg)
    {
        $this->mng = new MongoClient($cfg['mongourl'], $cfg['option']);
    }

    public function __destruct()
    {

    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
    }

    public function setDb($db)
    {
        $this->_db = $db;
    }

    private function _getCollection()
    {
        return $this->mng->selectDB($this->_db)->selectCollection($this->_collection);
    }

    public function save($data)
    {
        $c = $this->_getCollection();
        try {
            $c->save($data);
        } catch (MongoException $e) {
            echo "error message: " . $e->getMessage() . "\n";
            echo "error code: " . $e->getCode() . "\n";
        }

    }

    public function get()
    {
        $c = $this->_getCollection();
        return $c->findOne(/*condition, filed, option*/);
    }

    public function update($data)
    {
        $c = $this->_getCollection();
        try {
            return $c->update(array('userkey' => $data["userkey"]), array('$set' => $data));
        } catch (MongoException $e) {
            echo "error message: " . $e->getMessage() . "\n";
            echo "error code: " . $e->getCode() . "\n";
        }
    }

    /**
     * 批量插入
     * @param $data
     */
    public function batchInsert($data)
    {
        try {
            if (count($this->_batchin_arr) > self::MAXBATCHUPNUM) {
                $c = $this->_getCollection();
                $batch = new MongoInsertBatch($c, array('ordered' => true));
                foreach ($this->_batchin_arr as $doc) {
                    $batch->add((object)$doc);
                }
                $batch->execute();
                unset($this->_batchin_arr);
                $this->_batchin_arr = array();
            } else {
                array_push($this->_batchin_arr, $data);
            }
        }
        catch(MongoException $e) {
            // 这里插入重复，不做特殊处理
//            echo "error message: " . $e->getMessage() . "\n";
//            echo "error code: " . $e->getCode() . "\n";
        }


    }

    public function batchUpdate($data)
    {
        try {
            if (count($this->_batchup_arr) > self::MAXBATCHUPNUM) {
                $c = $this->_getCollection();
                $batch = new MongoUpdateBatch($c);
                foreach ($this->_batchup_arr as $doc) {
                    $batch->add((object)$doc);
                }
                $batch->execute();
                unset($this->_batchup_arr);
                $this->_batchup_arr = array();
            } else {
                $update = array(
                    'q' => array('userkey' => $data['userkey']),
                    'u' => array('$set' => $data),
                    'multi' => false,
                    'upsert' => true,
                );
                array_push($this->_batchup_arr, $update);
            }
        } catch (MongoException $e) {
            echo "error message: " . $e->getMessage() . "\n";
            echo "error code: " . $e->getCode() . "\n";
        }
    }
}