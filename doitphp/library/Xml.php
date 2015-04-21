<?php
/**
 * XML文件的操作类
 *
 * 注:本类生成xml文件只限内容极为简单数据,如操作复杂数据请用php5.0自带的simpleXml函数组
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Xml.php 2.0 2012-12-29 19:40:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Xml {

    /**
     * 加载xml文件.支持文件名及xml代码.
     *
     * @access public
     *
     * @param string $fileName xml文件名或xml代码内容
     *
     * @return string
     */
    public static function loadXml($fileName) {

        if (!$fileName) {
            return false;
        }

        return (strpos($fileName, '<?xml')===false) ? simplexml_load_file($fileName) : simplexml_load_string($fileName);
    }

    /**
     * 将XML代码转化为数组
     *
     * @access public
     *
     * @param string $string xml代码内容或xml文件的路径
     *
     * @return array
     */
    public static function xmlDecode($string) {

        //参数分析
        if (!$string) {
            return false;
        }

        $xml = self::loadXml($string);

        return json_decode(json_encode($xml), true);
    }

    /**
     * 数据转化为xml代码.
     *
     * @access protected
     *
     * @param array $data xml内容数组
     *
     * @return string
     */
    protected static function _dataToXml($data) {

        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml.="\t<$key>";
            $xml.= (is_array($val) || is_object($val)) ? self::_dataToXml($val) : str_replace(array("&","<",">","\"", "'", "-"), array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&#45;"), $val);
            list($key, ) = explode(' ', $key);
            $xml.="</$key>\n";
        }

        return $xml;
    }

    /**
     * 进行对xml编码.
     *
     * @param string $data xml内容数组
     * @param string $root
     * @return string
     */
    public static function xmlEncode($data, $root = null, $encoding = 'UTF-8') {

        if (!$data) {
            return false;
        }

        $root = is_null($root) ? 'root' : trim($root);
        $xml  = "<?xml version=\"1.0\" encoding=\"{$encoding}\" ?>\n";
        $xml .= "<".$root.">\n";
        $xml .= self::_dataToXml($data);
        $xml .= "</".$root.">";

        return $xml;
    }
}