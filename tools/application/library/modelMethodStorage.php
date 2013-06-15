<?php
/**
 * model method cookie数据存贮管理
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: modelMethodStorage.php 1.0 2013-02-13 10:39:56Z tommy <streen003@gmail.com> $
 * @package library
 * @since 1.0
 */

class modelMethodStorage extends methodStorage {

	/**
	 * 存贮文件的名称
	 *
	 * @var string
	 */
	const METHOD_CACHE_NAME = 'doit_tools_model_method_storage';

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