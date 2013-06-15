<?php
/**
 * 控制器Action数据存贮管理
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: noteStorage.php 1.0 2013-01-26 21:52:56Z tommy <streen003@gmail.com> $
 * @package library
 * @since 1.0
 */

class actionStorage {

	/**
	 * action 存贮 cookie name
	 *
	 * @var string
	 */
	const ACTION_CACHE_NAME = 'doit_tools_action_storage';

	/**
	 * 获取Action信息
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function getAction() {

		$cacheFilePath = self::_parseCacheFilePath();
        if (!is_file($cacheFilePath)) {
            return array();
        }
        $storageData = file_get_contents($cacheFilePath, LOCK_EX);
		if (!$storageData) {
			return array();
		}

		return unserialize($storageData);
	}

	/**
	 * 添加action信息
	 *
	 * @access public
	 *
	 * @param string $name Action名称
	 * @param string $desc action描述
	 *
	 * @return boolean
	 */
	public function addAction($name, $desc = null) {

		//参数分析
		if (!$name) {
			return false;
		}

		$actionData = array('name'=>$name, 'desc'=>$desc);

		//get action cookie
		$actionCookie = $this->getAction();
		if (!$actionCookie) {
			$actionCookie[] = $actionData;
			return $this->_setActionStorage($actionCookie);
		}

		array_push($actionCookie, $actionData);

		return $this->_setActionStorage($actionCookie);
	}

	/**
	 * 编辑Action信息
	 *
	 * @access public
	 *
	 * @param integer $key action序列号
	 * @param string $name Action名称
	 * @param string $desc action描述
	 *
	 * @return boolean
	 */
	public function editAction($key = 0, $name, $desc = null) {

		//参数分析
		if (!$name) {
			return false;
		}

		//get action cookie
		$actionCookie = $this->getAction();

		if (!isset($actionCookie[$key])) {
			return false;
		}

		$actionCookie[$key] = array('name'=>$name, 'desc'=>$desc);

		return $this->_setActionStorage($actionCookie);
	}

	/**
	 * 删除Action
	 *
	 * @access public
	 *
	 * @param integer $key Action序列号
	 *
	 * @return boolean
	 */
	public function deleteAction($key = 0) {

		//get action cookie
		$actionCookie = $this->getAction();
		if (!isset($actionCookie[$key])) {
			return false;
		}

		unset($actionCookie[$key]);

		return $this->_setActionStorage($actionCookie);
	}

	/**
	 * 清空Action
	 *
	 * @access public
	 * @return boolean
	 */
	public function clearAction() {

		return File::deleteFile(self::_parseCacheFilePath());
	}

	/**
	 * 保存Action cookie数据
	 *
	 * @access protected
	 *
	 * @param array $data 所要保存的cookie数据
	 *
	 * @return boolean
	 */
	protected function _setActionStorage($data = null) {

		return File::writeFile(self::_parseCacheFilePath(), serialize($data));
	}


	/**
	 * 分析缓存文件的路径
	 *
	 * @access protected
	 * @return string
	 */
	protected static function _parseCacheFilePath() {

	    return sys_get_temp_dir() . '/' . md5(self::ACTION_CACHE_NAME);
	}
}