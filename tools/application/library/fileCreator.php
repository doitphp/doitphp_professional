<?php
/**
 * file: classFileCreator.php
 *
 * 生成类文件代码内容
 * @author tommy<string@gmail.com>
 * @copyright Copyright (C) www.doitphp.com 2012 All rights reserved.
 * @version $Id: classFileCreator.php 1.0 2012-10-05 12:25:21Z $
 * @package extension
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
	exit('403:Access Forbidden!');
}

class fileCreator {

	/**
	 * 获取文件头代码注释
	 *
	 * @access public
	 *
	 * @param string $fileName 文件名
	 * @param string $info 文件描述
	 * @param string $desc 文件详细描述
	 * @param string $author 文件作者
	 * @param string $copyright 文件版权信息
	 * @param string $package 组件
	 * @param string $license 类文件的使用协议
	 * @param string $link 文件的相关连接
	 *
	 * @return string
	 */
	public static function fileNote($fileName = null, $info = null, $author = null, $copyright = null, $package = null, $desc = null, $license = null, $link = null) {

		$info 		= is_null($info) ? 'Enter description here ...' : $info;

		$string  = "/**\n";
		$string .= " * {$info}\n";
		$string .= " *\n";

		if ($desc) {
			$string .= " * {$desc}\n";
			$string .= " *\n";
		}

		$string .= " * @author {$author}\n";
		if ($link) {
			$string .= " * @link {$link}\n";
		}
		$string .= " * @copyright Copyright (C) {$copyright} All rights reserved.\n";
		if ($license) {
			$string .= " * @license {$license}\n";
		}

		$string .= " * @version \$Id: {$fileName} 1.0 " . date('Y-m-d H:i:s'). "Z" . (!is_null($author) ? " {$author}" : "") . " \$\n";
		$string .= " * @package {$package}\n";
		$string .= " * @since 1.0\n";
		$string .= " */\n\n";

		return $string;
	}

	/**
	 * 获取类方法的代码注释
	 *
	 * @access public
	 *
	 * @param string $access 类方法的访问权限
	 * @param string $return 返回的数据类型
	 * @param array $params 类方法的参数
	 * @param string $info 类方法描述
	 * @param string $desc 类方法的详细描述
	 *
	 * @return string
	 */
	public static function methodNote($access = 'public', $return = null, $params = array(), $info = null, $desc = null) {

		$access 	= self::parseAccess($access);
		$info 		= (!$info) ? 'Enter description here ...' : $info;

		$string  = "\n    /**\n";
		$string .= "     * {$info}\n";
		$string .= "     *\n";

		if ($desc) {
			$string .= "     * {$desc}\n";
			$string .= "     *\n";
		}

		$string .= "     * @access {$access}\n";

		//分析类方法参数
		if ($params && is_array($params) && is_array($params[0])) {
			$string .= "     *\n";
			foreach ($params as $lines) {
				$string .= "     * @param {$lines[1]} \${$lines[0]}" . (isset($lines[2]) ? ' '. $lines[2] : ''). "\n";
			}
			$string .= "     *\n";
		}

		$string .= "     * @return {$return}\n";
		$string .= "     */\n";

		return $string;
	}

	/**
	 * 获取类方法的代码内容
	 *
	 * @access public
	 *
	 * @param string $methodName 类方法名称
	 * @param string $access 类方法的访问权限
	 * @param array $params 类方法的参数
	 * @param string $content 类方法的内容
	 * @param boolean $isStatic 类方法是否支持静态调用
	 *
	 * @return string
	 */
	public static function methodCode($methodName, $access = 'public', $params = array(), $content = null, $isStatic = false) {

		//parse params
		if (!$methodName) {
			return false;
		}

		$access = self::parseAccess($access);

		//parse paramString
		$paramString = "";
		if ($params && is_array($params)) {

			$paramArray = array();
			foreach ($params as $key=>$value) {
				if (!is_numeric($key)) {

					if (in_array(strtolower($value), array('true', 'false', 'null'))) {
						$value = strtolower(trim($value));
					} else {
						if (is_null($value)) {
							$value = 'null';
						} else {
							$value = (!is_numeric($value)) ? "'{$value}'" : "{$value}";
						}
					}

					$paramArray[] = "\${$key} = {$value}";

				} else {

					$paramArray[] = "\${$value}";
				}
			}

			$paramString = implode(', ', $paramArray);
			unset($paramArray);
		}

		$string  = "    {$access} " . ($isStatic === true ? 'static ' : '') . "function {$methodName}({$paramString}) {\n";

		if (!is_null($content) && is_string($content)) {
			$string .= "        {$content}";
		}

		$string .= "\n    }\n";

		return $string;
	}

	/**
	 * 获取类文件代码内容(开始部分)
	 *
	 * @access public
	 *
	 * @param string $className 类的名称
	 * @param string $extend 继承类的名称
	 * @param boolean $isAbsolute 类是否为absolute属性
	 *
	 * @return string
	 */
	public static function classCodeStart($className, $extend = null, $isAbsolute = false) {

		//parse params
		if (!$className) {
			return false;
		}

		$string  = ($isAbsolute === true) ? "abstract " : "";
		$string .= (!$extend) ? "class {$className} {\n" : "class {$className} extends {$extend} {\n";

		return $string;
	}

	/**
	 * 获取类文件代码内容(结束部分)
	 *
	 * @access public
	 * @return string
	 */
	public static function classCodeEnd() {

		return "\n}";
	}

	/**
	 * 获取文件读取权限的代码内容
	 *
	 * @access public
	 * @param string $content 代码内容
	 * @return string
	 */
	public static function authCode($content = null) {

		$content = (!$content) ? "if (!defined('IN_DOIT')) {\n    exit('403:Access Forbidden!');\n}\n\n" : trim($content);
		return $content;
	}

	/**
	 * 分析函数权限
	 *
	 * @access public
	 * @param string $access 权限
	 * @return string
	 */
	public static function parseAccess($accessName = 'public') {

		//parse params
		if (!$accessName) {
			return 'public';
		}

		$accessName = strtolower(trim($accessName));

		//当accessName为数据字时
		if (!in_array($accessName, array('public', 'protected', 'private'))) {
			if (!is_numeric($accessName)) {
				return 'public';
			}

			$accessName 	= !in_array($accessName, array(1, 2, 3)) ? 1 : $accessName;
			$accessArray 	= array(1=>'public', 2=>'protected', 3=>'private');
			$accessName 	= $accessArray[$accessName];
		}

		return $accessName;
	}
}