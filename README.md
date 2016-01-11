commonswoole
=======
目录结构
  * conf ---主配置文件及逻辑配置文件
  * lib  ---公共类封装
  * logic---业务逻辑类
  

commonswoole_0.0.1 对swoole的httpserver进行了封装，对业务和swoole框架进行了隔离，同时支持http和process两个模式，其中http实现了业务路由功能， process可以根据id进行简单功能细分

logic目录下所有逻辑类必须实现LogicInterface里面的httpTaskProcess和workTaskProcess方法

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
###三、第三方库封装
>1、ssh

>2、redis

>3、mongo

>4、memcache

>5、bloomfiter

>6、httpcurl

>7、ip_query

>8、kafka


TODO
=======
>1、mysql封装

>2、logic中加入lib库的sample样例

>3、加入tcp、udp和websocket封装支持
