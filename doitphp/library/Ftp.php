<?php
/**
 * FTP操作类
 *
 * @author DaBing<InitPHP>, tommy
 * @copyright  CopyRight DoitPHP team, initphp team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Ftp.php 2.0 2012-12-23 00:05:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Ftp {

    /**
     * FTP 连接 ID
     *
     * @var object
     */
    private $_linkId;


    /**
     * 连接FTP服务器
     *
     * @access public
     *
     * @param string $server FTP服务器地址
     * @param integer $port FTP服务器端口
     * @param string $username FTP用户名
     * @param string $password FTP密码
     *
     * @return boolean
     */
    public function connect($server, $port = 21, $username, $password) {

        //参数分析
        if (!$server || !$username || !$password) {
            return false;
        }

        try {
            $this->_linkId = @ftp_connect($server, $port);
            if (!@ftp_login($this->_linkId, $username, $password)){
                Controller::showMsg('Ftp Server 登陆失败');
            }

            //打开被动模拟
            ftp_pasv($this->_linkId, 1);

            return true;
        } catch (Exception $exception) {

            //抛出异常信息
            throw new DoitException('Ftp server connect error!<br/>' . $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * FTP-文件上传
     *
     * @access public
     *
     * @param string  $localFile 本地文件
     * @param string  $ftpFile Ftp文件
     *
     * @return boolean
     */
    public function upload($localFile, $ftpFile) {

        if (!$localFile || !$ftpFile) {
            return false;
        }

        $ftpPath = dirname($ftpFile);
        if ($ftpPath) {
            //创建目录
            $this->makeDir($ftpPath);
            @ftp_chdir($this->_linkId, $ftpPath);
            $ftpFile = basename($ftpFile);
        }

        $ret = ftp_nb_put($this->_linkId, $ftpFile, $localFile, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($this->_linkId);
           }

        if ($ret != FTP_FINISHED) {
            return false;
        }

        return true;
    }

    /**
     * FTP-文件下载
     *
     * @access public
     *
     * @param string  $localFile 本地文件
     * @param string  $ftpFile Ftp文件
     *
     * @return boolean
     */
    public function download($localFile, $ftpFile) {

        if (!$localFile || !$ftpFile) {
            return false;
        }

        $ret = ftp_nb_get($this->_linkId, $localFile, $ftpFile, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
               $ret = ftp_nb_continue ($this->_linkId);
        }

        if ($ret != FTP_FINISHED) {
            return false;
        }

        return true;
    }

    /**
     * FTP-创建目录
     *
     * @access public
     *
     * @param string  $path 路径地址
     *
     * @return boolean
     */
    public function makeDir($path) {

        if (!$path) {
            return false;
        }

           $dir  = explode("/", $path);
           $path = ftp_pwd($this->_linkId) . '/';
           $ret  = true;
           for ($i=0; $i<count($dir); $i++) {
            $path = $path . $dir[$i] . '/';
            if (!@ftp_chdir($this->_linkId, $path)) {
                if (!@ftp_mkdir($this->_linkId, $dir[$i])) {
                    $ret = false;
                    break;
                }
            }
            @ftp_chdir($this->_linkId, $path);
         }

        if (!$ret) {
            return false;
        }

         return true;
    }

    /**
     * FTP-删除文件目录
     *
     * @access public
     *
     * @param string  $dir 删除文件目录
     *
     * @return boolean
     */
    public function deleteDir($dir) {

        //参数分析
        if (!$dir) {
            return false;
        }

        $dir = $this->checkpath($dir);
        if (@!ftp_rmdir($this->_linkId, $dir)) {
            return false;
        }

        return true;
    }

    /**
     * FTP-删除文件
     *
     * @access public
     *
     * @param string  $file 删除文件
     *
     * @return boolean
     */
    public function deleteFile($file) {

        //参数分析
        if (!$file) {
            return false;
        }

        $file = $this->checkpath($file);
        if (@!ftp_delete($this->_linkId, $file)) {
            return false;
        }

        return true;
    }

    /**
     * FTP-FTP上的文件列表
     *
     * @access public
     *
     * @param string $path 路径
     *
     * @return boolean
     */
    public function nlist($path = '/') {

        return ftp_nlist($this->_linkId, $path);
    }

    /**
     * FTP-改变文件权限值
     *
     * @access public
     *
     * @param string $file 文件
     * @param string $value  值
     *
     * @return boolean
     */
    public function chmod($file, $value = 0777) {

        //参数分析
        if (!$file) {
            return false;
        }

        return @ftp_chmod($this->_linkId, $value, $file);
    }

    /**
     * FTP-返回文件大小
     *
     * @access public
     *
     * @param string $file 文件
     *
     * @return boolean
     */
    public function fileSize($file) {

        //参数分析
        if (!$file) {
            return false;
        }

        return ftp_size($this->_linkId, $file);
    }

    /**
     * FTP-文件修改时间
     *
     * @access public
     *
     * @param string $file 文件
     *
     * @return boolean
     */
    public function mdtime($file) {

        //参数分析
        if (!$file) {
            return false;
        }

        return ftp_mdtm($this->_linkId, $file);
    }

    /**
     * FTP-更改ftp上的文件名称
     *
     * @access public
     *
     * @param string $oldname 旧文件
     * @param string $newname 新文件名称
     *
     * @return boolean
     */
    public function rename($oldname, $newname) {

        //参数分析
        if (!$oldname || !$newname) {
            return false;
        }

        return ftp_rename ($this->_linkId, $oldname, $newname);
    }

    /**
     * 析构函数
     *
     * @access public
     * @return boolean
     */
    public function __destruct() {

        if ($this->_linkId) {
            ftp_close($this->_linkId);
        }

        return true;
    }
}
