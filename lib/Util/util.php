<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/12/2
 * Time: 20:24
 */

/**
 * 检查时间是否合法
 * @param $days
 * @return bool
 */
function checkdate_func($days)
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
function schedule_byweight()
{
    $cfg = array(
        array(
            'host' => '10.50.3.36',
            'port' => '8080',
            'weight' => 30
        ),
        array(
            'host' => '10.50.3.37',
            'port' => '8080',
            'weight' => 30
        ),
        array(
            'host' => '10.50.3.38',
            'port' => '8080',
            'weight' => 40
        )
    );

    $sum = 0;
    for ($i = 0; $i < count($cfg); $i++) {
        $sum += $cfg[$i]['weight'];
    }
    if ($i == count($cfg) && $sum != 100) {
        throw new Exception('sum of weight not is 100');
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
    throw new Exception('inter error seed: '.$seed.' max: '.$max.' min: '.$min);
}
