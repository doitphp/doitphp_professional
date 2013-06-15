<?php
/**
 * 默认引导Controller
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Index.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package Controller
 * @since 1.0
 */

class IndexController extends PublicController {

	/**
	 * 首页
	 *
	 * 默认系统首页
	 *
	 * @access public
	 * @return string
	 */
	public function indexAction() {

		//检查$_SERVER变量
		$serverVars = array('SCRIPT_NAME', 'REQUEST_URI', 'HTTP_HOST', 'SERVER_PORT', 'HTTP_USER_AGENT', 'REQUEST_TIME', 'HTTP_ACCEPT_LANGUAGE', 'REMOTE_ADDR', 'HTTP_REFERER');

		$missArray = array();
		foreach ($serverVars as $value) {
			if (!isset($_SERVER[$value])) {
				$missArray[] = $value;
			}
		}

		//支持的数据库
		$databaseArray = array();
		if (function_exists('mysql_get_client_info') || extension_loaded('pdo_mysql')) {
			$databaseArray[] = 'MySql';
		}
		if (function_exists('mssql_connect') || extension_loaded('pdo_mssql')) {
			$databaseArray[] = 'MSSQL';
		}
		if (function_exists('pg_connect') || extension_loaded('pdo_pgsql')) {
			$databaseArray[] = 'PostgreSQL';
		}
		if (function_exists('oci_connect') || extension_loaded('pdo_oci8') || extension_loaded('pdo_oci')) {
			$databaseArray[] = 'Oracle';
		}
		if (extension_loaded('sqlite') || extension_loaded('pdo_sqlite')) {
			$databaseArray[] = 'Sqlite';
		}
		if (extension_loaded('mongo')) {
			$databaseArray[] = 'MongoDB';
		}

		//检查GD库
		if (extension_loaded('gd')) {
			$gdinfo=gd_info();
			$gdResult = (!$gdinfo['FreeType Support']) ? '<span class="red">Not Support FreeType</span>' : 'Yes';
		} else {
			$gdResult = '<span class="red">No</span>';
		}


		//assign params
		$this->assign(array(
		'serverResult' => ($missArray) ? '<span class="red">$_SERVER不支持的变量为: ' . implode(', ', $missArray) . '</span>' : 'Yes',
		'webappPath'   => Configure::get('webappPath'),
		'databaseInfo' => implode(',', $databaseArray),
		'gdResult'     => $gdResult,
		));

		//display page
		$this->display();
	}

	/**
	 * 创建项目目录操作页
	 *
	 * @access public
	 * @return string
	 */
	public function webappAction() {


		//parse webapp dir
		$webappPath = Configure::get('webappPath');
		$webappStatus = !is_dir($webappPath) ? false : true;

		//parse wethoer apache server
		$isApache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) ? true : false;

		//assign params
		$this->assign(array(
		'webappStatus' => $webappStatus,
		'isApache' => $isApache,
		));

		//display page
		$this->display();
	}

	/**
	 * ajax调用：创建项目目录
	 *
	 * @access public
	 * @return string
	 */
	public function ajaxwebappAction() {

		//get params
		$serverName = $this->post('webserver_name');
		$htaccess   = $this->post('htaccess_state');

		$extension  = $this->post('ext_state');
		$module     = $this->post('module_state');
		$language   = $this->post('lang_state');

		$isApache    = ($serverName == 'apache') ? true : false;
		$hasHtaccess = ($htaccess == 'on') ? true : false;

		$hasExt      = ($extension == 'on') ? true : false;
		$hasModule   = ($module == 'on') ? true : false;
		$hasLang     = ($language == 'on') ? true : false;

		//instance webAppModel object
		$webAppModel = $this->model('WebApp');

		//分析根目录
		if (!$webAppModel->parseWebAppPath()) {
			$this->ajax(false, '对不起，创建webApp根目录操作失败！请重新操作');
		}

		//创建application目录
		if (!$webAppModel->createApplicationDir($isApache, $hasExt, $hasModule, $hasLang)) {
			$this->ajax(false, '对不起，创建application目录操作失败！请重新操作');
		}

		//创建asset目录
		if (!$webAppModel->createAssetDir()) {
			$this->ajax(false, '对不起，创建asset目录操作失败！请重新操作');
		}

		//创建缓存及日志目录
		if (!$webAppModel->createCacheDir($isApache)) {
			$this->ajax(false, '对不起，创建缓存目录操作失败！请重新操作');
		}
		if (!$webAppModel->createLogDir($isApache)) {
			$this->ajax(false, '对不起，创建日志目录操作失败！请重新操作');
		}

		//创建主引导文件
		if (!$webAppModel->createIndexFile()) {
			$this->ajax(false, '对不起，创建入口文件:index.php操作失败！请重新操作');
		}

		//创建htaccess文件
		if ($isApache == true && $hasHtaccess == true) {
			$webAppModel->createHtaccessFile();
		}

		//创建SEO引导文件
		$webAppModel->createRobotsFile();

		$this->ajax(true, '项目目录创建成功！');
	}

	/**
	 * phpinfo()执行页面
	 *
	 * @access public
	 * @return string
	 */
	public function phpinfoAction() {

		phpinfo();
	}

	public function testAction() {

		$this->dump($_COOKIE);
	}

}