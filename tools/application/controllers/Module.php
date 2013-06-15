<?php
/**
 * 模块目录的创建
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Module.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class ModuleController extends PublicController {

	/**
	 * 创建模块目录
	 *
	 * @access public
	 * @return string
	 */
	public function createmoduleAction() {

	    //get params
	    $path = $this->get('path');
	    if (!$path) {
	        $this->showMsg('对不起，错误的网址调用！');
	    }

	    //assign params
	    $this->assign(array(
            'path' => $path,
            'assetUrl' => $this->getAssetUrl('doit/js'),
	    ));

	    //display page
	    $this->display('ctr/createmodule');
	}

	/**
	 * ajax调用：创建模块目录
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreatemoduleAction() {

	    //获取参数
	    $modulePath = $this->post('path_box');
	    $moduleName = $this->post('module_name_box');

	    $widgetStatus = $this->post('widget_state');
	    $langStatus   = $this->post('lang_state');

	    if (!$modulePath || !$moduleName) {
	        $this->ajax(false, '对不起，错误的参数调用！');
	    }

	    $webAppPath  = Configure::get('webappPath');
	    $webAppPath  = str_replace('\\', '/', $webAppPath);

	    $moduleDir   = $webAppPath . $modulePath . '/' . $moduleName;
	    $moduleDir   = str_replace('//', '/', $moduleDir);

	    //判断module目录是否存在
	    if (is_dir($moduleDir)) {
	        $this->ajax(false, '对不起，所要创建的Module已存在！');
	    }

	    $moduleDirList = array(
        'controllers',
        'models',
        'views',
        'library',
	    );

	    if ($widgetStatus == 'on') {
	        $moduleDirList[] = 'widgets';
	    }
	    if ($langStatus == 'on') {
	        $moduleDirList[] = 'language';
	    }

	    foreach ($moduleDirList as $lines) {
	        $itemDir = $moduleDir . '/' . $lines;
	        if (!is_dir($itemDir)) {
	            File::makeDir($itemDir);
	        }
	    }

	    $this->ajax(true, '操作成功');
	}

}