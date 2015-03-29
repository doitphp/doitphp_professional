<?php
/**
 * doitPHP视图处理类
 *
 * 注:本类仅用于php格式的视图文件的处理
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: View.php 2.0 2012-12-16 12:55:22Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class View {

    /**
     * 单例模式实现化对象
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 视图布局名称(layout)
     *
     * @var string
     */
    protected $_layout = null;

    /**
     * 视图目录路径
     *
     * @var string
     */
    protected $_viewPath = null;

    /**
     * 视图变量数组
     *
     * @var array
     */
    protected $_options = array();

    /**
     * 视图缓存文件
     *
     * @var string
     */
    protected $_cacheFile = null;

    /**
     * 视图缓存重写开关
     *
     * @var boolean
     */
    protected $_cacheStatus = false;

    /**
     * 构造方法（函数）
     *
     * 用于初始化程序运行环境，或对基本变量进行赋值
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //获取当前的模块名称
        $moduleName = Doit::getModuleName();
        if (!$moduleName) {
            $viewDirName = 'views';
        } else {
            $viewDirName = 'modules/' . $moduleName . '/views';
        }

        //设置当前视图的默认目录路径
        $this->_viewPath = BASE_PATH . DS . $viewDirName;

        return true;
    }

    /**
     * 设置视图文件布局结构的文件名(layout)
     *
     * 注：layout默认为:null
     *
     * @access public
     *
     * @param string $layoutName 所要设置的layout名称
     *
     * @return boolean
     */
    public function setLayout($layoutName = null) {

        //参数分析
        if (!$layoutName) {
            return false;
        }

        $this->_layout = $layoutName;

        return true;
    }

    /**
     * 分析视图缓存文件是否需要重新创建
     *
     * @access public
     *
     * @param string $cacheId 缓存ID
     * @param integer $expire 缓存文件生存周期, 默认为一年
     *
     * @return boolean
     */
    public function cache($cacheId = null, $expire = null) {

        //参数分析
        if (!$cacheId) {
            $cacheId = Doit::getActionName();
        }
        if (!$expire) {
            $expire = 31536000;
        }

        //获取视图缓存文件
        $cacheFile = $this->_parseCacheFile($cacheId);
        if (is_file($cacheFile) && (filemtime($cacheFile) + $expire >= time())) {
            include $cacheFile;
            exit();
        }

        $this->_cacheStatus = true;
        $this->_cacheFile   = $cacheFile;

        return true;
    }

    /**
     * 视图变量赋值操作
     *
     * @access public
     *
     * @param mixed $keys 视图变量名
     * @param mixed $value 视图变量值
     *
     * @return mixed
     */
    public function assign($keys, $value = null) {

        //参数分析
        if (!$keys) {
            return false;
        }

        if (!is_array($keys)) {
            $this->_options[$keys] = $value;
            return true;
        }

        foreach ($keys as $handle=>$lines) {
            $this->_options[$handle] = $lines;
        }

        return true;
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

        //分析视图文件路径
        $viewFile = $this->_parseViewFile($fileName);

        //模板变量赋值
        if ($this->_options) {
            extract($this->_options, EXTR_PREFIX_SAME, 'data');
            $this->_options = array();
        }

        //获取当前视图的页面内容
        ob_start();
        include $viewFile;
        $viewContent = ob_get_clean();

        //分析,加载,显示layout视图内容
        if ($this->_layout) {
            $layoutFile = $this->_viewPath . '/layout/' . $this->_layout . VIEW_EXT;
            if (is_file($layoutFile)) {
                ob_start();
                include $layoutFile;
                $viewContent = ob_get_clean();
            }
        }

        //显示视图文件内容
        echo $viewContent;

        //当缓存重写开关开启时,创建缓存文件
        if ($this->_cacheStatus === true) {
            $this->_createCache($viewContent);
        }
    }

    /**
     * 加载并显示视图片段文件内容
     *
     * 相当于include 代码片段，当$return为:true时返回代码代码片段内容,反之则显示代码片段内容。注：本方法不支持layout视图
     *
     * @access public
     *
     * @param string $fileName 视图片段文件名称
     * @param array $data 视图模板变量，注：数组型
     * @param boolean $return 是否有返回数据。true:返回数据/false:没有返回数据，默认：false
     *
     * @return string
     */
    public function render($fileName = null, $data = array(), $return = false) {

        //分析视图文件路径
        $viewFile = $this->_parseViewFile($fileName);

        //模板变量赋值
        if ($data && is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
            unset($data);
        } else {
            //当且仅当本方法在处理action视图(非视图片段)时，对本类assign()所传递的视图变量进行赋值
            if (!$fileName && $this->_options) {
                extract($this->_options, EXTR_PREFIX_SAME, 'data');
                $this->_options = array();
            }
        }

        //获取当前视图的页面内容(视图片段)
        ob_start();
        include $viewFile;
        $viewContent = ob_get_clean();

        //返回信息
        if (!$return) {
            echo $viewContent;
        } else {
            return $viewContent;
        }
    }

    /**
     * 分析视图文件路径
     *
     * 获取视图的路径,便于程序进行include操作。注:本方法不支持视图布局结构(layout)
     *
     * @access publice
     *
     * @param string $fileName 视图文件名。注:名称中不带.php后缀。
     *
     * @return string
     */
    protected function _parseViewFile($fileName = null) {

        //参数分析
        if (!$fileName) {
            $viewFileName = Doit::getControllerName() . DS . Doit::getActionName();
        } else {
            $fileName     = str_replace('.', '/', $fileName);
            $viewFileName = (strpos($fileName, '/') === false) ? Doit::getControllerName() . DS . $fileName : $fileName;
        }

        $viewPath = $this->_viewPath . DS . $viewFileName . VIEW_EXT;

        //分析视图文件是否存在
        if (!is_file($viewPath)) {
            Controller::halt("The view File: {$viewPath} is not found!", 'Normal');
        }

        return $viewPath;
    }

    /**
     * 调用视图挂件(widget)
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

        return Controller::widget($widgetName, $params);
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

        //参数分析
        if (!$route) {
            return false;
        }

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

        return Controller::getSelfUrl($params);
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

        //参数分析
        if (!$actionName) {
            return false;
        }

        return Controller::getActionUrl($actionName, $params);
    }

    /**
     * 获取当前项目asset目录的url
     *
     * @access public
     *
     * @param string $dirName asset目录的子目录名
     *
     * @return string
     */
    public static function getAssetUrl($dirName = null) {

        return Controller::getAssetUrl($dirName);
    }

    /**
     * 获取当前的视图目录的路径
     *
     * @access public
     * @return string
     */
    public function getViewPath() {

        return $this->_viewPath;
    }

    /**
     * 设置当前的视图目录路径
     *
     * @access public
     *
     * @param string $viewPath 视图目录的路径
     *
     * @return boolean
     */
    public function setViewPath($viewPath) {

        //参数分析
        if (!$viewPath) {
            return false;
        }

        $this->_viewPath = $viewPath;

        return true;
    }

    /**
     * 创建视图的缓存文件
     *
     * @access protected
     *
     * @param string $content 缓存文件内容
     *
     * @return boolean
     */
    protected function _createCache($content = null) {

        //判断当前的缓存文件路径
        if (!$this->_cacheFile) {
            return false;
        }

        //参数分析
        if (is_null($content)) {
            $content = '';
        }

        //分析缓存目录
        $cacheDir = dirname($this->_cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        return file_put_contents($this->_cacheFile, $content, LOCK_EX);
    }

    /**
     * 分析视图缓存文件名
     *
     * @access protected
     *
     * @param string $cacheId 视图文件的缓存ID
     *
     * @return string
     */
    protected function _parseCacheFile($cacheId) {

        return CACHE_PATH . '/htmls/' . (!Doit::getModuleName() ? '' : Doit::getModuleName() . DS) . Doit::getControllerName() . DS . md5($cacheId) . '.action.html';
    }

    /**
     * 析构方法（函数）
     *
     * 当本类程序运行结束后，用于打扫战场，如：清空无效的内存占用等
     *
     * @access public
     * @return boolean
     */
    public function __destruct() {

        //重值
        $this->_options     = array();
        $this->_cacheStatus = false;

        return true;
    }

    /**
     * 单例模式实例化当前模型类
     *
     * @access public
     * @return object
     */
    public static function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}