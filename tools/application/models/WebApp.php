<?php
/**
 * 创建项目目录及文件管理模型
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Webapp.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> $
 * @package model
 * @since 1.0
 */

class WebAppModel {


	/**
	 * 创建项目引导文件（index.php）
	 *
	 * @access public
	 * @return string
	 */
	public function createIndexFile() {

		$filePath = Configure::get('webappPath') . 'index.php';

		$fileContent = <<<EOT
<?php
/**
 * application index
 *
 * @author tommy <streen003@gmail.com>
 * @copyright Copyright (C) 2009-2012 www.doitphp.com All rights reserved.
 * @version \$Id: index.php 1.0 2012-02-12 01:14:18Z tommy \$
 * @package application
 * @since 1.0
 */

define('IN_DOIT', true);

/**
 * 定义项目所在路径(根目录):APP_ROOT
 */
define('APP_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/**
 * 加载DoitPHP框架的初始化文件,如果必要可以修改文件路径
 */
require_once APP_ROOT . 'doitphp/Doit.php';

\$config = APP_ROOT . 'application/config/application.php';

/**
 * 启动应用程序(网站)进程
 */
doit::run(\$config);
EOT;

		return File::writeFile($filePath, $fileContent);
	}


	/**
	 * 创建项目的重写规则引导文件(.htaccess)
	 *
	 * @access public
	 * @return string
	 */
	public function createHtaccessFile() {

		$filePath    = Configure::get('webappPath') . '.htaccess';
		$fileContent = <<<EOT
RewriteEngine on

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule !\\.(js|ico|txt|gif|jpg|png|css)\\\$ index.php [NC,L]
EOT;

		return File::writeFile($filePath, $fileContent);
	}

	/**
	 * 创建项目搜索引擎网络爬虫引导文件(robots.txt)
	 *
	 * @access public
	 * @return string
	 */
	public function createRobotsFile() {

		$filePath = Configure::get('webappPath') . 'robots.txt';
		$fileContent = <<<EOT
User-agent: *
Crawl-delay: 10
Disallow: /doitphp/
Disallow: /tools/
Disallow: /application/
Disallow: /assets/
Disallow: /cache/
Disallow: /logs/
EOT;

		return File::writeFile($filePath, $fileContent);
	}

	/**
	 * 创建项目配置文件(application.php)
	 *
	 * @access protected
	 * @return string
	 */
	protected function _createConfigFile() {

		$filePath = Configure::get('webappPath') . 'application/config/application.php';
		$fileContent = <<<EOT
<?php
/**
 * 项目主配置文件
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version \$Id: application.php 1.0 2013-01-11 21:53:32Z tommy <streen003@gmail.com> \$
 * @package config
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}
EOT;

		return File::writeFile($filePath, $fileContent);
	}

	/**
	 * 创建403错误提示文件(index.html)
	 *
	 * @access protected
	 * @return string
	 */
	protected function _create403IndexFile($dirPath) {
		$filePath = $dirPath . '/index.html';
		$fileContent = <<<EOT
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>Directory access is forbidden.</p></body></html>
EOT;

		return File::writeFile($filePath, $fileContent);
	}

	/**
	 * 创建403错误提示文件(.htaccess)
	 *
	 * @access protected
	 * @return string
	 */
	protected function _create403HtaccessFile($dirPath) {
		$filePath = $dirPath . '/.htaccess';
		$fileContent = <<<EOT
deny from all
EOT;

		return File::writeFile($filePath, $fileContent);
	}

	/**
	 * 创建项目应用目录(application)
	 *
	 * @access public
	 *
	 * @param boolean $isApache 服务器软件是否为apache
	 * @param boolean $hasExt 是否有扩展目录
	 * @param boolean $hasModule 是否为扩展模块目录
	 * @param boolean $hasLang 是否有多语言支持目录
	 *
	 * @return string
	 */
	public function createApplicationDir($isApache = true, $hasExt = false, $hasModule = false, $hasLang = false) {

		$appDirArray = array(
		'application',
		'application/controllers',
		'application/models',
		'application/views',
		'application/library',
		'application/widgets',
		'application/widgets/views',
		'application/config',
		);

		if ($hasExt) {
			$appDirArray[] = 'application/extensions';
		}
		if ($hasModule) {
			$appDirArray[] = 'application/modules';
		}
		if ($hasLang) {
			$appDirArray[] = 'application/language';
		}

		$webAppPath = Configure::get('webappPath');

		$result = false;
		foreach ($appDirArray as $childDirName) {
			$dirPath = $webAppPath . $childDirName;
			$result = File::makeDir($dirPath);
			if (!$result) {
				return false;
			}
			//创建访问权限保护文件
			if ($isApache == true) {
				if ($childDirName == 'application') {
					$this->_create403HtaccessFile($dirPath);
				}
			} else {
				$this->_create403IndexFile($dirPath);
			}
			//创建主配置文件
			if ($childDirName == 'application/config') {
				$this->_createConfigFile();
			}
		}

		return true;
	}

	/**
	 * 创建项目仓库目录(asset)
	 *
	 * @access public
	 * @return string
	 */
	public function createAssetDir() {

		$assetDirArray = array(
		'assets',
		'assets/css',
		'assets/images',
		'assets/js',
		'assets/doit',
		);

		$webAppPath = Configure::get('webappPath');

		$result = false;
		foreach ($assetDirArray as $childDirName) {
			$dirPath = $webAppPath . $childDirName;
			$result = File::makeDir($dirPath);
			if (!$result) {
				return false;
			}
			//创建访问权限保护文件
			$this->_create403IndexFile($dirPath);
		}

		//分析doitphp所集成的css及image等资源文件
		$doitJsPath    = $webAppPath . 'assets/doit/js';
		$doitImagePath = $webAppPath . 'assets/doit/images';

		File::copyDir(DOIT_ROOT . 'vendors', $doitJsPath);
		File::copyDir(DOIT_ROOT . 'views/images', $doitImagePath);

		return true;
	}

	/**
	 * 创建项目仓库目录(cache)
	 *
	 * @access public
	 *
	 * @param boolean $isApache 服务器软件是否为apache
	 *
	 * @return string
	 */
	public function createCacheDir($isApache = false) {

		//parse cache dir array
		$cacheDirArray = array(
		'cache',
		'cache/models',
		'cache/temp',
		'cache/htmls',
		'cache/views',
		'cache/data',
		);

		$webAppPath = Configure::get('webappPath');

		$result = false;
		foreach ($cacheDirArray as $childDirName) {
			$dirPath = $webAppPath . $childDirName;
			$result = File::makeDir($dirPath, 0777);
			if (!$result) {
				return false;
			}

			//创建访问权限保护文件
			if ($isApache == true) {
				if ($childDirName == 'cache') {
					$this->_create403HtaccessFile($dirPath);
				}
			} else {
				$this->_create403IndexFile($dirPath);
			}
		}

		return true;
	}

	/**
	 * 创建项目日志目录(logs)
	 *
	 * @access public
	 *
	 * @param boolean $isApache 服务器软件是否为apache
	 *
	 * @return string
	 */
	public function createLogDir($isApache = false) {

		$dirPath = Configure::get('webappPath') . 'logs';

		$result = File::makeDir($dirPath);
		if (!$result) {
			return false;
		}

		(!$isApache) ? $this->_create403IndexFile($dirPath) : $this->_create403HtaccessFile($dirPath);

		return true;
	}

	/**
	 * 分析项目的根目录
	 *
	 * 当根目录不存在时，则创建根目录
	 *
	 * @access public
	 * @return string
	 */
	public function parseWebAppPath() {

		//parse webapp path
		$webAppPath = Configure::get('webappPath');

		return File::makeDir($webAppPath);
	}
}