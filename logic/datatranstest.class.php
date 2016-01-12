<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/11
 * Time: 11:27
 */
require_once(WORKROOT . '/lib/ssh/ssh.php');

class DataTransTest implements LogicInterface
{
//    private $_mgo;
    private $_cfg;
    private $_hec;

    public function __construct($cfg)
    {
        $this->_cfg = $cfg;
        $this->_hec = new HandleClass();
//        $this->_mgo = new MongoWrapper($cfg['tag_mongo']);
//        $this->_mgo->setDbName('users');
//        $this->_mgo->setColctName('android_users');
    }

    public function __destruct()
    {

    }

    public function httpTaskProcess($r = null)
    {

    }

    public function workTaskProcess($data = null)
    {
//        $cursor = $this->_mgo->getCursor(array());
//        $start = microtime(true);
//        $count = 0;
//        foreach ($cursor as $doc) {
//            if ($doc && isset($doc['wholeTags'])) {
//                $data = explode(' ', trim($doc['wholeTags']));
//                $datatem = array_slice($data, 0, 11);
//                if (in_array('文化', $datatem)) {
//                    $count++;
//                }
//            }
//            else {
//                Logger::logInfo('doc or doc wholetags is invaild , doc: ['.json_encode($doc, JSON_UNESCAPED_UNICODE).']');
//            }
//        }
//        Logger::logInfo((microtime(true) - $start) . 's and count=' . $count);
        //http://10.50.3.235:7000/DataTransTest?ym=201512&day=11&ip=10.50.4.68&search=%E5%81%A5%E5%BA%B7&pos=10&os=ios
        if (isset($data) && isset($data['query']) && isset($data['query']['ip']) && isset($data['query']['search']) && isset($data['query']['pos']) && isset($data['query']['os'])) {
            /* 日期格式外部curl严格控制， 如果不合法， 将无法查到日志 */
            if (isset($data['query']['ym']) && isset($data['query']['day'])) {
                $remote_ip = $data['query']['ip'];
                $this->_cfg['host'] = $remote_ip;
                $remote_ins = new SShWrapper($this->_cfg['ssh']);
                $ym = $data['query']['ym'];
                $day = $data['query']['day'];
                $search = $data['query']['search'];
                $pos = $data['query']['pos'];
                $os = $data['query']['os'];
                $split = $this->getSplit($pos);
                $remote_ins->setCmd('echo ' . $os . '; awk -F \' \' \'{print ' . $split . '}\' /data1/userclassify/raw/' . $os . '_usertags' . $ym . '' . $day . '|grep \'' . $search . '\'|wc -l');
                $remote_ins->doExec($this->_hec, 'handleExecOutRet');
            } else {
                throw new Exception('ymd is invaild');
            }
        } else {
            throw new Exception('query ip is invaild');
        }
    }

    public function getSplit($pos = 0)
    {
        $str = '';
        if ($pos && is_numeric($pos)) {
            for ($i = 1; $i < $pos; $i++) {
                $str .= '$' . $i;
            }
            return $str;
        } else {
            throw new Exception('pos is invaild');
        }
    }
}


class HandleClass
{
    public function __construct()
    {

    }

    public function handleExecOutRet($stream)
    {
        while (($line = fgets($stream, 4096)) !== false) {
            Logger::logInfo($line);
        }
    }
}