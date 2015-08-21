# DGStorage - Efficiency Without Compromise
**We've rewritten the entire library，使它更有效率地处理更大的数据，存储逻辑也发生了改变**

DGStorage 是一个极其适合于存储非结构化数据的快速数据库。
使用时，只需引用DGStorage库即可。在全新改进的Build中，也可以通过Shell操作DGStorage
+ 少量：数据库专为<code>1'000'000</code>(100万)条左右数据存储而设计。
+ 非结构化：DGStorage采用文件散列存储的方式，不像MongoDB，您不仅可以存储JSON格式的数据，也可以存储其它
+ 分布式：瞬间即可部署大量的存储实例。全新设计的索引可以使多条件查询瞬间完成
+ 快速：对于<code>10'000</code>条数据的存储，可以在<code>2</code>秒内完成。数据库的迁移也仅需要移动和压缩/解压缩

#Python3 - 方法
1.在Python3程序中引用DGStorage库：
    
    import DGStorage as DG
    
2.创建一个数据库实例：
    
    a=DG.DGStorage()
    
3.在已有文件夹的基础上创建数据库实例,或者选择一个已有的数据库实例：
    
    a.create('db')
    a.select('db')
    
4.尽情使用吧！
    
    a.add('20150101','Hello, Future!')
    a.get('20150101')

#PHP5 - 方法 
1.在PHP代码中引用DGStorage库：
    
    include_once('DGStorage.php');
    
2.创建一个数据库实例:
    
    $a=new DGStorage();
    
3.在已有文件夹的基础上创建数据库实例,或者选择一个已有的数据库实例：
    
    $a->create("db");
    $a->select("db");
    
4.尽情使用吧!
    
    $a->get('20150101');
    
**提示:如果PHP在Web环境下运行，默认的权限是用户权限时，可能无法访问部分目录**