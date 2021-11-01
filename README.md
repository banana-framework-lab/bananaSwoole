#  BananaSwoole

#### 框架介绍
BananaSwoole是Banana以自己的个人开发心得所抽象出的一个框架。此框架是一个免费开源轻量级
PHP开发框架。此框架对于其他框架来说，没有很明显的优势，但可以初步让没有封装过框架的新手与swoole新手提供一定
的帮助与学习的意义。<br>

#### 开发理念
BananaSwoole本身最核心的思想就是"海纳百川"，其中含义就是，能让每一个开发者都用起来顺手，框架本身要做到大部分功能在FPM与Swoole
都能够使用。

####  致敬 
BananaSwoole本身没有自己设计得ORM，采用了laravel的查询构造器，
在此致敬laravel；

#### 目录结构

###### 项目的基础目录结构<br>
project  项目的根目录<br>
├─app&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;服务程序目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─Index&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;服务程序名称(此处Index为服务程序的名称)<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Command&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;脚本服务目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Process&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;守护进程服务目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Common&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;公共文件目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Controller&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;控制器目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Handler&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;事件器目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Logic&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;逻辑层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Form&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;提交校验目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Model&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─RedisModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;redis缓存模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─MysqlModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;mysql数据库模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─HttpModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;请求模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Object&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;抽象类目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─Service&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;服务类目录<br>
│<br>
├─bin&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;bananaSwoole命令启动目录<br>
├─channel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;通道路由文件目录<br>
├─config&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;配置文件目录<br>
├─library&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;bananaSwoole核心代码目录<br>
├─public&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;web入口目录<br>
├─route&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;web路由配置目录<br>
├─sql&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;项目sql更新记录目录<br>
├─vendor&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;扩展资源文件目录<br>
└─log&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日志记录目录<br>

###### 框架的核心目录结构<br>

library根目录<br>
├─Abstracts&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;抽象类目录<br>
├─Common&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;框架的公共目录<br>
├─Container&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;容器目录<br>
├─Exception&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;特殊Exception目录<br>
├─Server&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BananaSwoole服务目录<br>
└─Utils&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;实体助手类目录<br>

#### 入口文件

###### 入口文件的写法
1.引入自动加载文件<br>
2.新建BananaSwooleServer类<br>
3.给Server类设置具体的业务Server类<br>
4.调用Server的run方法<br>

###### bananaSwoole启动Demo
```php
use App\Index\Server\IndexServer;
use Library\Server\BananaSwooleServer;

date_default_timezone_set('PRC');

$server = new BananaSwooleServer();
$server->setServer(new IndexServer());
$server->run();

```

#### 指令操作

###### 指令操作步骤
1.cd到项目的bin目录路径下
2.执行相关指令操作
```
// 指令格式如下
php bananaSwoole [server] [action] [server] [scrpit]
```

###### 命令启动有3中action形式

1. server
```
// 启动在public中的swoole服务
php bananaSwoole server start swoole

// 停止在public中的swoole服务
php bananaSwoole server stop swoole

// 热重启在public中的swoole服务
php bananaSwoole server reload swoole
```
2.command
```
// 停止在指定Index服务中的Command指令任务
php bananaSwoole command start Index Command
```

2.process
```
// 启动在指定Index服务中的Process指令任务
php bananaSwoole process start Index Process

// 停止在指定Index服务中的Process指令任务
php bananaSwoole process kill Index Process
```
