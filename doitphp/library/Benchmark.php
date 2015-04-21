<?php
/**
 * 基准测试类
 *
 * 用于对特定的代码段进行内存占用及执行时间的基准测试
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Benchmark.php 2.0 2012-12-31 11:12:25Z tommy <tommy@doitphp.com> $
 * @package library
 * @since 1.0
 */

class Benchmark {

    /**
     * 标记时间数据存贮容器
     *
     * @var array
     */
    protected static $_timeArray = array();

    /**
     * 标记内存占用数据存贮容器
     *
     * @var array
     */
    protected static $_memoArray = array();

    /**
     * 标记当前点的基准数据
     *
     * @access public
     *
     * @param string $name 标记名
     *
     * @return boolean
     */
    public static function mark($name) {

        //参数分析
        if (!$name) {
            return false;
        }

        //获取当前时间及内存占用
        self::$_timeArray[$name] = microtime(true);
        self::$_memoArray[$name] = self::_getMemUsage();

        return true;
    }

    /**
     * 获取测试代码片段的总的内存占用及执行时间
     *
     * @access public
     *
     * @param string $startName 开始标记名
     * @param string $endName 结束标记名
     *
     * @return array
     */
    public static function get($startName, $endName = null) {

        //参数分析
        if (!$startName) {
            return false;
        }

        //判断标记点时否存在
        if (!isset(self::$_timeArray[$startName])) {
            return false;
        }
        if ($endName && !isset(self::$_timeArray[$endName])) {
            return false;
        }

        $startTime = self::$_timeArray[$startName];
        $startMemo = self::$_memoArray[$startName];

        $endTime = (!$endName) ? microtime(true) : self::$_timeArray[$endName];
        $endMemo = (!$endName) ? self::_getMemUsage() : self::$_memoArray[$endName];

        $time    = sprintf('%6fs',$endTime - $startTime);
        $memory  = $endMemo = $startMemo;

        return array('time'=>$time, 'memory'=>$memory);
    }

    /**
     * 获取当前内存的占用数据
     *
     * @access protected
     * @return string
     */
    protected static function _getMemUsage() {

        return memory_get_usage();
    }

    /**
     * 获取每个标记点的内存及执行时间
     *
     * @access public
     * @return array
     */
    public static function getAll() {

        $data = array();
        foreach (self::$_timeArray as $key =>$value) {
            $data[$key] = array('time'=>$value, 'memory'=>self::$_memoArray[$key]);
        }

        return $data;
    }

    /**
     * 获取每个标记点的时间
     *
     * @access public
     * @return array
     */
    public static function getTime() {

        return self::$_timeArray;
    }

    /**
     * 获取每个标记点的内存占用
     *
     * @access public
     * @return array
     */
    public static function getMemory() {

        return self::$_memoArray;
    }
}