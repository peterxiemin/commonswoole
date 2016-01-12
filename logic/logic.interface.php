<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:50
 * Description: 逻辑类接口，http模式的使用httpTaskPorcess process模式使用workTaskProcess
 */

interface LogicInterface {
    /**
     * $r 表示swoole的request， 里面包含了请求的全部信息
     * @param null $r
     * @return mixed
     */
    public function httpTaskProcess($r = null);

    /**
     * $id 表示多进程模式下的分配id， 目前仅支持id : 0->workernum-1， 使用者可以直接
     * 使用，或者在在即的业务逻辑中，根据id自己做映射使用
     * @param int $id
     * @return mixed
     */
    public function workTaskProcess($data = null);
}