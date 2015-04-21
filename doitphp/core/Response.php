<?php
/**
 * 获取HTTP的响应信息
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Response.php 2.0 2012-11-28 00:50:27Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Response {

    /**
     * 设置页面编码
     *
     * @access public
     *
     * @param string $encode 编码名称
     *
     * @return void
     */
    public static function charset($encode = 'UTF-8') {

        header("Content-Type:text/html; charset={$encode}");

        return true;
    }

    /**
     * 禁用浏览器缓存
     *
     * @access public
     * @return boolean
     */
    public static function noCache() {

        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        return true;
    }

    /**
     * 设置网页生存周期
     *
     * @access public
     *
     * @param integer $seconds 生存周期（单位：秒）
     *
     * @return boolean
     */
    public static function expires($seconds = 1800) {

        $time = date('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + $seconds) . ' GMT';
        header("Expires: {$time}");

        return true;
    }

    /**
     * 网址(URL)跳转操作
     *
     * 页面跳转方法，例:运行页面跳转到自定义的网址(即:URL重定向)
     *
     * @access public
     *
     * @param string $url 所要跳转的网址(URL)
     *
     * @return void
     */
    public static function redirect($url) {

        //参数分析.
        if (!$url) {
            return false;
        }

        if (!headers_sent()) {
            header("Location:" . $url);
        }else {
            echo '<script type="text/javascript">location.href="' . $url . '";</script>';
        }

        exit();
    }

    /**
     * 显示提示信息操作
     *
     * 本方法支持URL的自动跳转，当显示时间有效期失效时则跳转到自定义网址，若跳转网址为空则函数不执行跳转功能，当自定义网址参数为-1时默认为:返回上一页。
     * 注：显示提示信息的页面模板内容可以自定义. 方法：在项目视图目录中的error子目录中新建message.php文件,自定义该文件内容。
     * 模板文件输出信息处代码参考doitphp子目录中文件：views/errors/message.php
     *
     * @access public
     *
     * @param string $message 所要显示的提示信息
     * @param string $gotoUrl 所要跳转的自定义网址
     * @param integer $limitTime 显示信息的有效期,注:(单位:秒) 默认为3秒
     *
     * @return string
     */
    public static function showMsg($message, $gotoUrl = null, $limitTime = 3) {

        //参数分析
        if (!$message) {
            return false;
        }

        //当自定义跳转网址存在时
        if ($gotoUrl) {
            $limitTime    = 1000 * $limitTime;
            //分析自定义网址是否为返回页
            if ($gotoUrl == -1) {
                $gotoUrl  = 'javascript:history.go(-1);';
                $message .= '<br/><a href="javascript:history.go(-1);" target="_self">如果你的浏览器没反应,请点击这里...</a>';
            } else{
                //防止网址过长，有换行引起跳转变不正确
                $gotoUrl  = str_replace(array("\n","\r"), '', $gotoUrl);
                $message .= '<br/><a href="' . $gotoUrl . '" target="_self">如果你的浏览器没反应,请点击这里...</a>';
            }
            $message .= '<script type="text/javascript">function doitRedirectUrl(url){location.href=url;}setTimeout("doitRedirectUrl(\'' . $gotoUrl . '\')", ' . $limitTime . ');</script>';
        }

        $messageTemplateFile = BASE_PATH . '/views/errors/message.php';

        is_file($messageTemplateFile) ? include_once $messageTemplateFile : include_once DOIT_ROOT . '/views/errors/message.php';

        exit();
    }

    /**
     * 优雅输出print_r()函数所要输出的内容
     *
     * 用于程序调试时,完美输出调试数据,功能相当于print_r().当第二参数为true时(默认为:false),功能相当于var_dump()。
     * 注:本方法一般用于程序调试
     *
     * @access public
     *
     * @param mixed $data 所要输出的数据
     * @param boolean $option 选项:true(显示var_dump()的内容)/ false(显示print_r()的内容)
     *
     * @return string
     */
    public static function dump($data = null, $option = false) {

        //设置页面编码
        if (!headers_sent()) {
            header("Content-Type:text/html; charset=utf-8");
        }

        //当输出print_r()内容时
        if(!$option){
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        } else {
            ob_start();
            var_dump($data);
            $output = ob_get_clean();

            $output = str_replace('"', '', $output);
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

            echo '<pre>', $output, '</pre>';
        }

        exit();
    }

    /**
     * 用于显示错误信息
     *
     * 若调试模式关闭时(即:DOIT_DEBUG为false时)，则将错误信息并写入日志
     *
     * @access public
     *
     * @param string $message 所要显示的错误信息
     * @param string $level 日志类型. 默认为Error. 参数：Warning, Error, Notice
     *
     * @return string
     */
    public static function halt($message, $level = 'Normal') {

        //参数分析
        if (!$message) {
            return false;
        }

        //调试模式下优雅输出错误信息
        $traces       = debug_backtrace();
        $traceString  = '';
        $sourceFile   = $traces[0]['file'] . '(' . $traces[0]['line'] . ')';

        if (defined('DOIT_DEBUG') && DOIT_DEBUG === true && $level != 'Normal') {
            foreach ($traces as $key=>$trace) {
                //代码跟踪级别限制
                if ($key > 2) {
                    break;
                }
                $argsString   = ($trace['args'] && is_array($trace['args'])) ? '(' . implode('.', $trace['args']) . ')': '';
                $traceString .= "#{$key} {$trace['file']}({$trace['line']}){$trace['class']}{$trace['type']}{$trace['function']}{$argsString}<br/>";
            }
        }

        //加载,分析,并输出excepiton文件内容
        include_once DOIT_ROOT . '/views/errors/exception.php';

        if (defined('DOIT_DEBUG') && DOIT_DEBUG === false && $level != 'Normal') {
            //写入程序运行日志
            Log::write($message, $level);
        }

        //终止程序
        exit();
    }

    /**
     * 输出供Ajax所调用的页面返回信息
     *
     * 返回json数据,供前台ajax调用
     *
     * @access public
     *
     * @param boolean $status 执行状态 : true/false 或 1/0
     * @param string $msg 返回信息, 默认为空
     * @param array $data 返回数据,支持数组。
     *
     * @return string
     */
    public static function ajax($status, $msg = null, $data = array()) {

        $result             = array();
        $result['status']   = $status;
        $result['msg']      = !is_null($msg) ? $msg : '';
        $result['data']     = $data;

        //设置页面编码
        header("Content-Type:text/html; charset=utf-8");

        exit(json_encode($result));
    }

}