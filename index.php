<?php
/**
 * application index
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (C) 2009-2012 www.doitphp.com All rights reserved.
 * @version $Id: index.php 1.0 2014-09-18 01:14:18Z tommy $
 * @package application
 * @since 1.0
 */

define('IN_DOIT', true);

/**
 * 定义项目所在路径(根目录):APP_ROOT
 */
define('APP_ROOT', dirname(__FILE__));

/**
 * 加载DoitPHP框架的初始化文件,如果必要可以修改文件路径
 */
require_once APP_ROOT . '/doitphp/DoitPHP.php';

$config = APP_ROOT . '/application/config/application.php';

/**
 * 启动应用程序(网站)进程
 */
doit::run($config);