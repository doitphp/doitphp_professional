<?php
/**
 * HTML标签组装类
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Html.php 2.0 2012-12-23 14:06:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Html {

    /**
     * 创建标题标签
     *
     * @access public
     *
     * @param  mixed $title 标题内容
     *
     * @return string.
     */
    public static function title($title){

        //参数分析
        if (!$title) {
            return false;
        }

        return '<title>' . $title . '</title>';
    }

    /**
     * 创建meta标签
     *
     * @access public
     *
     * @param mixed $name meta 名称
     * @param mixed $value meta 值
     *
     * @return string
     */
    public static function meta($name, $value){

        //参数分析
        if (!$name || is_null($value)) {
            return false;
        }

        return '<meta name="' . $name . '" content="' . $value . '">';
    }

    /**
     * 禁用浏览器缓存HTML标签
     *
     * @access public
     * @return string
     */
    public static function noCache() {

        return '<meta http-equiv="pragma" content="no-cache"><meta http-equiv="cache-control" content="no-cache"><meta http-equiv="expires" content="0">';
    }

    /**
     * 创建ICO标签
     *
     * @access public
     *
     * @param mixed $url 图标的访问网址
     *
     * @return string
     */
    public static function icon($url) {

        //参数分析
        if (!$url) {
            return false;
        }

        $options    = array('href'=>$url, 'type'=>'image/x-icon');
        $icoOptions = $options + array('rel'=>'icon');
        $shortCuts  = $options + array('rel'=>'shortcut icon');

        return self::tag('link', $icoOptions, null, false) . self::tag('link', $shortCuts, null, false);

    }

    /**
     * 创建RSS标签
     *
     * @access public
     *
     * @param  string $url RSS访问网址
     * @param  string $title 标题
     *
     * @return string
     */
    public static function rss($url, $title = null) {

        //参数分析
        if (!$url) {
            return false;
        }

        $options = array('href'=>$url, 'type'=>'application/rss+xml', 'rel'=>'alternate');
        if (!$title) {
            $options['title'] = $title;
        }

        return self::tag('link', $options, null, false);
    }

    /**
     * 将特殊字符转化为HTML代码
     *
     * @access public
     *
     * @param string $text 待转义的内容
     *
     * @return string
     */
    public static function encode($text) {

        //参数分析
        if (is_null($text)) {
            return false;
        }

        if (!is_array($text)) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        return array_map(array('Html', 'encode'), $text);
    }

    /**
     * 将HTML代码进行htmlspecialchars_decode()操作
     *
     * @access public
     *
     * @param string $text 待转义的内容
     *
     * @return string
     */
    public static function decode($text) {

        //参数分析
        if (is_null($text)) {
            return false;
        }

        if (!is_array($text)) {
            return htmlspecialchars_decode($text, ENT_QUOTES);
        }

        return array_map(array('Html', 'decode'), $text);
    }

    /**
     * 处理超级连接代码
     *
     * @access public
     *
     * @param string $text 文字连接内容
     * @param string $href 连接URL
     * @param array $options 其它内容
     *
     * @return string
     */
    public static function link($text, $href='#', $options = array()) {

        //参数分析
        if (!$text) {
            return false;
        }

        if ($href) {
            $options['href'] = $href;
        }

        //为了SEO效果,link的title处理.
        if (!isset($options['title']) && !isset($options['TITLE'])) {
            $options['title'] = $text;
        }

        return self::tag('a', $options, $text);
    }

    /**
     * 用于完成email的html代码的处理
     *
     * @access public
     *
     * @param string $text 文字说明
     * @param string $email 邮箱地址
     * @param array  $options 选项
     *
     * @return string
     */
    public static function email($text, $email = null, $options = array()) {

        //参数分析
        if (!$text) {
            return false;
        }

        $options['href'] =  'mailto:' . (is_null($email) ? $text : $email);

        return self::tag('a', $options, $text);
    }

    /**
     * 处理图片代码
     *
     * @access public
     *
     * @param string $src 图片网址
     * @param string $alt 提示内容
     * @param array $options 项目内容
     *
     * @return string
     */
    public static function image($src, $options = array(), $alt = null) {

        //参数分析
        if (!$src) {
            return false;
        }

        $options['src'] = $src;

        if ($alt) {
            $options['alt'] = $alt;
            //为了SEO效果,加入title.
            if (!isset($options['title'])) {
                $options['title'] = $alt;
            }
        }

        return self::tag('img', $options);
    }

    /**
     * 处理标签代码
     *
     * @access public
     *
     * @param string $tag 标签名
     * @param array $options 标签选项
     * @param  string $content 内容
     * @param boolean $closeTag 是否关闭
     *
     * @return string
     */
    public static function tag($tag, $options = array(), $content = null, $closeTag = true) {

        //参数分析
        if (!$tag) {
            return false;
        }

        $optionStr = '';
        //当$options不为空或类型不为数组时
        if (is_array($options)) {
            foreach ($options as $name=>$value) {
                $optionStr .= ' ' . $name . '="' . $value . '"';
            }
        }

        $html = '<' . $tag . $optionStr;

        if (!is_null($content)) {
            return $closeTag ? $html .'>' . $content . '</' . $tag . '>' : $html . '>' . $content;
        } else {
            return $closeTag ? $html . '/>' : $html . '>';
        }
    }

    /**
     * 加载css文件
     *
     * @access public
     *
     * @param string $url CSS文件访问网址
     * @param string $media media属性
     *
     * @return string
     */
    public static function css($url, $media = null) {

        //参数分析
        if (!$url) {
            return false;
        }
        if ($media) {
            $media = ' media="' . $media . '"';
        }

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . self::encode($url) . "\"" . $media . " />\r";
    }

    /**
     * 加载JavaScript文件
     *
     * @access public
     *
     * @param string $url JS文件访问网址
     *
     * @return string
     */
    public static function js($url) {

        return "<script type=\"text/javascript\" src=\"" . self::encode($url) . "\"></script>\r";
    }

    /**
     * 生成表格的HTML代码
     *
     * @access public
     *
     * @param array $content 表格内容
     * @param array  $options 选项
     *
     * @return string
     */
    public static function table($content=array(), $options = array()) {

        //参数分析
        if (!$content) {
            return false;
        }

        $html = self::tag('table', $options, false, false);

        foreach ($content as $lines) {
            if (is_array($lines)) {
                $html .= '<tr>';
                foreach ($lines as $value) {
                    $html .= self::tag('td','',$value);
                }
                $html .= '</tr>';
            }
        }

        return $html . '</table>';
    }

    /**
     * form开始HTML代码,即:将<form>代码内容补充完整.
     *
     * @access public
     *
     * @param string $action 后台处理网址
     * @param string $method 提交方式
     * @param array  $options 选项
     * @param boolean $enctypeItem 是否支持文件提交
     *
     * @return string
     */
    public static function formStart($action, $options = array(), $method = null, $enctypeItem = false) {

        //参数分析
        if (!$action) {
            return false;
        }

        $options['action'] = $action;
        $options['method'] = (!$method) ? 'post' : $method;
        if ($enctypeItem === true) {
            $options['enctype'] = 'multipart/form-data';
        }

        return self::tag('form', $options, false, false);
    }

    /**
     * form的HTML的结束代码
     *
     * @access public
     * @return string
     */
    public static function formEnd() {

        return '</form>';
    }

    /**
     * 处理input代码
     *
     * @access public
     *
     * @param string $type 类型
     * @param array $options 选项
     *
     * @return string
     */
    public static function input($type, $options = array()) {

        //参数分析
        if (!$type) {
            return false;
        }

        $options['type'] = $type;

        return self::tag('input', $options);
    }

    /**
     * 处理text表单代码
     *
     * @access public
     *
     * @param array $options 选项
     *
     * @return string
     */
    public static function text($options = array()) {

        return self::input('text', $options);
    }

    /**
     * 处理password输入框代码
     *
     * @access public
     *
     * @param array $options 选项
     *
     * @return string
     */
    public static function password($options = array()) {

        return self::input('password', $options);
    }

    /**
     * 处理submit提交按钮代码
     *
     * @access public
     *
     * @param array $options 选项
     *
     * @return string
     */
    public static function submit($options = array()) {

        return self::input('submit', $options);
    }

    /**
     * 处理reset按钮代码
     *
     * @access public
     *
     * @param array $options 选项
     *
     * @return string
     */
    public static function reset($options = array()) {

        return self::input('reset', $options);
    }

    /**
     * 处理button按钮代码
     *
     * @access public
     *
     * @param array $options 选项
     *
     * @return string
     */
    public static function button($options = array()) {

        return self::input('button', $options);
    }

    /**
     * 多行文字输入框TextArea的HTML代码处理
     *
     * @access public
     *
     * @param array  $options 属性
     * @param string $content 文字内容
     *
     * @return string
     */
    public static function textarea($options = array(), $content = null) {

        $optionStr = '';
        //当$options不为空或类型不为数组时
        if (is_array($options)) {
            foreach ($options as $name=>$value) {
                $optionStr .= ' ' . $name . '="' . $value . '"';
            }
        }

        $html = '<textarea' . $optionStr . '>';

        return ($content==true) ? $html . $content . '</textarea>' :  $html . '</textarea>';
    }

    /**
     * 处理下拉框SELECT的HTML代码
     *
     * @access public
     *
     * @param array $contentArray 菜单内容
     * @param array $options 选项
     * @param mixed $selected 默认选中的键值
     *
     * @return string
     */
    public static function select($contentArray, $options = array(), $selected = null) {

        if (!$contentArray || !is_array($contentArray)) {
            return false;
        }

        $optionStr = '';
        foreach ($contentArray as $key=>$value) {
            if (!is_null($selected)) {
                $optionStr .= ($key==$selected) ? '<option value="' . $key . '" selected="selected">' . $value . '</option>' : '<option value="' . $key . '">' . $value . '</option>';
            } else {
                $optionStr .= '<option value="' . $key . '">' . $value . '</option>';
            }
        }

        return self::tag('select', $options, $optionStr);
    }

    /**
     * 复选框HTML代码
     *
     *@access public
     *
     * @param array $contentArray 复选框内容
     * @param array $options 选项
     * @param array $selected 默认选中的键值
     *
     * @return string
     */
    public static function checkbox($contentArray, $options = array(), $selected = null) {

        //参数分析
        if (!$contentArray || !is_array($contentArray)) {
            return false;
        }

        $html = '';
        foreach ($contentArray as $key=>$value) {
            $options['value'] = $key;
            if (!is_null($selected) && is_array($selected)) {
                if (in_array($key, $selected)) {
                    $options['checked'] = 'checked';
                } else {
                    if (isset($options['checked'])) {
                        unset($options['checked']);
                    }
                }
            }
            $html .= '<label>'.self::input('checkbox', $options) . $value . '</label>';
        }

        return $html;
    }

    /**
     * 单选框HTML代码
     *
     *@access public
     *
     * @param array $contentArray 单选框内容
     * @param array $options 选项
     * @param mixed $selected 默认选中的键值
     *
     * @return string
     */
    public static function radio($contentArray, $options = array(), $selected = 0) {

        //参数分析
        if (!$contentArray || !is_array($contentArray)) {
            return false;
        }

        $html = '';
        foreach ($contentArray as $key=>$value) {
            $options['value'] = $key;
            if ($selected==$key) {
                $options['checked'] = 'checked';
            } else {
                if (isset($options['checked'])) {
                    unset($options['checked']);
                }
            }
            $html .= '<label>'.self::input('radio', $options).$value.'</label>';
        }

        return $html;
    }
}