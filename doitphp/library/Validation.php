<?php
/**
 * 常用的正则表达式来验证信息
 *
 * 如:网址 邮箱 手机号等
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Validation.php 2.0 2012-12-22 23:50:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Validation {

    /**
     * 正则表达式验证email格式
     *
     * @param string $str    所要验证的邮箱地址
     * @return boolean
     */
    public static function isEmail($str) {

        if (!$str) {
            return false;
        }

        return preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str) ? true : false;
    }

    /**
     * 正则表达式验证网址
     *
     * @param string $str    所要验证的网址
     * @return boolean
     */
    public static function isUrl($str) {

        if (!$str) {
            return false;
        }

        return preg_match('#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str) ? true : false;
    }

    /**
     * 验证字符串中是否含有汉字
     *
     * @param integer $string    所要验证的字符串。注：字符串编码仅支持UTF-8
     * @return boolean
     */
    public static function isChineseCharacter($string) {

        if (!$string) {
            return false;
        }

        return preg_match('~[\x{4e00}-\x{9fa5}]+~u', $string) ? true : false;
    }

    /**
     * 验证字符串中是否含有非法字符
     *
     * @param string $string    待验证的字符串
     * @return boolean
     */
    public static function isInvalidStr($string) {

        if (!$string) {
            return false;
        }

        return preg_match('#[!\#$%^&*(){}~`"\';:?+=<>/\[\]]+#', $string) ? true : false;
    }

    /**
     * 用正则表达式验证邮证编码
     *
     * @param integer $num    所要验证的邮政编码
     * @return boolean
     */
    public static function isPostNum($num) {

        if (!$num) {
            return false;
        }

        return preg_match('#^[1-9][0-9]{5}$#', $num) ? true : false;
    }

    /**
     * 正则表达式验证身份证号码
     *
     * @param integer $num    所要验证的身份证号码
     * @return boolean
     */
    public static function isPersonalCard($num) {

        if (!$num) {
            return false;
        }

        return preg_match('#^[\d]{15}$|^[\d]{18}$#', $num) ? true : false;
    }

    /**
     * 正则表达式验证IP地址, 注:仅限IPv4
     *
     * @param string $str    所要验证的IP地址
     * @return boolean
     */
    public static function isIp($str) {

        if (!$str) {
            return false;
        }

        if (!preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str)) {
            return false;
        }

        $ipArray = explode('.', $str);

        //真实的ip地址每个数字不能大于255（0-255）
        return ($ipArray[0]<=255 && $ipArray[1]<=255 && $ipArray[2]<=255 && $ipArray[3]<=255) ? true : false;
    }

    /**
     * 用正则表达式验证出版物的ISBN号
     *
     * @param integer $str    所要验证的ISBN号,通常是由13位数字构成
     * @return boolean
     */
    public static function isBookIsbn($str) {

        if (!$str) {
            return false;
        }

        return preg_match('#^978[\d]{10}$|^978-[\d]{10}$#', $str) ? true : false;
    }

    /**
     * 用正则表达式验证手机号码(中国大陆区)
     * @param integer $num    所要验证的手机号
     * @return boolean
     */
    public static function isMobile($num) {

        if (!$num) {
            return false;
        }

        return preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $num) ? true : false;
    }

    /**
     * 检查字符串是否为空
     *
     * @access public
     * @param string $string 字符串内容
     * @return boolean
     */
    public static function isMust($string = null) {

        //参数分析
        if (is_null($string)) {
            return false;
        }

        return is_null($string) ? false : true;
    }

    /**
     * 检查字符串长度
     *
     * @access public
     * @param string $string 字符串内容
     * @param integer $min 最小的字符串数
     * @param integer $max 最大的字符串数
     */
    public static function isLength($string = null, $min = 0, $max = 255) {

        //参数分析
        if (is_null($string)) {
            return false;
        }
        //获取字符串长度
        $length = (strlen($string) + mb_strlen($string, 'UTF8')) / 2;

        return (($length >= (int)$min) && ($length <= (int)$max)) ? true : false;
    }
}