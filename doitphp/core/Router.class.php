<?php
/**
 * 获取网址的路由信息类
 *
 * 分析访问网址，从网址中获取模块名、控制器、Action名
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Router.class.php 2.0 2012-11-20 20:52:54Z tommy <streen003@gmail.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

abstract class Router {

    /**
     * 当网址格式为$_GET模式时，Ctroller、Action等路由变量的变量名
     *
     * @var string
     */
    private static $_routerVar = 'router';

    /**
     * 分析路由网址, 获取当前的Controller名及action名
     *
     * @access public
     * @return array
     */
    public static function getRequest() {

        //当访问网址的格式时。如：http://yourdomain/index.php?router=member/list/index?id=23
        if (URL_FORMAT == Configure::GET_FORMAT) {

            if (isset($_GET[self::$_routerVar]) && $_GET[self::$_routerVar] == true) {

                $routerArray = explode(URL_SEGEMENTATION, trim($_GET[self::$_routerVar], URL_SEGEMENTATION));

                //获取和分析网址中的路由信息
                if (isset($routerArray[0]) && $routerArray[0]) {
                    $moduleArray = Configure::get('modules');
                    if ($moduleArray && is_array($moduleArray)) {
                        if (in_array($routerArray[0], $moduleArray)) {
                            //获取Module、Controller、Action名称
                            $moduleName     = $routerArray[0];
                            $controllerName = (isset($routerArray[1]) && $routerArray[1] == true) ? $routerArray[1] : DEFAULT_CONTROLLER;
                            $actionName     = (isset($routerArray[2]) && $routerArray[2] == true) ? $routerArray[2] : DEFAULT_ACTION;

                            return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
                        }
                    }

                    //获取Module、Controller、Action名称
                    $moduleName     = DEFAULT_MODULE;
                    $controllerName = $routerArray[0];
                    $actionName     = (isset($routerArray[1]) && $routerArray[1] == true) ? $routerArray[1] : DEFAULT_ACTION;

                    return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
                }
            }

            return array('module'=>DEFAULT_MODULE, 'controller'=>DEFAULT_CONTROLLER, 'action'=>DEFAULT_ACTION);
        }

        //当网址格式为路由网址时。如：http://yourdomain/index.php/member/list/mid/23/page/7
        if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI'])) {

            //网址分析
            $uri = self::_parseUri();

            //将处理过后的有效URL进行分析,提取有用数据.
            $uriInfoArray = explode(URL_SEGEMENTATION, $uri);

            if (isset($uriInfoArray[0]) && $uriInfoArray[0] == true) {
                //当项目的模块功能开启时
                $moduleArray = Configure::get('modules');
                if ($moduleArray && is_array($moduleArray)) {
                    //当路由网址中含有模块(module)信息时
                    if (in_array($uriInfoArray[0], $moduleArray)) {
                        //获取module、controller及action名称
                        $moduleName     = $uriInfoArray[0];
                        $controllerName = (isset($uriInfoArray[1]) && $uriInfoArray[1] == true) ? $uriInfoArray[1] : DEFAULT_CONTROLLER;
                        $actionName     = (isset($uriInfoArray[2]) && $uriInfoArray[2] == true) ? $uriInfoArray[2] : DEFAULT_ACTION;

                        //变量重组,将网址(URL)中的参数变量及其值赋值到$_GET全局超级变量数组中
                        if (($totalNum = sizeof($uriInfoArray)) > 4) {
                            for ($i = 3; $i < $totalNum; $i += 2) {
                                if (!isset($uriInfoArray[$i]) || !$uriInfoArray[$i] || !isset($uriInfoArray[$i + 1])) {
                                    continue;
                                }
                                $_GET[$uriInfoArray[$i]] = $uriInfoArray[$i + 1];
                            }
                        }

                        return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
                    }
                }

                //获取module、controller及action名称
                $moduleName     = DEFAULT_MODULE;
                $controllerName = $uriInfoArray[0];
                $actionName     = (isset($uriInfoArray[1]) && $uriInfoArray[1] == true) ? $uriInfoArray[1] : DEFAULT_ACTION;

                //变量重组,将网址(URL)中的参数变量及其值赋值到$_GET全局超级变量数组中
                if (($totalNum = sizeof($uriInfoArray)) > 3) {
                    for ($i = 2; $i < $totalNum; $i += 2) {
                        if (!isset($uriInfoArray[$i]) || !$uriInfoArray[$i] || !isset($uriInfoArray[$i + 1])) {
                            continue;
                        }
                        $_GET[$uriInfoArray[$i]] = $uriInfoArray[$i + 1];
                    }
                }

                return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
            }
        }

        return array('module'=>DEFAULT_MODULE, 'controller'=>DEFAULT_CONTROLLER, 'action'=>DEFAULT_ACTION);
    }

    /**
     * 在CLI模式下获取当前的Controller及Action名
     *
     * @access public
     * @return array
     */
    public static function getCliRequest() {

        if(isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == true) {
            $moduleArray = Configure::get('modules');
            if ($moduleArray && is_array($moduleArray)) {
                if (in_array($_SERVER['argv'][1], $moduleArray)) {
                    //获取Module、Controller、Action名称
                    $moduleName     = $_SERVER['argv'][1];
                    $controllerName = (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == true) ? $_SERVER['argv'][2] : DEFAULT_CONTROLLER;
                    $actionName     = (isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == true) ? $_SERVER['argv'][3] : DEFAULT_ACTION;

                    //分析并获取参数, 参数格式如: --param_name=param_value
                    if (($totalNum = sizeof($_SERVER['argv'])) > 4) {
                        for ($i = 4; $i < $totalNum; $i ++) {
                            if (!isset($_SERVER['argv'][$i]) || !$_SERVER['argv'][$i]) {
                                continue;
                            }

                            //CLI运行环境下参数模式:如 --debug=true, 不支持 -h -r等模式
                            if (substr($_SERVER['argv'][$i], 0, 2) == '--') {
                                $pos = strpos($_SERVER['argv'][$i], '=');
                                if ($pos !== false) {
                                    $key                   = substr($_SERVER['argv'][$i], 2, $pos - 2);
                                    $_SERVER['argv'][$key] = substr($_SERVER['argv'][$i], $pos + 1);
                                    unset($_SERVER['argv'][$i]);
                                }
                            }
                        }
                    }

                    return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
                }
            }

            //获取Module、Controller、Action名称
            $moduleName     = DEFAULT_MODULE;
            $controllerName = $_SERVER['argv'][1];
            $actionName     = (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == true) ? $_SERVER['argv'][2] : DEFAULT_ACTION;

            //分析并获取参数, 参数格式如: --param_name=param_value
            if (($totalNum = sizeof($_SERVER['argv'])) > 3) {
                for ($i = 3; $i < $totalNum; $i ++) {
                    if (!isset($_SERVER['argv'][$i]) || !$_SERVER['argv'][$i]) {
                        continue;
                    }

                    //CLI运行环境下参数模式:如 --debug=true, 不支持 -h -r等模式
                    if (substr($_SERVER['argv'][$i], 0, 2) == '--') {
                        $pos = strpos($_SERVER['argv'][$i], '=');
                        if ($pos !== false) {
                            $key                   = substr($_SERVER['argv'][$i], 2, $pos - 2);
                            $_SERVER['argv'][$key] = substr($_SERVER['argv'][$i], $pos + 1);
                            unset($_SERVER['argv'][$i]);
                        }
                    }
                }
            }

            return array('module'=>$moduleName, 'controller'=>$controllerName, 'action'=>$actionName);
        }

        return array('module'=>DEFAULT_MODULE, 'controller'=>DEFAULT_CONTROLLER, 'action'=>DEFAULT_ACTION);
    }

    /**
     * 网址分析,获取路由网址有效的URI
     *
     * @access private
     * @return string
     */
    private static function _parseUri() {

        //初始化$uri
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } else {
            //网址中省略引导文件名时
            $scriptBaseUrl = dirname($_SERVER['SCRIPT_NAME']);
            if (strpos($uri, $scriptBaseUrl) === 0) {
                $uri = substr($uri, strlen($scriptBaseUrl));
            }
        }

        $uri = ltrim($uri, '/');
        //当uri的内容极为简单,没有包含任何路由信息时
        if (!$uri) {
            return URL_SEGEMENTATION;
        }

        //当开启Rewrite设置时，去掉网址后缀
        if (DOIT_REWRITE === true) {
            $suffixLength = strlen(URL_SUFFIX);
            if ((strlen($uri) > $suffixLength) && (substr($uri, -$suffixLength) == URL_SUFFIX)) {
                $uri = substr($uri, 0, -$suffixLength);
            }
        }

        //如网址(URL)含有'?'(问号),则过滤掉问号(?)及其后面的所有字符串
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        //当自定义URL路由功能开启时
        if ($uri && Configure::get('application.customUrlRouter') === true) {
            $ruleArray = Configure::get('router');
            if (is_array($ruleArray) && $ruleArray) {
                //利用正则表达式将自定义的网址替换掉，替换为真实的网址
                foreach ($ruleArray as $routerKey=>$routerValue) {
                    $routerKey = str_replace(array(':any', ':num'), array('.+?', '[0-9]+'), $routerKey);
                    if (preg_match('#' . $routerKey . '#', $uri)) {
                        $uri = preg_replace('#' . $routerKey . '#', $routerValue, $uri);
                        break;
                    }
                }
            }
        }

        //当uri的内容极为简单,没有包含任何路由信息时
        if (!$uri || $uri == '/') {
            return URL_SEGEMENTATION;
        }

        return trim($uri, URL_SEGEMENTATION);
    }

    /**
     * 网址(URL)组装操作
     *
     * 注：组装绝对路径的URL
     *
     * @example
     * $memberUrl = Router::createUrl('member/list', array('mid'=>23, 'page'=>7));
     * 或
     * $memberUrl = Router::createUrl('member.list', array('mid'=>23, 'page'=>7));
     *
     * @access public
     *
     * @param string $route controller与action。例：controllerName/actionName
     * @param array $params URL路由其它字段。注：url的参数信息
     *
     * @return string
     */
    public static function createUrl($route, $params = array()) {

        //参数分析
        if (!$route) {
            return false;
        }

        //获取项目访问的基本网址
        $url = self::getBaseUrl() . '/';

        //统一路由网址分割符的操作
        $route = str_replace(array('//', '/', '.'), URL_SEGEMENTATION, $route);

        //当前的网址格式为:路由网址模式时
        if (URL_FORMAT == Configure::PATH_FORMAT) {

            //当开启Rewrite功能时
            if (DOIT_REWRITE === true) {
                $url .= $route;
            } else {
                $url .= ENTRY_SCRIPT_NAME . URL_SEGEMENTATION . $route;
            }

            if ($params && is_array($params)) {
                $paramArray = array();
                foreach ($params as $key=>$value) {
                    $paramArray[] = trim($key) . URL_SEGEMENTATION . trim($value);
                }
                $url .= URL_SEGEMENTATION . implode(URL_SEGEMENTATION, $paramArray);

                //清空不必要的内存占用
                unset($paramArray);

                if (DOIT_REWRITE === true) {
                    $url .= URL_SUFFIX;
                }
            }

            return $url;
        }

        //当开启Rewrite功能时
        if (DOIT_REWRITE === false) {
            $url .= ENTRY_SCRIPT_NAME;
        }

        $url .= '?' . self::$_routerVar . '=' . $route;

        if ($params && is_array($params)) {
            $url .= '&' . http_build_query($params);
        }

        return $url;
    }

    /**
     * 获取当前项目的根目录的URL
     *
     * 本类方法常用于网页的CSS, JavaScript，图片等文件的调用
     *
     * @access public
     * @return string
     */
    public static function getBaseUrl() {

        //处理URL中的//或\\情况,即:出现/或\重复的现象
        $url = str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));

        return rtrim($url, '/');
    }

}