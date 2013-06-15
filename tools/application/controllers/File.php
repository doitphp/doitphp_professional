<?php
/**
 * 项目文件管理操作
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: File.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class FileController extends PublicController {

	/**
	 * 显示项目文件及目录
	 *
	 * @access public
	 * @return string
	 */
	public function indexAction() {

		//get params
		$dir = $this->get('path');
		$dir = (!$dir) ? $dir : str_replace('//', '/', $dir);

		$webAppPath = Configure::get('webappPath');

		//parse path
		$path = $webAppPath . $dir;
		$path = str_replace(array('\\', '//'), '/', $path);
		if (!is_dir($path)) {
			$this->showMsg('对不起，所要显示文件的目录不存在！');
		}

		$fileModel = $this->model('File');

		//assign params
		$this->assign(array(
		'webAppPath'    => $webAppPath,
		'fileList'      => $fileModel->getFileList($path),
		'dir'           => $dir,
		'path'          => $path,
		'isSystem'      => $fileModel->isSystemDir($dir),
		'isController'  => $fileModel->isControllerDir($dir),
		'isWidget'      => $fileModel->isWidgetDir($dir),
		'isModel'       => $fileModel->isModelDir($dir),
		'isModule'      => $fileModel->isModuleDir($dir),
		'isLibrary'     => $fileModel->isLibraryDir($dir),
		'isExtensionDir'=> $fileModel->isExtensionDir($dir),
		'returnUrl'     => $this->getSelfUrl() . $fileModel->parseReturnUrl($dir),
		));

		//display page
		$this->display();
	}

	/**
	 * ajax调用：创建目录
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreatedirAction() {

	}

	/**
	 * ajax调用：文件上传
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxfileuploadAction() {

		//get params
	    $dirName     = $this->post('uploadDirName');
	    $uploadFile  = $_FILES['upload_file'];
	    if (!$dirName) {
	    	$this->ajax(false, '对不起！上传文件参数错误');
	    }

		//判断所上传的目录是否存在
        if (!is_dir($dirName)) {
            $this->ajax(false, '对不起，所要上传文件的目录不存在！');
        }

        $newFile = $dirName . '/' . $uploadFile['name'];

        //判断所要上传的文件是否存在
        if (is_file($newFile)) {
        	$this->ajax(false, '对不起，所要上传的文件已经存在！');
        }

        $fileUploadObj = $this->instance('FileUpload');

        $result = $fileUploadObj->setLimitSize(1024*1024*8)->render($uploadFile, $newFile);

        (!$result) ? $this->ajax(false, '对不起！操作失败，请重新操作') : $this->ajax(true, '文件上传成功！');
	}

	/**
	 * ajax调用：项目的文件删除操作
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxdeletefileAction() {

		//get params
		$dirName     = $this->post('dir_name');
	    $fileName    = $this->post('file_name');
	    if (!$dirName || !$fileName) {
	    	$this->ajax(false, '对不起，错误的参数调用');
	    }

	    //parse file path
	    $filePath = $dirName . '/' . $fileName;
	    if (!is_file($filePath)) {
	    	$this->ajax(false, '对不起，所要删除的文件不存在！');
	    }

	    if (!unlink($filePath)) {
	    	$this->ajax(false, '对不起，文件删除操作失败！请重新操作');
	    }

		$this->ajax(true, '文件删除成功！');
	}

	/**
	 * ajax调用：目录删除操作
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxdeletedirAction() {

	}

	/**
	 * ajax调用：文件及目录的重命名
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxrenameAction() {

	}

}