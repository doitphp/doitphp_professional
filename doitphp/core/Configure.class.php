<?php
/**
 * doitPHP配置文件管理类
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (C) 2012 www.doitphp.com All rights reserved.
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Configure.class.php 1.0 2012-11-11 22:01:36Z tommy $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Configure {

    /*
     * 网址格式
     *
     * @var string
     */
    const PATH_FORMAT = 'path';
    const GET_FORMAT  = 'get';

    /*
     * 视图格式
     *
     * @var string
     */
    const HTML_VIEW_EXT = '.html';
    const PHP_VIEW_EXT  = '.php';

    /**
     * 项目配置文件内容临时存贮数组
     *
     * @var array
     */
    private static $_config = array();

    /**
     * 应用配置文件内容存贮数组
     *
     * @var array
     */
    private static $_data = array();

    /**
     * 类方法：loadConfig()调用状态。如果调用则为true，反之为false。
     *
     * @var boolean
     */
    private static $_isStart = false;

    /**
     * 加载应用配置文件
     *
     * 加载应用配置文件，分析该配置文件内容，并将分析后的数据赋值给self::$_data。
     *
     * @access public
     *
     * @param string $filePath 应用配置文件路径
     *
     * @return boolean
     */
    public static function loadConfig($filePath = null) {

        //判断本方法是否被调用过，如果调用过，则直接返回。
        if (self::$_isStart == true) {
            return true;
        }

        //获取应用配置文件内容默认值
        $defaultConfig = self::_getDefaultConfig();

        $config = array();
        //当配置文件路径存在时
        if ($filePath) {
            //分析配置文件是否存在
            if (!is_file($filePath)) {
                Controller::halt('The configuration file: ' . $filePath . ' is not found!', 'Normal');
            }
            //获取应用配置文件内容
            include_once $filePath;

            //应用配置文件内容的默认值与配置文件值进行整合
            $config['application'] = (isset($config['application']) && is_array($config['application'])) ? $config['application'] + $defaultConfig : $defaultConfig;
        } else {
            $config['application'] = $defaultConfig;
        }

        self::$_data  = $config;
        self::$_isStart = true;

        return true;
    }

    /**
     * 获取参数值
     *
     * 根据某参数名,获取应用配置文件的该参数的参数值
     *
     * @access public
     *
     * @param string $key 应用配置文件内容的参数名
     *
     * @return mixed
     */
    public static function get($key) {

        //分析参数
        if (!$key) {
            return false;
        }

        if (strpos($key, '.') === false) {
            if (!isset(self::$_data[$key])) {
                return false;
            }
            return self::$_data[$key];
        }

        $keyArray = explode('.', $key);

        $value = false;
        if ($keyArray) {
            foreach ($keyArray as $keyId=>$keyName) {
                if ($keyId == 0) {
                    if (!isset(self::$_data[$keyName])) {
                        $value = false;
                        break;
                    }
                    $value = self::$_data[$keyName];
                } else {
                    if (!isset($value[$keyName])) {
                        $value = false;
                        break;
                    }
                    $value = $value[$keyName];
                }
            }
        }

        return $value;
    }

    /**
     * 设置参数值
     *
     * 设置应用配置文件内容的参数值
     *
     * @access public
     *
     * @param string $key 应用配置文件内容的参数名
     * @param mixed $value 应用配置文件内容的参数值
     *
     * @return boolean
     */
    public static function set($key, $value = null) {

        //分析参数
        if (!$key) {
            return false;
        }

        if (strpos($key, '.') === false) {
            self::$_data[$key] = $value;
            return true;
        }

        $keyArray  = explode('.', $key);
        $keyString = "['" . implode("']['", $keyArray) . "']";

        eval('self::$_data' . $keyString . '=$value;');

        return true;
    }

    /**
     * 获取项目配置文件内容
     *
     * 根据DoitPHP项目的配置文件名称，获取该项目配置文件的内容，并将该内容进行返回
     *
     * @access public
     *
     * @param string $fileName 项目配置文件名。注：不含“.php”后缀。
     *
     * @return array
     */
    public static function getConfig($fileName) {

        //参数分析.
        if (!$fileName) {
            return false;
        }

        if (!isset(self::$_config[$fileName])) {
            $filePath = CONFIG_DIR . $fileName . '.php';
            //判断文件是否存在
            if (!is_file($filePath)) {
                Controller::halt('The configuration file: ' . $fileName . '.php is not exists!', 'Normal');
            }

            $config = array();
            include_once $filePath;
            self::$_config[$fileName] = $config;
        }

        return self::$_config[$fileName];
    }

    /**
     * 获取应用配置文件的默认值
     *
     * @access private
     * @return array
     */
    private static function _getDefaultConfig() {

        //定义变量$defaultConfig
        $defaultConfig = array();

        //设置应用目录(application)的路径
        $defaultConfig['basePath']            = APP_ROOT . 'application';

        //设置缓存目录的路径
        $defaultConfig['cachePath']           = APP_ROOT . 'cache';

        //设置日志目录的路径
        $defaultConfig['logPath']             = APP_ROOT . 'logs';

        //设置是否开启调试模式（开启后,程序运行出现错误时,显示错误信息,便于程序调试）
        $defaultConfig['debug']               = false;

        //设置日志写入功能是否开启
        $defaultConfig['log']                 = false;

        //设置应路由网址的重写模式是否开启
        $defaultConfig['rewrite']             = false;

        //设置路由网址格式(path：为url路由格式；get:为标准普通url格式)
        $defaultConfig['urlFormat']           = self::PATH_FORMAT;

        //设置路由分割符
        $defaultConfig['urlSegmentation']     = '/';

        //设置路由网址后缀。注：只有开启URL的rewrite功能时，才生效
        $defaultConfig['urlSuffix']           = self::HTML_VIEW_EXT;

        //设置自定义路由模式是否开启
        $defaultConfig['customUrlRouter']     = false;

        //设置默认module、controller及action名
        $defaultConfig['defaultModule']       = '';
        $defaultConfig['defaultController']   = 'Index';
        $defaultConfig['defaultAction']       = 'index';

        //设置时区，默认时区为东八区(中国)时区(Asia/ShangHai)。
        $defaultConfig['defaultTimeZone']     = 'Asia/ShangHai';

        //设置视图文件的格式(php或html, 默认为php)
        $defaultConfig['viewExt']             = self::PHP_VIEW_EXT;

        //设置默认配置文件路径
        $defaultConfig['defaultConfigDir'] = APP_ROOT . 'application' . DIRECTORY_SEPARATOR . 'config';

        return $defaultConfig;
    }
}