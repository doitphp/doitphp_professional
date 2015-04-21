<?php
/**
 * Redis数据库的驱动操作类
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cache_Redis.php 2.0 2012-12-30 19:48:01Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cache_Redis {

    /**
     * 单例模式实例化对象
     *
     * @var object
     */
    protected static $_instance;

    /**
     * 数据库连接ID
     *
     * @var object
     */
    protected $_Redis;

    /**
     * 数据库连接参数默认值
     *
     * @var array
     */
    protected $_defaultOptions = array(
        'host'       => '127.0.0.1',
        'port'       => '6379',
        'password'   => null,
        'database'   => 0,
        'persistent' => false,
        'expire'     => 900,
    );

    /**
     * 构造函数
     *
     * @access public
     *
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     *
     * @return boolean
     */
    public function __construct($options = null) {

        if (!extension_loaded('redis')) {
            Controller::halt('The redis extension to be loaded!');
        }

        //当参数为空时,程序则自动加载配置文件中数据库连接参数
        if (!$options || !is_array($options)) {
            $options = Configure::get('redis');
            if (!$options) {
                $options = array();
            }
        }

        $options += $this->_defaultOptions;
        //连接数据库
        $this->_Redis  = new Redis();
        $connect = (!$options['persistent']) ? 'connect' : 'pconnect';
        $return = $this->_Redis->$connect($options['host'], $options['port'], $options['expire']);

        if ($return && $options['password']) {
            $return = $this->_Redis->auth($options['password']);
        }
        if ($return && $options['database']) {
            $return = $this->_Redis->select($options['database']);
        }

        return $return;
    }

    /**
     * 设置数据值
     *
     * @access public
     *
     * @param string $key KEY名称
     * @param mixed $value 获取得到的数据
     * @param integer $expire 缓存的生存周期
     *
     * @return boolean
     */
    public function set($key, $value, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }

        $value  = json_encode($value);
        $result = $this->_Redis->set($key, $value);

        if ($expire > 0) {
            $this->_Redis->setTimeout($key, $expire);
        }

        return $result;
    }

    /**
     * 通过KEY获取数据
     *
     * @access public
     *
     * @param string $key 数据Key
     *
     * @return mixed
     */
    public function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        $value = $this->_Redis->get($key);
        return json_decode($value, true);
    }

    /**
     * 删除一条数据
     *
     * @access public
     *
     * @param string $key 数据key
     *
     * @return boolean
     */
    public function delete($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_Redis->delete($key);
    }

    /**
     * 清空数据
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        return $this->_Redis->flushAll();
    }

    /**
     * 数据入队列
     *
     * @access public
     *
     * @param string $key KEY名称
     * @param mixed $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     *
     * @return boolean
     */
    public function push($key, $value, $right = true) {

        //参数分析
        if (!$key) {
            return false;
        }

        $value = json_encode($value);
        return ($right == true) ? $this->_Redis->rPush($key, $value) : $this->_Redis->lPush($key, $value);
    }

    /**
     * 数据出队列
     *
     * @access public
     *
     * @param string $key KEY名称
     * @param bool $left 是否从左边开始出数据
     *
     * @return mixed
     */
    public function pop($key, $left = true) {

        //参数分析
        if (!$key) {
            return false;
        }

        $value = ($left == true) ? $this->_Redis->lPop($key) : $this->_Redis->rPop($key);
        return json_decode($value);
    }

    /**
     * 数据自增
     *
     * @access public
     *
     * @param string $key 数据key
     * @param integer $value 自增数据值
     *
     * @return boolean
     */
    public function increment($key, $value = 1) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_Redis->incrBy($key, $value);
    }

    /**
     * 数据自减
     *
     * @access public
     *
     * @param string $key 数据key
     * @param integer $value 自减数据值
     *
     * @return boolean
     */
    public function decrement($key, $value = 1) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_Redis->decrBy($key, $value);
    }

    /**
     * key是否存在，存在返回ture
     *
     * @access public
     *
     * @param string $key KEY名称
     *
     * @return boolean
     */
    public function exists($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        return $this->_Redis->exists($key);
    }

    /**
     * 返回redis对象
     *
     * @access public
     * @return object
     */
    public function getConnection() {

        return $this->_Redis;
    }

    /**
     * 单例模式
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