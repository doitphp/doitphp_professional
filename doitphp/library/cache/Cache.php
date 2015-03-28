<?php
/**
 * 缓存引擎管理类
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cache.php 2.0 2012-01-31 22:30:13Z tommy $
 * @package cache
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cache {

    /**
     * 工厂模式实例化常用缓存类
     *
     * @access public
     *
     * @param string $adapter 缓存类型
     * @param array $options 参数
     *
     * @return object
     */
    public static function factory($adapter, $options = null) {

        //参数分析
        if (!$adapter) {
            return false;
        }
        $adapter = trim($adapter);

        $object = false;

        //分析缓存引擎
        switch ($adapter) {

            case 'Memcache':
                $object = Cache_Memcache::getInstance($options);
                break;

            case 'Redis':
                $object = Cache_Redis::getInstance($options);
                break;

            case 'File':
            case 'Apc':
            case 'Xcache':
            case 'Wincache':
            case 'Eaccelerator':
                $object = Doit::singleton('Cache_' . $adapter);
                break;
        }

        return $object;
    }
}