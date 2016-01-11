<?php
/**
 * Created by PhpStorm.
 * User: haohaizihcc
 * Date: 2016/01/08
 * Time: 15:00
 * Description: 配置文件自动加载类,继承自ArrayObject,可以实现数组的访问方式,借鉴自swoole框架的配置文件加载方法
 */
namespace lib\config;
class ConfFile extends \ArrayObject
{
    private $config_dir;
    private $config;
    static $debug = false;
    private $_ext = '.config.php';
    public function __construct()
    {
    }
    function setPath($dir)
    {
        $this->config_dir[] = $dir;
    }

    function offsetGet($index)
    {
        if(!isset($this->config[$index]))
        {
            $this->load($index);
        }
        return isset($this->config[$index])?$this->config[$index]:false;
    }

    function load($index)
    {
        foreach($this->config_dir as $dir)
        {
            $filename = $dir . '/' . $index . $this->_ext;
            if(is_file($filename))
            {
                $retData = include $filename;
                if(empty($retData) and self::$debug)
                {
                    trigger_error(__CLASS__.": $filename no return data");
                }
                else
                {
                    $this->config[$index] = $retData;
                }
            }
            elseif(self::$debug)
            {
                trigger_error(__CLASS__.": $filename not exists");
            }
        }
    }
    function offsetSet($index, $newval)
    {
        $this->config[$index] = $newval;
    }
    function offsetUnset($index)
    {
        unset($this->config[$index]);
    }
    function offsetExists($index)
    {
        return isset($this->config[$index]);
    }
}
?>