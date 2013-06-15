<?php
/**
 * 主菜单
 *
 * @author
 * @copyright Copyright (C)  All rights reserved.
 * @version $Id: MainMenu.php 1.0 2013-01-12 12:50:54Z $
 * @package Widget
 * @since 1.0
 */

class MainMenuWidget extends Widget {

	/**
	 * 主方法
	 *
	 * @access public
	 * @param array $params 参数
	 * @return string
	 */
	public function renderContent($params = null) {

		$webappStatus = (is_dir(Configure::get('webappPath') . 'application')) ? true : false;

		//assign params
		$this->assign(array(
		'controller' => Doit::getControllerName(),
		'action'     => Doit::getActionName(),
		'status'     => $webappStatus,
		));

		//display page
		$this->display();
	}

}