<?php
/**
 * 对cookie数据的管理操作
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cookie.php 2.0 2012-12-20 22:07:17Z tommy <tommy@doitphp.com> $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cookie {

    /**
     * Cookie存贮默认配置信息
     *
     * @var array
     */
    protected $_defaultOptions = array(
        'expire'    => 3600,
        'path'      => '/',
        'domain'    => null,
    );

    /**
     * Cookie的存贮设置选项
     *
     * @var array
     */
    protected $_options = null;

    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {
        $options = Configure::get('cookie');
        $this->_options = ($options && is_array($options)) ? $options : array();
        $this->_options += $this->_defaultOptions;

        return true;
    }

    /**
     * 获取某cookie变量的值
     *
     * @access public
     *
     * @param string $cookieName cookie变量名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public function get($cookieName, $default = null) {

        //参数分析
        if (!$cookieName) {
            return null;
        }
        if (!isset($_COOKIE[$cookieName])) {
            return $default;
        }

        if ($this->_options['secretkey']) {
            $value = Doit::singleton('Encrypt')->decode($_COOKIE[$cookieName], $this->_options['secretkey']);
            return unserialize($value);
        }

        return unserialize(base64_decode($_COOKIE[$cookieName]));
    }

    /**
     * 设置某cookie变量的值
     *
     * @access public
     *
     * @param string $cookieName cookie的变量名
     * @param mixed $value cookie值
     * @param integer $expire cookie的生存周期
     * @param string $path cookie所存放的目录
     * @param string $domain cookie所支持的域名
     *
     * @return boolean
     */
    public function set($cookieName, $value, $expire = null, $path = null, $domain = null) {

        //参数分析
        if (!$cookieName) {
            return false;
        }

        $expire = is_null($expire) ? $this->_options['expire'] : $expire;
        $path   = is_null($path) ? $this->_options['path'] : $path;
        $domain = is_null($domain) ? $this->_options['domain'] : $domain;

        $expire = $_SERVER['REQUEST_TIME'] + $this->_options['expire'];
        if ($this->_options['secretkey']) {
            $value = Doit::singleton('Encrypt')->encode(serialize($value), $this->_options['secretkey']);
        } else {
            $value = base64_encode(serialize($value));
        }

        setcookie($cookieName, $value, $expire, $path, $domain);
        $_COOKIE[$cookieName] = $value;

        return true;
    }

    /**
     * 删除某个Cookie变量
     *
     * @access public
     *
     * @param string $name cookie的名称
     *
     * @return boolean
     */
    public function delete($name) {

        //参数分析
        if (!$name) {
            return false;
        }

        self::set($name, null, '-3600');
        unset($_COOKIE[$name]);

        return true;
    }

    /**
     * 清空cookie
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        if (isset($_COOKIE)) {
            unset($_COOKIE);
        }

        return true;
    }

}