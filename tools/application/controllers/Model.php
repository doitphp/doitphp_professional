<?php
/**
 * Model文件操作
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Modle.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class ModelController extends PublicController {

	/**
	 * 创建Modle文件
	 *
	 * 注：高级操作
	 *
	 * @access public
	 * @return string
	 */
	public function createmodelAction() {

		//get params
		$path = $this->get('path');
		if (!$path) {
			$this->showMsg('对不起，错误的网址调用！');
		}

		//instance cookie storage
		$methodObj  = $this->instance('modelMethodStorage');

		//获取数据表列表信息
		$tableList = $this->_getDbTableList();
		if ($tableList) {
			$selectContentArray = array();
			foreach ($tableList as $tableName) {
				$selectContentArray[$tableName] = $tableName;
			}
			$tableListHtml = Html::select($selectContentArray, array('name'=>'model_table_name_box', 'class'=>'text', 'style'=>'line-height:24px; height:24px; width:auto;', 'id'=>'model_table_name_select'), Cookie::get('model_table_name_box'));
		} else {
			$tableListHtml = '<input type="text" class="text" style="width:150px;" name="model_table_name_box" id="model_table_name_select"/>';
		}

		//assign params
		$this->assign(array(
		'path'          => $path,
		'assetUrl'      => $this->getAssetUrl('doit/js'),
		'timeNow'       => time(),
		'methodList'    => $methodObj->getMethodList(),
		'tableNameHtml' => $tableListHtml,
		'bindTableStatus' => (Cookie::get('model_tabname_status_box') == 'on') ? true : false,
		));

		//display page
		$this->display();
	}

	/**
	 * 获取数据表列表信息
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDbTableList() {

		$dbParams = Configure::get('db');
		if (!$dbParams) {
			return false;
		}
		if (!isset($dbParams['charset'])) {
			$dbParams['charset'] = 'utf8';
		}


		//数据库连接
		$dbObj     = DbPdo::getInstance($dbParams);
		$tableList = $dbObj->getTableList();

		//当使用数据表前缀时
		if (isset($dbParams['prefix']) && $dbParams['prefix']) {
			$strLenNum = strlen($dbParams['prefix']);
			foreach ($tableList as $key=>$tableName) {
				if (substr($tableName, 0, $strLenNum) == $dbParams['prefix']) {
					$tableList[$key] = substr($tableName, $strLenNum);
				} else {
					unset($tableList[$key]);
				}
			}
		}

		return $tableList;
	}

	/**
	 * ajax调用：创建Model文件
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxcreatemodelAction() {

		//get params
		$modelPath = $this->post('path_box');
		if (!$modelPath) {
			$this->ajax(false, '对不起，错误的参数调用！');
		}

		$modelName = $this->post('model_name_box');
		if ($modelName) {
			$modelName = trim($modelName, '_');
		}
		if (!$modelName) {
			$this->ajax(false, '对不起，Model名称不能为空！');
		}

		$modelTableStatus = $this->post('model_tabname_status_box');
		$modelTableName   = $this->post('model_table_name_box');

		$modelTableStatus = ($modelTableStatus == 'on') ? true : false;
		//获取数据表字段信息
		if ($modelTableStatus) {
			if (!$modelTableName) {
				$this->ajax(false, '对不起，所绑定的数据表名不能为空！');
			}

			//分析数据库连接参数
			$dbParams = Configure::get('db');
			if (!$dbParams) {
				$this->ajax(false, '对不起，配置文件没有配置数据库连接参数！');
			}
			if (!isset($dbParams['charset'])) {
				$dbParams['charset'] = 'utf8';
			}

			//数据库连接
			$dbObj     = DbPdo::getInstance($dbParams);

			$tableInfo = $dbObj->getTableInfo($modelTableName);
		}

		$author      = $this->post('note_author_box');
		$copyright   = $this->post('note_copyright_box');
		$license     = $this->post('note_license_box');
		$link        = $this->post('note_link_box');
		$description = $this->post('note_description_box');

		//分析model文件的路径
		$webAppPath = Configure::get('webappPath');
		$webAppPath = str_replace('\\', '/', $webAppPath);

		$modelFilePath = $webAppPath . $modelPath;
		$modelFilePath = str_replace('//', '/', $modelFilePath);

		//当model name命名中带"_"时
		$pos = strpos($modelName, '_');
		if ($pos !== false) {
			$childDirArray  = explode('_', $modelName);
			$modelFileName  = array_pop($childDirArray) . '.php';
			$modelFilePath .= '/' . strtolower(implode('/', $childDirArray));
		} else {
		    $modelFileName = $modelName . '.php';
		}
		$modelFilePath .= '/' . $modelFileName;

		//当所要创建的model文件存在时
		if (is_file($modelFilePath)) {
			$this->ajax(false, '对不起，所要创建的Model文件已存在！');
		}

		//instance cookie object
		$storageObj       = $this->instance('noteStorage');
		$methodStorageObj = $this->instance('modelMethodStorage');
		$methodList       = $methodStorageObj->getMethodList();

		//parse model file content
		$modelContent  = "<?php\n";
		$modelContent .= fileCreator::fileNote($modelFileName, $description, $author, $copyright, 'Model', null, $license, $link);
		$modelContent .= fileCreator::classCodeStart($modelName . 'Model', 'Model', false);

		//parse method list
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
				$modelContent .= fileCreator::methodNote($lines['access'], $lines['return'], $methodNoteParams, $lines['desc']);
				$modelContent .= fileCreator::methodCode($lines['name'], $lines['access'], $methodCodeParams);
			}
		}

		//分析数据表字段信息
		if ($modelTableStatus) {
			$modelContent .= fileCreator::methodNote('protected', 'array', array(), '定义数据表主键') . fileCreator::methodCode('primaryKey', 'protected', array(), "return '{$tableInfo['primaryKey'][0]}';");
			$modelContent .= fileCreator::methodNote('protected', 'array', array(), '定义数据表字段信息') . fileCreator::methodCode('tableFields', 'protected', array(), "return " . var_export($tableInfo['fields'], true) . ";");
			$modelContent .= fileCreator::methodNote('protected', 'array', array(), '定义数据表名称') . fileCreator::methodCode('tableName', 'protected', array(), "return '{$modelTableName}';");
		}

		$modelContent .= fileCreator::classCodeEnd();

		//生成controller文件
		$result = File::writeFile($modelFilePath, $modelContent);

		if (!$result) {
			$this->ajax(false, '对不起，生成Model文件失败！请重新操作');
		}

		//cookie数据处理
		$methodStorageObj->clearMethod();

		$storageObj->setNote($author, $copyright, $license, $link);

		$this->ajax(true, '操作成功！');
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
		$storageObj = $this->instance('modelMethodStorage');
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
		$storageObj = $this->instance('modelMethodStorage');

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
		$storageObj = $this->instance('modelMethodStorage');

		//数据排重
		$methodList = $storageObj->getMethodList();
		foreach ($methodList as $lines) {
			if ($lines['name'] == $name) {
				$this->ajax(false, '对不起，所要添加的Method名已存在！');
			}
		}

		$result = $storageObj->addMethod($name, $desc, $access, $return);
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
		$storageObj = $this->instance('modelMethodStorage');

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
		$storageObj = $this->instance('modelMethodStorage');

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
		$storageObj = $this->instance('modelMethodStorage');

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
		$storageObj = $this->instance('modelMethodStorage');

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
		$storageObj = $this->instance('modelMethodStorage');

		$result = $storageObj->deleteMethodParams($methodId, $key);
		if (!$result) {
			$this->ajax(false, '对不起，操作失败！请重新操作');
		}

		$this->ajax(true, '恭喜！执行成功');
	}
}