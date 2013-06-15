<?php
/**
 * method cookie数据存贮管理
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: methodCookieStorage.php 1.0 2013-02-13 10:39:56Z tommy <streen003@gmail.com> $
 * @package library
 * @since 1.0
 */

class methodStorage {

	/**
	 * 存贮文件的名称
	 *
	 * @var string
	 */
	const METHOD_CACHE_NAME = 'doit_tools_method_storage';

	/**
	 * 添加 method
	 *
	 * @access public
	 *
	 * @param string $name Method Name
	 * @param string $desc Method describe
	 * @param string $access 访问权限(public, private, protected)
	 * @param string $returnType 返回的数据类型
	 *
	 * @return boolean
	 */
	public function addMethod($name, $desc = null, $access = 'public', $returnType = 'unknown') {

		//parse params
		if (!$name) {
			return false;
		}

		$methodData = array(
		'name'   => $name,
		'desc'   => $desc,
		'access' => $access,
		'return' => $returnType,
		);

		$methodStorageData = $this->getMethodList();
		if (!$methodStorageData) {
			$methodStorageData[] = $methodData;
			return $this->_setStorage($methodStorageData);
		}

		array_push($methodStorageData, $methodData);

		return $this->_setStorage($methodStorageData);
	}

	/**
	 * 添加 method params
	 *
	 * @access public
	 *
	 * @param integer $methodId Method Id
	 * @param string $name Params Name
	 * @param string $desc Params describe
	 * @param string $type Params type
	 * @param mixed default default data
	 *
	 * @return boolean
	 */
	public function addMethodParams($methodId = 0, $name, $type = 'unknown', $desc = null, $default = null) {

		//parse params
		if (is_null($methodId) || !$name) {
			return false;
		}

		//get method cookie list
		$methodList = $this->getMethodList();
		if (!isset($methodList[$methodId])) {
			return false;
		}

		//当参数不存在时
		if (!isset($methodList[$methodId]['params'])) {
			$methodList[$methodId]['params'][] = array(
			'name'    => $name,
			'desc'    => $desc,
			'type'    => $type,
			'default' => $default,
			);

			return $this->_setStorage($methodList);
		}

		$paramsArray = array(
			'name'    => $name,
			'desc'    => $desc,
			'type'    => $type,
			'default' => $default,
			);

		array_push($methodList[$methodId]['params'], $paramsArray);

		return $this->_setStorage($methodList);
	}

	/**
	 * 编辑 method params
	 *
	 * @access public
	 *
	 * @param integer $methodId Method Id
	 * @param integer $id Params Id
	 * @param string $name Params Name
	 * @param string $desc Params describe
	 * @param string $type Params type
	 * @param mixed default default data
	 *
	 * @return boolean
	 */
	public function editMethodParams($methodId = 0, $id = 0, $name, $type = 'unknown', $desc = null, $default = null) {

		//parse params
		if (is_null($methodId) || is_null($id) || !$name) {
			return false;
		}

		//get method cookie list
		$methodList = $this->getMethodList();
		if (!isset($methodList[$methodId]['params'][$id])) {
			return false;
		}

		//set method cookie list
		$paramsArray = array(
			'name' => $name,
			'desc' => $desc,
			'type' => $type,
			'default' => $default,
		);

		$methodList[$methodId]['params'][$id] = $paramsArray;

		return $this->_setStorage($methodList);
	}

	/**
	 * 删除method params
	 *
	 * @access public
	 *
	 * @param integer $methodId Method Id
	 * @param integer $id Params Id
	 *
	 * @return boolean
	 */
	public function deleteMethodParams($methodId = 0, $id = 0) {

		//parse params
		if (is_null($methodId) || is_null($id)) {
			return false;
		}

		//get method cookie list
		$methodList = $this->getMethodList();
		if (!isset($methodList[$methodId]['params'][$id])) {
			return false;
		}

		unset($methodList[$methodId]['params'][$id]);

		return $this->_setStorage($methodList);
	}

	/**
	 * 获取method list
	 *
	 * @access public
	 * @return array
	 */
	public function getMethodList() {

		$methodCacheFilePath = $this->_parseCacheFilePath();
		if (!is_file($methodCacheFilePath)) {
		    return array();
		}

		$storageData = file_get_contents($methodCacheFilePath, LOCK_EX);
		if (!$storageData) {
			return array();
		}

		return unserialize($storageData);
	}

	/**
	 * 根据method 来获取method info
	 *
	 * @access public
	 *
	 * @param integer $id method Id
	 *
	 * @return array
	 */
	public function getMethodInfo($id = 0) {

		//parse params
		if (is_null($id)) {
			return false;
		}

		$methodList = $this->getMethodList();
		if (!isset($methodList[$id])) {
			return array();
		}

		return $methodList[$id];
	}

	/**
	 * 编辑method
	 *
	 * @access public
	 *
	 * @param integer $id Mehtod list id
	 * @param string $name Method Name
	 * @param string $desc Method describe
	 * @param string $access 访问权限(public, private, protected)
	 * @param string $returnType 返回的数据类型
	 *
	 * @return boolean
	 */
	public function editMethod($id, $name, $desc = null, $access = 'public', $returnType = 'unknown') {

		//parse params
		if (is_null($id) || !$name) {
			return false;
		}

		//get method list
		$methodStorageData = $this->getMethodList();
		if (!isset($methodStorageData[$id])) {
			return false;
		}

		$methodData = array(
		'name' => $name,
		'desc' => $desc,
		'access' => $access,
		'return' => $returnType,
		'params' => isset($methodStorageData[$id]['params']) ? $methodStorageData[$id]['params'] : null,
		);

		$methodStorageData[$id] = $methodData;

		return $this->_setStorage($methodStorageData);
	}

	/**
	 * 删除method
	 *
	 * @access public
	 *
	 * @param integer $id method Id
	 *
	 * @return boolean
	 */
	public function deleteMethod($id) {

		//parse params
		if (is_null($id)) {
			return false;
		}

		//get method list
		$methodStorageData = $this->getMethodList();
		if (!isset($methodStorageData[$id])) {
			return false;
		}

		unset($methodStorageData[$id]);

		return $this->_setStorage($methodStorageData);
	}

	/**
	 * 清空Action
	 *
	 * @access public
	 * @return boolean
	 */
	public function clearMethod() {

		return File::deleteFile($this->_parseCacheFilePath());
	}

	/**
	 * 保存cookie数据
	 *
	 * @access protected
	 *
	 * @param array $data 所要保存的cookie数据
	 *
	 * @return boolean
	 */
	protected function _setStorage($data = null) {

		return File::writeFile($this->_parseCacheFilePath(), serialize($data));
	}

	/**
	 * 分析缓存文件的路径
	 *
	 * @access protected
	 * @return string
	 */
	protected function _parseCacheFilePath() {

	    return sys_get_temp_dir() . '/' . md5(self::METHOD_CACHE_NAME);
	}
}