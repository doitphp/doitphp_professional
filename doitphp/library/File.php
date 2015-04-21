<?php
/**
 * 文件管理操作类
 *
 * 用于文件夹内容的读取,复制,剪切等操作
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: File.php 2.0 2012-12-23 18:30:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class File {

    /**
     * 分析目标目录的读写权限
     *
     * @access public
     *
     * @param string $dirName 目标目录
     * @param string $mod 权限值
     *
     * @return boolean
     */
    public static function makeDir($dirName, $mode = 0755) {

        //参数分析
        if (!$dirName) {
            return false;
        }

        if (is_dir($dirName)) {
            return true;
        }
        mkdir($dirName, $mode, true);

        return true;
    }

    /**
     * 获取目录内文件
     *
     * @access public
     *
     * @param string $dirName 所要读取内容的目录名
     *
     * @return string
     */
    public static function readDir($dirName) {

        //参数分析
        if (!$dirName) {
            return false;
        }

        //define filter file name
        $filterArray = array('.cvs', '.svn', '.git');

        $dir    = self::_parseDir($dirName);
        $handle = opendir($dir);

        $files  = array();
        while (false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..' || in_array($file, $filterArray)) {
                continue;
            }
            $files[] = $file;
        }

        closedir($handle);

        return $files;
    }

    /**
     * 将一个文件夹内容复制到另一个文件夹
     *
     * @access public
     *
     * @param string $source 被复制的文件夹名
     * @param string $dest 所要复制文件的目标文件夹
     *
     * @return boolean
     */
    public static function copyDir($source, $dest) {

        //参数分析
        if (!$source || !$dest) {
            return false;
        }

        $parseDir  = self::_parseDir($source);
        $destDir   = self::_parseDir($dest, true);

        $fileList  = self::readDir($parseDir);

        foreach ($fileList as $file) {
            if (is_dir($parseDir . DS . $file)) {
                self::copyDir($parseDir . DS . $file, $destDir . DS . $file);
            } else {
                copy($parseDir . DS . $file, $destDir . DS . $file);
            }
        }

        return true;
    }

    /**
     * 移动文件夹, 相当于WIN下的ctr+x(剪切操作)
     *
     * @access public
     *
     * @param string $source 原目录名
     * @param string $dest 目标目录
     *
     * @return boolean
     */
    public static function moveDir($source, $dest) {

        //参数分析
        if (!$source || !$dest) {
            return false;
        }

        $parseDir = self::_parseDir($source);
        $destDir  = self::_parseDir($dest, true);

        $fileList = self::readDir($parseDir);

        foreach ($fileList as $file) {
            if (is_dir($parseDir . DS . $file)) {
                self::moveDir($parseDir . DS . $file, $destDir . DS . $file);
            } else {
                if (copy($parseDir . DS . $file, $destDir . DS . $file)) {
                    unlink($parseDir . DS . $file);
                }
            }
        }

        rmdir($parseDir);

        return true;
    }

    /**
     * 删除文件夹
     *
     * @access public
     *
     * @param string $fileDir 所要删除文件的路径
     *
     * @return boolean
     */
    public static function deleteDir($fileDir) {

        //参数分析
        if (!$fileDir){
            return false;
        }

        //清空子目录及内部文件
        self::clearDir($fileDir);

        rmdir($fileDir);

        return true;
    }

    /**
     * 清空文件夹内的文件及子目录
     *
     * @access public
     *
     * @param string $dirName 所要清空内容的文件夹名称
     * @param boolean $option 是否删除子目录, 注：当为false时,只删除子目录中的文件,目录不会删除
     *
     * @return boolean
     */
    public static function clearDir($dirName, $option = true) {

        //参数分析
        if (!$dirName){
            return false;
        }

        $parseDir = self::_parseDir($dirName);
        $fileList = self::readDir($parseDir);

        foreach ($fileList as $file) {
            if (is_dir($parseDir . DS . $file)) {
                self::clearDir($parseDir . DS . $file, $option);
                if ($option == true) {
                    rmdir($parseDir . DS . $file);
                }
            } else {
                unlink($parseDir . DS . $file);
            }
        }

        return true;
    }

    /**
     * 分析文件夹是否存在
     *
     * @access protected
     *
     * @param string $dirName 所要操作的文件目录名
     * @param boolean $isMkdir 是否创建目录
     *
     * @return string
     */
    protected static function _parseDir($dirName, $isMkdir = false) {

        //参数分析
        if (!$dirName) {
            return false;
        }

        if ($isMkdir === true) {
            self::makeDir($dirName, 0755);
        } else {
            if (!is_dir($dirName)) {
                Controller::halt('The dir: ' . $dirName . ' is not found!');
            }
        }

        return $dirName;
    }

    /**
     * 文件写操作
     *
     * @access public
     *
     * @param string $fileName 文件路径
     * @param string $content 文件内容
     *
     * @return boolean
     */
    public static function writeFile($fileName, $content = '') {

        //参数分析
        if (!$fileName) {
            return false;
        }

        //分析文件目录
        self::_parseDir(dirname($fileName), true);

        return file_put_contents($fileName, $content, LOCK_EX);
    }

    /**
     * 文件复制
     *
     * @access public
     *
     * @param string $sourceFile 源文件(被复制的文件)
     * @param string $destFile 所要复制的文件
     *
     * @return boolean
     */
    public static function copyFile($sourceFile, $destFile) {

        //参数分析
        if (!$sourceFile || !$destFile) {
            return false;
        }

        //文件及目录分析
        if (!is_file($sourceFile)) {
            Controller::halt('The file: ' . $sourceFile . ' is not found!');
        }
        self::_parseDir(dirname($destFile), true);

        return copy($sourceFile, $destFile);
    }

    /**
     * 文件重命名或移动文件
     *
     * @access public
     *
     * @param string $sourceFile 源文件
     * @param string $destFile 新文件名或路径
     *
     * @return boolean
     */
    public static function moveFile($sourceFile, $destFile) {

        //参数分析
        if (!$sourceFile || !$destFile) {
            return false;
        }

        //文件及目录分析
        if (!is_file($sourceFile)) {
            Controller::halt('The file:' . $sourceFile . ' is not found!');
        }
        self::_parseDir(dirname($destFile), true);

        return rename($sourceFile, $destFile);
    }

    /**
     * 删除文件
     *
     * @access public
     *
     * @param string $fileName 文件路径
     *
     * @return boolean
     */
    public static function deleteFile($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        //文件分析
        if (!is_file($fileName)) {
            return true;
        }

        return unlink($fileName);
    }

    /**
     * 字节格式化 把字节数格式为 B K M G T 描述的大小
     *
     * @access public
     *
     * @param integer $bytes 文件大小
     * @param integer $dec 小数点后的位数
     *
     * @return string
     */
    public static function formatBytes($bytes, $dec = 2) {

        $unitPow = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $pos = 0;
        while ($bytes >= 1024) {
             $bytes /= 1024;
             $pos++;
        }

        return round($bytes, $dec) . ' ' . $unitPow[$pos];
    }
}