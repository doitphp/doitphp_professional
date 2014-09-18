<?php
/**
 * DoitPHP自动加载引导类
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: AutoLoad.php 2.0 2012-12-01 11:52:00Z tommy <streen003@gmail.com> $
 * @package core
 * @since 1.0
 */

abstract class AutoLoad {

    /**
     * DoitPHP核心类引导数组
     *
     * 用于自动加载文件运行时,引导路径
     * @var array
     */
    private static $_coreClassArray = array(
    'Request'           => 'core/Request.php',
    'Response'          => 'core/Response.php',
    'Model'             => 'core/Model.php',
    'DbCommand'         => 'core/DbCommand.php',
    'DbPdo'             => 'core/DbPdo.php',
    'Log'               => 'core/Log.php',
    'DoitException'     => 'core/DoitException.php',
    'Widget'            => 'core/Widget.php',
    'View'              => 'core/View.php',
    'Template'          => 'core/Template.php',
    'WidgetTemplate'    => 'core/WidgetTemplate.php',
    'Extension'         => 'core/Extension.php',
    'Pager'             => 'library/Pager.php',
    'Script'            => 'library/Script.php',
    'File'              => 'library/File.php',
    'Html'              => 'library/Html.php',
    'Form'              => 'library/Form.php',
    'Cookie'            => 'library/Cookie.php',
    'Session'           => 'library/Session.php',
    'Image'             => 'library/Image.php',
    'PinCode'           => 'library/PinCode.php',
    'Curl'              => 'library/Curl.php',
    'Client'            => 'library/Client.php',
    'Check'             => 'library/Check.php',
    'Xml'               => 'library/Xml.php',
    'FileDownload'      => 'library/FileDownload.php',
    'FileUpload'        => 'library/FileUpload.php',
    'Excel'             => 'library/Excel.php',
    'Csv'               => 'library/Csv.php',
    'Security'          => 'library/Security.php',
    'Text'              => 'library/Text.php',
    'Encrypt'           => 'library/Encrypt.php',
    'Tree'              => 'library/Tree.php',
    'Player'            => 'library/Player.php',
    'Zip'               => 'library/Zip.php',
    'DbMongo'           => 'library/DbMongo.php',
    'Language'          => 'library/Language.php',
    'Cart'              => 'library/Cart.php',
    'Cache'             => 'library/cache/Cache.php',
    'Cache_Memcache'    => 'library/cache/Memcache.php',
    'Cache_Redis'       => 'library/cache/Redis.php',
    'Cache_File'        => 'library/cache/File.php',
    'Cache_Apc'         => 'library/cache/Apc.php',
    'Cache_Wincache'    => 'library/cache/Wincache.php',
    'Cache_Xcache'      => 'library/cache/Xcache.php',
    'Cache_Eaccelerator'=> 'library/cache/Eaccelerator.php',
    'Pinyin'            => 'library/Pinyin.php',
    'Calendar'          => 'library/Calendar.php',
    'Benchmark'         => 'library/Benchmark.php',
    'HtmlBuilder'       => 'library/HtmlBuilder.php',
    'HttpResponse'      => 'library/HttpResponse.php',
    'Ftp'               => 'library/Ftp.php',
    'Wsdl'              => 'library/Wsdl.php',
    );

    /**
     * 项目文件的自动加载
     *
     * doitPHP系统自动加载核心类库文件(core目录内的文件)及运行所需的controller文件、model文件、widget文件等
     *
     * 注:并非程序初始化时将所有的controller,model等文件都统统加载完,再执行其它。
     * 理解本函数前一定要先理解AutoLoad的作用。
     * 当程序运行时发现所需的文件没有找到时,AutoLoad才会被激发,按照register()的程序设计来完成对该文件的加载
     *
     * @access public
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     *
     * @return void
     */
    public static function register($className) {

        //参数分析
        if (!$className) {
            return false;
        }

        //doitPHP核心类文件的加载分析
        if (isset(self::$_coreClassArray[$className])) {
            //当$className在核心类引导数组中存在时, 加载核心类文件
            Doit::loadFile(DOIT_ROOT . DS . self::$_coreClassArray[$className]);
        } elseif (substr($className, -10) == 'Controller') {
            //controller文件自动载分析
            self::_loadController($className);
        } elseif (substr($className, -5) == 'Model') {
            //modlel文件自动加载分析
            self::_loadModel($className);
        } elseif (substr($className, -6) == 'Widget') {
            //加载所要运行的widget文件
            self::_loadWidget($className);
        } else {
            //分析加载扩展类文件目录(library)的文件
            if (!self::_loadLibrary($className)) {
                //根据配置文件improt的引导设置，自动加载文件
                if (!self::_loadImportConfigFile($className)) {
                    //最后，当运行上述自动加载规则，均没有加载所需要的文件时，提示错误信息
                    Controller::halt('The Class ' . $className .' File is not found !', 'Normal');
                }
            }
        }

        return true;
    }

    /**
     * 自动加载控制器文件
     *
     * @access private
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     *
     * @return void
     */
    private static function _loadController($className) {

        //获取当前所运行的模块(Module)
        $moduleName = Doit::getModuleName();

        //获取Controller文件目录路径
        if (!$moduleName) {
            $controllerHomePath = BASE_PATH . DS . 'controllers' . DS;
        } else {
            $controllerHomePath = BASE_PATH . DS . 'modules' . DS . $moduleName . DS . 'controllers' . DS;
        }

        //分析controller名称
        $controllerName = substr($className, 0, -10);

        //分析Controller子目录的情况。注:controller文件的命名中下划线'_'相当于目录的'/'。
        if (strpos($controllerName, '_') === false) {
            $controllerFilePath = $controllerHomePath . $controllerName . '.php';
            if (!is_file($controllerFilePath)) {
                //当Controller文件不存在时,系统直接报错
                Controller::halt('The Controller File: ' . $controllerFilePath .' is not found!', 'Normal');
            }
            //当文件在Controller根目录下存在时,直接加载
            Doit::loadFile($controllerFilePath);
        } else {
            //当$controller中含有'_'字符时,将'_'替换为路径分割符。如："/" 或 "\"
            $childDirArray      = explode('_', strtolower($controllerName));
            $controllerFileName = ucfirst(array_pop($childDirArray));
            $childDirName       = implode(DS, $childDirArray);
            unset($childDirArray);
            //分析并获取Controller中子目录中的Controller文件径
            $controllerFilePath = $controllerHomePath . $childDirName . DS . $controllerFileName . '.php';
            if (!is_file($controllerFilePath)) {
                //当文件在子目录里没有找到时
                Controller::halt('The Controller File: ' . $controllerFilePath .' is not found!', 'Normal');
            }
            //当子目录中所要加载的文件存在时
            Doit::loadFile($controllerFilePath);
        }
    }

    /**
     * 自动加载模型文件
     *
     * @access private
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     *
     * @return void
     */
    private static function _loadModel($className) {

        //获取当前所运行的模块(Module)
        $moduleName = Doit::getModuleName();

        //获取Model文件目录路径
        if (!$moduleName) {
            $modelHomePath = BASE_PATH . DS . 'models' . DS;
        } else {
            $modelHomePath = BASE_PATH . DS . 'modules' . DS . $moduleName . DS . 'models' . DS;
        }

        //分析Model文件的实际路径
        $modelFilePath = self::_parseFilePath($modelHomePath, substr($className, 0, -5));

        //当Model文件存在时
        if (!is_file($modelFilePath)) {
            //当所要加载的Model文件不存在时,显示错误提示信息
            Controller::halt('The Model file: ' . $modelFilePath . ' is not found!', 'Normal');
        }

        //加载Model文件
        Doit::loadFile($modelFilePath);
        return true;
    }

    /**
     * 自动加载挂件文件
     *
     * @access private
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     * @param boolean $supportModule 是否支持模块(Module)文件的自动加载 (true:支持/false:不支持)
     *
     * @return void
     */
    private static function _loadWidget($className, $supportModule = true) {

        //定义所要加载的文件是否在模块(Module)目录中。(true:在/false:不在)
        $isModule = false;

        //获取当前所运行的模块(Module)
        $moduleName = Doit::getModuleName();

        //当支持模块(Module)文件自动加载开关开启时
        if ($supportModule) {
            //获取Widget文件目录路径
            if (!$moduleName) {
                $widgetHomePath = BASE_PATH . DS . 'widgets' . DS;
            } else {
                $widgetHomePath = BASE_PATH . DS . 'modules' . DS . $moduleName . DS . 'widgets' . DS;
                //重定义所要加载的文件是否在模块(Module)目录中
                $isModule = true;
            }
        } else {
            $widgetHomePath = BASE_PATH . DS . 'widgets' . DS;
        }

        //分析Widget文件的实际路径
        $widgetFilePath = self::_parseFilePath($widgetHomePath, substr($className, 0, -6));
        //当Widget文件在Widget根目录中存在时
        if (!is_file($widgetFilePath)) {
            //当模块(Module)中Widget目录不存在所要加载的Widget文件时，对application中Widget目录进行分析并加载Widget文件
            if ($isModule) {
                return self::_loadWidget($className, false);
            }
            //当所要加载的Widget文件不存在时,显示错误提示信息
            Controller::halt('The Widget file: ' . $widgetFilePath . ' is not found!', 'Normal');
        }

        //加载Widget文件
        Doit::loadFile($widgetFilePath);
        return true;
    }

    /**
     * 自动加载自定义类文件
     *
     * @access private
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     * @param boolean $supportModule 是否支持模块(Module)文件的自动加载 (true:支持/false:不支持)
     *
     * @return void
     */
    private static function _loadLibrary($className, $supportModule = true) {

        //定义所要加载的文件是否在模块(Module)目录中。(true:在/false:不在)
        $isModule = false;

        //当支持模块(Module)文件自动加载开关开启时
        if ($supportModule) {
            //获取当前所运行的模块(Module)
            $moduleName = Doit::getModuleName();

            //获取library文件目录路径
            if (!$moduleName) {
                $libraryHomePath = BASE_PATH . DS . 'library' . DS;
            } else {
                $libraryHomePath = BASE_PATH . DS . 'modules' . DS . $moduleName . DS . 'library' . DS;
                //重定义所要加载的文件是否在模块(Module)目录中
                $isModule = true;
            }
        } else {
            //获取library文件目录路径
            $libraryHomePath = BASE_PATH . DS . 'library' . DS;
        }

        //分析library文件的实际路径
        $libraryFilePath = self::_parseFilePath($libraryHomePath, $className);

        //当library文件在library根目录中存在时
        if (!is_file($libraryFilePath)) {
            //当模块(Module)中Model目录不存在所要加载的Model文件时，对application中Model目录进行分析并加载Model文件
            if ($isModule) {
                return self::_loadLibrary($className, false);
            }
            //当所要加载的library文件不存在时
            return false;
        }

        //加载library类文件
        Doit::loadFile($libraryFilePath);
        return true;
    }

    /**
     * 加载自定义配置文件所引导的文件
     *
     * @access private
     *
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     *
     * @return void
     */
    private static function _loadImportConfigFile($className) {

        //定义自动加载状态。(true:已加载/false:未加载)
        $atuoLoadStatus = false;

        //分析配置文件import引导信息
        $importRules = Configure::get('import');

        //当配置文件引导信息合法时
        if ($importRules && is_array($importRules)) {
            foreach ($importRules as $rules) {
                if (!$rules) {
                    continue;
                }

                //当配置文件引导信息中含有*'时，将设置的规则中的*替换为所要加载的文件类名
                if (strpos($rules, '*') !== false) {
                    $filePath = str_replace('*', $className, $rules);
                } else {
                    $filePath = self::_parseFilePath($rules, $className);
                }

                //当自定义自动加载的文件存在时
                if (is_file($filePath)) {
                    //加载文件
                    Doit::loadFile($filePath);
                    $atuoLoadStatus = true;
                    break;
                }
            }
        }

        return $atuoLoadStatus;
    }

    /**
     * 分析文件的实际路径
     *
     * @access private
     *
     * @param string $homePath 文件的home目录(如：controller文件的home为application/controllers)
     * @param string $className 所需要加载的类的名称,注:不含后缀名
     *
     * @return string
     */
    protected static function _parseFilePath($homePath, $className) {

        //当className中含有下划线('_')时
        if (strpos($className, '_') !== false) {
            $childDirArray = explode('_', $className);
            $classFileName = array_pop($childDirArray);
            return $homePath . strtolower(implode(DS, $childDirArray)) . DS . $classFileName . '.php';
        }

        return $homePath . $className . '.php';
    }

}