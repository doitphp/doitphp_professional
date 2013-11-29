<?php
/**
 * APC Opcode缓存操作类
 *
 * @author Jessica<cndingo@qq.com>
 * @copyright  Copyright (c) 2009 Jessica(董立强)
 * @link http://www.doitphp.com
 * @license Licensed under the MIT license
 * @version $Id: Cache_Apc.class.php 2.0 2012-12-30 18:53:01Z Jessica$
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cache_Apc {

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

        if (!extension_loaded('apc')) {
            Controller::halt('The apc extension to be loaded.');
        }

        return true;
    }

    /**
     * 设置一个缓存变量
     *
     * @access public
     *
     * @param string $key 缓存Key
     * @param mixed $value 缓存内容
     * @param int $expire 缓存时间(秒)
     *
     * @return boolean
     */
    public function set($key, $value, $expire = null) {

        //参数分析
        if ($key) {
            return false;
        }
        $expire = is_null($expire) ? $this->_defaultOptions['expire'] : $expire;

        return apc_store($key, $value, $expire);
    }

    /**
     * 获取一个已经缓存的变量
     *
     * @access public
     *
     * @param string $key  缓存Key
     *
     * @return mixed
     */
    public function get($key) {

        //参数分析
        if ($key) {
            return false;
        }

        return apc_fetch($key);
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

        return apc_add($key, $value, $expire);
    }

    /**
     * 删除一个已经缓存的变量
     *
     * @access public
     *
     * @param stirng $key 所要删除的数据key
     *
     * @return boolean
     */
    public function delete($key) {

        //参数分析
        if ($key) {
            return false;
        }

        return apc_delete($key);
    }

    /**
     * 清空全部缓存变量
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        return apc_clear_cache('user');
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
