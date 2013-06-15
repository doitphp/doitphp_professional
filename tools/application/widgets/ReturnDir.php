<?php
/**
 * file: ReturnDir.php
 *
 * Enter description here ...
 * @author
 * @copyright Copyright (C)  All rights reserved.
 * @version $Id: ReturnDir.php 1.0 2013-01-24 21:39:38Z $
 * @package Widget
 * @since 1.0
 */

class ReturnDirWidget extends Widget {

	/**
	 * Main method
	 *
	 * @access public
	 * @param array $params 参数
	 * @return string
	 */
	public function renderContent($params = null) {

		//get params
		$dir = $this->get('path');
		$dir = (!$dir) ? $dir : str_replace('//', '/', $dir);

		$webAppPath = Configure::get('webappPath');

		//parse path
		$path = $webAppPath . $dir;
		$path = str_replace('//', '/', $path);

		//assing params
		$this->assign(array(
		'dir' => $dir,
		'path' => $path,
		));

		//display page
		$this->display();
	}

}