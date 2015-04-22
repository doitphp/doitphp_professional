<?php
/**
 * DoitPHP核心类
 *
 * 初始化框架的基本设置、路由分发、及提供常用的类方法(静态加载文件、单例模式实例化对象、获取当前的Module名称、获取当前的Controller名称、获取当前的Action名称)
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: DoitPHP.php 2.0 2012-11-25 20:09:56Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

/**
 * 定义错误提示级别
 */
error_reporting(E_ALL^E_NOTICE);

/**
 * 定义目录分隔符
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * 定义DoitPHP项目的基本路径
 */
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__FILE__) . DS . '..');
}

/**
 * 定义DoitPHP框架文件所在路径
 */
if (!defined('DOIT_ROOT')) {
    define('DOIT_ROOT', dirname(__FILE__));
}


/**
 * Doitphp框架核心全局控制类
 *
 * 用于初始化程序运行及完成基本设置
 * @author tommy <tommy@doitphp.com>
 * @version 2.0
 */
abstract class Doit {

    /**
     * 模块名(module)
     *
     * @var string
     */
    private static $_module;

    /**
     * 控制器(controller)
     *
     * @var string
     */
    private static $_controller;

    /**
     * 动作(action)
     *
     * @var string
     */
    private static $_action;

    /**
     * 对象注册表
     *
     * @var array
     */
    private static $_objects = array();

    /**
     * 载入的文件名(用于PHP函数include所加载过的)
     *
     * @var array
     */
    private static $_incFiles = array();

    /**
     * 项目执行
     *
     * 供项目入口文件所调用,用于启动框架程序运行
     *
     * @access public
     *
     * @param string $configFilePath 配置文件的路径
     *
     * @return object
     */
    public static function run($configFilePath = null) {

        //初始化运行环境
        self::_init($configFilePath);

        return self::_createWebApplication(Router::getRequest());
    }

    /**
     * 在PHP CLI运行模式下的项目执行
     *
     * 在CLI运行模式下供项目入口文件所调用,用于启动框架程序运行
     *
     * @access public
     *
     * @param string $configFilePath 配置文件的路径
     *
     * @return object
     */
    public static function execute($configFilePath = null) {

        //当前PHP运行模式不为CLI时
        if (PHP_SAPI != 'cli') {
            return false;
        }

        //初始化运行环境
        self::_init($configFilePath);

        return self::_createWebApplication(Router::getCliRequest());
    }

    /**
     * 创建页面控制器对象
     *
     * @access private
     *
     * @param array $routeInfo URL路由信息
     *
     * @return object
     */
    private static function _createWebApplication($routerInfo) {

        //参数分析
        if (!$routerInfo || !is_array($routerInfo)) {
            return false;
        }

        //定义变量_app
        static $_app = array();

        self::$_module     = $routerInfo['module'];
        self::$_controller = ucfirst($routerInfo['controller']);
        self::$_action     = strtolower($routerInfo['action']);

        $appId = (self::$_module) ? self::$_module . '-' . self::$_controller . '-' . self::$_action : self::$_controller . '-' . self::$_action;

        if (!isset($_app[$appId])) {

            //通过实例化及调用所实例化对象的方法,来完成controller中action页面的加载
            $controller = self::$_controller . 'Controller';
            $action     = self::$_action . 'Action';

            $controllerHomePath = BASE_PATH . ((self::$_module) ? '/modules/' . self::$_module : '') . '/controllers';
            //分析Controller子目录的情况。注:controller文件的命名中下划线'_'相当于目录的'/'。
            if (strpos($controller, '_') === false) {
                $controllerFilePath = $controllerHomePath . DS . $controller . '.php';
                if (!is_file($controllerFilePath)) {
                    //当controller名称中不含有'_'字符时
                    self::_show404Error();
                }
                //当文件在controller根目录下存在时,直接加载。
                self::loadFile($controllerFilePath);
            } else {
                //当$controller中含有'_'字符时,将'_'替换为路径分割符。如："/" 或 "\"
                $childDirArray      = explode('_', strtolower($controller));
                $controllerFileName = ucfirst(array_pop($childDirArray));
                $childDirName       = implode(DS, $childDirArray);
                unset($childDirArray);
                //重新组装Controller文件的路径
                $controllerFilePath = $controllerHomePath . DS . $childDirName . DS . $controllerFileName . '.php';
                if (!is_file($controllerFilePath)) {
                    //当文件在子目录里没有找到时
                    self::_show404Error();
                }
                //当子目录中所要加载的文件存在时
                self::loadFile($controllerFilePath);
            }

            //创建一个页面控制器对象(Controller Object)
            $appObject = new $controller();

            if (method_exists($controller, $action)){
                $_app[$appId] = $appObject->$action();
            } else {
                //所调用方法在所实例化的对象中不存在时
                self::_show404Error();
            }
        }

        return $_app[$appId];
    }

    /**
     * 初始化常用的全局常量
     *
     * 定义常用的全局常量：重写模式、路由分割符、伪静态网址的后缀、基本网址
     *
     * @access private
     *
     * @param string $filePath 配置文件的路径
     *
     * @return boolean
     */
    private static function _init($filePath = null) {

        //加载对配置文件管理的类文件
        self::loadFile(DOIT_ROOT . '/core/Configure.php');
        //加载路由网址分析的类文件
        self::loadFile(DOIT_ROOT . '/core/Router.php');
        //加载Controller基类
        self::loadFile(DOIT_ROOT . '/core/Controller.php');

        //加载并分析项目的主配置文件
        Configure::loadConfig($filePath);

        //定义是否开启调试模式。开启后,程序运行出现错误时,显示错误信息,便于程序调试。
        if (!defined('DOIT_DEBUG')) {
            define('DOIT_DEBUG', Configure::get('application.debug'));
        }

        //定义URL的Rewrite功能是否开启。如开启后,需WEB服务器软件如:apache或nginx等,要开启Rewrite功能。
        if (!defined('DOIT_REWRITE')) {
            define('DOIT_REWRITE', Configure::get('application.rewrite'));
        }

        //定义项目应用目录(application)的基本路径
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', rtrim(Configure::get('application.basePath'), '/'));
        }

        //定义项目缓存目录(cache)的基本路径
        if (!defined('CACHE_PATH')) {
            define('CACHE_PATH', rtrim(Configure::get('application.cachePath'), '/'));
        }

        //定义项目入口文件的名称
        if (!defined('ENTRY_SCRIPT_NAME')) {
            define('ENTRY_SCRIPT_NAME', basename($_SERVER['SCRIPT_NAME']));
        }

        //定义网址路由的分割符。注：分割符不要与其它网址参数等数据相冲突
        if (!defined('URL_SEGEMENTATION')) {
            define('URL_SEGEMENTATION', Configure::get('application.urlSegmentation'));
        }

        //定义路由网址的格式。注：get/path
        if (!defined('URL_FORMAT')) {
            define('URL_FORMAT', Configure::get('application.urlFormat'));
        }

        //定义视图文件格式
        if (!defined('VIEW_EXT')) {
            define('VIEW_EXT', Configure::get('application.viewExt'));
        }

        //定义默认的Module名称。注:名称要全部使用小写字母
        if (!defined('DEFAULT_MODULE')) {
            define('DEFAULT_MODULE', Configure::get('application.defaultModule'));
        }

        //定义默认的Controller名称。注:为提高不同系统平台的兼容性,名称首字母要大写,其余小写
        if (!defined('DEFAULT_CONTROLLER')) {
            define('DEFAULT_CONTROLLER', Configure::get('application.defaultController'));
        }

        //定义默认的Action名称。注:名称要全部使用小写字母
        if (!defined('DEFAULT_ACTION')) {
            define('DEFAULT_ACTION', Configure::get('application.defaultAction'));
        }

        return true;
    }

    /**
     * 显示404错误提示
     *
     * 当程序没有找到相关的页面信息时,或当前页面不存在时，显示的提示信息内容
     *
     * @access private
     * @return string
     */
    private static function _show404Error() {

        $viewFilePath = BASE_PATH . '/views/errors/error404.html';
        //判断自定义404页面文件是否存在,若不存在则加载默认404页面
        is_file($viewFilePath) ? self::loadFile($viewFilePath) : self::loadFile(DOIT_ROOT . '/views/errors/error404.html');

        //既然提示404错误信息,程序继续执行下去也毫无意义,所以要终止(exit).
        exit();
    }

    /**
     * 返回唯一的实例(单例模式)
     *
     * 程序开发中,model,module, widget, 或其它类在实例化的时候,将类名登记到doitPHP注册表数组($_objects)中,当程序再次实例化时,直接从注册表数组中返回所要的对象.
     * 若在注册表数组中没有查询到相关的实例化对象,则进行实例化,并将所实例化的对象登记在注册表数组中.此功能等同于类的单例模式.
     *
     * 注:本方法只支持实例化无须参数的类.如$object = new pagelist(); 不支持实例化含有参数的.
     * 如:$object = new pgelist($total_list, $page);
     *
     * <code>
     * $object = Doit::singleton('pagelist');
     * </code>
     *
     * @access public
     * @param string $className  要获取的对象的类名字
     * @return object 返回对象实例
     */
    public static function singleton($className) {

        //参数分析
        if (!$className) {
            return false;
        }

        $className = trim($className);

        if (isset(self::$_objects[$className])) {
            return self::$_objects[$className];
        }

        return self::$_objects[$className] = new $className();
    }

    /**
     * 静态加载文件(相当于PHP函数require_once)
     *
     * include 以$fileName为名的php文件,如果加载了,这里将不再加载.
     * @param string $filePath 文件路径,注:含后缀名
     * @return boolean
     */
    public static function loadFile($filePath) {

        //参数分析
        if (!$filePath) {
            return false;
        }

        //判断文件有没有加载过,加载过的直接返回true
        if (!isset(self::$_incFiles[$filePath])) {

            //分析文件是不是真实存在,若文件不存在,则只能...
            if (!is_file($filePath)) {
                //当所要加载的文件不存在时,错误提示
                Controller::halt('The file: ' . $filePath . ' is not found!', 'Normal');
            }

            include_once $filePath;
            self::$_incFiles[$filePath] = true;
        }

        return self::$_incFiles[$filePath];
    }

    /**
     * 获取当前运行的Module名称
     *
     * @example $moduleName = Doit::getModuleName();
     *
     * @access public
     * @return string module名称(字母全部小写)
     */
    public static function getModuleName() {

        return self::$_module;
    }

    /**
     * 获取当前运行的Controller名称
     *
     * @example $controllerName = Doit::getControllerName();
     *
     * @access public
     * @return string controller名称(字母全部小写)
     */
    public static function getControllerName() {

        return strtolower(self::$_controller);
    }

    /**
     * 获取当前运行的Action名称
     *
     * @example $actionName = Doit::getActionName();
     *
     * @access public
     * @return string action名称(字母全部小写)
     */
    public static function getActionName() {

        return self::$_action;
    }
}

/**
 * 自动加载引导文件的加载
 */
Doit::loadFile(DOIT_ROOT . '/core/AutoLoad.php');

/**
 * 调用SPL扩展,注册__autoload()函数.
 */
spl_autoload_register(array('AutoLoad', 'loadClass'));