<?php
/**
 * 日历生成操作类
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Calendar.php 2.0 2012-12-30 01:28:54Z tommy <tommy@doitphp.com> $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Calendar {

    /**
     * 日历的年份
     *
     * @var integer
     */
    protected $_year = null;

    /**
     * 日历的月份
     *
     * @var integer
     */
    protected $_month = null;

    /**
     * 日历中已使用的日期
     *
     * @var array
     */
    protected $_usedDays = array();

    /**
     * 设置日历的年份
     *
     * 默认为当前的年份
     *
     * @access public
     *
     * @param integer $year 所要设置的年份
     *
     * @return object
     */
    public function setYear($year) {

        //参数分析
        if ($year && is_int($year)) {
            $this->_year = $year;
        }

        return $this;
    }

    /**
     * 设置月份
     *
     * 默认为当前月份
     *
     * @access public
     *
     * @param integer $month 所要设置的月份
     *
     * @return object
     */
    public function setMonth($month) {

        //参数分析
        if ($month && is_int($month)) {
            $this->_month = $month;
        }

        return $this;
    }

    /**
     * 设置已占用的日期
     *
     * @access public
     *
     * @param array $days 已占用的日期。注：本参数为数组
     *
     * @return object
     */
    public function setUsedDays($days) {

        //参数分析
        if ($days && is_array($days)) {
            $this->_usedDays = $days;
        }

        return $this;
    }

    /**
     * 输出日历数组
     *
     * @access public
     * @return array
     */
    public function render() {

        return $this->_processData();
    }

    /**
     * 处理日历数组
     *
     * @access protected
     * @return array
     */
    protected function _processData() {

        //分析数组
        $this->_year  = (!$this->_year) ?  date('Y') : $this->_year;
        $this->_month = (!$this->_month) ? date('m') : $this->_month;

        $yearNow  = date('Y');
        $monthNow = date('m');
        $dateNow  =  date('j');

        $timeIndex = mktime(0, 0, 0, $this->_month, 1, $this->_year);

        //获取当前所在月份的总天数，第一天的星期数。
        $totalDays   = date('t', $timeIndex);
        $dayIndex    = date('w', $timeIndex);

        //计算日历的总行数。
        $totalRowNum  = ceil(($totalDays + $dayIndex)/7);

        //分析日期占用
        $usedDayArray = (!$this->_usedDays) ? array() : array_keys($this->_usedDays);

        //分析日历数组
        $data = array('year'=>$this->_year, 'month'=>$this->_month, 'content'=>array());

        for ($i = 0; $i < $totalRowNum; $i ++) {
            for($k = 0; $k < 7; $k ++) {
                //所要显示的日期
                $dateShow = intval( 7 * $i + $k - $dayIndex + 1);
                if (($dateShow < 1) || ($dateShow > $totalDays)) {
                    $data['content'][$i][$k] = array('date'=> null, 'status'=> false);
                } else {
                    //分析已占用的日期状态
                    $usedStatus  = in_array($dateShow, $usedDayArray) ? true : false;
                    $todayStatus = (!$usedStatus && ($dateShow == $dateNow) && ($yearNow == $this->_year && $monthNow == $this->_month)) ? true : false;

                    $data['content'][$i][$k] = array('date' => $dateShow);
                    if ($usedStatus) {
                        $data['content'][$i][$k]['status'] = 'used';
                        $data['content'][$i][$k]['ext']   = $this->_usedDays[$dateShow];
                    } else if ($todayStatus) {
                        $data['content'][$i][$k]['status'] = 'today';
                    } else {
                        $data['content'][$i][$k]['status'] = true;
                    }
                }
            }
        }

        return $data;
    }

}