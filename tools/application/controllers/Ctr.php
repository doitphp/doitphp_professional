<?php
/**
 * 控制器文件操作
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Ctr.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class CtrController extends PublicController {

	/**
	 * 创建controller文件
	 *
	 * 注：高级操作
	 *
	 * @access public
	 * @return string
	 */
	public function createcontrollerAction() {

		//get params
		$path = $this->get('path');
		if (!$path) {
			$this->showMsg('对不起，错误的网址调用！');
		}

		//instance cookie storage
		$storageObj = $this->instance('noteStorage');
		$actionObj  = $this->instance('actionStorage');
		$methodObj  = $this->instance('controllerMethodStorage');

		//assign params
		$this->assign(array(
		'path' => $path,
		'assetUrl' => $this->getAssetUrl('doit/js'),
		'timeNow' => time(),
		'actionList' => $actionObj->getAction(),
		'methodList' => $methodObj->getMethodList(),
		'viewDirStatus' => (Cookie::get('controller_view_state') == 'on') ? true : false,
		'viewFileStatus' => (Cookie::get('controller_view_file_state') == 'on') ? true : false,
		'viewFileExt' => Cookie::get('controller_view_file_ext'),
		));

		//display page
		$this->display();
	}

	/**
	 * ajax调用：创建Controller文件
	 *
	 * 注：高级操作
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreatecontrollerAction() {

		//get params
		$controllerPath = $this->post('path_box');
		if (!$controllerPath) {
			$this->ajax(false, '对不起，错误的参数调用！');
		}

		$controllerName = $this->post('controller_name_box');
		if ($controllerName) {
			$controllerName = trim($controllerName, '_');
		}
		if (!$controllerName) {
			$this->ajax(false, '对不起，Controller 名称不能为空！');
		}
		$controllerName = ucfirst(strtolower($controllerName));

		$viewStatus     = $this->post('controller_view_state');
		$viewFileStatus = $this->post('controller_view_file_state');
		$viewFileExt    = $this->post('controller_view_file_ext', 'php');

		$viewStatus     = ($viewStatus == 'on') ? true : false;
		$viewFileStatus = ($viewFileStatus == 'on') ? true : false;

		$author      = $this->post('note_author_box');
		$copyright   = $this->post('note_copyright_box');
		$license     = $this->post('note_license_box');
		$link        = $this->post('note_link_box');
		$description = $this->post('note_description_box');

		//parse controller file path
		$webAppPath = Configure::get('webappPath');
		$webAppPath = str_replace('\\', '/', $webAppPath);

		$controllerFilePath = $webAppPath . $controllerPath;
		$controllerFilePath = str_replace('//', '/', $controllerFilePath);

		//当controller name 中有下划线时
		$pos = strpos($controllerName, '_');
		if ($pos !== false) {
			$childDirArray       = explode('_', strtolower($controllerName));
			$controllerFileName  = ucfirst(array_pop($childDirArray)) . '.php';
			$controllerFilePath .= '/' . implode('/', $childDirArray);

		} else {
		    $controllerFileName  = $controllerName . '.php';
		}
		$controllerFilePath .= '/' . $controllerFileName;

		//当所要创建的Controller文件存在时
		if (is_file($controllerFilePath)) {
			$this->ajax(false, '对不起，所要创建的Controller文件已存在！');
		}

		//instance storage object
		$storageObj       = $this->instance('noteStorage');
		$actionObj        = $this->instance('actionStorage');
		$methodStorageObj = $this->instance('controllerMethodStorage');

		$actionList = $actionObj->getAction();
		$methodList = $methodStorageObj->getMethodList();

		//parse controller file content
		$controllerContent  = "<?php\n";
		$controllerContent .= fileCreator::fileNote($controllerFileName, $description, $author, $copyright, 'Controller', null, $license, $link);
		$controllerContent .= fileCreator::classCodeStart($controllerName . 'Controller', 'Controller', false);

		//parse contoller file action content
		$actionArray = array();
		if ($actionList) {
			foreach ($actionList as $lines) {
				$controllerContent .= fileCreator::methodNote('public', 'void', null, $lines['desc']);
				$controllerContent .= fileCreator::methodCode($lines['name'] . 'Action', 'public');
				$actionArray[] = strtolower($lines['name']);
			}
		} else {
		    $controllerContent .= fileCreator::methodNote('public', 'void', null, '引导页');
		    $controllerContent .= fileCreator::methodCode('indexAction', 'public');
		    $actionArray[] = strtolower('index');
		}

		//parse controller file method content
		if ($methodList) {
			foreach ($methodList as $lines) {
				//parse method params data
				$methodNoteParams = array();
				$methodCodeParams = array();
				if (isset($lines['params'])) {
					foreach ($lines['params'] as $rows) {
						$methodNoteParams[] = array($rows['name'], $rows['type'], $rows['desc']);
						if (is_null($rows['default']) || $rows['default'] == '') {
							$methodCodeParams[] = $rows['name'];
						} else {
							$methodCodeParams[$rows['name']] = $rows['default'];
						}
					}
				}
				$controllerContent .= fileCreator::methodNote($lines['access'], $lines['return'], $methodNoteParams, $lines['desc']);
				$controllerContent .= fileCreator::methodCode($lines['name'], $lines['access'], $methodCodeParams);
			}
		}

		$controllerContent .= fileCreator::classCodeEnd();

		//生成controller文件
		$result = File::writeFile($controllerFilePath, $controllerContent);

		if (!$result) {
			$this->ajax(false, '对不起，生成Controller文件失败！请重新操作');
		}

		//处理视图目录及视图文件
		if ($viewStatus) {
			//分析视图目录的路径
			$viewDirPath = $webAppPath . $controllerPath;
			$viewDirPath = substr(str_replace('//', '/', $viewDirPath), 0, -11) . 'views/' . strtolower($controllerName);

			File::makeDir($viewDirPath);

			//生成视图文件
			if ($viewFileStatus && $actionArray) {
				foreach ($actionArray as $actionName) {
					$viewFilePath = $viewDirPath . '/' . $actionName . '.' . $viewFileExt;
					File::writeFile($viewFilePath);
				}
			}
		}

		//cookie数据处理
		$actionObj->clearAction();
		$methodStorageObj->clearMethod();

		$storageObj->setNote($author, $copyright, $license, $link);

		$this->ajax(true, '操作成功！');
	}

	/**
	 * 新增action信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function addactionAction() {

		//display page
		$this->render();
	}

	/**
	 * 新增method信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function addmethodAction() {

		//display page
		$this->render();
	}

	/**
	 * 新增action信息(ajax调用)
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxaddactionAction() {

		//get params
		$name = $this->post('name');
		$desc = $this->post('desc');

		if (!$name) {
			$this->ajax(false, '对不起，参数不正确！');
		}
		//Action名称统一为小写字母
		$name = strtolower($name);

		//instance cookie storage
		$actionObj  = $this->instance('actionStorage');

		//数据排重
		$actionList = $actionObj->getAction();
		foreach ($actionList as $lines) {
			if ($lines['name'] == $name) {
				$this->ajax(false, '对不起，所要添加的Action名已存在！请更改Action名称');
			}
		}

		$result = $actionObj->addAction($name, $desc);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * 新增method信息(ajax调用)
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxaddmethodAction() {

		//get params
		$name = $this->post('name');
		$desc = $this->post('desc');
		$access = $this->post('access');
		$return = $this->post('returnType');

		if (!$name) {
			$this->ajax(false, '对不起，参数不正确！');
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		//数据排重
		$methodList = $storageObj->getMethodList();
		foreach ($methodList as $lines) {
			if ($lines['name'] == $name) {
				$this->ajax(false, '对不起，所要添加的Method名已存在！请更改Method名称');
			}
		}

		$result = $storageObj->addMethod($name, $desc, $access, $return);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * 新增method参数信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function addmethodparamsAction() {

		//get params
		$id = $this->get('id');
		if (is_null($id)) {
			exit('对不起，错误的网址调用！');
		}

		$this->assign(array(
		'id' => $id,
		));

		//display page
		$this->render();
	}

	/**
	 * 编辑method参数信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function editmethodparamsAction() {

		//get params
		$id = $this->get('id');
		$key = $this->get('key');
		if (is_null($id) || is_null($key)) {
			exit('对不起，错误的网址调用！');
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		//get method params info
		$methodInfo = $storageObj->getMethodInfo($id);
		if (!$methodInfo) {
			exit('对不起，错误的网址调用！');
		}
		if (!isset($methodInfo['params'][$key])) {
			exit('对不起，错误的网址调用！');
		}

		$this->assign(array(
		'id' => $id,
		'key' => $key,
		'paramsInfo' => $methodInfo['params'][$key],
		));

		//display page
		$this->render();
	}

	/**
	 * 编辑action信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function editactionAction() {

		//get params
		$id = $this->get('id');
		if (is_null($id)) {
			exit('对不起，不正确的网址调用！');
		}

		//instance cookie storage
		$actionObj  = $this->instance('actionStorage');
		$actionCookie = $actionObj->getAction();
		if (!isset($actionCookie[$id])) {
			exit('对不起，你所查看的数据不存在！');
		}

		$this->assign(array(
		'id' => $id,
		'actionInfo' => $actionCookie[$id],
		));

		//display page
		$this->render();
	}

	/**
	 * 编辑Method信息(弹窗页面)
	 *
	 * @access public
	 * @return string
	 */
	public function editmethodAction() {

		//get params
		$id = $this->get('id');
		if (is_null($id)) {
			exit('对不起，不正确的网址调用！');
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');
		$methodInfo = $storageObj->getMethodInfo($id);
		if (!$methodInfo) {
			exit('对不起，你所查看的数据不存在！');
		}

		$this->assign(array(
		'id' => $id,
		'methodInfo' => $methodInfo,
		));

		//display page
		$this->render();
	}

	/**
	 * 编辑action信息(ajax调用)
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxeditactionAction() {

		//get params
		$id   = $this->post('id');
		$name = $this->post('name');
		$desc = $this->post('desc');

		if (is_null($id) || !$name) {
			$this->ajax(false, '对不起，参数不正确！');
		}
		//Action名称统一为小写字母
		$name = strtolower($name);

		//instance cookie storage
		$storageObj = $this->instance('actionStorage');

		//数据排重
		$actionList = $storageObj->getAction();
		foreach ($actionList as $key=>$lines) {
			if (($lines['name'] == $name) && ($id != $key)) {
				$this->ajax(false, '对不起，所要编辑的Action名已存在！请更改Action名称');
			}
		}

		$result = $storageObj->editAction($id, $name, $desc);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * 编辑method信息(ajax调用)
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxeditmethodAction() {

		//get params
		$id   = $this->post('id');
		$name = $this->post('name');
		$desc = $this->post('desc');
		$access = $this->post('access');
		$return = $this->post('returnType');

		if (is_null($id) || !$name) {
			$this->ajax(false, '对不起，参数不正确！');
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		//数据排重
		$methodList = $storageObj->getMethodList();
		foreach ($methodList as $key=>$lines) {
			if (($lines['name'] == $name) && ($id != $key)) {
				$this->ajax(false, '对不起，所要添加的Method名已存在！请更改Method名称');
			}
		}

		$result = $storageObj->editMethod($id, $name, $desc, $access, $return);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * ajax调用：删除action
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxdeleteactionAction() {

		//get params
		$id = $this->post('id');
		if (is_null($id)) {
			$this->ajax(false, '对不起，错误的参数调用！');
		}

		//instance cookie storage
		$storageObj = $this->instance('actionStorage');

		$result = $storageObj->deleteAction($id);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * ajax调用：删除method
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxdeletemethodAction() {

		//get params
		$id = $this->post('id');
		if (is_null($id)) {
			$this->ajax(false, '对不起，错误的参数调用！');
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		$result = $storageObj->deleteMethod($id);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * ajax调用：增加method参数
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxaddmethodparamsAction() {

		//get params
		$methodId = $this->post('id');
		$name     = $this->post('name');
		$desc     = $this->post('desc');
		$type     = $this->post('type');
		$default  = $this->post('defaultVal');

		if (is_null($methodId) || !$name) {
			return false;
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		//数据排重
		$methodInfo = $storageObj->getMethodInfo($methodId);
		if (isset($methodInfo['params']) && $methodInfo['params']) {
			foreach ($methodInfo['params'] as $lines) {
				if ($lines['name'] == $name) {
					$this->ajax(false, '对不起，所要添加的参数名已存在！');
				}
			}
		}

		$result = $storageObj->addMethodParams($methodId, $name, $type, $desc, $default);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * ajax调用：编辑method参数
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxeditmethodparamsAction() {

		//get params
		$methodId = $this->post('id');
		$key      = $this->post('key');
		$name     = $this->post('name');
		$desc     = $this->post('desc');
		$type     = $this->post('type');
		$default  = $this->post('defaultVal');

		if (is_null($methodId) || is_null($key) || !$name) {
			return false;
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		//数据排重
		$methodInfo = $storageObj->getMethodInfo($methodId);
		if (isset($methodInfo['params']) && $methodInfo['params']) {
			foreach ($methodInfo['params'] as $id=>$lines) {
				if (($lines['name'] == $name) && ($id != $key)) {
					$this->ajax(false, '对不起，所要添加的参数名已存在！请更改参数名称');
				}
			}
		}

		$result = $storageObj->editMethodParams($methodId, $key, $name, $type, $desc, $default);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * ajax调用：删除method参数
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxdeletemethodparamsAction() {

		//get params
		$methodId = $this->post('id');
		$key      = $this->post('key');

		if (is_null($methodId) || is_null($key)) {
			return false;
		}

		//instance cookie storage
		$storageObj = $this->instance('controllerMethodStorage');

		$result = $storageObj->deleteMethodParams($methodId, $key);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * 创建挂件
	 *
	 * @access public
	 * @return string
	 */
	public function createwidgetAction() {

	    //get params
	    $path = $this->get('path');
	    if (!$path) {
	        $this->showMsg('对不起，错误的网址调用！');
	    }

	    //assign params
	    $this->assign(array(
		'path' => $path,
		'assetUrl' => $this->getAssetUrl('doit/js'),
		'viewFileStatus' => (Cookie::get('widget_view_file_state') == 'on') ? true : false,
		'viewFileExt' => Cookie::get('widget_view_file_ext'),
	    ));

	    //display page
	    $this->display();
	}

	/**
	 * ajax调用：创建挂件
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreatewidgetAction() {

	    //get params
	    $widgetPath  = $this->post('path_box');
	    if (!$widgetPath) {
	        $this->ajax(false, '对不起，错误的参数调用！');
	    }

	    $widgetName  = $this->post('widget_name_box');
	    if ($widgetName) {
	        $widgetName = trim($widgetName, '_');
	    }
	    if (!$widgetName) {
	        $this->ajax(false, '对不起，Widget 名称不能为空！');
	    }
        $viewStatus  = $this->post('widget_view_file_state');
        $viewFileExt = $this->post('widget_view_file_ext', 'php');

        $viewStatus  = ($viewStatus == 'on') ? true : false;

        $author      = $this->post('note_author_box');
        $copyright   = $this->post('note_copyright_box');
        $license     = $this->post('note_license_box');
        $link        = $this->post('note_link_box');
        $description = $this->post('note_description_box');

        //parse widget file path
        $webAppPath = Configure::get('webappPath');
        $webAppPath = str_replace('\\', '/', $webAppPath);

        $widgetFilePath = $webAppPath . $widgetPath;
        $widgetFilePath = str_replace('//', '/', $widgetFilePath);

        File::makeDir($widgetFilePath);

        //当widget name命名中带"_"时
        $pos = strpos($widgetName, '_');
        if ($pos !== false) {
            $childDirArray   = explode('_', $widgetName);
            $widgetFileName  = array_pop($childDirArray);
            $widgetFilePath .= '/' . strtolower(implode('/', $childDirArray));
        } else {
            $widgetFileName = $widgetName;
        }

        $widgetFilePath .= '/' . $widgetFileName . '.php';

        //parse widget file wether exists
        if (is_file($widgetFilePath)) {
            $this->ajax(false, '对不起，所要创建的文件已存在！');
        }

        //parse widget file content
        $widgetContent  = "<?php\n";
        $widgetContent .= fileCreator::fileNote($widgetFileName, $description, $author, $copyright, 'Widget', null, $license, $link);
        $widgetContent .= fileCreator::classCodeStart($widgetName . 'Widget', 'Widget', false);
        //parse widget method
        $widgetContent .= fileCreator::methodNote('public', 'void', array(array('params', 'array', '参数')), 'main method');
        $widgetContent .= fileCreator::methodCode('renderContent', 'public', array('params'=>'null'));

        $widgetContent .= fileCreator::classCodeEnd();

        //create widget file
        $result = File::writeFile($widgetFilePath, $widgetContent);
        if (!$result) {
            $this->ajax(false, '对不起，生成Widget文件失败！请重新操作');
        }

        //create widget view file
        if ($viewStatus) {
            $viewDirPath = $webAppPath . $widgetPath . '/views';
            $viewDirPath = str_replace('//', '/', $viewDirPath);
            File::makeDir($viewDirPath);

            $viewFilePath = $viewDirPath . '/' . strtolower($widgetName) . '.' . $viewFileExt;
            File::writeFile($viewFilePath);
        }

        //cookie数据处理
        $storageObj       = $this->instance('noteStorage');
        $storageObj->setNote($author, $copyright, $license, $link);

	    $this->ajax(true, '操作成功');
	}

	/**
	 * 创建扩展插件
	 *
	 * @access public
	 * @return string
	 */
	public function createextAction() {

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
	    $this->display();
	}

	/**
	 * ajax调用：创建扩展插件
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreateextAction() {

	    //get params
	    $extPath  = $this->post('path_box');
	    if (!$extPath) {
	        $this->ajax(false, '对不起，错误的参数调用！');
	    }

	    $extName  = $this->post('ext_name_box');
	    if ($extName) {
	        $extName = trim($extName, '_');
	    }
	    if (!$extName) {
	        $this->ajax(false, '对不起，扩展模块名称不能为空！');
	    }
	    if (strpos($extName, '_') !== false) {
	        $this->ajax(false, '对不起，扩展模块名称中不允许含有下划线（"_"）！请更改扩展模块名');
	    }

	    $author      = $this->post('note_author_box');
	    $copyright   = $this->post('note_copyright_box');
	    $license     = $this->post('note_license_box');
	    $link        = $this->post('note_link_box');
	    $description = $this->post('note_description_box');

	    //parse widget file path
	    $webAppPath  = Configure::get('webappPath');
	    $webAppPath  = str_replace('\\', '/', $webAppPath);

	    $extFilePath = $webAppPath . $extPath . '/' . $extName;
	    $extFilePath = str_replace('//', '/', $extFilePath);

	    File::makeDir($extFilePath);

	    $extFileName  = $extName;
	    $extFilePath .= '/' . $extFileName . '.php';

	    //判断文件是否存在
	    if (is_file($extFilePath)) {
	        $this->ajax(false, '对不起，所要创建的扩展模块已存在！');
	    }

	    //创建扩展模块引导文件
        $extFileContent  = "<?php\n";
        $extFileContent .= fileCreator::fileNote($extFileName, $description, $author, $copyright, 'extension', null, $license, $link);
        $extFileContent .= fileCreator::classCodeStart($extName . 'Ext', 'Extension', false);
        //parse widget method
        $extFileContent .= fileCreator::methodNote('public', 'mixed', null, '构造方法');
        $extFileContent .= fileCreator::methodCode('__construct', 'public');

        $extFileContent .= fileCreator::classCodeEnd();

        //create widget file
        $result = File::writeFile($extFilePath, $extFileContent);
        if (!$result) {
            $this->ajax(false, '对不起，生成Widget文件失败！请重新操作');
        }

	    $this->ajax(true, '操作成功！');
	}

}