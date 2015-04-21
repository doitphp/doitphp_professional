<?php
/**
 * 文件上传类
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: FileUpload.php 2.0 2012-12-23 22:30:38Z tommy <tommy@doitphp.com> $
 * @package library
 * @since 1.0
 */

class FileUpload {

    /**
     * 文件限制大小（默认：8M）
     *
     * @var integer
     */
    protected $_limitSize = 8388608;

    /**
     * 上传的文件信息
     *
     * @var string
     */
    protected $_files = array();

    /**
     * 文件的限制类型
     *
     * @var string
     */
    protected $_limitType = array();

    /**
     * 返回的错误信息
     *
     * @var string
     */
    protected $_errorMsg = null;

    /**
     * 文件上传处理
     *
     * @access public
     *
     * @param array $files $_FILE的参数名
     * @param string $destFile 上传后的文件路径
     *
     * @return boolean
     */
    public function render($files, $destFile) {

        //参数分析
        if (!$files || !$destFile) {
            return false;
        }

        $this->_files = $files;

        //分析文件大小
        if (!$this->_parseFileSize()) {
            return false;
        }

        //分析文件类型
        if (!$this->_parseFileType()) {
            return false;
        }

        if (!move_uploaded_file($this->_files['tmp_name'], $destFile)) {
            $this->_errorMsg = '文件上传失败！请重新上传';
            return false;
        }

        return true;
    }

    /**
     * 设置上传文件的限制格式，即：文件后缀。
     *
     * @access public
     *
     * @param array $type 所限制上传文件后缀。注：本参数为数组
     *
     * @return object
     */
    public function setLimitType($type) {

        //参数分析
        if ($type && is_array($type)) {
            $this->_limitType = $type;
        }

        return $this;
    }

    /**
     * 设置上传文件的最大的限制大小
     *
     * @access public
     *
     * @param integer $fileSize 文件的大小（file size）。
     *
     * @return object
     */
    public function setLimitSize($fileSize) {

        //参数分析
        if ($fileSize) {
            $this->_limitSize = (int)$fileSize;
        }

        return $this;
    }

    /**
     * 获取错误提示信息
     *
     * @access public
     * @return string
     */
    public function getErrorInfo() {

        return $this->_errorMsg;
    }

    /**
     * 分析文件大小
     *
     * @access protected
     * @return boolean
     */
    protected function _parseFileSize() {

        if ($this->_limitSize) {
            if ($this->_files['size'] > $this->_limitSize) {
                $this->_errorMsg = '上传文件的大小超出所允许的上传范围！';
                return false;
            }
        }

        return true;
    }

    /**
     * 分析文件的格式类型（Mime Tyep）
     *
     * @access protected
     * @return boolean
     */
    protected function _parseFileType() {

        if ($this->_limitType && is_array($this->_limitType)) {

            //获取文件后缀
            $extName = strtolower(substr(strrchr($this->_files['name'], '.'), 1));
            if (!in_array($extName, $this->_limitType)) {
                $this->_errorMsg = '上传文件的格式不正确！';
                return false;
            }
        }

        return true;
    }

}