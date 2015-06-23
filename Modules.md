| author | icyleaf|
|:-------|:-------|
| version | 0.1    |
| status | draft  |

Module Library 是一个方便管理 Kohana 系统自身扩展配置的库类，通过它使用代码就可以轻松实现获取当前所有 Modules 信息，已激活的 Moduels， 未激活的 Modules，最重要的是还可以添加 Modules，激活和关闭 Module。

使用也很简单，下载之后放在 application/libraries 目录下面，在控制器加入下面范例代码：
```
// Instance Module library
$module = Module::instance();

// list all modules of application in application/config/config.php
echo Kohana::debug($module->list_all());

// list active modules of application in application/config/config.php
echo Kohana::debug($module->list_active());

// list inactive modules of application in application/config/config.php
echo Kohana::debug($module->list_inactive());

// active 'auth' module
$module->active('auth');

// inactive 'auth' module
$module->inactive('auth');

// add 'sample_module' module with description.
$module->add('sample_module', 'Just a sample module');
```

其中list\_all(), list\_acitve(), list\_inactive() 返回的是一个包括 modules 名称，描述和路径的数组信息。

不过请注意，此库类需要在 application/config/config.php 中的 $config['modules'] 数组中添加一个注解式的标记已方便此库类的操作。

不放心的朋友可以对 application/config/config.php 进行备份在进行测试使用。

## 源码(Source) ##

Kohana v2.3.x

[Module Library](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/libraries/Module.php)