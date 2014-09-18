<?php
/**
 * JS代码片段生成类
 *
 * 主要用于前台提交表单时对表单元素的检查
 * 注：本类函数所返回的数据均为js片段。要结合jquery来使用
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Form.php 2.0 2012-12-23 16:03:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Form {

    /**
     * 检查文本框内容是否为空
     *
     * 在表单提交时,用JS验证某 input 的text输入框内容是否为空
     * 本函数通过解析为相关的JS代码，通过JS来判断的.主要用于前台页面
     *
     * @access public
     *
     * @param string $objTag 所要验证的input的文本输入框的选择器标签
     * @param string $info 提示信息
     * @param string $resutlTag 用于显示提示信息的区域的选择器标签。注：本参数可为空,当为空时,则默认为alert('提示信息')。
     *
     * @return string
     */
    public static function isEmpty($objTag, $info, $resutlTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        $string  = "if($('" . $objTag . "').val() == ''){";
        $string .= (!is_null($resutlTag)) ? "$('" . $resutlTag . "').html('" . $info . "').show();" : "alert('" . $info . "');";
        $string .= "$('" . $objTag . "').focus();$('" . $objTag . "').css('border-color', '#C00');return false;}";

        return $string;
    }

    /**
     * 检查两个文本输入框内容是否一致
     *
     * 常用于密码和确认密码的验证
     *
     * @access public
     *
     * @param string $objTag 选择器标签（密码）
     * @param string $objConfirmTag 选择器标签(确认密码)
     * @param string $info 提示信息
     * @param string $resultTag 用于显示提示信息的区域的选择器标签. 注：本参数可为空,当为空时,则默认为alert('提示信息')
     *
     * @return string
     */
    public static function isSame($objTag, $objConfirmTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$objConfirmTag || !$info) {
            return false;
        }

        $string  = "if($('" . $objTag ."').val() != $('" . $objConfirmTag . "').val()) {";
        $string .= (!is_null($resultTag)) ? "$('" . $resultTag . "').html('" . $info . "').show();" : "alert('" . $info . "');";
        $string .= "$('" . $objConfirmTag . "').focus();$('" . $objConfirmTag . "').css('border-color', '#C00');return false;}";

        return $string;
    }

    /**
     * 检查字符串长度
     *
     * 当不等合字符串要求时,提示错误信息
     *
     * @access public
     *
     * @param string $objTag 所要验证的input的文本输入框的选择器标签
     * @param integer $minNum 最少数字长度
     * @param integer $maxNum 最大数字长度
     * @param string $info 提示信息
     * @param string $resutlTag 用于显示提示信息的区域的选择器标签。 注：本参数可为空,当为空时,则默认为alert('提示信息')。
     *
     * @return string
     */
    public static function isLen($objTag, $minNum = null, $maxNum = null, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info || (is_null($minNum) && is_null($maxNum))) {
            return false;
        }

        $string = "if (!(";
        if (!is_null($minNum) && !is_null($maxNum)) {
            $string .= "$('" . $objTag . "').val().length >= {$minNum} && $('" . $objTag . "').val().length <= {$maxNum}";
        } else if (!is_null($minNum)){
            $string .= "$('{$objTag}').val().length >= {$minNum}";
        } else {
            $string .= "$('{$objTag}').val().length <= {$maxNum}";
        }
        $string .= ")) {" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";

        return $string;
    }

    /**
     * 检查是否为数字
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isNum($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^[-+]?\d+$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否为英文字符、数字、下划线的组合字符串
     *
     * 常用于检查用户名的是否含有非法字符
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isString($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^[a-z,A-Z0-9-_]+$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否含有非法字符
     *
     * 常用于检查用户名的是否含有非法字符
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isInvalidStr($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(/[!#$%^&*(){}~`\"';:?+=<>/\[\]]+/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否为邮箱
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isEmail($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否为网址
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isUrl($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^(http|ftp|https|ftps):\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否为邮政编码
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isPostNum($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^[1-9][0-9]{5}$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 检查是否为手机号
     *
     * @access public
     *
     * @param string $objTag 选择器标签
     * @param string $info 提示信息
     * @param string $resultTag 显示提示信息的HTML的选择器标签
     *
     * @return string
     */
    public static function isMobile($objTag, $info, $resultTag = null) {

        //参数判断
        if (!$objTag || !$info) {
            return false;
        }

        return "if(!/^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$/.test($('{$objTag}').val())){" . ((!is_null($resultTag)) ? "$('{$resultTag}').html('{$info}').show();" : "alert('{$info}');") . "$('{$objTag}').focus();$('{$objTag}').css('border-color', '#C00');return false;}";
    }

    /**
     * 密码强度分析
     *
     * 利用jquery的密码强度插件,来检查密码的强度,并在密码框架的右侧将结果显示出来.注：使用本函数前应先加载jquery。
     *
     * @access public
     *
     * @param string $objTab 选择器标签
     *
     * @return string
     */
    public static function passwordStrength($objTab) {

        //参数判断
        if (!$objTab) {
            return false;
        }

        //分析JS文件存放目录
        $baseDirUrl = Controller::getAssetUrl('doit/js');
        $string     = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/jquery/jquery.passwordStrength.min.js?version=1.0\"></script>";
        $string    .= "<script type=\"text/javascript\">$(document).ready(function(){\$('{$objTab}').passwordStrength();});</script>";

        return $string;
    }
}