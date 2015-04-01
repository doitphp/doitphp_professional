<?php
/**
 * 对cookie数据的管理操作
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
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
    protected static $_defaultConfig = array(
        'expire' => 3600,
        'path'   => '/',
        'domain' => null,
    );

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
    public static function get($cookieName, $default = null) {

        //参数分析
        if (!$cookieName) {
            return null;
        }
        if (!isset($_COOKIE[$cookieName])) {
            return $default;
        }

        return unserialize(base64_decode($_COOKIE[$cookieName]));
    }

    /**
     * 设置某cookie变量的值
     *
     * @access public
     *
     * @param string $name cookie的变量名
     * @param mixed $value cookie值
     * @param integer $expire cookie的生存周期
     * @param string $path cookie所存放的目录
     * @param string $domain cookie所支持的域名
     *
     * @return boolean
     */
    public static function set($name, $value, $expire = null, $path = null, $domain = null) {

        //参数分析
        if (!$name) {
            return false;
        }

        //获取cookie的配置信息
        if (is_null($expire)) {
            $configExpire = Configure::get('cookie.expire');
            $expire       = (!$configExpire) ? self::$_defaultConfig['expire'] : $configExpire;
        }
        if (is_null($path)) {
            $configPath = Configure::get('cookie.path');
            $path       = (!$configPath) ? self::$_defaultConfig['path'] : rtrim($configPath, '/') . '/';
        }
        if (is_null($domain)) {
            $configDomain = Configure::get('cookie.domain');
            $domain       = (!$configDomain) ? self::$_defaultConfig['domain'] : $configDomain;
        }

        $expire = $_SERVER['REQUEST_TIME'] + $expire;
        $value  = base64_encode(serialize($value));

        setcookie($name, $value, $expire, $path, $domain);
        $_COOKIE[$name] = $value;

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
    public static function delete($name) {

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
    public static function clear() {

        if (isset($_COOKIE)) {
            unset($_COOKIE);
        }

        return true;
    }

}