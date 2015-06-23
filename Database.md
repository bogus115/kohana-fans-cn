# 扩展系统内建的 Database 库 #

| author | icyleaf|
|:-------|:-------|
| version | 0.1    |

虽然我不懂 Ruby 和 ROR，不过在当初接触的时候就感觉 ROR 的多数据库配置是一个很棒的想法，之前由于忙碌没有来做，昨天看到 lzyy 在博客上面发布了自己的[实现想法](http://blog.pianzhizhe.com/archives/84)，感觉还是有些麻烦，今天自己做了另外一种方法。

## 源码(Source) ##

Kohana v2.3.x

  * libraries/[MY\_Database.php](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/libraries/MY_Database.php)
  * config/[database.php](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/config/database.php)