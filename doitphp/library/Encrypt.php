<?php
/**
 * 数据的加密,解密
 *
 * @author tommy <tommy@doitphp.com>, 付超群
 * @copyright Copyright (c) 2010 Tommycode Studio, ColaPHP
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Encrypt.php 2.0 2012-12-29 16:20:14Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Encrypt {

    /**
     * config data
     *
     * @var array
     */
    protected $_config = array();

    /**
     * 加密字符串(密钥)
     *
     * @var string
     */
    protected static $_key = 'your-secret-code';

    /**
     * 构造方法
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //set config infomation
        $this->_config = array(
        'hash'      => 'sha1',
        'xor'       => false,
        'mcrypt'    => function_exists('mcrypt_encrypt') ? true : false,
        'noise'     => true,
        'cipher'    => MCRYPT_RIJNDAEL_256,
        'mode'      => MCRYPT_MODE_ECB
        );

        return true;
    }

    /**
     * 设置或获取配置参数($_config)信息
     *
     * @access public
     *
     * @param mixed $key 键值
     * @param mixed $value 参数值
     *
     * @return mixed
     */
    public function config($key = null, $value = null) {

        if (is_null($key)) {
            return $this->_config;
        }

        if (is_array($key)) {
            $this->_config = $key + $this->_config;
            return $this;
        }

        if (is_null($value)) {
            return $this->_config[$key];
        }

        $this->_config[$key] = $value;

        return $this;
    }

    /**
     * 加密
     *
     * @access public
     *
     * @param string $string 待加密的字符串
     * @param string $key 密钥
     *
     * @return string
     */
    public function encode($string, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if ($this->_config['xor']) {
            $string = $this->_xorEncode($string, $key);
        }

        if ($this->_config['mcrypt']) {
            $string = $this->_mcryptEncode($string, $key);
        }

        if ($this->_config['noise']) {
            $string = $this->_noise($string, $key);
        }

        return base64_encode($string);
    }

    /**
     * 解密
     *
     * @access public
     *
     * @param string $string 待解密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    public function decode($string, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string)) {
            return false;
        }

        $string = base64_decode($string);

        if ($this->_config['noise']) {
            $string = $this->_denoise($string, $key);
        }

        if ($this->_config['mcrypt']) {
            $string = $this->_mcryptDecode($string, $key);
        }

        if ($this->_config['xor']) {
            $string = $this->_xorDecode($string, $key);
        }

        return $string;
    }

    /**
     * Mcrypt encode
     *
     * @access protected
     *
     * @param string $string 待加密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _mcryptEncode($string, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return mcrypt_encrypt($cipher, $key, $string, $mode, $vect);
    }

    /**
     * Mcrypt decode
     *
     * @access protected
     *
     * @param string $string 待解密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _mcryptDecode($string, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return rtrim(mcrypt_decrypt($cipher, $key, $string, $mode, $vect), "\0");
    }

    /**
     * XOR encode
     *
     * @access protected
     *
     * @param string $string 待加密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _xorEncode($string, $key) {

        $rand = $this->_config['hash'](rand());
        $code = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $r     = substr($rand, ($i % strlen($rand)), 1);
            $code .= $r . ($r ^ substr($string, $i, 1));
        }

        return $this->_xor($code, $key);
    }

    /**
     * XOR decode
     *
     * @access protected
     *
     * @param string $string 待解密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _xorDecode($string, $key) {

        $string = $this->_xor($string, $key);
        $code   = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $code .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
        }

        return $code;
    }

    /**
     * XOR
     *
     * @access protected
     *
     * @param string $string 待加密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _xor($string, $key) {

        $hash = $this->_config['hash']($key);
        $code = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $code .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
        }

        return $code;
    }

    /**
     * Noise
     *
     * @access protected
     *
     * @see http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
     *
     * @param string $string 待加密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _noise($string, $key) {

        $hash    = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen  = strlen($string);
        $code    = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $code .= chr((ord($string[$i]) + ord($hash[$j])) % 256);
        }

        return $code;
    }

    /**
     * Denoise
     *
     * @access protected
     *
     * @param string $string 待解密的字符串
     * @param string $key 附加码
     *
     * @return string
     */
    protected function _denoise($string, $key) {

        $hash    = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen  = strlen($string);
        $code    = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $temp = ord($string[$i]) - ord($hash[$j]);
            if ($temp < 0) $temp = $temp + 256;
            $code .= chr($temp);
        }

        return $code;
    }

    /**
     * 生成随机码
     *
     * @access public
     *
     * @param integer $length 随机码长度 (0~32)
     *
     * @return string
     */
    public static function randCode($length = 5) {

        //参数分析
        $length = (int)$length;
        $length = ($length > 32) ? 32 : $length;

        $code  = md5(uniqid(mt_rand(), true));
        $start = mt_rand(0, 32 - $length);

        return substr($code, $start, $length);
    }
}