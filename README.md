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
在此致敬laravel；在开发系统级web时，采用前后端分离的模式，使用
panjiachen的前端vue-admin-template，在此致敬panjiachen

#### 目录结构

######项目的基础目录结构<br>
project  项目的根目录<br>
├─app&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;服务程序目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─Demo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;服务程序名称(此处Demo为服务程序的名称)<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Command&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;脚本服务目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Common&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;公共文件目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Controller&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;控制器目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Handler&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;事件器目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Logic&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;逻辑层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─MiddleWare&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;中间件层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Model&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─CacheModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;缓存模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─DatabaseModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;数据库模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─HttpModel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;请求模型层目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Object&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;抽象类目录<br>
│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─Property&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;模型实体类目录<br>
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
└─web&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;项目前端目文件录<br>

###### 框架的核心目录结构<br>

library根目录<br>
├─App&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;框架提供的一些实体类目录<br>
├─Base&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;基类目录<br>
├─Cert&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;用于php中curl的https请求<br>
├─Common&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;框架的公共目录<br>
├─Entity&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;实体类目录<br>
├─Exception&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;特殊Exception目录<br>
├─Helper&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;实体助手类目录<br>
├─Object&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;抽象类目录<br>
├─Pool&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;连接池类目录<br>
├─Runtime&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;运行数据记录目录<br>
├─Server&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Server类目录<br>
└─Virtual&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;抽象类目录<br>

#### 入口文件

######入口文件的写法
1.引入自动加载文件<br>
2.新建Server类，来规定Swoole还是FPM的执行方式<br>
3.给Server类注入具体的业务Server类<br>
4.调用Server的run方法<br>

###### Swoole的形式
```php
 use Library\App\Server\DefaultSwooleServer;
 use Library\Server\SwooleServer;
 
 require dirname(__FILE__) . '/../vendor/autoload.php';
 
 // 新建SwooleServer类
 $server = new SwooleServer();
 // 配置配置文件的下标
 $server->setConfigIndex('server');
 // 传入默认的Swoole业务类进行初始化Server类的数据
 $server->init(new DefaultSwooleServer());
 // 执行Server类
 $server->run();
```

###### FPM的形式
```php
use Library\App\Server\DefaultFpmServer;
use Library\Server\FpmServer;

require dirname(__FILE__) . '/../vendor/autoload.php';

// 新建FpmServer类，传入默认的Fpm业务类
$server = new FpmServer(new DefaultFpmServer());
// 执行server类
$server->run();

```

#### 指令操作

######指令操作步骤
1.cd到项目的bin目录路径下
2.执行相关指令操作
```
// 指令格式如下
php bananaSwoole [action] [server] [command]
```

###### 命令启动有4中action形式
1. start
```
// 启动在public中的index服务
php bananaSwoole start index
```
2. stop
```
// 停止在public中的index服务
php bananaSwoole stop index
```
3. reload
```
// 热重启在public中的index服务
php bananaSwoole reload index
```
4. command
```
// 停止在指定Index服务中的Command指令任务
php bananaSwoole command Index Command
```
