<?php
/**
 * Memcache 缓存操作类
 *
 * 注:部分代码参考了Qeephp 2.1 memecached.php代码
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cache_Memcache.php 2.0 2012-12-30 18:00:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

/**
 * 使用说明
 *
 * @author tommy<tommy@doitphp.com>
 *
 * @example
 *
 * 参数范例
 * $memOptions = array(
 *     'servers'=> array(
 *         array('host'=>'127.0.0.1', 'port'=>11211, 'persistent'=>true, 'weight'=>1, 'timeout'=>60),
 *         array('host'=>'192.168.0.101', 'port'=>11211, 'persistent'=>true, 'weight'=>2, 'timeout'=>60),
 *     ),
 *     'compressed'=>true,
 *     'expire' => 3600,
 *     'persistent' => true,
 * );
 *
 * 实例化
 *
 * 法一:
 * $memcache = new Cache_Memcache($memOptions);
 *
 */

class Cache_Memcache {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * Memcached实例
     *
     * @var objeact
     */
    private $_dbLink;

    /**
     * 默认的缓存服务器
     *
     * @var array
     */
    protected $_defaultServer = array(
        /**
         * 缓存服务器地址或主机名
         */
        'host' => '127.0.0.1',

        /**
         * 缓存服务器端口
         */
        'port' => '11211',
    );

    /**
     * 默认的缓存策略
     *
     * @var array
     */
    protected $_defaultOptions = array(

        /**
         * 缓存服务器配置,参看$_defaultServer
         * 允许多个缓存服务器
         */
        'servers' => array(),

        /**
         * 是否压缩缓存数据
         */
        'compressed' => false,

        /**
         * 缓存有效时间
         *
         * 如果设置为 0 表示缓存永不过期
         */
        'expire' => 900,

        /**
         * 是否使用持久连接
         */
        'persistent' => true,
    );

    /**
     * 构造方法
     *
     * @access public
     *
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     *
     * @return boolean
     */
    public function __construct($options = null) {

        //分析memcache扩展模块的加载
        if (!extension_loaded('memcache')) {
            Controller::halt('The memcache extension to be loaded before use!');
        }

        //获取Memcache服务器连接参数
        if (!$options || !is_array($options)) {
            $options = Configure::get('memcache');
        }

        if (is_array($options) && $options) {
            $this->_defaultOptions = $options + $this->_defaultOptions;
        }

        if (!$this->_defaultOptions['servers']) {
            $this->_defaultOptions['servers'][] = $this->_defaultServer;
        }

        $this->_dbLink = new Memcache();

        foreach ($this->_defaultOptions['servers'] as $server) {
            $server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => true);
            $this->_dbLink->addServer($server['host'], $server['port'], $this->_defaultOptions['persistent']);
        }

        return true;
    }

    /**
     * 写入缓存
     *
     * @access public
     *
     * @param string $key 缓存Key
     * @param mixed $data 缓存内容
     * @param int $expire 缓存时间(秒)
     *
     * @return boolean
     */
    public function set($key, $data, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }
        if (is_null($expire)) {
            $expire = $this->_defaultOptions['expire'];
        }

        return $this->_dbLink->set($key, $data, (!$this->_defaultOptions['compressed']) ? 0 : MEMCACHE_COMPRESSED, $expire);
    }

    /**
     * 读取缓存,失败或缓存撒失效时返回false
     *
     * @access public
     *
     * @param string $key 所要读取数据的key
     *
     * @return mixed
     */
    public function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_dbLink->get($key);
    }

    /**
     * 删除指定的缓存
     *
     * @access public
     *
     * @param string $key 所要删除数据的Key
     *
     * @return boolean
     */
    public function delete($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_dbLink->delete($key);
    }

    /**
     * 添加一条数据
     *
     * @access public
     *
     * @author ColaPHP
     * @param string $key 数据key
     * @param integer $value 数据值
     *
     * @return boolean
     */
    public function add($key, $value = 1) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_dbLink->increment($key, $value);
    }

    /**
     * 数据自减
     *
     * @access public
     *
     * @param string $key 数据key
     * @param integer $value 数据值
     *
     * @return boolean
     */
    public function decr($key, $value = 1) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_dbLink->decrement($key, $value);
    }

    /**
     * 清除所有的缓存数据
     *
     * @access public
     * @return boolean
     */
     public function clear() {

          return $this->_dbLink->flush();
     }

    /**
     * 返回redis对象
     *
     * @access public
     * @return object
     */
    public function getConnection() {

        return $this->_dbLink;
    }

     /**
      * 获取memcache server状态
      *
      * @access public
      * @return string
      */
    public function stats() {

        return $this->_dbLink->getStats();
     }

     /**
      * 析构函数
      *
      * @access public
      * @return boolean
      */
     public function __destruct() {

         if ($this->_dbLink) {
             $this->_dbLink->close();
         }

         return true;
     }

    /**
     * 单例模式
     *
     * 用于本类的单例模式(singleton)实例化
     *
     * @access public
     *
     * @param array $params 数据库连接参数
     *
     * @return object
     */
    public static function getInstance($params = null) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }
}