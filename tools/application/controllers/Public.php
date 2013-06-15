<?php
/**
 * 共用Controller
 *
 * 提供登陆判断等共用类方法
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Public.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class PublicController extends Controller {

	/**
	 * 登陆验证
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function _parseLogin() {

		$loginStatus = $this->getCookie(Configure::get('loginCookieName'));
		if (!$loginStatus) {
			if (substr(Doit::getActionName(), 0, 4) == 'ajax') {
				$this->ajax(false, '对不起，您没有登陆或登陆Cookie已过期，请重新登陆！');
			}
			//将当前网址存贮在cookie中
			$this->setCookie(Configure::get('gotoUrlCookieName'), $_SERVER['REQUEST_URI']);

			//跳转到登陆页面
			$this->redirect($this->createUrl('login/index'));
		}

		return true;
	}

	/**
	 * 前函数(方法)
	 *
	 * @access public
	 * @return boolean
	 */
	public function init() {

		//分析是否登陆
		$this->_parseLogin();

		//设置layout视图
		$this->setLayout('main');

		return true;
	}

	/**
	 * 判断是否创建项目目录
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function _parseWebAppRoot() {

		//分析webapp目录是否存在
		$webappPath = Configure::get('webappPath');
		if (!is_dir($webappPath)) {
			$errorMsg = "对不起！项目目录：{$webappPath} 不存在！请创建项目根目录";
			if (substr(Doit::getActionName(), 0, 4) == 'ajax') {
				$this->ajax(false, $errorMsg);
			} else {
				$this->showMsg($errorMsg);
			}
		}

		//分析应用目录
		if (!is_dir($webappPath . 'application')) {
			$errorMsg = "对不起！您还没有创建WebApp目录。请进行如下操作:WebApp管理->创建WebApp目录";
			if (substr(Doit::getActionName(), 0, 4) == 'ajax') {
				$this->ajax(false, $errorMsg);
			} else {
				$this->showMsg($errorMsg);
			}
		}

		return true;
	}

}