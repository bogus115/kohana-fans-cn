| author | icyleaf|
|:-------|:-------|
| version | 0.1    |
| status | draft  |

DBManager Module 灵感来自 WordPress 插件 WP\_DBManager，就连名字都很类似，其实对于其功能也是按照它实现。

目前对于此扩展实现的功能如下：

  1. 仅支持 Mysql 数据库
  1. 获得当前 Mysql 版本以及 Kohana 连接数据库等信息
  1. 获取当前所有表数据
  1. 备份数据库（支持Gzip压缩）
  1. 优化数据库（支持自动优化）
  1. 修复数据库
  1. 下载数据库备份文件
  1. 删除数据库备份文件

此扩展支持配置和 i18n，以及对目前来说一个简易的演示页面。

目前还没有对实现自动备份后进行 Email 通知的功能，以及以后会对多种数据库支持。和限制最大化数据库备份文件。至于是否可以在进行数据库表数据的操作（比如查询，删除表，修改表等）是否还需要支持？

## 源码(Source) ##

Kohana v2.3.x

[DBManager Module](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/modules/dbmanager/)

Kohana v3.0

[DBManager Module](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/3.0/modules/dbmanager/)