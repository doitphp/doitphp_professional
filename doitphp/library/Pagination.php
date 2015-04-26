<?php
/**
 * 分页类
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Pagination.php 2.0 2012-12-29 11:40:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Pagination {

    /**
     * 连接网址
     *
     * @var string
     */
    protected $_url = null;

    /**
     * 当前页
     *
     * @var integer
     */
    protected $_page = 1;

    /**
     * list总数
     *
     * @var integer
     */
    protected $_total = 0;

    /**
     * 分页总数
     *
     * @var integer
     */
    protected $_totalPages = 0;

    /**
     * 每个页面显示的list数目
     *
     * @var integer
     */
    protected $_num = 10;

    /**
     * list允许放页码数量,如:1.2.3.4就这4个数字,则$perCircle为4
     *
     * @var integer
     */
    protected $_perCircle = 10;

    /**
     * 分页程序的扩展功能开关,默认关闭
     *
     * @var boolean
     */
    protected $_ext = false;

    /**
     * list中的坐标. 如:7,8,九,10,11这里的九为当前页,在list中排第三位,则$center为3
     *
     * @var integer
     */
    protected $_center = 3;

    /**
     * 是否为ajax分页模式
     *
     * @var boolean
     */
    protected $_isAjax = false;

    /**
     * ajax分页的动作名称
     *
     * @var string
     */
    protected $_ajaxActionName = null;

    /**
     * 分页css名
     *
     * @var string
     */
    protected $_styleFile = null;

    /**
     * 分页隐藏开关
     *
     * @var boolean
     */
    protected $_hiddenStatus = false;

    /**
     * 第一页
     *
     * @var string
     */
    public $firstPage = '第一页';

    /**
     * 上一页
     *
     * @var string
     */
    public $prePage = '上一页';

    /**
     * 下一页
     *
     * @var string
     */
    public $nextPage = '下一页';

    /**
     * 最后一页
     *
     * @var string
     */
    public $lastPage = '最末页';

    /**
     * 分页附属说明
     *
     * @var string
     */
    public $note = null;

    /**
     * 获取总页数
     *
     * @access protected
     * @return integer
     */
    protected function _getTotalPage() {

        return ceil($this->_total / $this->_num);
    }

    /**
     * 获取当前页数
     *
     * @access protected
     * @return integer
     */
    protected function _getPageNum() {

        //当URL中?page=5的page参数大于总页数时
        return ($this->_page > $this->_totalPages) ? $this->_totalPages : $this->_page;
    }

    /**
     * 设置每页显示的列表数
     *
     * @access public
     *
     * @param integer $num 每页显示的列表数
     *
     * @return object
     */
    public function num($num = null) {

        //参数分析
        if ($num) {
            $this->_num = $num;
        }

        return $this;
    }

    /**
     * 设置总列表数
     *
     * @access public
     *
     * @param integer $totalNum 总列表数
     *
     * @return object
     */
    public function total($totalNum = null) {

        //参数分析
        if ($totalNum) {
            $this->_total = $totalNum;
        }

        return $this;
    }

    /**
     * 开启分页的隐藏功能
     *
     * @access public
     *
     * @param boolean $item 隐藏开关 , 默认为true
     *
     * @return object
     */
    public function hide($item = true) {

        if ($item === true) {
            $this->_hiddenStatus = true;
        }

        return $this;
    }

    /**
     * 设置分页跳转的网址
     *
     * @access public
     *
     * @param string $url 分页跳转的网址
     *
     * @return object
     */
    public function url($url = null) {

        //当网址不存在时
        if ($url) {
            $this->_url = trim($url);
        }

        return $this;
    }

    /**
     * 设置当前的页数
     *
     * @access public
     *
     * @param integer $page 当前的页数
     *
     * @return object
     */
    public function page($page = null) {

        //参数分析
        if($page) {
            $this->_page = $page;
        }

        return $this;
    }

    /**
     * 开启分页扩展
     *
     * @access public
     *
     * @param boolean $ext 是否开启分页扩展功能（true:是/false:否）
     *
     * @return object
     */
    public function ext($ext = true) {

        //将$ext转化为小写字母.
        $this->_ext = ($ext) ? true : false;

        return $this;
    }

    /**
     * 设置分页列表的重心
     *
     * @access public
     *
     * @param integer $num 分页列表重心(即：页数)
     *
     * @return object
     */
    public function center($num) {

        //参数分析
        if ($num && is_int($num)) {
            $this->_center = $num;
        }

        return $this;
    }

    /**
     * 设置分页列表的列表数
     *
     * @access public
     *
     * @param integer $num 分页列表的列表数
     *
     * @return object
     */
    public function circle($num) {

        //参数分析
        if ($num && is_int($num)) {
            $this->_perCircle = $num;
        }

        return $this;
    }

    /**
     * 开启ajax分页模式
     *
     * @access public
     *
     * @param string $action 动作名称
     *
     * @return object
     */
    public function ajax($action) {

        if ($action) {
            $this->_isAjax           = true;
            $this->_ajaxActionName   = $action;
        }

        return  $this;
    }

    /**
     * 输出处理完毕的分页HTML
     *
     * @access public
     * @return string
     */
    public function output() {

        //获取分页数组
        $data = $this->_processData();

        //获取HTML内容
        $html = '<div class="doitphp_pagelist_box"><ul>';

        //分析扩展信息
        if ($data['ext'] === true && $this->note) {
            $html .= str_replace(array('{$totalNum}', '{$totalPage}', '{$num}'), array($data['total'], $data['totalpage'], $data['num']), $this->note);
        }

        //分析上一页
        if (isset($data['prepage'])) {
            foreach ($data['prepage'] as $lines) {
                $content = ($data['ajax'] === true) ? "<a href='{$lines['url']}' onclick='{$data['ajaxaction']}('{$lines['url']}'); return false;'>{$lines['text']}</a>" : "<a href='{$lines['url']}' target='_self'>{$lines['text']}</a>";
                $html   .= '<li class="pagelist_ext">' . $content . '</li>';
            }
        }

        //分析分页列表
        if (isset($data['listpage'])) {
            foreach ($data['listpage'] as $lines) {
                if ($lines['current'] === true) {
                    $html .= '<li class="pagelist_current">' . $lines['text'] . '</li>';
                } else {
                    $content = ($data['ajax'] === true) ? "<a href='{$lines['url']}' onclick='{$data['ajaxaction']}('{$lines['url']}'); return false;'>{$lines['text']}</a>" : "<a href='{$lines['url']}' target='_self'>{$lines['text']}</a>";
                    $html .= '<li>' . $content . '</li>';
                }
            }
        }

        //分析下一页
        if (isset($data['nextpage'])) {
            foreach ($data['nextpage'] as $lines) {
                $content = ($data['ajax'] === true) ? "<a href='{$lines['url']}' onclick='{$data['ajaxaction']}('{$lines['url']}'); return false;'>{$lines['text']}</a>" : "<a href='{$lines['url']}' target='_self'>{$lines['text']}</a>";
                $html   .= '<li class="pagelist_ext">' . $content . '</li>';
            }
        }

        $html .= '</ul></div>';
        return $html;
    }

    /**
     * 输出分页数组
     *
     * @access public
     * @return array
     */
    public function render() {

        return $this->_processData();
    }

    /**
     * 处理分页数组
     *
     * @access protected
     * @return array
     */
    protected function _processData() {

        //支持长的url.
        $this->_url        = trim(str_replace(array("\n","\r"), '', $this->_url));

        //获取总页数.
        $this->_totalPages = $this->_getTotalPage();

        //获取当前页.
        $this->_page       = $this->_getPageNum();

        $data = array();

        //当未有分页数据时
        if (!$this->_totalPages) {
            return $data;
        }

        //当分页隐藏功能开启时
        if (($this->_hiddenStatus === true) && ($this->_total <= $this->_num)) {
            return $data;
        }

        $data['total']     = $this->_total;
        $data['num']       = $this->_num;
        $data['totalpage'] = $this->_totalPages;
        $data['page']      = $this->_page;
        $data['url']       = $this->_url;

        $data['ajax']      = $this->_isAjax;
        if ($this->_isAjax) {
            $data['ajaxAction'] = $this->_ajaxActionName;
        }
        $data['ext']       = $this->_ext;

        //分析上一页
        if ($this->_page != 1 && $this->_totalPages > 1) {
            $data['prepage'] = array(
            array('text'=>$this->firstPage, 'url'=>$this->_url . 1),
            array('text'=>$this->prePage, 'url'=>$this->_url . ($this->_page - 1)),
            );
        }

        //分析下一页
        if ($this->_page != $this->_totalPages && $this->_totalPages > 1) {
            $data['nextpage'] = array(
            array('text'=>$this->nextPage, 'url'=>$this->_url . ($this->_page + 1)),
            array('text'=>$this->lastPage, 'url'=>$this->_url . $this->_totalPages),
            );
        }

        //分析分页列表
        if ($this->_totalPages > $this->_perCircle) {
            if ($this->_page + $this->_perCircle >= $this->_totalPages + $this->_center) {
                $list_start   = $this->_totalPages - $this->_perCircle + 1;
                $list_end     = $this->_totalPages;
            } else {
                $list_start   = ($this->_page>$this->_center) ? $this->_page - $this->_center + 1 : 1;
                $list_end     = ($this->_page>$this->_center) ? $this->_page + $this->_perCircle-$this->_center : $this->_perCircle;
            }
        } else {
            $list_start       = 1;
            $list_end         = $this->_totalPages;
        }

        for($i = $list_start; $i <= $list_end; $i ++) {
            //分析当前页
            if ($i == $this->_page) {
                $data['listpage'][] = array('text'=>$i, 'current'=>true);
            } else {
                $data['listpage'][] = array('text'=>$i, 'current'=>false, 'url'=> $this->_url . $i);
            }
        }

        return $data;
    }

    /**
     * 输出下拉菜单式分页的HTML(仅限下拉菜单)
     *
     * @access public
     *
     * @return mixed
     */
    public function select() {

        //获取分页数组
        $data = $this->_processData();
        if (!$data) {
            return null;
        }

        $string = '<select name="doitphp_select_pagelist" class="pagelist_select_box" onchange="self.location.href=this.options[this.selectedIndex].value">';
        for ($i = 1; $i <= $data['totalpage']; $i ++) {
            $string .= ($i == $data['page']) ? '<option value="' . $data['url'] . $i . '" selected="selected">' . $i . '</option>' : '<option value="' . $data['url'] . $i . '">' . $i . '</option>';
        }
        $string .= '</select>';

        return $string;
    }

    /**
     * 加载pager的CSS文件
     *
     * @access public
     *
     * @param string $styleName
     *
     * @return string
     */
    public function loadCss($styleName = 'classic') {

        //设置分页CSS样式
        if (!$this->_styleFile) {
            $this->setMode($styleName);
        }

        $cssFile = Controller::getBaseUrl() . '/assets/doit/images/' . $this->_styleFile;

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $cssFile . "\"/>\r";
    }

    /**
     * 视图中加载pager的CSS文件
     *
     * 注：本方法虽与loadCss()功能一样，不过本方法是供在视图中使用的，主要用在ajax分页时。如：Pagination::loadCssFile();
     *
     * @access public
     *
     * @param string $styleName
     *
     * @return string
     */
    public static function loadCssFile($styleName = 'classic') {

        //设置分页CSS样式
        switch ($styleName) {
            case 'classic':
                $_styleFile = 'doitphp_pagelist_classic.min.css';
                break;

            default:
                $_styleFile = 'doitphp_pagelist_default.min.css';
        }

        $cssFile = Controller::getBaseUrl() . '/assets/doit/images/' . $_styleFile;

        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $cssFile . "\"/>\r";
    }

    /**
     * 设置分页的样式
     *
     * 注：一般情况下用不到本方法，除非使用ajax分页时
     *
     * @access public
     *
     * @param string $styleName 分页样式名
     *
     * @return object
     */
    public function setMode($styleName = 'classic') {

        switch ($styleName) {

            case 'classic':
                $this->_styleFile = 'doitphp_pagelist_classic.min.css';
                break;

            default:
                $this->_styleFile = 'doitphp_pagelist_default.min.css';
                $this->note       = '<li class="pagelist_note">共{$totalNum}条{$totalPage}页 {$num}条/页</li>';
        }

        return $this;
    }
}