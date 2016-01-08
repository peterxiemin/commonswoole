<?php
/**
 * 自动加载  使用命名空间模式  需要放到项目的根目录下
 */
class Autoloader
{
	public static function load($class)
	{	
		$path = dirname(__FILE__) . '/' . strtolower(str_replace('\\', '/', $class)) . '.php';
		if (file_exists($path))
		{
			include $path;
			return true;
		}
		return false;
	}
}
