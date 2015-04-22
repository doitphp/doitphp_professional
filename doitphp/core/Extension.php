<?php
/**
 * 扩展模块基类
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Extension.php 2.0 2012-12-19 23:29:01Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Extension extends Controller {

    /**
     * 扩展模块实例化对象存贮数组
     *
     * @var object
     */
    private static $_extensionObjArray = array();

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
     * 加载视图处理类并完成视图类的实例化
     *
     * 注：本类方法为回调类方法。
     *
     * @access protected
     * @return object
     */
    protected function initView() {

        return null;
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

        return false;
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

        return false;
    }

    /**
     * 获取当前扩展目录的路径
     *
     * @access protected
     *
     * @param string $extensionName 扩展插件名称
     *
     * @return string
     */
    protected static function _getExtRoot($extensionName) {

        return BASE_PATH . '/extensions/' . $extensionName;
    }

    /**
     * 获取当前扩展目录的路径
     *
     * @access public
     * @return string
     */
    public function getExtRoot() {

        return BASE_PATH . '/extensions/' . $this->getExtName();
    }

    /**
     * 获取当前的扩展模块的名称
     *
     * @access public
     * @return string
     */
    public function getExtName() {

        return substr(get_class($this), 0, -3);
    }

    /**
     * 加载并单例模式实例化扩展模块（通常为第三方程序）
     *
     *  注：这里所调用的扩展模声要放在项目extension目录里的子目录中
     *
     * @access public
     *
     * @param string $extensionName 扩展插件名称
     *
     * @access object
     */
    final public static function loadExtension($extensionName) {

        //参数分析
        if (!$extensionName) {
            return false;
        }

        //当所加载的扩展模块还示被实例化时
        if (!isset(self::$_extensionObjArray[$extensionName])) {

            //加载扩展模块的引导文件(index)
            $extensionPath = self::_getExtRoot($extensionName) . DS . $extensionName . 'Ext.php';
            Doit::loadFile($extensionPath);

            self::$_extensionObjArray[$extensionName] = Doit::singleton($extensionName . 'Ext');
        }

        return self::$_extensionObjArray[$extensionName];
    }
}