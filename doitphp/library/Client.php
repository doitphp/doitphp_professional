<?php
/**
 * client class file
 *
 * 获取客户端IP地址,操作系统,浏览器信息等
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Client.php 1.3 2011-11-11 20:50:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Client {

    /**
     * 获取客户端系统语言
     *
     * @access public
     * @return string
     */
    public static function getLanguage() {

        return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']) : null;
    }

    /**
     * 获取当前页面的url来源
     *
     * @access public
     * @return string
     */
    public static function getUrlSource() {

        return isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : null;
    }

    /**
     * 获取客户端浏览器信息.
     *
     * @access public
     * @return string
     */
    public static function getBrowserAgent() {

        return isset($_SERVER['HTTP_USER_AGENT']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT']) : null;
    }

    /**
     * 获取客户端浏览器信息
     *
     * @access public
     * @return string
     */
    public static function getBrowser() {

        $userAgentInfo = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);

        $browserArray = array(
        'MSIE 11.0' => 'IE11',
        'MSIE 10.0' => 'IE10',
        'MSIE 9.0'  => 'IE9',
        'MSIE 8.0'  => 'IE8',
        'MSIE 7.0'  => 'IE7',
        'MSIE 6.0'  => 'IE6',
        'Firefox'   => 'Firefox',
        'Chrome'    => 'Chrome',
        'Safari'    => 'Safari',
        'Elinks'    => 'Elinks',
        'OmniWeb'   => 'OmniWeb',
        'Links'     => 'Links',
        'Lynx'      => 'Lynx',
        'Arora'     => 'Arora',
        'Epiphany'  => 'Epiphany',
        'Konqueror' => 'Konqueror',
        'EudoraWeb' => 'EudoraWeb',
        'Minimo'    => 'Minimo',
        'NetFront'  => 'NetFront',
        'POLARIS'   => 'Polaris',
        'BlackBerry'=> 'BlackBerry',
        'Nokia'     => 'Nokia',
        );

        foreach ($browserArray as $key=>$value) {
            if(strpos($userAgentInfo,$key)) {
                return $value;
            }
        }

        return 'Others';
    }

    /**
     * 获取客户端操作系统信息
     *
     * @access public
     * @return string
     */
    public static function getOs() {

        $userAgentInfo = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);

        $OsArray = array(
        'Windows NT 6.3'  => 'Windows 8.1',
        'Windows NT 6.2'  => 'Windows 8',
        'Windows NT 6.1'  => 'Windows 7',
        'Windows NT 5.1'  => 'Windows XP',
        'Android'         => 'Android',
        'iPhone'          => 'iPhone',
        'Symbian'         => 'Symbian',
        'Mac OS X'        => 'Mac OS X',
        'Windows Phone'   => 'Windows Phone',
        'Ubuntu'          => 'Ubuntu',
        'Debian'          => 'Debian',
        'Fedora'          => 'Fedora',
        'redhat'          => 'RedHat',
        'Linux'           => 'Linux',
        'FreeBSD'         => 'FreeBSD',
        'SunOS'           => 'SunOS',
        'OpenBSD'         => 'OpenBSD',
        'NetBSD'          => 'NetBSD',
        'Mac OS X Mach-O' => 'OS X Mach',
        'Windows NT 6.0'  => 'Windows Vista',
        'Windows NT 5.2'  => 'Windows 2003',
        'Windows NT 5.0'  => 'Windows 2000',
        'Windows ME'      => 'Windows ME',
        'PPC Mac OS X'    => 'OS X PPC',
        'Intel Mac OS X'  => 'OS X Intel',
        'Win98'           => 'Windows 98',
        'Win95'           => 'Windows 95',
        'WinNT4.0'        => 'Windows NT4.0',
        'AppleWebKit'     => 'WebKit',
        'Mint/8'          => 'Mint 8',
        'Minefield'       => 'Minefield Alpha',
        'gentoo'          => 'Gentoo',
        'Kubuntu'         => 'Kubuntu',
        'Slackware/13.0'  => 'Slackware 13',
        'DragonFly'       => 'DragonFly',
        'IRIX'            => 'IRIX',
        'Windows CE'      => 'Windows CE',
        'PalmOS'          => 'PalmOS',
        'DragonFly'       => 'DragonFly',
        'webOS'           => 'webOS',
        'PalmSource'      => 'PalmSource',
        );

        foreach ($OsArray as $key=>$value) {
            if(strpos($userAgentInfo,$key)) {
                return $value;
            }
        }

        return 'Others';
    }
}