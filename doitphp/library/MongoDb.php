<?php
/**
 * Mongodb数据库的操作驱动类
 *
 * 用于mongodb数据库的增、删、改、查等操作
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: MongoDb.php 2.0 2012-12-29 21:31:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class MongoDb {

    /**
     * 单例模式实例化本类
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 数据库连接实例化对象名
     *
     * @var object
     */
    protected $_dbLink = null;

    /**
     * mongo实例化对象
     *
     * @var object
     */
    protected $_mongo = null;

    /**
     * 数据库连接参数默认值
     *
     * @var array
     */
    protected $_defaultConfig = array(
        'dsn'    => 'mongodb://localhost:27017',
        'option' => array('connect' => true),
    );

    /**
     * 构造方法
     *
     * 用于初始化运行环境,或对基本变量进行赋值
     *
     * @access public
     *
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     *
     * @return boolean
     */
    public function __construct($params = array()) {

        if (!extension_loaded('mongo')) {
            Controller::halt('The mongo extension to be loaded!');
        }

        //参数分析
        if (!$params || !is_array($params)) {
            //加载数据库配置文件.
            $params = Configure::get('mongo');
        }

        $params = is_array($params) ? $params + $this->_defaultConfig : $this->_defaultConfig;
        if (!isset($params['dbname']) || !$params['dbname']) {
            Controller::halt('The file of MongoDB config is error, dbname is not found!');
        }

        try {
            //实例化mongo
            $this->_mongo = new Mongo($params['dsn'], $params['option']);

            //连接mongo数据库
            $this->_dbLink = $this->_mongo->selectDB($params['dbname']);

            //用户登录
            if (isset($params['username']) && isset($params['password'])) {
                $this->_dbLink->authenticate($params['username'], $params['password']);
            }

            return true;
        } catch (Exception $exception) {

            //抛出异常信息
            throw new DoitException('MongoDb connect error!<br/>' . $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Select Collection
     *
     * @author ColaPHP
     *
     * @access public
     *
     * @param string $collection 集合名称(相当于关系数据库中的表)
     *
     * @return object
     */
    public function collection($collection) {

        return $this->_dbLink->selectCollection($collection);
    }

    /**
     * 查询一条记录
     *
     * @access public
     *
     * @param string $collnections 集合名称(相当于关系数据库中的表)
     * @param array $query 查询的条件array(key=>value) 相当于key=value
     * @param array fields 需要列表的字段信息array(filed1,filed2)
     *
     * @return array
     */
    public function getOne($collnections, $query, $fields=array()) {

        return $this->collection($collnections)->findOne($query, $fields);
    }

    /**
     * 查询多条记录
     *
     * @access public
     *
     * @param string $collnections 集合名称(相当于关系数据库中的表)
     * @param array $query 查询的条件array(key=>value) 相当于key=value
     * @param array fields 需要列表的字段信息array(filed1,filed2)
     *
     * @return array
     */
    public function getAll($collnections, $query, $fields=array()) {

        $result = array();
        $cursor = $this->collection($collnections)->find($query, $fields);
        while ($cursor->hasNext()) {
            $result[] = $cursor->getNext();
        }

        return $result;
    }

    /**
     * 插入数据
     *
     * @access public
     *
     * @param string $collnections 集合名称(相当于关系数据库中的表)
     * @param array $data 所要写入的数据信息
     *
     * @return boolean
     */
    public function insert($collnections, $data) {

        return $this->collection($collnections)->insert($data);
    }

    /**
     * 更改数据
     *
     * @access public
     *
     * @param string $collnections 集合名称(相当于关系数据库中的表)
     * @param array $query 查询的条件array(key=>value) 相当于key=value
     * @param array $data 所要更改的信息
     * @param array $options 选项
     *
     * @return boolean
     */
    public function update($collection, $query, $data, $options=array('safe'=>true,'multiple'=>true)) {

        return $this->collection($collection)->update($query, $data, $options);
    }

    /**
     * 删除数据
     *
     * @access public
     *
     * @param string $collnections 集合名称(相当于关系数据库中的表)
     * @param array $query 查询的条件array(key=>value) 相当于key=value
     * @param array $option 选项
     *
     * @return boolean
     */
    public function delete($collection, $query, $option=array("justOne"=>false)) {

        return $this->collection($collection)->remove($query, $option);
    }

     /**
     * MongoId
     *
     * @author ColaPHP
     *
     * @access public
     *
     * @param string $id 获取mongoId
     *
     * @return object
     */
    public static function id($id = null)
    {
        return new MongoId($id);
    }

    /**
     * MongoTimestamp
     *
     * @author ColaPHP
     *
     * @access public
     *
     * @param int $sec
     * @param int $inc
     *
     * @return MongoTimestamp
     */
    public static function Timestamp($sec = null, $inc = 0)
    {
        if (!$sec) {
            $sec = $_SERVER['REQUEST_TIME'];
        }

        return new MongoTimestamp($sec, $inc);
    }

    /**
     * GridFS
     *
     * @author ColaPHP
     *
     * @access public
     *
     * @return object
     */
    public function gridFS($prefix = 'fs')
    {
        return $this->_dbLink->getGridFS($prefix);
    }

    /**
     * 析构函数
     *
     * 程序执行完毕，打扫战场
     *
     * @access public
     *
     * @return void
     */
    public function __destruct() {

        if ($this->_dbLink) {
            $this->_dbLink = null;
        }

        if ($this->_mongo) {
            $this->_mongo->close();
        }

        return true;
    }

    /**
     * 单例模式
     *
     * 用于本类的单例模式(singleton)实例化
     *
     * @access public
     *
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     *
     * @return object
     */
    public static function getInstance($params = array()) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }

}