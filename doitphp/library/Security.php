<?php
/**
 * 安全相关类
 *
 * 用于过滤XSS(跨网站攻击)攻击代码、令牌密码验证等操作
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Security.php 2.0 2012-12-29 15:57:21Z tommy <tommy@doitphp.com> $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Security {

    /**
     * Token生存时间周期
     *
     * @var integer
     */
    protected static $_expire = 7200;

    /**
     * 加密字符串(密钥)
     *
     * @var string
     */
    protected static $_key = 'your-secret-code';

    /**
     * 过滤XSS(跨网站攻击)代码
     *
     * 通常用于富文本提交内容的过滤。提升网站安全必备
     *
     * @access public
     *
     * @param string $string 待过滤的内容
     *
     * @return string
     */
    public static function removeXss($string) {

        //参数分析
        if (!$string) {
            return $string;
        }

        if (is_array($string)) {
            return array_map(array('Security', 'removeXss'), $string);
        }

        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $string = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $string);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search= 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // @ @ search for the hex values
            $string = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $string); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $string = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $string); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $stringBefore = $string;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                     $pattern .= '(';
                     $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                     $pattern .= '|';
                     $pattern .= '|(&#0{0,8}([9|10|13]);)';
                     $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $string = preg_replace($pattern, $replacement, $string); // filter out the hex tags
                if ($stringBefore == $string) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }

        return $string;
    }

    /**
     * 生成令牌密码
     *
     * @access public
     *
     * @param string $string 所要加密的字符(也可以是随机的)
     * @param string $expire 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     *
     * @return string
     */
    public static function getToken($string, $expire = null, $key = null) {

        //参数分析
        if (!$string) {
            return false;
        }

        //设置token生存周期及附加加密码
        $expire = (!$expire) ? self::$_expire : $expire;
        $key    = (!$key) ? self::$_key : $key;
        $per    = ceil($_SERVER['REQUEST_TIME'] / $expire);

        return hash_hmac('md5', $per . $string, $key);
    }

    /**
     * 令牌密码验证
     *
     * @access public
     *
     * @param string $string 所要加密的字符(也可以是随机的)
     * @param string $tokenCode 所要验证的加密字符串
     * @param string $expire 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     *
     * @return boolean
     */
    public function checkToken($string, $tokenCode, $expire = null, $key = null) {

        //参数分析
        if (!$string || !$tokenCode) {
            return false;
        }

        //设置token生存周期及附加加密码
        $expire = (!$expire) ? self::$_expire : $expire;
        $key    = (!$key) ? self::$_key : $key;
        $per    = ceil($_SERVER['REQUEST_TIME'] / $expire);

        //获取token值
        $sourceToken = hash_hmac('md5', $per . $string, $key);

        return ($sourceToken == $tokenCode) ? true : false;
    }
}