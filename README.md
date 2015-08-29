# DGStorage - Efficiency Without Compromise.

DGStorage is a database based on filesystem.
Import ```DGStorage``` lib to use it. alos can use Shell or Web work closely with DGStorage。
You can include our DGStorage lib in your code, or using shell/web work with DGStorage.
+ Unstruction：DGStorage based on filesystem，doesn't like MongoDB，you can not only stroage JSON fils, but also other files.
+ Distribution：Deploy more database collection in a very short time. More quickly for multiple terms query.
+ Quickly：For<code>10'000</code>datas，could finished in <code>2</code>seconds。

#Document
See:https://github.com/DGideas/DGStorage/wiki/Version-Lambda-(LTS%E7%89%88%E6%9C%AC)---%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87%E6%96%87%E6%A1%A3
* For twice develop:https://github.com/DGideas/DGStorage-toolkit

#Downloads
* https://github.com/DGideas/DGStorage/releases

#Python3 - Very quickly guide
1. Import ```DGStorage``` lib in your Python3 app:
```Python
    
    import DGStorage as DG
    
```
2. create a database connect：
```Python
    
    a=DG.DGStorage()
    
```
3. Create or select a database collection:
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
1. You can use (etc..) append, index method ,just like work with list object.
2. Designed for big data, don't worry about how many datas you should deal with.
3. Zip and unzip while database collection.

#PHP5 - Very quickly guide 
1. Import ```DGStorage``` lib in your PHP5 app:
```PHP
    
    include_once('DGStorage.php');
    
```
2. create a database connect：
```PHP
    
    $a=new DGStorage();
    
```
3. Create or select a database collection:
```PHP
    
    $a->create("db");
    
    $a->select("db");
    
```
4. Use it!
```PHP
    
    $a->add('20150101','hello');
    
    $a->get('20150101'); //do not use this format in PHP：$a->get("something")[1]
    
    //Becouse PHP don't know the return var is a array or not
    
```
**Hint:If DGStorage's PHP version running under web environment,reading dir need RWRWRW right**

##Features for PHP5 version
1. 即将提供HTTP/HTTPS协议的API（应用程序开发接口），容许您将数据库进行网络部署

2. 即将提供基于HTTP/HTTPS的网页面板，轻松进行数据的可视化工作

#C++ - Method
under developing...

#Node.js - Method
under developing...
