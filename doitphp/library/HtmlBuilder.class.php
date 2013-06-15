<?php
/**
 * 批量生成HTML静态文件类
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  CopyRight DoitPHP team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: HtmlBuilder.class.php 2.0 2012-12-23 19:07:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class HtmlBuilder {

    /**
     * 静态文件的存放目录
     *
     * @var string
     */
    protected $_htmlPath;

    /**
     * HTML重写功能开启状态
     *
     * @var boolean
     */
    protected $_isWrite;

    /**
     * HTML文件名
     *
     * @var string
     */
    protected $_fileName;

    /**
     * 构造方法
     *
     * @access publid
     * @return boolean
     */
    public function __construct() {

        $this->_htmlPath     = CACHE_PATH . 'htmls' . DIRECTORY_SEPARATOR;
        $this->_isWrite      = false;

        File::makeDir($this->_htmlPath);

        return true;
    }

    /**
     * 设置HTML存放目录
     *
     * @access public
     *
     * @params string $path HTML文件存路径
     *
     * @return object
     */
    public function setPath($path) {

        if ($path) {
            $this->_htmlPath = $path;
        }

        return $this;
    }

    /**
     * 页面缓存开启
     *
     * @access public
     *
     * @param string $fileName    文件名
     *
     * @return void
     */
    public function start($fileName = null) {

        //parse file name
        $fileName = $this->_getFilePath($fileName);

        //分析重写开关
        if(is_file($fileName)) {
            include $fileName;
            exit();
        }

        $this->_isWrite  = true;
        $this->_fileName = $fileName;

        ob_start();
    }

    /**
     * 页面缓存结束
     *
     * @access public
     * @return string
     */
    public function end() {

        //获取页面内容
        $htmlContent = ob_get_clean();

        if ($this->_fileName && ($this->_isWrite === true)) {

            File::writeFile($this->_fileName, $htmlContent, LOCK_EX);
        }

        echo $htmlContent;
    }

    /**
     * 获取生成的文件的路径
     *
     * @access protected
     *
     * @param string $fileName 文件名
     *
     * @return string
     */
    protected function _getFilePath($fileName = null) {

        //参数分析
        if (!$fileName) {
        	$fileName = Doit::getActionName();
        }

        $moduleName = Doit::getModuleName();

        return $this->_htmlPath . (!$moduleName ? '' : $moduleName . DIRECTORY_SEPARATOR) . Doit::getControllerName() . DIRECTORY_SEPARATOR . $fileName . '.html';
    }

    /**
     * 将url页面内容写入html文件
     *
     * @access public
     *
     * @param string $fileName 所要生成的HTML文件名(注：不带.html后缀)
     * @param string $url 所要生成HTML的页面的URL
     *
     * @return boolean
     */
    public function createHtml($fileName, $url) {

        //parse params
        if (!$fileName || !$url) {
            return false;
        }

        $fileName = $this->_htmlPath . $fileName . '.html';

        ob_start();
        //获取并显示url的页面内容
        echo file_get_contents($url);

        return File::writeFile($fileName, ob_get_clean());
    }
}