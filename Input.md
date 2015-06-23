# 扩展系统内建的 Input 库 #

| author | icyleaf|
|:-------|:-------|
| version | 0.1    |

由于在系统内建的 Input 库类存在 [ip\_address()](http://khnfans.cn/docs/libraries/input#ip_address) 方法，所以把 ip\_location() 方法也放入在此库类之中。

## 方法 ##

### ip\_location() ###

**ip\_location($ip='', $isFull=FALSE)** 利用 QQ 纯真 IP 数据库实现 IP 归属地的查询。其有两个个参数：

  * (string) 需要查询归属地的 IP 地址
  * (boolean) 是否使用完全版数据库。完全版是由纯真网站制作，可以从[官网下载](http://update.cz88.Net/soft/qqwry.rar)，精简版的是由Discuz！论坛系统中提取出来的。其区别在于，精简版可以查出 ip 所在的国家和城市；而完全版可以在此之外，检测到使用的宽带接入商或网吧名称。默认使用\*精简版**。**

**小提示：**
  1. 精简版的存放在本 SVN 里面，路径：`svn/trunk/vender/qqwry` 目录
  1. 方法实现提取自 Discuz! 系统之中，如涉及版权问题，版权归其所有。

实例：
```
// 由于 input 库类有系统自动加载，不用实例化即可使用
// 返回： 北京 - 网通
echo $this->input->ip_location('123.117.180.163');
```

### format\_smiles() ###

**format\_smiles($string)** 对输入的字符型表情进行图片呈现化的处理。目前只有一个固定参数：

  * (string) 需要格式化表情的字符串

```
// 转换 :) 表情
echo $this->input->format_smiles('hello everyone:)');
```

## 源码(Source) ##

### Kohana 2.3.x ###
[MY\_Input Library](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/libraries/MY_Input.php)