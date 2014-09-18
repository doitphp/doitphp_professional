<?php
/**
 * 文件缓存操作类
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cache_File.php 2.0 2012-12-30 21:04:41Z tommy <streen003@gmail.com> $
 * @package cache
 * @since 1.0
 */

class Cache_File {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 缓存目录
     *
     * @var string
     */
     protected $_cachePath = null;

    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_defaultOptions = array('expire' => 86400);

    /**
     * 构造方法
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->_cachePath = CACHE_PATH . DS . 'data' . DS;

        return true;
    }

    /**
     * 设置缓存
     *
     * @access public
     *
     * @param string $key 缓存数据key
     * @param mixed $value 缓存数据值
     * @param string $expire 缓存生存周期
     *
     * @return boolean
     */
    public function set($key, $value, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }
        $expire   = is_null($expire) ? $this->_defaultOptions['expire'] : $expire;
        $expire   = time() + $expire;

        //分析缓存文件
        $filePath = $this->_parseCacheFile($key);

        //分析缓存内容
        $content  = '<?php if(!defined(\'IN_DOIT\'))exit(); return array(' . $expire . ', ' . var_export($value, true) . ');';

        return File::writeFile($filePath, $content);
    }

    /**
     * 获取缓存数据
     *
     * @access public
     *
     * @param string $key 缓存数据key
     *
     * @return mixed
     */
    public function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        //分析缓存文件
        $filePath = $this->_parseCacheFile($key);

        if (!is_file($filePath)) {
            return false;
        }

        $data = include $filePath;

        //当缓存文件过期时
        if (time() > $data[0]) {
            unlink($filePath);
            return false;
        }

        return $data[1];
    }

    /**
     * 缓存一个变量到数据存储
     *
     * @access public
     *
     * @param string $key 数据key
     * @param mixed $value 数据值
     * @param int $expire 缓存时间(秒)
     *
     * @return boolean
     */
    public function add($key, $value, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }
        $expire = is_null($expire) ? $this->_defaultOptions['expire'] : $expire;

        return !$this->get($key) ? $this->set($key, $value, $expire) : false;
    }

    /**
     * 删除一条缓存数据
     *
     * @access public
     *
     * @param string $key 缓存数据key
     *
     * @return boolean
     */
    public function delete($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        //分析缓存文件
        $filePath = $this->_parseCacheFile($key);

        return is_file($filePath) ? unlink($filePath) : true;
    }

    /**
     * 清空所有的文件缓存
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        $fileList = File::readDir($this->_cachePath);

        //删除所有的缓存文件
        foreach ($fileList as $fileName) {
            if (strpos($fileName, '.filecache.php') !== false) {
                unlink($this->_cachePath . $fileName);
            }
        }

        return true;
    }

    /**
     * 分析缓存文件名
     *
     * @access protected
     *
     * @param string $key 缓存数据key
     *
     * @return string
     */
    protected function _parseCacheFile($key) {

        return $this->_cachePath . md5($key) . '.filecache.php';
    }

    /**
     * 单例模式
     *
     * 用于本类的单例模式(singleton)实例化
     *
     * @access public
     * @return object
     */
    public static function getInstance() {

        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}