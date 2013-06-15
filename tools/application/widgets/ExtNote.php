<?php
/**
 * file: ExtNote.php
 *
 * Enter description here ...
 * @author
 * @copyright Copyright (C)  All rights reserved.
 * @version $Id: ExtNote.php 1.0 2013-01-24 22:05:42Z $
 * @package Widget
 * @since 1.0
 */

class ExtNoteWidget extends Widget {

	/**
	 * Main method
	 *
	 * @access public
	 * @param array $params 参数
	 * @return string
	 */
	public function renderContent($params = null) {

		//get cookie params
		$cookieObj = $this->instance('noteStorage');
		$noteCookie = $cookieObj->getNote();
		if (!$noteCookie) {
			$noteCookie = array(
			'desc' => null,
			'author' => null,
			'copyright' => null,
			'lisence' => null,
			'link' => null,
			);
		}

		//assign params
		$this->assign('noteInfo', $noteCookie);

		//display page
		$this->display();
	}

}