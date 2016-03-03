<?php
/**
 * Created by PhpStorm.
 * User: xiemin
 * Date: 2015/11/10
 * Time: 11:45
 * Description: ������ڣ����Ե�throw�쳣���û�б�ҵ�񲶻񣬽���������в���
 */

define('WORKROOT', __DIR__);
date_default_timezone_set("Asia/Shanghai");

//�Զ�����
require_once(WORKROOT . '/commonswoole/CommonFunc.php');
//����ȫ�ֺ���
require_once(WORKROOT . '/lib/Think/Common/functions.php');

spl_autoload_register('\CommonSwoole\commonfunc::autoload');
try {
    $sw = new commonswoole\SSWrapper();
    $sw->onInit();
    $sw->onStart();
    $sw->onExit();
} catch (\Exception $e) {
    lib\log\Logger::logWarn("throw error message: [" . $e->getMessage() . "] error code : [" . $e->getCode() . "]");
    //��ӡջ��Ϣ
    lib\log\Logger::logWarn($e->getTraceAsString());
}
