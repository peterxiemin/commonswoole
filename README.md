commonswoole
=======
目录结构
------
  * conf ---主配置文件及逻辑配置文件
  * lib  ---公共类封装
  * logic---业务逻辑类

框架特性
------
  * 全面采用命名空间和auload类自动加载模式
  * 性能较原生swoole少量损耗
  * 丰富的第三方库简化业务开发难度

commonswoole_0.0.1 对swoole的httpserver进行了封装，对业务和swoole框架进行了隔离，同时支持http和process两个模式，其中http实现了业务路由功能， process可以根据id进行简单功能细分。

###一、http服务
只需要开发人员在自己的业务逻辑类中，将业务代码放入httpTaskProcess，返回的内容会自动封装成json格式
```
public function httpTaskProcess($r = null) {
	$ip = $r->get['ip'];
	return lib\iplib\IP::find($ip);
}
```
###二、process服务
只需要开发人员在自己的业务逻辑类中，workTaskProcess,会在日志中详细记录task任务的执行情况
```
public function workTaskProcess($data = null) {
        echo 'I am a Task';
}
```
###三、定时器服务
新增定时器服务，只需要在timer/config.php配置好定时器，配合process模块，就可应对毫秒定时器业务需求
```
```
###四、第三方库封装
>1、ssh

>2、redis

>3、mongo

>4、memcache

>5、bloomfiter

>6、httpcurl

>7、ip_query

>8、kafka

>9、mysql


TODO
=======

>1、加入tcp、udp和websocket封装支持

>2、对mysql封装进行优化
