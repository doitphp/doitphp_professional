<?php
/**
 * doitPHP视图处理类
 *
 * 注:本类仅用于HTML格式的视图文件的处理
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Template.php 2.0 2012-12-16 17:32:22Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Template {

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
     * 视图编译缓存目录
     *
     * @var string
     */
    protected $_compilePath = null;

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
     * 模板标签左侧边限字符
     *
     * @var string
     */
    public $leftDelimiter = '<!--\s?{';

    /**
     * 模板标签右侧边限字符
     *
     * @var string
     */
    public $rightDelimiter = '}\s?-->';


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
            $viewDirName = 'modules' . DS . $moduleName . DS . 'views';
        }

        //设置当前视图的默认目录路径
        $this->_viewPath = BASE_PATH . DS . $viewDirName;

        //设置视图编译缓存文件的默认目录路径
        $this->_compilePath = CACHE_PATH . DS . 'views' . DS;

        return true;
    }

    /**
     * 设置视图文件布局结构的文件名(layout)
     *
     * layout默认为:null
     *
     * @access public
     *
     * @param string $layoutName 所要设置的layout名称
     *
     * @return boolean
     */
    public function setLayout($layoutName = null) {

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

        //模板变量赋值
        if ($this->_options) {
            extract($this->_options, EXTR_PREFIX_SAME, 'data');
            $this->_options = array();
        }

        //分析视图文件名
        $fileName    = $this->_parseViewName($fileName);

        //获取视图模板文件及编译文件的路径
        $viewFile    = $this->_getViewFile($fileName);
        $compileFile = $this->_getCompileFile($fileName);

        //分析视图编译文件是否需要重新生成
        if ($this->_isCompile($viewFile, $compileFile)) {
            $templateContent = $this->_loadViewFile($viewFile);
            //重新生成编译缓存文件
            $this->_createCompileFile($compileFile, $templateContent);
        }

        //分析layout视图
        if ($this->_layout) {
            $layoutFile   = $this->_viewPath . DS . 'layout' . DS . $this->_layout . VIEW_EXT;
            $layoutStatus = is_file($layoutFile) ? true : false;
        } else {
            $layoutStatus = false;
        }

        //当没有使用layout视图时
        if (!$layoutStatus) {
            //加载编译缓存文件
            ob_start();
            include $compileFile;
            $viewContent = ob_get_clean();
        } else {
            //加载layout文件
            $layoutCompileFile = $this->_getCompileFile('layout' . DS . $this->layout);
            if ($this->_isCompile($layoutFile, $layoutCompileFile)) {
                //重新生成layout视图编译文件
                $layoutContent = $this->_loadViewFile($layoutFile);
                $this->_createCompileFile($layoutCompileFile, $layoutContent);
            }

            //获取视图编译文件内容
            ob_start();
            include $compileFile;
            $viewContent = ob_get_clean();

            //获取所要显示的页面的视图编译内容
            ob_start();
            include $layoutCompileFile;
            $viewContent = ob_get_clean();
        }

        //显示视图内容
        echo $viewContent;

        //创建视图缓存文件
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

        //分析视图文件名
        $viewName    = $this->_parseViewName($fileName);

        //获取视图模板文件及编译文件的路径
        $viewFile    = $this->_getViewFile($viewName);
        $compileFile = $this->_getCompileFile($viewName);

        //分析视图编译文件是否需要重新生成
        if ($this->_isCompile($viewFile, $compileFile)) {
            $templateContent = $this->_loadViewFile($viewFile);
            //重新生成编译缓存文件
            $this->_createCompileFile($compileFile, $templateContent);
        }

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

        //加载编译缓存文件
        ob_start();
        include $compileFile;
        $viewContent = ob_get_clean();

        //返回信息
        if (!$return) {
            echo $viewContent;
        } else {
            return $viewContent;
        }
    }

    /**
     * 加载并显示视图片段文件内容
     *
     * 用于处理视图标签include的视图内容
     *
     * @access protected
     *
     * @param string $fileName 视图片段文件名称
     *
     * @return string
     */
    protected function _include($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        return $this->render($fileName);
    }

    /**
     * 生成视图编译文件
     *
     * @access protected
     *
     * @param string $compileFile 编译文件名
     * @param string $content    编译文件内容
     *
     * @return void
     */
    protected function _createCompileFile($compileFile, $content) {

        //分析编译文件目录
        $compileDir = dirname($compileFile);
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0777, true);
        }

        $content = "<?php if(!defined('IN_DOIT')) exit(); ?>\n" . $content;

        return file_put_contents($compileFile, $content, LOCK_EX);
    }

    /**
     * 加载视图文件
     *
     * 加载视图文件并对视图标签进行编译
     *
     * @access protected
     *
     * @param string $viewFile 视图文件及路径
     *
     * @return string
     */
    protected function _loadViewFile($viewFile) {

        //分析视图文件是否存在
        if (!is_file($viewFile)) {
            Controller::halt("The view file: {$viewFile} is not found!", 'Normal');
        }

        $viewContent = file_get_contents($viewFile);

        //编译视图标签
        return $this->_handleViewFile($viewContent);
    }

    /**
     * 编译视图标签
     *
     * @access protected
     *
     * @param string $viewContent 视图(模板)内容
     *
     * @return string
     */
    protected function _handleViewFile($viewContent) {

        //参数分析
        if (!$viewContent) {
            return false;
        }

        //正则表达式匹配的模板标签
        $regexArray = array(
        '#'.$this->leftDelimiter.'\s*include\s+(.+?)\s*'.$this->rightDelimiter.'#is',
        '#'.$this->leftDelimiter.'php\s+(.+?)'.$this->rightDelimiter.'#is',
        '#'.$this->leftDelimiter.'\s?else\s?'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s?\/if\s?'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s?\/loop\s?'.$this->rightDelimiter.'#i',
        );

        ///替换直接变量输出
        $replaceArray = array(
        "<?php \$this->_include('\\1'); ?>",
        "<?php \\1 ?>",
        "<?php } else { ?>",
        "<?php } ?>",
        "<?php } } ?>",
        );

        //对固定的视图标签进行编辑
        $viewContent = preg_replace($regexArray, $replaceArray, $viewContent);

        //处理if, loop, 变量等视图标签
        $patternArray = array(
        '#'.$this->leftDelimiter.'\s*(\$.+?)\s*'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s?(if\s.+?)\s?'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s?(elseif\s.+?)\s?'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s?(loop\s.+?)\s?'.$this->rightDelimiter.'#i',
        '#'.$this->leftDelimiter.'\s*(widget\s.+?)\s*'.$this->rightDelimiter.'#is',
        );
        $viewContent = preg_replace_callback($patternArray, array($this, '_parseTags'), $viewContent);

        return $viewContent;
    }

    /**
     * 分析编辑视图标签
     *
     * @access protected
     *
     * @param string $tag 视图标签
     *
     * @return string
     */
    protected function _parseTags($tag) {

        //变量分析
        $tag = stripslashes(trim($tag[1]));

        //当视图标签为空时
        if(!$tag) {
            return '';
        }

        //变量标签处理
        if (substr($tag, 0, 1) == '$') {
            return '<?php echo ' . $this->_getVal($tag) . '; ?>';
        }

        //分析判断,循环标签
        $tagSel = array_shift(explode(' ', $tag));
        switch ($tagSel) {

            case 'if' :
                return $this->_compileIfTag(substr($tag, 3));
                break;

            case 'elseif' :
                return $this->_compileIfTag(substr($tag, 7), true);
                break;

            case 'loop' :
                return $this->_compileForeachStart(substr($tag, 5));
                break;

            case 'widget' :
                return $this->_compileWidgetTag(substr($tag, 7));
                break;

            default :
                return $tagSel;
        }
    }

    /**
     * 处理if标签
     *
     * @access public
     *
     * @param string $tagArgs 标签内容
     * @param bool $elseif 是否为elseif状态
     *
     * @return  string
     */
    protected function _compileIfTag($tagArgs, $elseif = false) {

        //分析标签内容
        preg_match_all('#\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S#i', $tagArgs, $match);
        $tokens = $match[0];

        //当$match[0]不为空时
        $tokenArray = array_map(array($this, '_getVal'), $match[0]);
        $tokenString = implode(' ', $tokenArray);
        //清空不必要的内存占用
        unset($tokenArray);

        return ($elseif === false) ? '<?php if (' . $tokenString . ') { ?>' : '<?php } else if (' . $tokenString . ') { ?>';
    }

    /**
     * 处理foreach标签
     *
     * @access protected
     *
     * @param string $tagArgs 标签内容
     *
     * @return string
     */
    protected function _compileForeachStart($tagArgs) {

        //分析标签内容
        preg_match_all('#(\$.+?)\s+(.+)#i', $tagArgs, $match);
        $loopVar = $this->_getVal($match[1][0]);

        return '<?php if (is_array(' . $loopVar . ')) { foreach (' . $loopVar . ' as ' . $match[2][0] . ') { ?>';
    }

    /**
     * 处理widget标签
     *
     * @access public
     *
     * @param string $tagArgs 标签内容
     *
     * @return string
     */
    protected function _compileWidgetTag($tagArgs) {

        //判断是否为参数传递标签
        $pos = strpos($tagArgs, '$');

        if ($pos !== false) {
            $widgetId  = trim(substr($tagArgs, 0, $pos));
            $params    = $this->_getVal(trim(substr($tagArgs, $pos)));

            return '<?php Controller::widget(\'' . $widgetId . '\', ' . $params . '); ?>';
        }

        return '<?php Controller::widget(\'' . $tagArgs . '\'); ?>';
    }

    /**
     * 处理视图标签中的变量标签
     *
     * @access protected
     *
     * @param string $val 标签名
     *
     * @return string
     */
    protected function _getVal($val) {

        //当视图变量不为数组时
        if (strpos($val, '.') === false) {
            return $val;
        }

        $valArray = explode('.', $val);
        $_varName = array_shift($valArray);

        return $_varName . "['" . implode("']['", $valArray) . "']";
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
     * 获取视图文件的路径
     *
     * @access protected
     *
     * @param string $fileName    视图名. 注：不带后缀
     *
     * @return string    视图文件路径
     */
    protected function _getViewFile($fileName) {

        return $this->_viewPath . $fileName . VIEW_EXT;
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

        return $this->_compilePath . $fileName . '.action.compilecache.php';
    }

    /**
     * 分析视图文件名
     *
     * @access publice
     *
     * @param string $fileName 视图文件名。注:名称中不带.php后缀。
     *
     * @return string
     */
    protected function _parseViewName($fileName = null) {

        //参数分析
        if (!$fileName) {
            return Doit::getControllerName() . DS . Doit::getActionName();
        }

        $fileName = str_replace('.', '/', $fileName);
        if (strpos($fileName, '/') === false) {
            $fileName = Doit::getControllerName() . DS . $fileName;
        }

        return $fileName;
    }

    /**
     * 缓存重写分析
     *
     * 判断缓存文件是否需要重新生成. 返回true时,为需要;返回false时,则为不需要
     *
     * @access protected
     *
     * @param string $viewFile 视图文件名
     * @param string $compileFile 视图编译文件名
     *
     * @return boolean
     */
    protected function _isCompile($viewFile, $compileFile) {

        return (is_file($compileFile) && (filemtime($compileFile) >= filemtime($viewFile))) ? false : true;
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

        return CACHE_PATH . DS . 'htmls' . DS . (!Doit::getModuleName() ? '' : Doit::getModuleName() . DS) . Doit::getControllerName() . DS . md5($cacheId) . '.action.html';
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
     * 析构方法（函数）
     *
     * 当本类程序运行结束后，用于打扫战场，如：清空无效的内存占用等
     *
     * @access public
     * @return boolean
     */
    public function __destruct() {

        //重值
        $this->_options = array();
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