<?php
/**
 * 项目主配置文件
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: application.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package config
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

//登陆用户及密码
$config['loginUser'] = array(
	'username'=>'doitphp',
	'password'=>123456,
);

//设置WebApp目录
$config['webappPath'] = substr(APP_ROOT, 0, -6);

//设置DoitPHP框架文件目录
$config['doitphpPath'] = APP_ROOT . '../doitphp/';

//登陆cookie名
$config['loginCookieName'] = 'doitToolsLoinStatus';

//跳转网址cookie名
$config['gotoUrlCookieName'] = 'doitToolsGotoUrl';

//数据库连接参数
$config['db'] = array(
	'dsn' => 'mysql:host=localhost;dbname=dz',
	'username' => 'root',
	'password' => '123456',
);