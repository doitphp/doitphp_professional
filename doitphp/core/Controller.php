<?php
/**
 * DoitPHP控制器基类
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Controller.php 2.0 2012-12-01 23:12:30Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Controller {

    /**
     * 视图实例化对象名
     *
     * @var object
     */
    protected static $_viewObject = null;

    /**
     * 构造方法
     *
     * 用于初始化本类的运行环境,或对基本变量进行赋值
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //时区设置,默认为中国(北京时区)
        date_default_timezone_set(Configure::get('application.defaultTimeZone'));

        //设置异常处理
        set_exception_handler(array($this, '_exception'));

        //关闭魔术变量，提高PHP运行效率
        if (get_magic_quotes_runtime()) {
            @set_magic_quotes_runtime(0);
        }

        //将全局变量进行魔术变量处理,过滤掉系统自动加上的'\'.
        if (get_magic_quotes_gpc()) {
            $_POST    = ($_POST) ? $this->_stripSlashes($_POST) : array();
            $_GET     = ($_GET) ? $this->_stripSlashes($_GET) : array();
            $_SESSION = ($_SESSION) ? $this->_stripSlashes($_SESSION) : array();
            $_COOKIE  = ($_COOKIE) ? $this->_stripSlashes($_COOKIE) : array();
        }

        //实例化视图对象
        self::$_viewObject = $this->initView();

        //回调函数,实例化控制器(Controller)时,执行所要补充的程序
        $this->init();

        return true;
    }

    /**
     * 获取$_GET的参数值
     *
     * 获取$_GET的全局超级变量数组的某参数值,并进行转义化处理，提升代码安全。注:参数支持数组。
     *
     * @access public
     *
     * @param string $key 所要获取$_GET的参数名
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function get($key = null, $default = null, $isEncode = true) {

        return Request::get($key, $default, $isEncode);
    }

    /**
     * 获取$_POST参数值
     *
     * 获取$_POST全局变量数组的某参数值,并进行转义等处理，提升代码安全。注:参数支持数组
     *
     * @access public
     *
     * @param string $key 所要获取$_POST的参数名称
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function post($key = null, $default = null, $isEncode = true) {

        return Request::post($key, $default, $isEncode);
    }

    /**
     * 获取并分析$_GET或$_POST全局超级变量数组某参数的值
     *
     * 获取并分析$_POST['参数']的值 ，当$_POST['参数']不存在或为空时，再获取$_GET['参数']的值
     *
     * @access public
     *
     * @param string $key 所要获取的参数名称
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function getParams($key = null, $default = null, $isEncode = true) {

        return Request::getParams($key, $default, $isEncode);
    }

    /**
     * 获取PHP在CLI运行模式下的参数
     *
     * @access public
     *
     * @param string $key 参数键值, 注:不支持数组
     * @param mixed $default 默认参数值
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function getCliParams($key = null, $default = null, $isEncode = true) {

        return Request::getCliParams($key, $default, $isEncode);
    }

    /**
     * 获取某cookie变量的值
     *
     * @access public
     *
     * @param string $cookieName cookie变量名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public static function getCookie($cookieName = null, $default = null) {

        return Cookie::get($cookieName, $default);
    }

    /**
     * 设置某cookie变量的值
     *
     * @access public
     *
     * @param string $cookieName cookie的变量名
     * @param mixed $value cookie值
     * @param integer $expire cookie的生存周期
     * @param string $path cookie所存放的目录
     * @param string $domain cookie所支持的域名
     *
     * @return boolean
     */
    public static function setCookie($cookieName, $value, $expire = null, $path = null, $domain = null) {

        return Cookie::set($cookieName, $value, $expire, $path, $domain);
    }

    /**
     * 显示提示信息操作
     *
     * 本方法支持URL的自动跳转，当显示时间有效期失效时则跳转到自定义网址，若跳转网址为空则函数不执行跳转功能，当自定义网址参数为-1时默认为:返回上一页。
     *
     * @access public
     *
     * @param string $message 所要显示的提示信息
     * @param string $gotoUrl 所要跳转的自定义网址
     * @param integer $limitTime 显示信息的有效期,注:(单位:秒) 默认为3秒
     *
     * @return string
     */
    public static function showMsg($message, $gotoUrl = null, $limitTime = 3) {

        return Response::showMsg($message, $gotoUrl, $limitTime);
    }

    /**
     * 用于显示错误信息
     *
     * 若调试模式关闭时(即:DOIT_DEBUG为false时)，则将错误信息并写入日志
     *
     * @access public
     *
     * @param string $message 所要显示的错误信息
     * @param string $level 日志类型. 默认为Error. 参数：Warning, Error, Notice
     *
     * @return string
     */
    public static function halt($message, $level = 'Normal') {

        return Response::halt($message, $level);
    }

    /**
     * 优雅输出print_r()函数所要输出的内容
     *
     * 用于程序调试时,完美输出调试数据,功能相当于print_r().当第二参数为true时(默认为:false),功能相当于var_dump()。注:本方法一般用于程序调试
     *
     * @access public
     *
     * @param string $data 所要输出的数据
     * @param boolean $option 选项:true(显示var_dump()的内容)/ false(显示print_r()的内容)
     *
     * @return string
     */
    public static function dump($data, $option = false) {

        return Response::dump($data, $option);
    }

    /**
     * 网址(URL)跳转操作
     *
     * 页面跳转方法，例:运行页面跳转到自定义的网址(即:URL重定向)
     *
     * @access public
     *
     * @param string $url 所要跳转的网址(URL)
     *
     * @return void
     */
    public static function redirect($url) {

        return Response::redirect($url);
    }

    /**
     * 获取当前项目的根目录的URL
     *
     * 本类方法常用于网页的CSS, JavaScript，图片等文件的调用
     *
     * @access public
     * @return string
     */
    public static function getBaseUrl() {

        return Router::getBaseUrl();
    }

    /**
     * 网址(URL)组装操作
     *
     * 注：组装绝对路径的URL
     *
     * @access public
     *
     * @param string $route controller与action。例：controllerName/actionName
     * @param array $params URL路由其它字段。注：url的参数信息
     *
     * @return string
     */
    public static function createUrl($route, $params = array()) {

        return Router::createUrl($route, $params);
    }

    /**
     * 获取当前运行的Action的URL
     *
     * 获取当前Action的URL. 注:该网址由当前的控制器(Controller)及动作(Action)组成。注：支持参数信息
     *
     * @access public
     *
     * @param array $params url路由其它字段。注：url的参数信息
     *
     * @return string
     */
    public static function getSelfUrl($params = array()) {

        //分析当前的路由信息
        $moduleName = Doit::getModuleName();
        $route      = ((!$moduleName) ? '' : $moduleName . URL_SEGEMENTATION) . Doit::getControllerName() . URL_SEGEMENTATION . Doit::getActionName();

        return self::createUrl($route, $params);
    }

    /**
     * 获取当前Controller内的某Action的url
     *
     * 获取当前控制器(Controller)内的动作(Action)的url。 注：该网址仅由项目入口文件和控制器(Controller)组成，支持其它参数信息
     *
     * @access public
     *
     * @param string $actionName 所要获取url的action的名称
     * @param array $params url路由其它字段。注：url的参数信息
     *
     * @return string
     */
    public static function getActionUrl($actionName, $params = array()) {

        //参数判断
        if (!$actionName) {
            return false;
        }

        //分析当前的路由信息
        $moduleName = Doit::getModuleName();
        $route      = ((!$moduleName) ? '' : $moduleName . URL_SEGEMENTATION) . Doit::getControllerName() . URL_SEGEMENTATION . $actionName;

        return self::createUrl($route, $params);
    }

    /**
     * 获取当前项目asset目录的url
     *
     * @example
     * $this->getAssetUrl();
     * 或
     * $this->getAssetUrl('images/thickbox');
     * 或
     * $this->getAssetUrl('images.thickbox');
     *
     * @access public
     *
     * @param string $dirName asset目录的子目录名
     *
     * @return string
     */
    public static function getAssetUrl($dirName = null) {

        //参数分析
        if ($dirName) {
            $dirName = str_replace('.', '/', $dirName);
        }

        //获取assets根目录的url
        $assetUrl = self::getBaseUrl() . '/assets';

        //分析assets目录下的子目录
        if (!is_null($dirName)) {
            $assetUrl .= '/' . $dirName;
        }

        return $assetUrl;
    }

    /**
     * 获取当前运行程序的域名网址
     *
     * @access public
     * @return string
     */
    public static function getServerName() {

        return Request::serverName();
    }

    /**
     * 获取客户端IP
     *
     * @access public
     * @return string
     */
    public static function getClientIp() {

        return Request::clientIp();
    }

    /**
     * 类的单例实例化操作
     *
     * 用于类的单例模式的实例化,当某类已经实例化，第二次实例化时则直接反回初次实例化的object,避免再次实例化造成的系统资源浪费。
     *
     * @access public
     *
     * @param string $className 所要实例化的类名
     *
     * @return object
     */
    public static function instance($className) {

        //参数判断
        if (!$className) {
            return false;
        }

        return Doit::singleton($className);
    }

    /**
     * 单例模式实例化一个Model对象
     *
     * 单例模式实现化一个model对象。初次实例化某Model后, 当第二次实例化时则直接调用初次实现化的结果(object)
     *
     * @access public
     *
     * @param string $modelName 所要实例化的Modle名称
     *
     * @return object
     */
    public static function model($modelName) {

        //参数判断
        if (!$modelName) {
            return false;
        }

        //分析model名
        $modelName = trim($modelName) . 'Model';

        return Doit::singleton($modelName);
    }

    /**
     * 加载并单例模式实例化扩展插件
     *
     * 注：这里所调用的扩展插件存放在extension目录里的子目录中。如：当加参数为demo,则子目录为名demo
     * ext是extension简写
     *
     * @access public
     *
     * @param string $extensionName 扩展插件名称
     *
     * @return object
     */
    public static function ext($extensionName) {

        //参数分析
        if (!$extensionName) {
            return false;
        }

        return Extension::loadExtension($extensionName);
    }

    /**
     * 静态加载文件
     *
     * 相当于inclue_once()。注：如果在模块的Controller文件中调用本模块的文件，可使用#_SELF_PATH代表本模块的根目录路径。
     *
     * @example
     * 例一：
     * $this->import('snoopy.php');
     *
     * 例二：
     * $this->import(BASE_PATH . '/extensions/editer/fck.php');
     *
     * 例三：在模块(Module)的Controller代码中加载application的library目录里的文件:snoopy.php
     * $this->import('snoopy.php', false);
     *
     * @access public
     *
     * @param string $fileName 所要加载的文件。注：默认目录为application的子目录：library
     *
     * @return void
     */
    public static function import($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        //统一路径分隔符
        $fileName = str_replace('\\', '/', $fileName);

        //当所要加载的文件具有详细的路径时
        if (strpos($fileName, '/') !== false) {
            //分析获取文件的路径
            $filePath = realpath($fileName);
        } else {
            $moduleName = Doit::getModuleName();
            //分析获取文件的路径
            $filePath   = realpath(BASE_PATH . ((!$moduleName) ? '' : '/modules/' . $moduleName) . '/library/' . $fileName);
        }

        return Doit::loadFile($filePath);
    }

    /**
     * 静态加载项目设置目录(config目录)中的配置文件
     *
     * 加载项目设置目录(config)中的配置文件,当第一次加载后,第二次加载时则不再重新加载文件。返回结果为配置文件内容，默认为数据格式为数组
     *
     * @access public
     *
     * @param string $configName 所要加载的配置文件名 注：不含后缀名
     *
     * @return array
     */
    public static function getConfig($configName) {

        return Configure::getConfig($configName);
    }

    /**
     * 设置视图文件布局结构视图的文件名(layout)
     *
     * @access public
     *
     * @param string $layoutName 所要设置的layout名称。默认值为:null，即：不使用layout视图
     *
     * @return boolean
     */
    public function setLayout($layoutName = null) {

        return self::$_viewObject->setLayout($layoutName);
    }

    /**
     * 分析并加载视图缓存
     *
     * @access public
     *
     * @param string $cacheId 页面文件的缓存ID
     * @param integer $expire 页面缓存的生存周期
     *
     * @return mixed
     */
    public function cache($cacheId = null, $expire = null) {

        return self::$_viewObject->cache($cacheId, $expire);
    }

    /**
     * 视图变量赋值操作
     *
     * @access public
     *
     * @param mixed $keys 视图变量名
     * @param mixed $value 视图变量值
     *
     * @return void
     */
    public function assign($keys, $value = null) {

        return self::$_viewObject->assign($keys, $value);
    }

    /**
     * 显示当前页面的视图内容
     *
     * 包括视图页面中所含有的挂件(widget), 视图布局结构(layout), 及render()所加载的视图片段等
     *
     * @access public
     *
     * @param string $fileName 视图名称
     *
     * @return string
     */
    public function display($fileName = null) {

        return self::$_viewObject->display($fileName);
    }

    /**
     * 调用视图文件的挂件(widget)
     *
     * 加载挂件内容，一般用在视图内容中
     *
     * @access public
     *
     * @param string $widgetName 所要加载的widget名称,注没有后缀名
     * @param array $params 所要传递的参数
     *
     * @return boolean
     */
    public static function widget($widgetName, $params = array()) {

        //参数分析
        if (!$widgetName) {
            return false;
        }

        //分析Widget名称
        $widgetName = trim($widgetName) . 'Widget';
        Doit::singleton($widgetName)->renderContent($params);

        return true;
    }

    /**
     * 加载并显示视图片段文件内容
     *
     * 相当于include 代码片段，当$return为:true时返回代码代码片段内容,反之则显示代码片段内容
     *
     * @access public
     *
     * @param string $fileName 视图片段文件名称
     * @param array $data 视图模板变量，注：数组型
     * @param boolean $return 视图内容是否为返回，当为true时为返回，为false时则为显示。 默认为:false
     *
     * @return string
     */
    public function render($fileName = null, $data = array(), $return = false) {

        return self::$_viewObject->render($fileName, $data, $return);
    }

    /**
     * Ajax调用返回的数据处理
     *
     * 返回json数据,供前台ajax调用
     *
     * @access public
     *
     * @param boolean $status 执行状态 : true/false 或 1/0
     * @param string $msg 返回信息
     * @param array $data 返回数据,支持数组
     *
     * @return string
     */
    public function ajax($status = true, $msg = null, $data = array()) {

        return Response::ajax($status, $msg, $data);
    }

    /**
     * 返回视图的实例化对象
     *
     * @access public
     * @return object
     */
    public static function getView() {

        return self::$_viewObject;
    }

    /**
     * 加载视图处理类并完成视图类的实例化
     *
     * 注：本类方法为回调类方法。通过在Controller Class的继承子类中重载本类方法，来实现自定义DoitPHP项目的视图机制的操作。
     *
     * @access protected
     * @return object
     */
    protected function initView() {

        //分析视图类文件路径
        $filePath = DOIT_ROOT . '/core/' . ((VIEW_EXT == Configure::VIEW_EXT_PHP) ? 'View.php' : 'Template.php');

        //加载视图处理类文件
        Doit::loadFile($filePath);

        //返回视图实例化对象
        return (VIEW_EXT == Configure::VIEW_EXT_PHP) ? View::getInstance() : Template::getInstance();
    }

    /**
     * Controller的前函数(类方法)
     *
     * 用于添加Action Method执行前的程序处理,相当于构造方法(被构造方法所调用)
     *
     * @access protected
     * @return boolean
     */
    protected function init() {

        return true;
    }

    /**
     * stripslashes()的同功能操作
     *
     * @access protected
     *
     * @param mixed $data 所要处理的变量名
     *
     * @return mixed
     */
    protected static function _stripSlashes($data = array()) {

        //参数分析
        if (!$data) {
            return $data;
        }

        if (!is_array($data)) {
            return stripslashes($data);
        }

        return array_map(array($this, '_stripSlashes'), $data);
    }

    /**
     * 自定义异常处理
     *
     * @access public
     *
     * @param object $exception 异常类的实例化对象
     *
     * @return string
     */
    public static function _exception($exception) {

        echo $exception->__toString();
    }

    /**
     * 自动变量获取
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的获取。
     *
     * @access public
     *
     * @param string $name name
     *
     * @return mixed
     */
    public function __get($name) {

        //参数分析
        $name = trim(strtolower($name));

        switch ($name) {

            case 'model':
                return Model::getInstance();

            case 'view':
                return $this->getView();

            case 'pager':
                return $this->instance('Pagination');

            case 'image':
                return $this->instance('Image');

            case 'cookie':
                return $this->instance('Cookie');

            case 'session':
                return $this->instance('Session');

            default:
                $this->halt('Undefined property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * 类方法自动调用引导
     *
     * 用于处理类外调用本类不存在的方法时的信息提示
     *
     * @access public
     *
     * @param string $method 类方法名称
     * @param array $args 所调用类方法的参数
     *
     * @return mixed
     */
    public function __call($method, $args) {

        switch ($method) {

            case 'encode':
                return call_user_func_array(array('Html', 'encode'), $args);

            case 'noCache':
                return Response::noCache();

            case 'charset':
                return call_user_func_array(array('Response', 'charset'), $args);

            case 'expires':
                return call_user_func_array(array('Response', 'expires'), $args);

            case 'env':
                return call_user_func_array(array('Request', 'env'), $args);

            case 'server':
                return call_user_func_array(array('Request', 'server'), $args);

            case 'files':
                return call_user_func_array(array('Request', 'files'), $args);

            default:
                $this->halt('The method: ' . $method . '() is not found in ' . get_class($this) . ' class!', 'Normal');
        }
    }
}