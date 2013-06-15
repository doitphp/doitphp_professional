DoitPHP 2.0 
=============================

感谢您选用doitphp, 这是一个简单易用,运行高效,易于扩展的轻量级PHP框架


安装
------------

1.将doitphp的压缩包解压后,在解压后的文件内你会看到以下文件和目录

      doitphp/		   框架的源文件
      tools/		   doitphp的辅助开发工具
      LICENSE              doitphp的许可证
      README               说明文件


2.运行doitphp的辅助开发工具(http://hostname/doitphpPath/tools/index.php),

	默认用户名:doitphp, 密码:123456 
	
	默认用户名密码更改文件./tools/application/config/application.php


3.设置数据库连接配置参数
    如果要使用doitphp的辅助开发工具创建Model文件时，若Model文件绑定数据表时，需要辅助开发工具的配置文件中设置数据库连接信息。
	配置文件路径为：./tools/application/config/application.php
	将以下内容更改为项目所连接的数据库即可
	//数据库连接参数
	$config['db'] = array(
		'dsn' => 'mysql:host=localhost;dbname=dz',
		'username' => 'root',
		'password' => '123456',
	);


要求
------------

基本要求:web服务器运行的PHP版本5.1.0或以上,且支持gd扩展. 