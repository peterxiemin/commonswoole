<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2016/1/12
 * Time: 19:05
 */

namespace commonswoole;

class CommonFunc
{
    public static function autoLoad($c)
    {
        $path = WORKROOT . '/' . str_replace('\\', '/', $c) . '.php';
        if (file_exists($path)) {
            include $path;
            return true;
        }
        return false;
    }

    public static function getLogicConf($c)
    {
        static $_config = array();
        if (isset($_config[$c])) {
            return $_config[$c];
        }

        $cfg = WORKROOT . '/conf/' . str_replace('\\', '/', $c) . '.config.php';
        if (file_exists($cfg)) {
            $ret = include_once($cfg);
            $_config[$c] = $ret;
            return $_config[$c];
        }
        return null;
    }


    /**
     * 检查时间是否合法
     * @param $days
     * @return bool
     */
    public static function checkdate_func($days)
    {
        $reg = "/\d{4}\_\d{2}\_\d{2}/";
        preg_match($reg, $days, $arr);
        return count($arr) == 0 ? false : true;
    }

    /**
     * 能够根据权重进行负载均衡
     * @return mixed
     * @throws Exception
     */
    public static function schedule_byweight()
    {
//    $cfg = array(
//        array(
//            'host' => '10.50.3.36',
//            'port' => '8080',
//            'weight' => 30
//        ),
//        array(
//            'host' => '10.50.3.37',
//            'port' => '8080',
//            'weight' => 30
//        ),
//        array(
//            'host' => '10.50.3.38',
//            'port' => '8080',
//            'weight' => 40
//        )
//    );

        $sum = 0;
        for ($i = 0; $i < count($cfg); $i++) {
            $sum += $cfg[$i]['weight'];
        }
        if ($i == count($cfg) && $sum != 100) {
            throw new \Exception('sum of weight not is 100');
        }


        $seed = rand(0, 99);
        $min = $max = 0;
        for ($i = 0; $i < count($cfg); $i++) {
            $max += $cfg[$i]['weight'];
            $min = $max - $cfg[$i]['weight'];
            if ($min <= $seed && $seed < $max) {
                return $cfg[$i];
            }
        }
        throw new \Exception('inter error seed: '.$seed.' max: '.$max.' min: '.$min);
    }
}

