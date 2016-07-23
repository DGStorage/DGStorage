# DGStorage - Efficiency Without Compromise.

Python Version:
[![Build Status](https://travis-ci.org/DGideas/DGStorage.svg?branch=master)](https://travis-ci.org/DGideas/DGStorage)
[![Build status](https://ci.appveyor.com/api/projects/status/43hfd4pbj78ukw0t?svg=true)](https://ci.appveyor.com/project/DGideas/dgstorage)

DGStorage is a database based on filesystem.

Import ```DGStorage``` lib to use it. alos can use Shell or Web work closely with DGStorage.

You can include our lib in your code, or using shell/web work with DGStorage.

#Document
See:https://github.com/DGStorage/DGStorage/wiki/Dorado-(2016%E5%B9%B41%E6%9C%88%EF%BC%8CLTS%E7%89%88%E6%9C%AC)-%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87%E6%96%87%E6%A1%A3
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
    
    include('DGStorage.php');
    
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
1. Will support HTTP/HTTPS API.Allow you deploy DGStorage online.

2. Will support web panel.
