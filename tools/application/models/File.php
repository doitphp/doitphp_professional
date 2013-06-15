<?php
/**
 * 文件管理
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: File.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package model
 * @since 1.0
 */

class FileModel {

	/**
	 * 获取目录内的文件列表数据
	 *
	 * @access public
	 *
	 * @param string $dirPath 目录的路径
	 *
	 * @return array
	 */
	public function getFileList($dirPath) {

		//parse params
		if (!$dirPath) {
			return false;
		}

		//get file list data
        $fileObject = new DirectoryIterator($dirPath);

        $fileArray = array();
        foreach ($fileObject as $lines) {
            //文件过滤
            if ($lines->isDot()) {
                continue;
            }
            $mod = '';
            if ($lines->isReadable()) {
                $mod .= 'r ';
            }
            if ($lines->isWritable()) {
                $mod .= 'w ';
            }
            if ($lines->isExecutable()) {
                $mod .= 'x ';
            }

            //parse ico image
            $extension = strtolower(substr(strrchr($lines->getFilename(), '.'), 1));
            switch ($extension) {
                case 'php':
                    $ico = 'php.gif';
                    break;

                case 'html':
                    $ico = 'htm.gif';
                    break;

                case 'txt':
                    $ico = 'txt.gif';
                    break;

                case 'css':
                    $ico = 'css.gif';
                    break;

                case 'js':
                    $ico = 'js.gif';
                    break;

                case 'gif':
                   $ico = 'gif.gif';
                   break;

                case 'jpg':
                case 'jpeg':
                   $ico = 'jpg.gif';
                   break;

                case 'png':
                    $ico = 'image.gif';
                    break;

                default:$ico = '';
            }

            $fileArray[] = array(
            'name'	        => $lines->getFilename(),
            'size'	        => File::formatBytes($lines->getSize()),
            'isdir'         => $lines->isDir(),
            'time'	        => date('Y-m-d H:i:s', $lines->getMTime()),
            'ico'           => $ico,
            'mod'			=> $mod,
            'ext'			=> $extension,
            );
        }

        return $fileArray;
	}

	/**
	 * 分析目录是否为系统目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isSystemDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return  (substr($dirName, 0, 8) == '/doitphp' || substr($dirName, 0, 6) == '/tools' || substr($dirName, 0, 12) == '/assets/doit') ? true : false;
	}

	/**
	 * 分析目录是否为Controller目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isControllerDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return (substr($dirName, -11) == 'controllers') ? true : false;
	}

	/**
	 * 分析目录是否为Widget目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isWidgetDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return (substr($dirName, -7) == 'widgets') ? true : false;
	}

	/**
	 * 分析目录是否为Model目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isModelDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}
		//排除特殊情况：cache目录中的子目录:models
		if ($dirName == '/cache/models') {
			return false;
		}

		return (substr($dirName, -6) == 'models') ? true : false;
	}

	/**
	 * 分析目录是否为Extension目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isExtensionDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return (substr($dirName, -10) == 'extensions') ? true : false;
	}

	/**
	 * 分析目录是否为library目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isLibraryDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return (substr($dirName, -7) == 'library') ? true : false;
	}

	/**
	 * 分析目录是否为Module目录
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return boolean
	 */
	public function isModuleDir($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		return (substr($dirName, -7) == 'modules') ? true : false;
	}

	/**
	 * 分析上一级目录名称
	 *
	 * @access public
	 *
	 * @param string $dirName 目录的名称
	 *
	 * @return string
	 */
	public function parseReturnUrl($dirName) {

		//parse params
		if (!$dirName) {
			return false;
		}

		$parentDir = str_replace('\\', '/', dirname($dirName));

		return ($dirName && $dirName != '/' && $parentDir != '/') ? '/?path=' . $parentDir : '';
	}
}