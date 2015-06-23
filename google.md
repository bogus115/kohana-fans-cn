# Google 辅助函数 #

| author | icyleaf|
|:-------|:-------|
| version | 0.1    |

为了使得 Google API 可以更好的在 Kohana 中的使用，或许这个辅助函数起的名字太大，如果内容太多的且使用辅助函数无法完成，那么只好转换为库类。

## 方法 ##

### translate() ###

**translate($text, $lang='zh-CN|en')** 利用 Google Translation 把指定的字符串内容进行翻译功能，其有两个参数：

  * (string) $text - 需要翻译的内容
  * (string) $lang - 用竖线（"|"）把原翻译的内容语言和目标语言的国家代号，默认，'zh-CN|en' = 简体中文翻译为英文。(参考资料：[国家代号](http://code.google.com/intl/zh-CN/apis/ajaxlanguage/documentation/reference.html)）

实例：
```
// 默认方法
echo google::translate('Kohana 中文爱好者网站');
// 返回：Kohana Chinese fans site


// 自定义翻译：中文转换为日文
echo google::translate('Kohana 中文爱好者网站', 'zh_CN|ja');
// 返回：Kohana中国のファンサイト
```

### url\_friendly() ###

**url\_friendly($string)** 主要是对 SEO 优化处理，可以把地址输出 URI 优化为更利于 SEO 抓取的友好化地址。处理的内容可包括所有国家的语言，可以把汉语，日语，韩语，法语，西班牙语，德语等等转换为英文的地址链接。例如，我想把标题“Welcome to Beijing 2008 Olympic Games”优化为“welcome-to-beijing-2008-olympic-games”。

参数：

  * (string) $string - 需要友好化的内容

实例：
```
// 简体中文优化
echo google::url_friendly('Kohana 中文爱好者网站');
// 返回：kohana-chinese-fans-site


// 法语优化
echo google::translate('Guantanamo: le chauffeur de Ben Laden plaide non coupable à l'ouverture de son procès');
// 返回：guantanamo-le-chauffeur-de-ben-laden-plaide-non-coupable-a-l- ouverture-de-son-proces
```

## 源码（Source） ##

Kohana v2.3.x

[google helper](http://code.google.com/p/kohana-fans-cn/source/browse/trunk/2.3.x/helper/google.php)