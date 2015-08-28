# DGStorage - Efficiency Without Compromise.

DGStorage 是一个适合于存储非结构化数据的快速数据库。
Import ```DGStorage``` lib to use it. 亦可以通过Shell或者Web操作DGStorage。
You can include our DGStorage lib in your code, or using shell/web work with DGStorage.
+ 非结构化：DGStorage采用文件散列存储的方式，doesn't like MongoDB，您不仅可以存储JSON格式的数据，也可以存储其它
+ 分布式：瞬间即可部署大量的存储实例。More quickly for multiple terms query.
+ 快速：For<code>10'000</code>datas，可以在<code>2</code>秒内完成。数据库的迁移也仅需要移动和压缩/解压缩

#Twice develop
See:https://github.com/DGideas/DGStorage-toolkit

#Python3 - Very quickly guide
1. Import ```DGStorage``` lib in your Python3 app:
```Python
    
    import DGStorage as DG
    
```
2. create a database connect：
```Python
    
    a=DG.DGStorage()
    
```
3. 在已有文件夹的基础上创建数据库实例,或者选择一个已有的数据库实例：
```Python
    
    a.create('db')
    
    a.select('db')
    
```
4. Use it!
```Python
    
    a.add('20150101','Hello, Future!')
    
    a.get('20150101')
    
    a.zip('db')
    
    a.unzip('db')
    
```
##Features for Python3 version
1. 提供了一系列列表的方法，如append,index等，能够按照您熟悉的方式操作数据
2. 针对大数据进行优化，保障您使用喜爱的方法轻松处理大数据，而不用关心数据量的问题
3. 提供了.zip和.unzip方法，能快速将数据库打包为一个可读的文本文件，并迅速解包

#PHP5 - Very quickly guide 
1. 在PHP代码中引用DGStorage库：
```PHP
    
    include_once('DGStorage.php');
    
```
2. 创建一个数据库实例:
```PHP
    
    $a=new DGStorage();
    
```
3. 在已有文件夹的基础上创建数据库实例,或者选择一个已有的数据库实例：
```PHP
    
    $a->create("db");
    
    $a->select("db");
    
```
4. 尽情使用吧!
```PHP
    
    $a->add('20150101','hello');
    
    $a->get('20150101'); //PHP版本的get方法不能使用这个格式：$a->get("something")[1]
    
    //因为PHP不知道返回值是不是可索引序列(数组)
    
```
**Hint:如果PHP在Web环境下运行，对目录的读写操作需要RWRWRW（所有人可写）权限**

##Features for PHP5 version
1. 即将提供HTTP/HTTPS协议的API（应用程序开发接口），容许您将数据库进行网络部署

2. 即将提供基于HTTP/HTTPS的网页面板，轻松进行数据的可视化工作

#C++ - Method
under developing...

#Node.js - Method
under developing...
