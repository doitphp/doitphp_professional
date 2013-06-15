<?php
/**
 * cookie数据存贮管理
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: noteStorage.php 1.0 2013-01-26 21:52:56Z tommy <streen003@gmail.com> $
 * @package library
 * @since 1.0
 */

class noteStorage {

	/**
	 * 文件附加信息cookie name
	 *
	 * @var string
	 */
	const NOTE_COOKIE_NAME = 'doit_tools_note_cookie';

	/**
	 * cookie 生存周期
	 *
	 * @var integer
	 */
	const COOKIE_EXPIRE_TIME = 259200;

	/**
	 * 保存附加信息数据
	 *
	 * @access public
	 *
	 * @param string $desc 文件描述
	 * @param string $author 文件作者
	 * @param string $copyright 文件版权
	 * @param string $lisence 发行协议
	 * @param string $link 文件的相关链接
	 *
	 * @return boolean
	 */
	public function setNote($author = null, $copyright = null, $lisence = null, $link = null) {

		$data = array(
			'author' => $author,
			'copyright' => $copyright,
			'lisence' => $lisence,
			'link' => $link,
		);

		return Cookie::set(self::NOTE_COOKIE_NAME, serialize($data), self::COOKIE_EXPIRE_TIME);
	}

	/**
	 * 获取文件注释的附加信息
	 *
	 * @access public
	 * @return array
	 */
	public function getNote() {

		$storageData = Cookie::get(self::NOTE_COOKIE_NAME);
		if (!$storageData) {
			return false;
		}

		return unserialize($storageData);
	}
}