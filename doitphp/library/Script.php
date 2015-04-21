<?php
/**
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Script.php 2.0 2012-12-23 16:52:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Script {

    /**
     * 加载JS文件(只限doitphp集成的js文件)
     *
     * @access public
     *
     * @return string $scriptName js文件名(不含后缀)
     *
     * @return string
     */
    public static function add($scriptName) {

        //参数分析
        if (!$scriptName) {
            return false;
        }

        $scriptName = strtolower(trim($scriptName));

        //分析JS文件存放目录
        $baseDirUrl = Controller::getAssetUrl('doit/js');

        switch ($scriptName) {
            case 'jquery':
                $html = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/jquery/jquery.min.js?version=1.11.2\"></script>\r";
                break;
            case 'form':
                $html = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/jquery/jquery.form.min.js?version=3.51.0\"></script>\r";
                break;
            case 'calendar':
                $html = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/calendar/WdatePicker.js?version=4.7.2\"></script>\r";
                break;
            case 'checkbox':
                $html = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/jquery/jquery.checkbox.min.js?version=1.1\"></script>\r";
                break;
            case 'lazyload':
                $html = "<script type=\"text/javascript\" src=\"" . $baseDirUrl . "/jquery/jquery.lazyload.min.js?version=20110810\"></script>\r";
                break;
            default:
                $html = "<script type=\"text/javascript\" src=\"" . $scriptName . ".js\"></script>\r";
        }

        return $html;
    }

    /**
     * 日历调用
     *
     * @access public
     *
     * @param string $tag 选择器标签
     * @param string $startDate 可选的开始日期
     * @param string $endDate 可选的结束日期
     * @param string $dateFomat 日期格式.注：本参数支持时分秒
     *
     * @return string
     *
     * @example
     * 法一:
     * Script::calendar('#calendar_box');
     *
     * 法二:
     * Script::calendar('#calendar_box', '2010-05-04', '2012-12-21');
     *
     * 法三:(格式:年-月-日 时:分:秒)
     * Script::calendar('#calendar_box', '2010-05-04', '2012-12-21', 'yyyy-MM-dd HH:mm:ss');
     */
    public static function calendar($tag, $startDate = null, $endDate = null, $dateFomat = null) {

        //参数分析.
        if (!$tag) {
            return false;
        }

        //自定义日历参数
        $calendarParams = '';
        if ($startDate) {
            $calendarParams .= ((!$calendarParams) ? '{' : ',') . 'minDate:\'' . $startDate . '\'';
        }
        if ($endDate) {
            $calendarParams .= ((!$calendarParams) ? '{' : ',') . 'maxDate:\'' . $endDate . '\'';
        }
        if($dateFomat) {
            $calendarParams .= ((!$calendarParams) ? '{' : ',') . 'dateFmt:\'' . $dateFomat . '\'';
        }
        $calendarParams     .= (!$calendarParams) ? '' : '}';

        return "<script type=\"text/javascript\">\$(document).ready(function(){\$('" . $tag . "').addClass('doitphp_calendar');\$('" . $tag . "').click(function(){WdatePicker(" . $calendarParams . ");});});</script>\r";
    }

    /**
     * 处理jquery的ajax post JS代码. 注:非jquery的$.post,而是$.ajax
     *
     * @access public
     *
     * @param string $url 接收网址
     * @param string $data 参数
     * @param string $beforeSend 数据提交前的处理函数
     * @param string $success 数据提交后的处理函数
     * @param string $dataType 返回数据格式 (xml, json, script, htmlt, jsonp等)
     *
     * @return string
     */
    public static function ajaxPost($url, $data = null, $beforeSend = null, $success = null, $dataType = null) {

        //参数分析
        if (!$url) {
            return false;
        }

        return self::ajaxRequest($url, 'POST', $data, $beforeSend, $success, $dataType);
    }

    /**
     * 处理jquery的ajax GET代码. 注:非jquery的$.get,而是$.ajax
     *
     * @access public
     *
     * @param string $url 接收网址
     * @param string $data 参数
     * @param string $beforeSend 数据提交前的处理函数
     * @param string $success 数据提交后的处理函数
     * @param string $dataType 返回数据格式 (xml, json, script, html, jsonp等)
     * @return string
     */
    public static function ajaxGet($url, $data = null, $beforeSend = null, $success = null, $dataType = null) {

        //参数分析
        if (!$url) {
            return false;
        }

        return self::ajaxRequest($url, 'GET', $data, $beforeSend, $success, $dataType);
    }

    /**
     * 处理jquery的ajax 调用代码. 即:jquery的$.ajax
     *
     * @access public
     *
     * @param string $url 接收网址
     * @param string $type HTTP传输方式(post, get)
     * @param string $data 参数
     * @param string $beforeSend 数据提交前的处理函数
     * @param string $success 数据提交后的处理函数
     * @param string $dataType 返回数据格式 (xml, json, script, html, jsonp等)
     *
     * @return string
     */
    public static function ajaxRequest($url, $type = 'POST', $data = array(), $beforeSend = null, $success = null, $dataType = null) {

        //参数分析
        if (!$url) {
            return false;
        }

        $optionArray = array();
        if ($data && is_array($data)) {
            $paramsArray = array();
            foreach ($data as $key=>$value) {
                $paramsArray[] =  $key . ':"' . addslashes($value) . '"';
            }
            $optionArray[] = "data:{" . implode(', ', $paramsArray) . "}";
        }
        if ($beforeSend) {
            $optionArray[] = "beforeSend:{$beforeSend}";
        }
        if ($success) {
            $optionArray[] = "success:{$success}";
        }
        if ($dataType) {
            $optionArray[] = "dataType:'{$dataType}'";
        }

        return "\$.ajax({url:'{$url}',type:'{$type}'," . implode(',', $optionArray). "});";
    }

    /**
     * Ajax Loading 加载图片的HTML代码,small为小图片.
     *
     * @access public
     *
     * @param string $options 图片类型(非图片格式类型)
     *
     * @return string
     */
    public static function ajaxLoadingImage($options = 'small') {

        //选择 ajax loading 类型图片
        $imageName = self::_parseAjaxImage($options);

        return '<img src="' . Controller::getAssetUrl('doit/images') . '/' . $imageName . '"/>';
    }

    /**
     * 处理$.load()的jquery代码.
     *
     * @access public
     *
     * @param string $tag jquery的选择器标签
     * @param string $url 所要加载内容的网址
     * @param string $imageOption ajax loading图片类型
     *
     * @return string
     */
    public static function ajaxBoxLoad($tag, $url, $imageOption = 'small', $loadingImgId = 'doitphp_ajax_loading_image') {

        //参数分析.
        if (!$tag || !$url) {
            return false;
        }

        $baseDirUrl = Controller::getAssetUrl('doit/images');

        //选择ajax loading image 的类型.
        $imageName   = self::_parseAjaxImage($imageOption);

        //组装JS代码
        return "<script type=\"text/javascript\">\$(document).ready(function() {\$('" . $tag . "').append('<img src=\"" . $baseDirUrl . '/' . $imageName . "\" id=\"" . $loadingImgId . "\"/>');var left=parseInt((\$('" . $tag . "').width()-\$('#" . $loadingImgId . "').width())/2);var top=parseInt((\$('" . $tag . "').height()-\$('#" . $loadingImgId . "').height())/2);\$('#" . $loadingImgId . "').css({'margin-top':top+'px','margin-left':left+'px'});\$('" . $tag."').load('" . $url . "',function() {\$('#" . $loadingImgId . "').remove()})});</script>\r";
    }

    /**
     * 处理jquery 插件 lazyload的调用代码
     *
     * @access public
     *
     * @param string $tag 图片的选择器标签
     * @param string $options 图片类型(非图片格式类型)
     *
     * @return string
     */
    public static function lazyload($tag = 'img', $options = 'small') {

        //选择AJAX加载类型图片
        $imageName = self::_parseAjaxImage($options);

        return "<script type=\"text/javascript\">\$(document).ready(function(){\$('" . $tag . "').lazyload({placeholder:'" . Controller::getAssetUrl('doit/images') . '/' . $imageName . "',effect:'fadeIn'});});</script>\r";
    }

    /**
     * 分析ajax加载图片文件名
     *
     * @access public
     *
     * @param string $option 选项
     *
     * @return string
     */
    protected static function _parseAjaxImage($options = 'small') {

        switch ($options) {
            case 'small':
                $imageName = 'ajax_loading_small.gif';
                break;
            case 'big':
                $imageName = 'ajax_loading_big.gif';
                break;
            case 'snake':
                $imageName = 'ajax_loading_snake.gif';
                break;
            case 'ring':
                $imageName = 'ajax_loading_ring.gif';
                break;
            default : $imageName = 'ajax_loading_small.gif';
        }

        return $imageName;
    }


    /**
     * 完成jquery form插件提交表单数据的JS代码
     *
     * @access public
     *
     * @param string $formTags form标签的选择器
     * @param string $before_fn 提交表单前所绑定的函数名
     * @param string $success_fn 提交表单后所绑定的函数名
     * @param string $dataType 返回数据格式 (xml, json, script, html, jsonp等)
     *
     * @return string
     */
    public static function ajaxFormSubmit($formTags, $before = null, $success = null, $dataType = null) {

        $optionArray = array();
        if (!is_null($before)) {
            $optionArray[] = "beforeSubmit:{$before}";
        }
        if (!is_null($success)) {
            $optionArray[] = "success:{$success}";
        }
        if (!is_null($dataType)) {
            $optionArray[] = "dataType:'{$dataType}'";
        }

        $optionStr = implode(',', $optionArray);

        return '<script type="text/javascript">$(document).ready(function(){$(\'' . $formTags. '\').ajaxForm({' . $optionStr . '});});</script>';
    }
}