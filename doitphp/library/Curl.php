<?php
/**
 * 常用的CURL操作(GET、POST)
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Curl.php 2.0 2012-12-22 21:45:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Curl {

    /**
     * 浏览器的AGENT信息
     *
     * @var string
     */
    protected static $_userAgent = null;

    /**
     * cookie的存贮文件路径
     *
     * @var string
     */
    protected static $_cookieFilePath = null;

    /**
     * 是否curl get获取页面信息时支持cookie存贮
     *
     * @var boolean
     */
    protected static $_cookieSupport = false;


    /**
     * 设置浏览器的AGENT信息
     *
     * @access public
     *
     * @param string $userAgent 浏览器的AGENT信息
     *
     * @return object
     */
    public function setUserAgent($userAgent) {

        //参数分析
        if (!$userAgent) {
            return false;
        }

        self::$_userAgent = $userAgent;

        return $this;
    }

    /**
     * 设置cookie的存贮文件路径
     *
     * @access public
     *
     * @param string $filePath 存贮cookie的文件路径
     *
     * @return object
     */
    public function setCookieFile($filePath) {

        //参数分析
        if (!$filePath) {
            return false;
        }

        self::$_cookieSupport  = true;
        self::$_cookieFilePath = $filePath;

        return $this;
    }

    /**
     * 设置cookie功能是否开启
     *
     * @access public
     *
     * @param boolean $isOn 是否开启
     *
     * @return object
     */
    public function setCookieStatus($isOn = true) {

        self::$_cookieSupport = $isOn;

        return $this;
    }

    /**
     * 用CURL模拟获取网页页面内容
     *
     * @access public
     *
     * @param string $url     所要获取内容的网址
     * @param array  $data    所要提交的数据
     * @param string $proxy   代理设置
     * @param integer $expire 时间限制
     *
     * @return string
     *
     * @example
     *
     * $url = 'http://www.doitphp.com/';
     *
     * $curl = new Curl();
     * $curl ->get($url);
     */
    public static function get($url, $data = array(), $proxy = null, $expire = 30) {

        //参数分析
        if (!$url) {
            return false;
        }

        //分析是否开启SSL加密
        $ssl = strtolower(substr($url, 0, 8)) == 'https://' ? true : false;

        //读取网址内容
        $ch = curl_init();

        //设置代理
        if (!$proxy) {
            curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        }

        //分析网址中的参数
        if ($data) {
            $paramUrl = http_build_query($data, '', '&');
            $extStr   = (strpos($url, '?') !== false) ? '&' : '?';
            $url      = $url . (($paramUrl) ? $extStr . $paramUrl : '');
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($ssl) {
            // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        }

        if (self::$_cookieSupport === true) {
            $cookieFile = self::_parseCookieFile();
            //cookie设置
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }

        //设置浏览器
        if (self::$_userAgent || $_SERVER['HTTP_USER_AGENT']) {
            curl_setopt($ch, CURLOPT_USERAGENT, (!self::$_userAgent) ? $_SERVER['HTTP_USER_AGENT'] : self::$_userAgent);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    /**
     * 获取cookie存贮文件的路径
     *
     * @access protected
     * @return stirng
     */
    protected static function _parseCookieFile() {

        //分析cookie数据存贮文件
        if (self::$_cookieFilePath) {
            return self::$_cookieFilePath;
        }

        return CACHE_PATH . '/temp/' . md5('doitphp_curl_cookie') . '.txt';
    }

    /**
     * 用CURL模拟提交数据
     *
     * @access public
     *
     * @param string $url        post所要提交的网址
     * @param array  $data       所要提交的数据
     * @param string $proxy      代理设置
     * @param integer $expire    所用的时间限制
     *
     * @return string
     */
    public static function post($url, $data = array(), $proxy = null, $expire = 30) {

        //参数分析
        if (!$url) {
            return false;
        }

        //分析是否开启SSL加密
        $ssl = strtolower(substr($url, 0, 8)) == 'https://' ? true : false;

        //读取网址内容
        $ch = curl_init();

        //设置代理
        if (!$proxy) {
            curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        if ($ssl) {
            // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        }

        if (self::$_cookieSupport === true) {
            $cookieFile = self::_parseCookieFile();
            //cookie设置
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }

        //设置浏览器
        if (self::$_userAgent || $_SERVER['HTTP_USER_AGENT']) {
            curl_setopt($ch, CURLOPT_USERAGENT, (!self::$_userAgent) ? $_SERVER['HTTP_USER_AGENT'] : self::$_userAgent);
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        //发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POST, true);
        //Post提交的数据包
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        //使用自动跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $expire);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}