<?php
/**
 * 挂件(Widget)视图处理类
 *
 * 注:本类仅用于HTML格式的视图文件的处理
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: WidgetTemplate.php 2.0 2012-12-16 23:12:00Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class WidgetTemplate extends Template {

    /**
     * widget的名称,默认为null
     *
     * @var string
     */
    public $widgetId = null;


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

        //获取Widget文件目录路径
        if (!$moduleName) {
            $widgetPath = BASE_PATH . DS . 'widgets' . DS;
        } else {
            $widgetPath = BASE_PATH . DS . 'modules' . DS . $moduleName . DS . 'widgets' . DS;
        }

        //设置挂件视图模板目录的路径
        $this->_viewPath = $widgetPath . 'views' . DS;

        //设置视图编译缓存文件的默认目录路径
        $this->_compilePath = CACHE_PATH . DS . 'views' . DS . 'widgets' . DS;

        return true;
    }

    /**
     * 设置视图文件布局结构视图的文件名(layout)
     *
     * 挂件(Widget)的视图机制不支持layout视图
     *
     * @access public
     *
     * @param string $layoutName 所要设置的layout名称。默认值为:null，即：不使用layout视图
     *
     * @return boolean
     */
    public function setLayout($layoutName = null) {

        return false;
    }

    /**
     * 分析并加载视图缓存
     *
     * 注：挂件(Widget)的视图机制不支持视图缓存
     *
     * @access public
     *
     * @param string $cacheId 页面文件的缓存ID
     * @param integer $expire 页面缓存的生存周期
     *
     * @return mixed
     */
    public function cache($cacheId = null, $expire = null) {

        return false;
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

        //分析挂件视图名称
        $fileName = (!$fileName) ? $this->widgetId : $fileName;

        //获取视图模板文件及编译文件的路径
        $viewFile    = $this->_getViewFile($fileName);
        $compileFile = $this->_getCompileFile($fileName);

        //分析视图编译文件是否需要重新生成
        if ($this->_isCompile($viewFile, $compileFile)) {
            $templateContent = $this->_loadViewFile($viewFile);
            //重新生成编译缓存文件
            $this->_createCompileFile($compileFile, $templateContent);
        }

        //加载编译缓存文件
        ob_start();
        include $compileFile;
        $widgetContent = ob_get_clean();

        //返回信息
        if (!$return) {
            echo $widgetContent;
        } else {
            return $widgetContent;
        }
    }

    /**
     * 显示当前页面的视图内容
     *
     * 注：挂件(Widget)的视图机制不支持Layout视图
     *
     * @access public
     *
     * @param string $fileName 视图名称
     *
     * @return string
     */
    public function display($fileName = null) {

        //参数分析
        if (!$fileName) {
            $fileName = $this->widgetId;
        }

        //模板变量赋值
        if ($this->_options) {
            extract($this->_options, EXTR_PREFIX_SAME, 'data');
            $this->_options = array();
        }

        //获取视图模板文件及编译文件的路径
        $viewFile    = $this->_getViewFile($fileName);
        $compileFile = $this->_getCompileFile($fileName);

        //分析视图编译文件是否需要重新生成
        if ($this->_isCompile($viewFile, $compileFile)) {
            $templateContent = $this->_loadViewFile($viewFile);
            //重新生成编译缓存文件
            $this->_createCompileFile($compileFile, $templateContent);
        }

        //加载编译缓存文件
        ob_start();
        include $compileFile;
        $widgetContent = ob_get_clean();

        //显示挂件视图内容
        echo $widgetContent;
    }

    /**
     * 获取视图编译文件的路径
     *
     * @access protected
     *
     * @param string $fileName 视图名. 注:不带后缀
     *
     * @return string
     */
    protected function _getCompileFile($fileName) {

        return $this->_compilePath . $fileName . '.widget.compilecache.php';
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