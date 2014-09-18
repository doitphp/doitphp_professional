<?php
/**
 * 日志内容的管理
 *
 * 日志的写入操作及日志内容的查询显示
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Log.php 2.0 2012-11-29 23:33:00Z tommy <streen003@gmail.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Log {

    /**
     * 写入日志
     *
     * @access public
     *
     * @param string $message     所要写入的日志内容
     * @param string $level       日志类型. 参数：Warning, Error, Notice
     * @param string $logFileName 日志文件名
     *
     * @return boolean
     */
    public static function write($message, $level = 'Error', $logFileName = null) {

        //参数分析
        if (!$message) {
            return false;
        }

        //当日志写入功能关闭时
        if(Configure::get('application.log') === false){
            return true;
        }

        $logFilePath = self::_getLogFilePath($logFileName);

        //分析日志文件存放目录
        $logDir = dirname($logFilePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        //分析记录日志的当前页面
        $moduleId     = Doit::getModuleName();
        $controllerId = ((!$moduleId) ? '' : $moduleId . '::') . Doit::getControllerName();
        $actionId     = Doit::getActionName();

        //分析日志内容
        $message      = "[{$controllerId}][{$actionId}]:" . $message;

        return error_log(date('[Y-m-d H:i:s]') . " {$level}: {$message} IP: {$_SERVER['REMOTE_ADDR']}\n", 3, $logFilePath);
    }

    /**
     * 显示日志内容
     *
     * 显示日志文件内容,以列表的形式显示.便于程序调用查看日志
     *
     * @access public
     *
     * @param string $logFileName 所要显示的日志文件内容,默认为null, 即当天的日志文件名.注:不带后缀名.log
     *
     * @return string
     */
    public static function show($logFileName = null) {

        //参数分析
        $logFilePath    = self::_getLogFilePath($logFileName);
        $logContent     = is_file($logFilePath) ? file_get_contents($logFilePath) : '';

        $logArray       = explode("\n", $logContent);
        $totalLines     = sizeof($logArray);

        //清除不必要内存占用
        unset($logContent);

        //输出日志内容
        echo '<table width="85%" border="0" cellpadding="0" cellspacing="1" style="background:#0478CB; font-size:12px; line-height:25px;">';

        foreach ($logArray as $key=>$logString) {

            if ($key == $totalLines - 1) {
                continue;
            }

            $bgColor = ($key % 2 == 0) ? '#FFFFFF' : '#C6E7FF';

            echo '<tr><td height="25" align="left" bgcolor="' . $bgColor .'">&nbsp;' . $logString . '</td></tr>';
        }

        echo '</table>';
    }

    /**
     * 获取当前日志文件名
     *
     * @example
     *
     * $this->__getLogFilePath('sql');
     * 或
     * $this->__getLogFilePath('2012-11.2012-11-23');
     * 或
     * $this->__getLogFilePath('2012-11/2012-11-23');
     *
     * @access private
     *
     * @param $logFileName 日志文件名
     *
     * @return string
     */
    private static function _getLogFilePath($logFileName = null) {

        //参数分析
        if ($logFileName && strpos($logFileName, '.') !== false) {
            $logFileName = str_replace('.', '/', $logFileName);
        }

        //组装日志文件路径
        $logFilePath = rtrim(Configure::get('application.logPath'), '/') . DS;
        if (!$logFileName) {
            $logFilePath .= date('Y-m') . '/' . date('Y-m-d');
        } else {
            if (strpos($logFileName, '/') !== false) {
                $logFilePath .= $logFileName;
            } else {
                $logFilePath .= date('Y-m') . '/' . $logFileName;
            }
        }
        $logFilePath .= '.log';

        return $logFilePath;
    }
}