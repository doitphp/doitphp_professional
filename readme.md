#DoitPHP

本项目Fork自[原作者](https://github.com/doitphp/doitphp)而来，因原作者维护进度较慢所以我重开一个DoitPHP分支维护迭代。原作者的更新将在Review后根据具体情况Merge。

DoitPHP适合各种规模的Web应用，对于中小型项目，它提供了丰富的Library，对于大型项目，它有一个非常轻量级的核心，易于扩展。

目前我所在的公司一个规模在小型和中等之间的项目即由DoitPHP承载。

[Changelog]

2014-02-25
- Controller基类增加getActionName方法，在init方法里通过判断action可以实现简单的拦截器功能
- 配置文件路径设置和修改逻辑优化
- 修复Check类isMust方法的BUG


2014-02-10
- Controller基类增加isPost、getClientUa方法，方便判断post及构造更安全的Cookie/Session
- Controller基类中_stripSlashes方法的array_map被替换为foreach以兼容PHP5.2.X及以下
- Controller基类的showMsg方法默认跳转路径修改为后退，默认等待时间减少为3秒
- 增加默认配置文件路径定义，因此Configure类getConfig的BUG也得到修复
- 修改Model基类的query方法，追加fetchAll操作
- 修复Model基类setErrorInfo方法传入参数为二维数组时的转义错误
- 修改Check类的isMust方法，为空时将返回true，反之返回false，更符合语义
- 修改Check类的isLength方法，统计逻辑更新为全角字符计为2两个字符，半角字符计为1个字符
- 修复Pager类output方法中$data参数为空时的错误


----


DoitPHP(原Tommyframework)是一个基于BSD开源协议发布的轻量级PHP框架。简而言之：DoitPHP运行高效，易学易用，易于扩展。换而言之：DoitPHP运行高效而不失功能强大，操作灵活而又能扩展自如。作为PHP框架里的“后起之秀”，DoitPHP秉承了那些优秀的PHP框架所共有的：代码的OOP编写风格、URL的路由功能、MVC的架构思想、UID的数据库操作、以及AJAX的前端页面技术支持。并在操作和功能设计上进行了微创新：

一、DoitPHP的辅助开发工具(DoitPHP Tools)，其强大的“脚手架”功能，使得利用本框架进行程序开发更加容易。

二、简明高效的视图运行机制，使视图文件的开发操作变得简单易行。

三、灵活的扩展模块(module)设计，能够非常容易地调用如：SMARTY、ADODB、CKEDITOR、TINYEDITOR、FPDF、PHPMAILER、PHPRPC等第三方开源程序。

四、集成了JQUERY及JQUERY FORM、LAZYLOAD等JQUERY插件，使前端页面开发中实现AJAX LOADING图片加载效果、灯箱效果(锁屏效果)、TAB菜单.、CHECKBOX的全选或反选效果以及图片的惰性加载效果等，让其变得得心应手。

五、提供了PHP程序开发中常用的操作类库，无论是COOKIE、SESSION的操作，还是实现网页页面的分页效果，实现音频、视频、图片幻灯片的播放，生成RAR、EXCEL、PDF等文件类型的文件，实现MEMCACH、XCACHE、APC等常见的缓存操作，实现WEB SERVICE等PHP的高级应用。利用扩展类库进行操作，如同“信手拈来”。

六、丰富的数据库驱动，支持MYSQL、POSTGRESQL、ORACLE、SQLITE、MSSQL、MONGODB、REDIS等数据库。
