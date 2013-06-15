<?php
/**
 * 创建普通的类文件
 *
 * @author tommy<streen003@gmail.com>;
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2013 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Lib.php 1.0 2013-03-23 00:06:56Z tommy&lt;streen003@gmail.com&gt; $
 * @package Controller
 * @since 1.0
 */

class LibController extends PublicController {

	/**
	 * 创建普通的类文件
	 *
	 * @access public
	 * @return void
	 */
	public function createclassAction() {

	    //get params
	    $path = $this->get('path');
	    if (!$path) {
	        $this->showMsg('对不起，错误的网址调用！');
	    }

	    //instance cookie storage
	    $methodObj  = $this->instance('classMethodStorage');

	    //assign params
	    $this->assign(array(
	            'path'          => $path,
	            'assetUrl'      => $this->getAssetUrl('doit/js'),
	            'timeNow'       => time(),
	            'methodList'    => $methodObj->getMethodList(),
	    ));

	    //display page
	    $this->display();
	}

	/**
	 * ajax调用：创建类文件
	 *
	 * @access public
	 * @return void
	 */
	public function ajaxcreateclassAction() {

	    //get params
	    $classPath = $this->post('path_box');
	    if (!$classPath) {
	        $this->ajax(false, '对不起，错误的参数调用！');
	    }

	    $className = $this->post('class_name_box');
	    if ($className) {
	        $className = trim($className, '_');
	    }
	    if (!$className) {
	        $this->ajax(false, '对不起，Class名称不能为空！');
	    }

	    $author      = $this->post('note_author_box');
	    $copyright   = $this->post('note_copyright_box');
	    $license     = $this->post('note_license_box');
	    $link        = $this->post('note_link_box');
	    $description = $this->post('note_description_box');

	    //分析model文件的路径
	    $webAppPath = Configure::get('webappPath');
	    $webAppPath = str_replace('\\', '/', $webAppPath);

	    $classFilePath = $webAppPath . $classPath;
	    $classFilePath = str_replace('//', '/', $classFilePath);

	    //当model name命名中带"_"时
	    $pos = strpos($className, '_');
	    if ($pos !== false) {
	        $childDirArray  = explode('_', $className);
	        $classFileName  = array_pop($childDirArray) . '.php';
	        $classFilePath .= '/' . strtolower(implode('/', $childDirArray));
	    } else {
	        $classFileName = $className . '.php';
	    }
	    $classFilePath .= '/' . $classFileName;

	    //当所要创建的model文件存在时
	    if (is_file($classFilePath)) {
	        $this->ajax(false, '对不起，所要创建的Class文件已存在！');
	    }

	    //instance cookie object
	    $storageObj       = $this->instance('noteStorage');
	    $methodStorageObj = $this->instance('classMethodStorage');
	    $methodList       = $methodStorageObj->getMethodList();

	    //parse model file content
	    $classContent  = "<?php\n";
	    $classContent .= fileCreator::fileNote($classFileName, $description, $author, $copyright, 'Library', null, $license, $link);
	    $classContent .= fileCreator::classCodeStart($className, null, false);

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
	            $classContent .= fileCreator::methodNote($lines['access'], $lines['return'], $methodNoteParams, $lines['desc']);
	            $classContent .= fileCreator::methodCode($lines['name'], $lines['access'], $methodCodeParams);
	        }
	    }

	    $classContent .= fileCreator::classCodeEnd();

	    //生成controller文件
	    $result = File::writeFile($classFilePath, $classContent);

	    if (!$result) {
	        $this->ajax(false, '对不起，生成Model文件失败！请重新操作');
	    }

	    //cookie数据处理
	    $methodStorageObj->clearMethod();

	    $storageObj->setNote($author, $copyright, $license, $link);

	    $this->ajax(true, '操作成功！');
	}

	/**
	 * 添加类方法操作页面
	 *
	 * @access public
	 * @return void
	 */
	public function addmethodAction() {

	    //display page
	    $this->render();
	}

	/**
	 * ajax调用：添加类方法
	 *
	 * @access public
	 * @return void
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
	    $storageObj = $this->instance('classMethodStorage');

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
	 * 编辑类方法的操作页面
	 *
	 * @access public
	 * @return void
	 */
	public function editmethodAction() {

	    //get params
	    $id = $this->get('id');
	    if (is_null($id)) {
	        exit('对不起，不正确的网址调用！');
	    }

	    //instance cookie storage
	    $storageObj = $this->instance('classMethodStorage');
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
	 * ajax调用：编辑类方法
	 *
	 * @access public
	 * @return void
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
	    $storageObj = $this->instance('classMethodStorage');

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
	 * ajax调用：删除类方法
	 *
	 * @access public
	 * @return void
	 */
	public function ajaxdeletemethodAction() {

	    //get params
	    $id = $this->post('id');
	    if (is_null($id)) {
	        $this->ajax(false, '对不起，错误的参数调用！');
	    }

	    //instance cookie storage
	    $storageObj = $this->instance('classMethodStorage');

	    $result = $storageObj->deleteMethod($id);
	    if (!$result) {
	        $this->ajax(false, '对不起，操作失败！请重新操作');
	    }

	    $this->ajax(true, '恭喜！执行成功');
	}

	/**
	 * 添加类方法参数操作页面
	 *
	 * @access public
	 * @return void
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
	 * ajax调用：添加类方法参数
	 *
	 * @access public
	 * @return void
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
	    $storageObj = $this->instance('classMethodStorage');

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
	 * 编辑类方法参数操作页面
	 *
	 * @access public
	 * @return void
	 */
	public function editmethodparamsAction() {

	    //get params
	    $id = $this->get('id');
	    $key = $this->get('key');
	    if (is_null($id) || is_null($key)) {
	        exit('对不起，错误的网址调用！');
	    }

	    //instance cookie storage
	    $storageObj = $this->instance('classMethodStorage');

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
	 * ajax调用：编辑类方法参数
	 *
	 * @access public
	 * @return void
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
	    $storageObj = $this->instance('classMethodStorage');

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
	 * ajax调用：删除类方法参数
	 *
	 * @access public
	 * @return void
	 */
	public function ajaxdeletemethodparamsAction() {

	    //get params
	    $methodId = $this->post('id');
	    $key      = $this->post('key');

	    if (is_null($methodId) || is_null($key)) {
	        return false;
	    }

	    //instance cookie storage
	    $storageObj = $this->instance('classMethodStorage');

	    $result = $storageObj->deleteMethodParams($methodId, $key);
	    if (!$result) {
	        $this->ajax(false, '对不起，操作失败！请重新操作');
	    }

	    $this->ajax(true, '恭喜！执行成功');
	}
}