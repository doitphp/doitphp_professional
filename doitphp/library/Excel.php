<?php
/**
 * 用于生成excel文件操作
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Excel.php 2.0 2012-12-23 00:50:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Excel {

    /**
     * EXCEL表格的xml代码
     *
     * @var string
     */
    protected $_xmlTable;

    /**
     * EXCEL的标题xml代码
     *
     * @var string
     */
    protected $_xmlMenu;

    /**
     * 处理EXCEL中一行代码,相当于HTML中的行标签<tr></tr>
     *
     * @access protected
     *
     * @param  array $data 行数据
     *
     * @return string
     */
    protected function _handleRow($data) {

        //参数分析
        if (empty($data) || !is_array($data)) {
            return false;
        }

        $xml = "<Row>\n";
        foreach ($data as $key=>$value) {
            $xml .= ($key>0&&empty($data[$key-1])) ? $this->_handleIndexCell($value, $key+1) : $this->_handleCell($value);
        }
        $xml .= "</Row>\n";

        return $xml;
    }

    /**
     * 处理EXCEL多行数据的代码.
     *
     * @access protected
     *
     * @param array $data 多行数据
     *
     * @return string
     */
    protected function _addRows($data) {

        //参数分析
        if (empty($data) || !is_array($data) || !is_array($data[0])) {
            return false;
        }

        $xmlArray = array();
        foreach ($data as $row) {
            $xmlArray[] = $this->_handleRow($row);
        }

        return implode('', $xmlArray);
    }

    /**
     * 配置EXCEL表格的标题
     *
     * @access public
     *
     * @param array $data 所要生成的excel的标题,注:参数为数组
     *
     * @return boolean
     */
    public function setMenu($data) {

        //参数分析
        if (empty($data) || !is_array($data) || is_array($data[0]) || array_search('', $data)) {
            return false;
        }

        $xml = "<Row>\n";
        foreach ($data as $value) {
            $type = (is_numeric($data)&&(substr($data, 0, 1)!=0)) ? 'Number' : 'String';
            $xml .= "<Cell><Data ss:Type=\"{$type}\">" . $value . "</Data></Cell>\n";
        }
        $xml .= "</Row>\n";
        $this->_xmlMenu = $xml;

        return true;
    }

    /**
     * 处理EXCEL表格的内容,相当于table.
     *
     * @access public
     *
     * @param array $data Excel内容数据
     *
     * @return string
     */
    public function setData($data) {

        //参数分析
        if (!$data || !is_array($data) || !is_array($data[0])) {
            return false;
        }

        $xmlRows = $this->_addRows($data);

        if (!$xmlRows) {
            if (!$this->_xmlMenu) {
                return false;
            } else {
                $content = $this->_xmlMenu;
            }
        } else {
            if (!$this->_xmlMenu) {
                $content = $xmlRows;
            } else {
                $content = $this->_xmlMenu.$xmlRows;
            }
        }

        return $this->_xmlTable = "<Table>\n" . $content . "</Table>\n";
    }

    /**
     * 处理EXCEL表格信息代码
     *
     * @access protected
     * @return string
     */
    protected function _parseTable() {

        $xmlWorksheet = "<Worksheet ss:Name=\"Sheet1\">\n";

        if (empty($this->_xmlTable)) {
            $xmlWorksheet .= "<Table/>\n";
        } else{
            $xmlWorksheet .= $this->_xmlTable;
        }

        $xmlWorksheet .= "</Worksheet>\n";

        return $xmlWorksheet;
    }

    /**
     * 处理EXCEL中的表格,相当于html中的标签<td></td>
     *
     * @access protected
     *
     * @param string $data 表格数据
     *
     * @return string
     */
    protected function _handleCell($data) {

        //参数分析
        if (is_array($data)) {
            return false;
        }

        $type = (is_numeric($data)&&(substr($data, 0, 1)!=0)) ? 'Number' : 'String';

        return "<Cell><Data ss:Type=\"".$type."\">" . $data . "</Data></Cell>\n";
    }

    /**
     * 处理EXCEL中CELL代码,当该CELL前的一个CELL内容为空时.
     *
     * @access protected
     *
     * @param string $data 表格数据
     * @param integer $key 键值
     *
     * @return string
     */
    protected function _handleIndexCell($data, $key) {

        //参数分析
        if (is_array($data)) {
            return false;
        }

        $type = (is_numeric($data)&&(substr($data, 0, 1)!=0)) ? 'Number' : 'String';

        return "<Cell ss:Index=\"" . $key . "\"><Data ss:Type=\"" . $type . "\">" . $data . "</Data></Cell>\n";
    }


    /**
     * 分析EXCEL的文件头
     *
     * @access protected
     * @return string
     */
    protected function _parseHeader() {

        return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:o=\"urn:schemas-microsoft-com:office:office\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
    }

    /**
     * 生成EXCEL文件并下载.
     *
     * @access public
     *
     * @param string $fileName 所要生成的excel的文件名,注:文件名中不含后缀名
     *
     * @return void
     */
    public function download($fileName) {

        //参数分析
        if (empty($fileName)) {
            return false;
        }

        header('Pragma: no-cache');
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: inline; filename=\"" . $fileName . ".xls\"");
        $excelXml = $this->_parseHeader().$this->_parseTable()."</Workbook>";

        echo $excelXml;
    }

    /**
     * 析构函数(类方法)
     *
     * @return void
     */
    public function __destruct(){

        exit();
    }
}