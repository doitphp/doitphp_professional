<?php
/**
 * Xcache Opcode缓存操作类
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cache_Xcache.class.php 2.0 2012-12-30 19:00:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cache_Xcache {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_defaultOptions = array('expire' => 900);

    /**
     * 构造方法
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //分析xcache扩展模块
        if (!extension_loaded('xcache')) {
            Controller::halt('The xcache extension to be loaded before use!');
        }

        return true;
    }

    /**
     * 写入缓存
     *
     * @access public
     *
     * @param string $key 缓存key
     * @param mixted $value 缓存值
     * @param integer $expire 生存周期
     *
     * @return boolean
     */
     public function set($key, $value, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }
        $expire = is_null($expire) ? $this->_defaultOptions['expire'] : $expire;

        return xcache_set($key, $value, $expire);
     }

    /**
     * 读取缓存，失败或缓存撒失效时返回 false
     *
     * @access public
     *
     * @param string $key 缓存key
     *
     * @return mixted
     */
     public function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        return xcache_isset($key) ? xcache_get($key) : false;
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

        return !xcache_isset($key) ? $this->set($key,$value,$expire) : false;
    }

    /**
     * 删除指定的缓存
     *
     * @access public
     *
     * @param string $key 缓存Key
     *
     * @return boolean
     */
     public function delete($key) {

         //参数分析
        if (!$key) {
            return false;
        }

        return xcache_unset($key);
     }

    /**
     * 清空全部缓存变量
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        return xcache_clear_cache(XC_TYPE_VAR, 0);
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