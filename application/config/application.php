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

/**
 * 设置时区，默认时区为东八区(中国)时区(Asia/ShangHai)。
 */
//$config['application']['defaultTimeZone'] = 'Asia/ShangHai';

/**
 * 设置URL网址的格式。
 *  Configure::GET_FORMAT为：index.php?router=controller/action&params=value;
 *  Configure::PATH_FORMAT为:index.php/controller/action/params/value。
 * 默认为：Configure::PATH_FORMAT
 */
//$config['application']['urlFormat'] = Configure::GET_FORMAT;

/**
 * 设置是否开启URL路由网址重写(Rewrite)功能。true:开启；false:关闭。默认：关闭。
 */
//$config['application']['rewrite'] = true;

/**
 * 设置是否开启Debug调用功能。true:开启；false:关闭。默认：关闭。
 */
//$config['application']['debug'] = true;

/**
 * 设置是否开启日志记录功能。true:开启；false:关闭。默认：关闭。
 */
//$config['application']['log'] = true;

/**
 * 自定义项目(application)目录路径的设置。注：结尾无需"/"，建议用绝对路径。
 */
//$config['application']['basePath'] = APP_ROOT . 'application';

/**
 * 自定义缓存(cache)目录路径的设置。注：结尾无需"/"，建议用绝对路径。
 */
//$config['application']['cachePath'] = APP_ROOT . 'cache';

/**
 * 自定义日志(log)目录路径的设置。注：结尾无需"/"，建议用绝对路径。
 */
//$config['application']['logPath'] = APP_ROOT . 'logs';

/**
 * 设置视图文件的格式。Configure::VIEW_EXT_HTML为html;Configure::VIEW_EXT_PHP为php。默认为：php。
 */
//$config['application']['viewExt'] = Configure::VIEW_EXT_HTML;

/**
 * 设置数据库(关系型数据库)的连接参数。 注：仅支持PDO连接。
 *
 * @example
 * 例一：单数据库
 * $config['db'] = array(
 *    'dsn'      => 'mysql:host=localhost;dbname=doitphp',
 *    'username' => 'root',
 *    'password' => '123qwe',
 *    'prefix'   => 'do_',
 *    'charset'  => 'utf8',
 * );
 *
 * 例二：数据库主从分离
 * $config['db'] = array(
 *     'master'  => array(
 *         'dsn'      => '...',
 *         'username' => '...',
 *         'password' => '...',
 *     ),
 *     'slave'   => array(
 *         'dsn'      => '...',
 *         'username' => '...',
 *         'password' => '...',
 *     ),
 *     'prefix'  => 'do_',
 *     'charset' => 'utf8',
 * );
 * 注：prefix为数据表前缀。当没有前缀时，此参数可以省略。charset为数库编码。默认值为：utf8。如编码为utf8时，此参数也可以省略。
 */
//$config['db']['dsn'] = 'mysql:host=yourHost;dbname=yourDbname';
//$config['db']['username'] = 'yourUsername';
//$config['db']['password'] = 'yourPassword';