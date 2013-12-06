<?php
/**
 * 模型（Model）基类
 *
 * 提供数据库操作常用的类方法
 *
 * @author tommy <streen003@gmail.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) Copyright (c) 2012 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Model.class.php 2.0 2012-12-12 21:33:45Z tommy <streen003@gmail.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Model {

    /**
     * 数据表名
     *
     * @var string
     */
    protected $_tableName = null;

    /**
     * 数据表字段信息
     *
     * @var array
     */
    protected $_tableField = array();

    /**
     * 数据表的主键信息
     *
     * @var string
     */
    protected $_primaryKey = null;

    /**
     * model所对应的数据表名的前缀
     *
     * @var string
     */
    protected $_prefix = null;

    /**
     * 错误信息
     *
     * @var string
     */
    protected $_errorInfo = null;

    /**
     * 数据库连接参数
     *
     * @var array
     */
    protected $_config = array();

    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $_parts = array();

     /**
     * 主数据库实例化对象
     *
     * @var object
     */
    protected $_master = null;

    /**
     * 从数据库实例化对象
     *
     * @var object
     */
    protected $_slave = null;

    /**
     * 数据库实例化是否为单例模式
     *
     * @var boolean
     */
    protected $_singleton = false;

    /**
     * 单例模式实现化对象
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 构造方法（函数）
     *
     * 用于初始化程序运行环境，或对基本变量进行赋值
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //分析数据库连接参数
        $this->_config = $this->_parseConfig();

        //执行前函数(类方法)
        $this->init();

        return true;
    }

    /**
     * 获取当前模型(Model)文件所对应的数据表的名称
     *
     * 注:若数据表有前缀($prefix)时，系统将自动加上数据表前缀。
     *
     * @access public
     * @return string
     */
    public function getTableName() {

        if (!$this->_tableName) {
            //调用回调类方法，获取当前Model文件所绑定的数据表名
            $tableName = $this->tableName();

            //当回调类方法未获取到数据表时，则默认为当前的Model类名
            $tableName = ($tableName == true) ? trim($tableName) : substr(strtolower(get_class($this)), 0, -5);

            //分析数据表名，当有前缀时，加上前缀
            $this->_tableName = (!$this->_prefix) ? $tableName : $this->_prefix . $tableName;
        }

        return $this->_tableName;
    }

    /**
     * 获取当前模型（Model）文件所对应的数据表主键
     *
     * 注：数据表的物理主键，真实存在的，不是虚拟的。
     *
     * @access protected
     * @return string
     */
    protected function _getPrimaryKey() {

        if (!$this->_primaryKey) {
            //从回调方法中获取数据表主键
            $primaryKey = $this->primaryKey();
            if (!$primaryKey) {
                $tableName = $this->getTableName();
                //当回调方法中未获取数据表主键时，则从缓存文件中读取
                if (!$this->_loadCache($tableName)) {
                    $this->_createCache($tableName);
                }
            } else {
                $this->_primaryKey = trim($primaryKey);
            }

        }

        return $this->_primaryKey;
    }

    /**
     * 获取当前模型（Model）文件所对应的数据表字段信息
     *
     * @access protected
     * @return array
     */
    protected function _getTableFields() {

        if (!$this->_tableField) {
            //调用回调方法(获取数据表字段信息)
            $tableFields = $this->tableFields();
            if (!$tableFields) {
                $tableName = $this->getTableName();
                //当回调方法未获取到数据表字段信息时，则从缓存文件中读取
                if (!$this->_loadCache($tableName)) {
                    $this->_createCache($tableName);
                }
            } else {
                $this->_tableField = $tableFields;
            }
        }

        return $this->_tableField;
    }

    /**
     * 设置当前模型（Model）文件所对应的数据表的名称
     *
     * 注：数据表名称不含数据表前缀（$prefix）
     *
     * @access public
     *
     * @param string $tableName 数据表名称
     *
     * @return object
     */
    public function setTableName($tableName) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        $this->_tableName = trim($tableName);

        return $this;
    }

    /**
     * 回调类方法：自定义数据表名
     *
     * 在继承类中重载本方法可以定义所对应的数据表的名称
     *
     * @access protected
     * @return string
     */
    protected function tableName() {

        return null;
    }

    /**
     * 回调类方法：自定义数据表主键
     *
     * 在继承类中重载本方法可以定义所对应的数据表的主键。
     *
     * @access protected
     * @return string
     */
    protected function primaryKey() {

        return null;
    }

    /**
     * 回调类方法：自定义数据表字段信息
     *
     * 在继承类中重载本方法可以定义所对应的数据表的字段信息。
     *
     * @access protected
     * @return array
     */
    protected function tableFields() {

        return array();
    }

    /**
     * 获取当前模型（Model）文件所对应的数据表前缀
     *
     * @access public
     * @return string
     */
    public function getTablePrefix() {

        return $this->_prefix;
    }

    /**
     * 加载当前模型（Model）文件的缓存文件内容
     *
     * 注：缓存文件内容为：当前模型（Model）文件所对应的数据表的字段信息及主键信息。
     *
     * @access protected
     *
     * @param string $tableName 数据表名称
     *
     * @return array
     */
    protected function _loadCache($tableName) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        $cacheFile = $this->_getCacheFile($tableName);

        //分析缓存文件是否存在
        if (!is_file($cacheFile)) {
            return false;
        }

        $cachContent = include $cacheFile;

        $this->_primaryKey = $cachContent['primaryKey'];
        $this->_tableField = $cachContent['fields'];

        //清空不必要的内存占用
        unset($cachContent);

        return true;
    }

    /**
     * 创建当前模型（Model）文件的缓存文件
     *
     * 注：缓存文件包含当前模型（Model）文件所对应的数据表的字段和主键信息，用于减轻数据反复查询数据表字段信息的操作，从而提高程序的运行效率。
     *
     * @access protected
     *
     * @param string $tableName 数据表名称
     *
     * @return array
     */
    protected function _createCache($tableName) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        //获取数据表字段信息
        $tableInfo = $this->_master()->getTableInfo($tableName);

        $this->_primaryKey = $tableInfo['primaryKey'][0];
        $this->_tableField = $tableInfo['fields'];

        //分析缓存文件内容
        $cacheDataArray = array(
        'primaryKey' => $this->_primaryKey,
        'fields'     => $this->_tableField,
        );

        $cacheContent   = "<?php\nif (!defined('IN_DOIT')) exit();\nreturn " . var_export($cacheDataArray, true) . ";";

        //分析缓存文件路径
        $cacheFile = $this->_getCacheFile($tableName);

        //分析缓存目录
        $cacehDir = dirname($cacheFile);
        if (!is_dir($cacehDir)) {
            mkdir($cacehDir, 0777, true);
        }

        //将缓存内容写入缓存文件
        file_put_contents($cacheFile, $cacheContent, LOCK_EX);

        return true;
    }

    /**
     * 删除当前模型（Model）文件的缓存文件
     *
     * 注：如果自定了数据表的字段信息及主键信息，则当前模型（Model）文件的缓存文件不再被使用。
     *
     * @access public
     *
     * @param string $tableName 数据表名
     *
     * @return boolean
     */
    public function removeCache($tableName) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        //分析缓存文件路径
        $cacheFile = $this->_getCacheFile($tableName);

        //当文件存在时，删除文件
        if (!is_file($cacheFile)) {
            return true;
        }

        return unlink($cacheFile);
    }

    /**
     * 分析当前model缓存文件的路径
     *
     * @access protected
     *
     * @param string $tableName 数据表名
     *
     * @return string    缓存文件的路径
     */
    protected function _getCacheFile($tableName) {

        //分析Model缓存文件的目录
        $cachePath = CACHE_PATH . 'models' . DIRECTORY_SEPARATOR;

        return $cachePath . $tableName . '.tableInfo.cache.php';
    }

    /**
     * 调试类方法：优雅输出print_r()函数所要输出的内容
     *
     * 注：详细信息参见Controller Class中的类方法dump()。
     *
     * @access public
     *
     * @param mixed $data 所要输出的数据
     * @param boolean $type 输出的信息是否含有数据类型信息。true：支持/false：不支持
     *
     * @return array
     */
    public function dump($data, $type = false) {

        return Controller::dump($data, $type);
    }

    /**
     * 实例化模型类
     *
     * 用于自定义业务逻辑时,实例化其它的模型类。
     *
     * @access public
     *
     * @param string $modelName 所要实例化的模型类的名称
     *
     * @return object
     */
    public function model($modelName) {

        //参数分析
        if (!$modelName) {
            return false;
        }

        return Controller::model($modelName);
    }

    /**
     * 静态获取配置文件的内容
     *
     * 注：此配置文件非数据库连接配置文件，而是其它用途的配置文件。详细信息参见Controller Class中的类方法getConfig()。
     *
     * @access public
     *
     * @param string $fileName 配置文件的名称。注：不含有“.php”后缀
     *
     * @return array
     */
    public function getConfig($fileName) {

        //参数分析
        if (!$fileName) {
            return false;
        }

        return Configure::getConfig($fileName);
    }

    /**
     * 获取当前数据库连接的实例化对象
     *
     * 使用本函数(类方法），可以实现对原生PDO所提供的函数的调用。
     *
     * @access public
     *
     * @param boolean $adapter 是否为主数据库。true：主数据库/false：从数据库
     *
     * @return object
     */
    public function getConnection($adapter = true) {

        if (!$adapter) {
            return $this->_slave();
        }

        return $this->_master();
    }

    /**
     * 获取数据表写入时的最新的Insert Id
     *
     * @access public
     * @return integer
     */
    public function lastInsertId() {

        return $this->_master->lastInsertId();
    }

    /**
     * 事务处理：开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        return $this->_master->startTrans();
    }

    /**
     * 事务处理：提交事务处理
     *
     * @access public
     * @return boolean
     */
    public function commit() {

        return $this->_master->commit();
    }

    /**
     * 事务处理：事务回滚
     *
     * @access public
     * @return boolean
     */
    public function rollback() {

        return $this->_master->rollback();
    }

    /**
     * 执行SQL语句
     *
     * 注：本方法用于无需返回信息的操作。如：更改、删除、添加数据信息(即：用于执行非查询SQL语句)
     *
     * @access public
     *
     * @param string $sql 所要执行的SQL语句
     * @param array $params 待转义的数据。注：本参数支持字符串及数组，如果待转义的数据量在两个或两个以上请使用数组
     *
     * @return boolean
     */
    public function execute($sql, $params = null) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //转义数据表前缀
        $sql = str_replace('#__', $this->_prefix, $sql);

        return $this->_master()->execute($sql, $params);
    }

    /**
     * 执行SQL语句
     *
     * 注：用于执行查询性的SQL语句（需要数据返回的情况）。
     *
     * @access public
     *
     * @param string $sql 所要执行的SQL语句
     * @param array $params 待转义的数据。注：本参数支持字符串及数组，如果待转义的数据量在两个或两个以上请使用数组
     *
     * @return boolean
     */
    public function query($sql, $params = null) {

        //参数分析
        if (!$sql) {
            return false;
        }

        //转义数据表前缀
        $sql = str_replace('#__', $this->_prefix, $sql);

        return $this->_slave()->query($sql, $params);
    }

    /**
     * 获取查询信息的全部数据
     *
     * 注：本函数（类方法）需与query()组合使用。
     *
     * @access public
     *
     * @param string $model 返回数据的索引类型：字段型/数据型 等。默认：字段型
     *
     * @return array
     */
    public function fetchAll($model = 'PDO::FETCH_ASSOC') {

        //参数分析
        if (!$model) {
            return false;
        }

        return $this->_slave()->fetchAll($model);
    }

    /**
     * 数据表写入操作
     *
     * @access public
     *
     * @param array $data 所要写入的数据内容。注：数据必须为数组
     * @param boolean $returnId 是否返回数据为:last insert id
     *
     * @return mixed
     */
    public function insert($data, $returnId = false) {

        //参数分析
        if (!$data || !is_array($data)) {
            return false;
        }

        //获取当前的数据表名
        $tableName   = $this->getTableName();

        //数据过滤
        $insertArray = $this->_filterFields($data);
        if (!$insertArray) {
            return false;
        }

        //清空不必要的内存占用
        unset($data);

        return $this->_master()->insert($tableName, $insertArray, $returnId);
    }

    /**
     * 数据表数据替换操作
     *
     * @access public
     *
     * @param array $data 所要替换的数据内容。注：数据必须为数组
     *
     * @return boolean
     */
    public function replace($data) {

        //参数分析
        if (!$data || !is_array($data)) {
            return false;
        }

        //获取当前的数据表名
        $tableName   = $this->getTableName();

        //数据过滤
        $replaceArray = $this->_filterFields($data);
        if (!$replaceArray) {
            return false;
        }

        //清空不必要的内存占用
        unset($data);

        return $this->_master()->replace($tableName, $replaceArray);
    }

    /**
     * 数据表更新操作
     *
     * @access public
     *
     * @param array $data 所要更改的数据内容
     * @param mixed $where 更改数据所须的条件
     * @param mixed $value 待转义的参数值
     *
     * @return boolean
     */
    public function update($data, $where = null, $value = null) {

        //参数分析
        if (!is_array($data) || !$data) {
            return false;
        }

        //分析执行条件
        $condition   = $this->_parseCondition($where, $value);
        if (!$condition['where']) {
            return false;
        }
        $condition['where'] = ltrim($condition['where'], 'WHERE ');

        //获取当前的数据表名
        $tableName   = $this->getTableName();

        //数据过滤
        $updateArray = $this->_filterFields($data);

        //清空不必要的内存占用
        unset($data);

        return $this->_master()->update($tableName, $updateArray, $condition['where'], $condition['value']);
    }

    /**
     * 数据表删除操作
     *
     * @access public
     *
     * @param mixed $where 删除数据所需的SQL条件
     * @param mixed $value 待转义的参数值
     *
     * @return boolean
     */
    public function delete($where = null, $value = null) {

        //分析执行条件
        $condition = $this->_parseCondition($where, $value);
        if (!$condition['where']) {
            return false;
        }
        $condition['where'] = ltrim($condition['where'], 'WHERE ');

        //获取当前的数据表名
        $tableName = $this->getTableName();

        return $this->_master()->delete($tableName, $condition['where'], $condition['value']);
    }

    /**
     * 主键查询：获取一行主键查询的数据
     *
     * 注：默认主键为数据表的物理主键
     *
     * @access public
     *
     * @param mixed $id 所要查询的主键值。注：本参数可以为数组。当为数组时，返回多行数据
     * @param array $fields 返回数据的有效字段(数据表字段)
     *
     * @return array
     */
    public function find($id, $fields = null) {

        //参数分析
        if (!$id) {
            return false;
        }

        //分析字段信息
        $fields = $this->_parseFields($fields);

        //获取当前数据表的名称及主键信息
        $tableName  = $this->getTableName();
        $primaryKey = $this->_getPrimaryKey();

        $sql = "SELECT {$fields} FROM {$tableName} WHERE {$primaryKey}";

        if (is_array($id)) {
            $id    = array_map(array($this, 'quoteInto'), $id);
            $sql  .= " IN (" . implode(',', $id) . ")";
            $myRow = $this->_slave()->getAll($sql);
        } else {
            $sql  .= " = " . $this->quoteInto($id);
            $myRow = $this->_slave()->getOne($sql);
        }

        return $myRow;
    }

    /**
     * 主键查询：获取数据表的全部数据信息
     *
     * 以主键为中心排序，获取数据表全部数据信息。注:如果数据表数据量较大时，慎用此函数（类方法），以免数据表数据量过大，造成数据库服务器内存溢出,甚至服务器宕机
     *
     * @access public
     *
     * @param array $fields 返回的数据表字段,默认为全部.即SELECT * FROM tableName
     * @param boolean $orderAsc 数据排序,若为true时为ASC,为false时为DESC, 默认为ASC
     * @param integer $limitStart limit启起ID
     * @param integer $listNum 显示的行数
     *
     * @return array
     */
    public function findAll($fields = null, $orderAsc = true, $limitStart = null, $listNum = null) {

        //分析数据表字段
        $fields = $this->_parseFields($fields);

        //获取当前 的数据表名及主键名
        $tableName  = $this->getTableName();
        $primaryKey =$this->_getPrimaryKey();

        //分析SQL语句limit片段
        $limitString = $this->_parseLimit($limitStart, $listNum);

        $sql = "SELECT {$fields} FROM {$tableName} ORDER BY {$primaryKey} " . (($orderAsc) ? "ASC" : "DESC") . " {$limitString}";

        return $this->_slave()->getAll($sql);
    }

    /**
     * 获取查询数据的单选数据
     *
     * 根据一个查询条件，获取一行数据，返回数据为数组型，索引为数据表字段名
     *
     * @access public
     *
     * @param mixed $where 查询条件
     * @param mixed $value 待转义的数值
     * @param mixed $fields 返回数据的数据表的有效字段，默认为全部字段。
     *
     * @return array
     */
    public function getOne($where = null, $value = null, $fields = null) {

        //分析查询条件
        $condition = $this->_parseCondition($where, $value);
        if (!$condition['where']) {
            return false;
        }

        //分析所要查询的字段
        $fields    = $this->_parseFields($fields);

        //获取当前的数据表
        $tableName = $this->getTableName();

        $sql = "SELECT {$fields} FROM {$tableName} {$condition['where']}";

        return $this->_slave()->getOne($sql, $condition['value']);
    }

    /**
     * 获取查询数据的全部数据
     *
     * 根据一个查询条件，获取多行数据。并且支持数据排序，及分页的内容显示
     *
     * @access public
     *
     * @param mixed $where 查询条件
     * @param mixed $value 待转义的数值
     * @param mixed $fields 返回数据的数据表字段，默认为全部字段。注：本参数推荐使用数组
     * @param mixed $orderDesc 排序条件
     * @param integer $limitStart limit查询的启起ID
     * @param integer $listNum 数据显示的行数
     *
     * @return array
     */
    public function getAll($where = null, $value = null, $fields = null, $orderDesc = null, $limitStart = null, $listNum = null) {

        //分析查询条件
        $condition   = $this->_parseCondition($where, $value);
        if (!$condition['where']) {
            return false;
        }

        //获取当前的数据表
        $tableName   = $this->getTableName();

        //分析所要查询的字段
        $fields      = $this->_parseFields($fields);

        //组装SQL语句
        $sql = "SELECT {$fields} FROM {$tableName} {$condition['where']}";

        //分析数据的排序
        $orderString = $this->_parseOrder($orderDesc);
        if ($orderString) {
            $sql .= ' ' . $orderString;
        }

        //分析数据的显示行数
        $limitString = $this->_parseLimit($limitStart, $listNum);
        if ($limitString) {
            $sql .= ' ' . $limitString;
        }

        return $this->_slave()->getAll($sql, $condition['value']);
    }

    /**
     * 字符串转义函数
     *
     * SQL语句指令安全过滤，用于字符转义
     *
     * @access public
     *
     * @param mixed $value 所要转义的字符或字符串。注：参数支持数组
     *
     * @return mixed
     */
    public function quoteInto($value = null) {

        return $this->_master()->escape($value);
    }

    /**
     * 过虑数据表字段信息
     *
     * 用于insert()、update()里的字段信息进行过虑，删除掉非法的字段信息。
     *
     * @access protected
     *
     * @param array $data 待过滤的含字段信息的数据。注：本参数为数组
     *
     * @return array
     */
    protected function _filterFields($data) {

        //参数分析
        if (!$data || !is_array($data)) {
            return false;
        }

        //获取数据表字段
        $tableFields = $this->_getTableFields();

        $filteredArray  = array();
        foreach ($data as $key=>$value) {
            if(in_array($key, $tableFields)) {
                $filteredArray[$key] = $value;
            }
        }

        return $filteredArray;
    }

    /**
     * 分析数据表字段信息
     *
     * @access protected
     *
     * @param array $fields 数据表字段信息。本参数可为数组
     *
     * @return string
     */
    protected function _parseFields($fields = null) {

        //当参数为空时
        if (!$fields) {
            if (isset($this->_parts['fields']) && $this->_parts['fields']) {
                $fields = $this->_parts['fields'];
                unset($this->_parts['fields']);
            } else {
                $fields = '*';
            }

            return $fields;
        } else {
            //必免重复数据或当前的数据对后面的代码产生负面影响所以将其清掉
            if (isset($this->_parts['fields'])) {
                unset($this->_parts['fields']);
            }
        }

        //当参数为数组时
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        return $fields;
    }

    /**
     * 分析SQL语句的条件语句
     *
     * @access protected
     *
     * @param mixed $where Where的条件
     * @param mixed $value 待转义的数值
     *
     * @return string
     */
    protected function _parseCondition($where = null, $value = null) {

        $conditionArray = array('where'=>null, 'value'=>null);

        if (!$where) {
            if (isset($this->_parts['where']) && $this->_parts['where']) {
                $conditionArray['where'] = $this->_parts['where'];
                unset($this->_parts['where']);
            }

            if (isset($this->_parts['whereValue']) && $this->_parts['whereValue']) {
                $conditionArray['value'] = $this->_parts['whereValue'];
                unset($this->_parts['whereValue']);
            }

            return $conditionArray;
        } else{
            //为避免当前的数据的负面影响，所以将数据清掉
            if (isset($this->_parts['where'])) {
                unset($this->_parts['where']);
            }

            if (isset($this->_parts['whereValue'])) {
                unset($this->_parts['whereValue']);
            }
        }

        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        $conditionArray['where'] = 'WHERE ' . $where;

        if (!is_null($value)) {
            if (!is_array($value)) {
                $value = array($value);
            }
            $conditionArray['value'] = $value;
        }

        return $conditionArray;
    }

    /**
     * 分析SQL语句的排序
     *
     * @access protected
     *
     * @param mixed $orderDesc Order by 排序
     *
     * @return string
     */
    protected function _parseOrder($orderDesc = null) {

        //参数分析
        if (!$orderDesc) {
            if (isset($this->_parts['order']) && $this->_parts['order']) {
                $orderDesc = $this->_parts['order'];
                unset($this->_parts['order']);
            }

            return $orderDesc;
        } else {
            //清除相关的数据，避免当前数据对以后代码产生负面影响
            if (isset($this->_parts['order'])) {
                unset($this->_parts['order']);
            }
        }

        if (is_array($orderDesc)) {
            $orderDesc = implode(',', $orderDesc);
        }

        return 'ORDER BY ' . $orderDesc;
    }

    /**
     * 分析SQL语句的limit语句
     *
     * @access protected
     *
     * @param integer $startId 启始id。注：参数为整形
     * @param integer $listNum 显示的行数
     *
     * @return string
     */
    protected function _parseLimit($startId = null, $listNum = null) {

        $limitString = '';

        //参数分析
        if (is_null($startId)) {
            if (isset($this->_parts['limit']) && $this->_parts['limit']) {
                $limitString = $this->_parts['limit'];
                unset($this->_parts['limit']);
            }

            return $limitString;
        } else {
            if (isset($this->_parts['limit'])) {
                unset($this->_parts['limit']);
            }
        }

        $limitString = "LIMIT " . (($listNum) ? "{$startId},{$listNum}" : $startId);

        return $limitString;
    }

    /**
     * 根据查询函数获取数据
     *
     * @access public
     *
     * @param string $funName 查询函数名称
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    protected function _getValueByFunction($funName, $fieldName = null, $where = null, $value = null) {

        //参数分析
        if (!$funName) {
            return false;
        }
        $funName = strtoupper($funName);

        //分析字段信息
        $fields = $this->_parseFields($fieldName);
        $pos    = strpos($fields, ',');
        if ($pos !== false) {
            $fields = trim(substr($fields, 0, $pos));
        }
        //当字段信息为空时，默认为当前的主键
        if ($fields == '*') {
            $fields = $this->_getPrimaryKey();
        }

        //分析判断条件
        $condition  = $this->_parseCondition($where, $value);

        //获取当前的数据表名
        $tableName  = $this->getTableName();

        $sql = "SELECT {$funName}({$fields}) AS valueName  FROM {$tableName} {$condition['where']}";

        $myRow = $this->_slave()->getOne($sql, $condition['value']);

        return (!$myRow) ? 0 : $myRow['valueName'];
    }

    /**
     * 获取查询信息的数据总行数
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    public function count($fieldName = null, $where = null, $value = null) {

        return $this->_getValueByFunction('count', $fieldName, $where, $value);
    }

    /**
     * 获取查询信息的某数据表字段的唯一值的数据
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return array
     */
    public function distinct($fieldName = null, $where = null, $value = null) {

        //分析字段信息
        $fields = $this->_parseFields($fieldName);
        $pos    = strpos($fields, ',');
        if ($pos !== false) {
            $fields = trim(substr($fields, 0, $pos));
        }
        //当字段信息为空时，默认为当前的主键
        if ($fields == '*') {
            $fields = $this->_getPrimaryKey();
        }

        //分析判断条件
        $condition  = $this->_parseCondition($where, $value);

        //获取当前的数据表名
        $tableName  = $this->getTableName();

        $sql = "SELECT DISTINCT {$fields} FROM {$tableName} {$condition['where']}";

        return $this->_slave()->getAll($sql, $condition['value']);
    }

    /**
     * 获取查询信息某数据表字段的最大值
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    public function max($fieldName = null, $where = null, $value = null) {

        return $this->_getValueByFunction('max', $fieldName, $where, $value);
    }

    /**
     * 获取查询信息某数据表字段的最小值
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    public function min($fieldName = null, $where = null, $value = null) {

        return $this->_getValueByFunction('min', $fieldName, $where, $value);
    }

    /**
     * 获取查询信息某数据表字段的数据和
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    public function sum($fieldName = null, $where = null, $value = null) {

        return $this->_getValueByFunction('sum', $fieldName, $where, $value);
    }

    /**
     * 获取查询信息某数据表字段的数据的平均值
     *
     * @access public
     *
     * @param string $fieldName 所要查询字段名称
     * @param mixed $where 查询条件
     * @param mixed $value 数值
     *
     * @return integer
     */
    public function avg($fieldName = null, $where = null, $value = null) {

        return $this->_getValueByFunction('avg', $fieldName, $where, $value);
    }

    /**
     * 创建SQL语句组装实例化对象
     *
     * @access public
     * @return object
     */
    public function createCommand() {

        return DbCommand::getInstance($this);
    }

    /**
     * 组装SQL语句的WHERE语句
     *
     * 用于getOne()、getAll()等类方法的条件查询。
     *
     * @access public
     *
     * @param mixed $where Where的条件
     * @param mixed $value 待转义的数值
     *
     * @return object
     */
    public function where($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }

        //分析参数条件，当参数为数组时
        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        $this->_parts['where'] = (isset($this->_parts['where']) && $this->_parts['where']) ? $this->_parts['where'] . ' AND ' . $where : ' WHERE ' . $where;

        //当$model->where('name=?', 'tommy');操作时,即：需要字符串转义
        if (!is_null($value)) {
            if (!is_array($value)) {
                $value = func_get_args();
                array_shift($value);
            }
            //当已执行过$this->where();语句时
            if(isset($this->_parts['whereValue']) && $this->_parts['whereValue']) {
                $this->_parts['whereValue'] = array_merge($this->_parts['whereValue'], $value);
            } else {
                $this->_parts['whereValue'] = $value;
            }
        }

        return $this;
    }

    /**
     * 组装SQL语句排序(ORDER BY)语句
     *
     * 用于findAll()、getAll()的数据排行
     *
     * @access public
     *
     * @param mixed $orderDesc 排序条件。注：本参数支持数组
     *
     * @return mixed
     */
    public function order($orderDesc) {

        //参数分析
        if (!$orderDesc) {
            return false;
        }

        if (is_array($orderDesc)) {
            $orderDesc = implode(',', $orderDesc);
        }

        $this->_parts['order'] = (isset($this->_parts['order']) && $this->_parts['order']) ? $this->_parts['order'] . ', ' . $orderDesc : ' ORDER BY ' . $orderDesc;

        return $this;
    }

    /**
     * 组装SQL语句的查询字段
     *
     * @access public
     *
     * @param mixed $fieldName 所要查询的数据表字段信息
     *
     * @return mixed
     */
    public function fields($fieldName) {

        //参数分析
        if (!$fieldName) {
            return false;
        }

        if (!is_array($fieldName)) {
            $fieldName = func_get_args();
        }

        $fieldName = implode(',', $fieldName);

        $this->_parts['fields'] = $fieldName;

        return $this;
    }

    /**
     * 组装SQL语句LIMIT语句
     *
     * limit(10,20)用于处理LIMIT 10, 20之类的SQL语句部分
     *
     * @access public
     *
     * @param integer $limitStart 启始id。注：参数为整形
     * @param integer $listNum 显示的行数
     *
     * @return object
     */
    public function limit($limitStart, $listNum = null) {

        //参数分析
        $limitStart = (int)$limitStart;
        $listNum    = (int)$listNum;

        $limitStr   = ($listNum) ? $limitStart . ', ' . $listNum : $limitStart;

        $this->_parts['limit'] = ' LIMIT ' . $limitStr;

        return $this;
    }

    /**
     * 组装SQL语句的LIMIT语句
     *
     * 注:本方法与$this-&gt;limit()功能相类，区别在于:本方法便于分页,参数不同
     *
     * @access public
     *
     * @param integer $page 当前的页数
     * @param integer $listNum 每页显示的数据行数
     *
     * @return object
     */
    public function pageLimit($page, $listNum) {

        //参数分析
        $page    = (int)$page;
        $listNum = (int)$listNum;

        if (!$listNum) {
            return false;
        }
        $page    = ($page < 1) ? 1 : $page;

        $startId = (int)$listNum * ($page - 1);

        return $this->limit($startId, $listNum);
    }

    /**
     * 分析配置文件中数据库连接的相关内容
     *
     * 对数据库配置文件进行分析,以明确主从分离信息
     *
     * @access protected
     * @return array
     */
    protected function _parseConfig() {

        //获取数据库连接参数信息
        $params = $this->setConfig();

        if (!$params || !is_array($params)) {
            Controller::halt('The config data of database connect is not correct!', 'Normal');
        }

        //获取数据表前缀，默认为空
        $this->_prefix     = (isset($params['prefix']) && $params['prefix']) ? trim($params['prefix']) : '';

        //分析默认参数，默认编码为:utf-8
        $params['charset'] = (isset($params['charset']) && $params['charset']) ? trim($params['charset']) : 'utf8';

        //分析主数据库连接参数
        $configParam                          = array();
        if (isset($params['master']) && $params['master']) {
            $configParam['master']            = $params['master'];
            $configParam['master']['charset'] = $params['charset'];
        } else {
            $configParam['master']            = $params;
        }

        //分析从数据库连接参数
        if (isset($params['slave']) && $params['slave']) {
            //当从数据库只有一组数据时(Only One)。
            if (isset($params['slave']['dsn'])) {
                $configParam['slave'] = $params['slave'];
            } else {
                //当从数据库有多组时，随机选择一组进行连接
                $randIndex            = array_rand($params['slave']);
                $configParam['slave'] = $params['slave'][$randIndex];
            }
            $configParam['slave']['charset'] = $params['charset'];
        } else {
            $this->_singleton     = true;
            $configParam['slave'] = $configParam['master'];
        }

        //将数据库的用户名及密码及时从内存中注销，提高程序安全性
        unset($params);

        return $configParam;
    }

    /**
     * 实例化主数据库(Master MySQL Adapter)
     *
     * @access protected
     * @return object
     */
    protected function _master() {

        if ($this->_master) {
            return $this->_master;
        }

        $this->_master = new DbPdo($this->_config['master']);

        if ($this->_singleton) {
            $this->_slave = $this->_master;
        }

        return $this->_master;
    }

    /**
     * 实例化从数据库(Slave Adapter)
     *
     * @access public
     * @return object
     */
    public function _slave() {

        if ($this->_slave) {
            return $this->_slave;
        }

        $this->_slave = new DbPdo($this->_config['slave']);

        if ($this->_singleton) {
            $this->_master = $this->_slave;
        }

        return $this->_slave;
    }

    /**
     * 设置当前模型的错误信息
     *
     * @access protected
     *
     * @param string $message 所要设置的错误信息
     *
     * @return boolean
     */
    protected function setErrorInfo($message) {

         //参数分析
         if (!$message) {
             return false;
         }

         //对信息进行转义
         $this->_errorInfo = trim($message);

         return true;
    }

    /**
     * 获取当前模型的错误信息
     *
     * @access public
     * @return string
     */
    public function getErrorInfo() {

        return $this->_errorInfo;
    }

    /**
     * 自动变量设置
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的赋值 。
     *
     * @access public
     *
     * @param string $name 属性名
     * @param mixed $value 属性值
     *
     * @return void
     */
    public function __set($name, $value) {

        //设置当前的数据表
        if ($name == 'tableName') {
            $this->_tableName = $this->quoteInto($value);
        }

        return true;
    }

    /**
     * 自动变量获取
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的获取。
     *
     * @access public
     *
     * @param string $name 属性名
     *
     * @return mixed
     */
    public function __get($name) {

        //过滤类中已有的变量
        $protectedParams = array(
            '_tableName',
            '_tableField',
            '_primaryKey',
            '_prefix',
            '_errorInfo',
            '_config',
            '_parts',
            '_master',
            '_slave',
            '_singleton',
            '_instance',
        );
        if (in_array($name, $protectedParams)) {
            return false;
        }

        //设置数据表名称
        $name      = ltrim(strtolower(preg_replace('#[A-Z]#', '_\\0', $name)), '_');
        $tableName = (!$this->_prefix) ? $name : $this->_prefix . $name;

        $tableList = $this->_master()->getTableList();

        if (in_array($tableName, $tableList)) {
            $this->setTableName($name);
            return $this;
        }

        return true;
    }

    /**
     * 类方法自动调用引导
     *
     * 用于处理类外调用本类不存在的方法时的信息提示
     *
     * @access public
     *
     * @param string $method 类方法名称
     * @param array $args 所调用类方法的参数
     *
     * @return mixed
     */
    public function __call($method, $args) {

        //分析 findByxx()的类方法调用
        if (strpos($method, 'findBy')!== false) {
            //分析所要查询的数据表字段名
            $fieldsName = substr($method, 6);
            $fieldsName = ltrim(strtolower(preg_replace('#[A-Z]#', '_\\0', $fieldsName)), '_');

            array_unshift($args, "{$fieldsName}=?");

            return call_user_func_array(array($this, 'getOne'), $args);
        }

        //分析 findAllByxx()的类方法调用
        if (strpos($method, 'findAllBy')!== false) {
            //分析所要查询的数据表字段名
            $fieldsName = substr($method, 9);
            $fieldsName = ltrim(strtolower(preg_replace('#[A-Z]#', '_\\0', $fieldsName)), '_');

            array_unshift($args, "{$fieldsName}=?");

            return call_user_func_array(array($this, 'getAll'), $args);
        }

        return Controller::halt("The method: {$method}() is not found in Model class!", 'Normal');
    }

    /**
     * 回调类方法：自定义当前模型（Model）的数据库连接参数
     *
     * @access protected
     * @return array
     */
    protected function setConfig() {
        return Configure::get('db');
    }

    /**
     * 回调类方法：前函数(类方法)
     *
     * 用于自定义实例化当前模型时所执行的程序
     *
     * @access protected
     * @return boolean
     */
    protected function init() {
        return true;
    }

    /**
     * 析构方法（函数）
     *
     * 当本类程序运行结束后，用于&quot;打扫战场&quot;，如：清空无效的内存占用等
     *
     * @access public
     * @return boolean
     */
    public function __destruct() {

        $this->_master = null;

        $this->_slave  = null;

        $this->_parts  = array();
    }

    /**
     * 单例模式实例化当前模型类
     *
     * @access public
     * @return object
     */
    public static function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
