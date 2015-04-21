<?php
/**
 * 获取$_POST, $_GET参数
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Request.php 2.0 2012-11-28 23:25:26Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Request {

    /**
     * 获取并分析$_GET数组某参数值
     *
     * 获取$_GET的全局超级变量数组的某参数值,并进行转义化处理，提升代码安全。注:参数支持数组
     *
     * @access public
     *
     * @param string $key 所要获取$_GET的参数名称
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function get($key = null, $default = null, $isEncode = true) {

        //参数分析
        if (!$key) {
            return self::_gets($isEncode);
        }

        //当$_GET[$key]不存在时
        if (!isset($_GET[$key])) {
            return self::_encode($default, $isEncode);
        }

        if (!is_array($_GET[$key])) {
            $params = is_null($_GET[$key]) ? $default : $_GET[$key];
            return self::_encode($params, $isEncode);
        }

        //当$_GET[$key]其值为数组时
        $getArray = array();
        foreach ($_GET[$key] as $keys=>$values) {
            $getArray[$keys] = self::_encode($values, $isEncode);
        }

        return $getArray;
    }

    /**
     * 对字符串进行htmlspecailchars()转码
     *
     * @access protected
     * @param mixted $params 待转码的字符串
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     * @return string
     */
    protected static function _encode($params = null, $isEncode = true) {

        //参数分析
        if (is_null($params)) {
            return $params;
        }

        if ($isEncode === false) {
            return (!is_array($params)) ? trim($params) : array_map('trim', $params);
        }

        //当参数不为数组时
        if (!is_array($params)) {
            return trim(htmlspecialchars($params, ENT_QUOTES, 'UTF-8'));
        }

        return array_map(array('Request', '_encode'), $params);
    }

    /**
     * 获取并分析$_POST数组某参数值
     *
     * 获取$_POST全局变量数组的某参数值,并进行转义等处理，提升代码安全。注:参数支持数组
     *
     * @access public
     *
     * @param string $key 所要获取$_POST的参数名称
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function post($key = null, $default = null, $isEncode = true) {

        //参数分析
        if (!$key) {
            return self::_posts($isEncode);
        }

        //当$_POST[$key]不存在时
        if (!isset($_POST[$key])) {
            return self::_encode($default, $isEncode);
        }

        if (!is_array($_POST[$key])) {
            $params = is_null($_POST[$key]) ? $default : $_POST[$key];
            return self::_encode($params, $isEncode);
        }

        //当$_POST[$key]其值为数组时
        $postArray = array();
        foreach ($_POST[$key] as $keys=>$values) {
            $postArray[$keys] = self::_encode($values, $isEncode);
        }

        return $postArray;
    }

    /**
     * 获取并分析$_GET或$_POST全局超级变量数组某参数的值
     *
     * 获取并分析$_POST['参数']的值 ，当$_POST['参数']不存在或为空时，再获取$_GET['参数']的值
     *
     * @access public
     *
     * @param string $key 所要获取的参数名称
     * @param mixed $default 默认参数, 注:只有$string不为数组时有效
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return mixed
     */
    public static function getParams($key = null, $default = null, $isEncode = true) {

        //参数分析
        if (!$key) {
            return self::_params($isEncode);
        }

        $params = self::post($key, null, $isEncode);

        //当$_POST[$key]值为空时
        return (!is_null($params)) ? $params : self::get($key, $default, $isEncode);
    }

    /**
     * 获取PHP在CLI运行模式下的参数
     *
     * @access public
     *
     * @param string $key 参数键值, 注:不支持数组
     * @param mixed $default 默认参数值
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return string
     */
    public static function getCliParams($key = null, $default = null, $isEncode = true) {

        //参数分析
        if (!$key) {
            return self::_cliParams($isEncode);
        }

        //当前PHP运行模式不为CLI时
        if (PHP_SAPI != 'cli') {
            return false;
        }

        if (!isset($_SERVER['argv'][$key])) {
            return self::_encode($default, $isEncode);
        }

        $cliParams = is_null($_SERVER['argv'][$key]) ? $default : $_SERVER['argv'][$key];

        return self::_encode($cliParams, $isEncode);
    }

    /**
     * 获取当前运行程序的网址域名
     *
     * 如：http://www.doitphp.com
     *
     * @access public
     *
     * @return string    网址(域名)
     */
    public static function serverName() {

        //获取网址域名部分.
        $serverName = (!$_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : strtolower($_SERVER['HTTP_HOST']);
        $serverPort = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . (int)$_SERVER['SERVER_PORT'];

        return (self::isSecure() ? 'https://' : 'http://') . $serverName . $serverPort;
    }

    /**
     * 判断当前的网络协议是否为https安全请求
     *
     * @access public
     *
     * @return boolean
     */
    public static function isSecure() {

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
    }

    /**
     * 获取客户端IP
     *
     * @access public
     *
     * @param string $default 默认IP
     *
     * @return string
     */
    public static function clientIp($default = '0.0.0.0') {

        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (!isset($_SERVER[$key]) || !$_SERVER[$key]) {
                continue;
            }
            return htmlspecialchars($_SERVER[$key]);
        }

        return $default;
    }

    /**
     * 批量获取$_POST的参数
     *
     * @access private
     *
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return array
     */
    private static function _posts($isEncode = true) {

        $postArray = array();

        //当有$_POST数据存在时
        if (isset($_POST) && $_POST) {
            $keyArray = array_keys($_POST);
            foreach ($keyArray as $keyName) {
                $postArray[$keyName] = self::post($keyName, null, $isEncode);
            }
        }

        return $postArray;
    }

    /**
     * 批量获取$_GET的参数
     *
     * @access private
     *
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return array
     */
    private static function _gets($isEncode = true) {

        $getArray = array();

        //当有$_GET数据存在时
        if (isset($_GET) && $_GET) {
            $keyArray = array_keys($_GET);
            foreach ($keyArray as $keyName) {
                $getArray[$keyName] = self::get($keyName, null, $isEncode);
            }
        }

        return $getArray;
    }

    /**
     * 批量获取$_GET和$_POST的参数
     *
     * @access private
     *
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return array
     */
    private static function _params($isEncode = true) {

        //获取$_GET数据
        $getArray  = self::_gets($isEncode);

        //获取$_POST数据
        $postArray = self::_posts($isEncode);

        return $postArray + $getArray;
    }

    /**
     * 批量获取PHP在CLI运行模式下的所有参数
     *
     * @access private
     *
     * @param boolean $isEncode 是否对符串进行htmlspecialchars()转码（true：是/ false：否）
     *
     * @return array
     */
    private static function _cliParams($isEncode = true) {

        //当前PHP运行模式不为CLI时
        if (PHP_SAPI != 'cli') {
            return false;
        }

        $argvArray = array();
        if (isset($_SERVER['argv']) && $_SERVER['argv']) {
            $keyArray = array_keys($_SERVER['argv']);
            foreach ($keyArray as $keyName) {
                $argvArray[$keyName] = self::getCliParam($keyName, null, $isEncode);
            }
        }

        return $argvArray;
    }

    /**
     * 获取全局变量$_SERVER的参数值
     *
     * @access public
     *
     * @param string $key 参数键值, 注:不支持数组
     * @param mixed $default 默认参数值
     *
     * @return string
     */
    public static function server($key = null, $default = null) {

        //参数分析
        if (!$key) {
            return $_SERVER;
        }

        return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }

    /**
     * 获取全局变量$_FILES的参数值
     *
     * @access public
     *
     * @param string $key 参数键值, 注:不支持数组
     * @param mixed $default 默认参数值
     *
     * @return string
     */
    public static function files($key = null, $default = null) {

        //参数分析
        if (!$key) {
            return $_FILES;
        }

        return isset($_FILES[$key]) ? $_FILES[$key] : $default;
    }

    /**
     * 获取全局变量$_ENV的参数值
     *
     * @access public
     *
     * @param string $key 参数键值, 注:不支持数组
     * @param mixed $default 默认参数值
     *
     * @return string
     */
    public static function env($key = null, $default = null) {

        //参数分析
        if (!$key) {
            return $_ENV;
        }

        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }

    /**
     * 判断是否为ajax调用
     *
     * @access public
     * @return boolean
     */
    public static function isAjax() {
        return (self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest') ? true : false;
    }

    /**
     * 判断是否为POST调用
     *
     * @access public
     * @return boolean
     */
    public static function isPost() {
        return (self::server('REQUEST_METHOD') == 'POST') ? true : false;
    }

    /**
     * 判断是否为GET调用
     *
     * @access public
     * @return boolean
     */
    public static function isGet() {
        return (self::server('REQUEST_METHOD') == 'GET') ? true : false;
    }
}