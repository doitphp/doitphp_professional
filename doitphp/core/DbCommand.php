<?php
/**
 * 常用的查询SQL语句的组装类
 *
 * 主要用来完成复杂的查询SQL语句的组装，特别适用于跨表数据库查询操作。
 *
 * @author tommy <tommy@doitphp.com>
 * @link http://www.doitphp.com
 * @copyright Copyright (C) 2015 www.doitphp.com All rights reserved.
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: DbCommand.php 1.0 2012-12-16 00:18:24Z tommy <tommy@doitphp.com> $
 * @package core
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class DbCommand {

     /**
     * 数据库连接实例化对象
     *
     * @var object
     */
    protected $_dbLink = null;

    /**
     * 数据表名的前缀
     *
     * @var string
     */
    protected $_prefix = null;

    /**
     * SQL语句容器，用于存放SQL语句，为SQL语句组装函数提供SQL语句片段的存放空间。
     *
     * @var array
     */
    protected $_parts = array();

    /**
     * 单例模式实现化对象
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * 构造方法
     *
     * 用于初始化程序运行环境，或对基本变量进行赋值
     *
     * @access public
     *
     * @param object $modelObjec Model的实例化对象名
     *
     * @return boolean
     */
    public function __construct($modelObject) {

        $this->_dbLink = $modelObject->_slave();
        $this->_prefix = $modelObject->_prefix;

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
     * @param array $args 参数值。注：本参数为数组
     *
     * @return mixed
     */
    public function __call($method, $args) {

        //参数分析
        $method = strtolower($method);

        if (in_array($method, array('count', 'max', 'min', 'sum', 'avg'))) {
            array_unshift($args, $method);
            return call_user_func_array(array($this, '_selectByFunction'), $args);
        }

        return Controller::halt("The method: {$method}() is not found in DbCommand class!", 'Normal');
    }

    /**
     * 输出类的实例化对象
     *
     * 直接调用函数，输出内容。
     *
     * @access public
     * @return string
     */
    public function __toString() {

        return $this->getSql();
    }

    /**
     * 组装SQL语句中的FROM语句
     *
     * 用于处理 SELECT fieldsName FROM tableName之类的SQL语句部分
     *
     * @access public
     *
     * @param mixed $tableName 所要查询的数据表名。注：本参数支持数组
     * @param mixed $fields 所要查询的数据表字段。默认数据表全部字段
     *
     * @return object
     */
    public function from($tableName, $fields = null) {

        //参数分析
        if (!$tableName) {
            return false;
        }

        if (is_array($tableName)) {
            $tableArray = array();
            foreach ($tableName as $key=>$value) {
                $tableArray[] = is_int($key) ? $this->_parseTableName($value) : $this->_parseTableName($value) . ' AS ' . $key;
            }
            $tableName  = implode(',', $tableName);
            //清空不必要的内存占用
            unset($tableArray);
        } else {
            $tableName = $this->_parseTableName($tableName);
        }

        //对数据表字段的分析
        $fields = $this->_parseFields($fields);

        //组装SQL中的FROM片段
        $this->_parts['from'] = "SELECT {$fields} FROM {$tableName}";

        return $this;
    }

    /**
     * 组装SQL语句的WHERE语句
     *
     * 用于处理 WHERE id=3721 诸如此类的SQL语句部分
     *
     * @access public
     *
     * @param mixed $where WHERE的条件内容
     * @param mixed $value 待转义的数值
     *
     * @return object
     */
    public function where($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }
        if (!is_null($value) && !is_array($value)) {
            $value = func_get_args();
            array_shift($value);
        }

        return $this->_where($where, $value, true);
    }

    /**
     * 组装SQL语句的ORWHERE语句
     *
     * 用于处理 ORWHERE id=2011 诸如此类的SQL语句部分
     *
     * @access public
     *
     * @param mixed $where WHERE的条件内容
     * @param mixed $value 待转义的数值
     *
     * @return object
     */
    public function orwhere($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }
        if (!is_null($value) && !is_array($value)) {
            $value = func_get_args();
            array_shift($value);
        }

        return $this->_where($where, $value, false);
    }

    /**
     * 组装SQL语句中WHERE及ORWHERE语句
     *
     * 本方法用来为方法where()及orwhere()提供&quot;配件&quot;
     *
     * @access protected
     *
     * @param mixed $where WHERE的条件内容
     * @param mixed $value 待转义的数值
     * @param boolean $isWhere 是否为WHERE语句。true:WHERE/false:ORWHERE
     *
     * @return string
     */
    protected function _where($where, $value = null, $isWhere = true) {

        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        if (!is_null($value)) {
            $where = $this->_prepare($where, $value);
        }

        if ($isWhere) {
            $this->_parts['where'] = (isset($this->_parts['where']) && $this->_parts['where']) ? "{$this->_parts['where']} AND {$where}" : "WHERE {$where}";
        } else {
            $this->_parts['orwhere'] = (isset($this->_parts['orwhere']) && $this->_parts['orwhere']) ? "{$this->_parts['orwhere']} AND {$where}" : "OR {$where}";
        }

        return $this;
    }

    /**
     * SQL语句的转义
     *
     * 完成SQL语句中关于数据值字符串的转义
     *
     * @access protected
     *
     * @param string $sql SQL语句
     * @param mixed  $value 待转义的数值
     *
     * @return string
     */
    protected function _prepare($sql, $value) {

        $sql   = str_replace('?', '%s', $sql);
        $value = $this->_dbLink->escape($value);

        return vsprintf($sql, $value);
    }

    /**
     * 组装SQL语句排序(ORDER BY)语句
     *
     * 用于处理 ORDER BY post_id ASC 诸如之类的SQL语句部分
     *
     * @access public
     *
     * @param mixed $orderDesc 排序条件
     *
     * @return object
     */
    public function order($orderDesc) {

        //参数分析
        if (!$orderDesc) {
            return false;
        }

        if (is_array($orderDesc)) {
            $orderDesc = implode(',', $orderDesc);
        }

        $this->_parts['order'] = (isset($this->_parts['order']) && $this->_parts['order']) ? "{$this->_parts['order']}, {$orderDesc}" : "ORDER BY {$orderDesc}";

        return $this;
    }

    /**
     * 组装SQL的GROUP BY语句
     *
     * 用于处理SQL语句中GROUP BY语句部分
     *
     * @access public
     *
     * @param string $fieldsName 所要排序的数据表字段名称
     *
     * @return object
     */
    public function group($fieldsName) {

        //参数分析
        if (!$fieldsName) {
            return false;
        }

        if (is_array($fieldsName)) {
            $fieldsName = implode(',', $fieldsName);
        }

        $this->_parts['group'] = (isset($this->_parts['group']) && $this->_parts['group']) ? "{$this->_parts['group']}, {$fieldsName}" : "GROUP BY {$fieldsName}";

        return $this;
    }

    /**
     * 组装SQL的HAVING语句
     *
     * 用于处理 having id=2011 诸如此类的SQL语句部分
     *
     * @access public
     *
     * @param string|array $where 条件语句
     * @param string $value    数据表某字段的数据值
     *
     * @return object
     */
    public function having($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }
        if (!is_null($value) && !is_array($value)) {
            $value = func_get_args();
            array_shift($value);
        }

        return $this->_having($where, $value, true);
    }

    /**
     * 组装SQL的ORHAVING语句
     *
     * 用于处理or having id=2011 诸如此类的SQL语句部分
     *
     * @access public
     *
     * @param string|array $where 条件语句
     * @param string $value    数据表某字段的数据值
     *
     * @return object
     */
    public function orhaving($where, $value = null) {

        //参数分析
        if (!$where) {
            return false;
        }
        if (!is_null($value) && !is_array($value)) {
            $value = func_get_args();
            array_shift($value);
        }

        return $this->_having($where, $value, false);
    }

    /**
     * 组装SQL的HAVING,ORHAVING语句
     *
     * 为having()及orhaving()方法的执行提供'配件'
     *
     * @access protected
     *
     * @param mixed $where 条件语句
     * @param string $value    数据表某字段的数据值
     * @param boolean $isHaving 当参数为true时，处理having()，当为false时，则为orhaving()
     *
     * @return string
     */
    protected function _having($where, $value = null, $isHaving = true) {

        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        if (!is_null($value)) {
            $where = $this->_prepare($where, $value);
        }

        if ($isHaving) {
            $this->_parts['having'] = (isset($this->_parts['having']) && $this->_parts['having']) ? "{$this->_parts['having']} AND {$where}" : "HAVING {$where}";
        } else {
            $this->_parts['orhaving'] = (isset($this->_parts['orhaving']) && $this->_parts['orhaving']) ? "{$this->_parts['orhaving']} AND {$where}" : "OR {$where}";
        }

        return $this;
    }

    /**
     * 组装SQL语句中LEFT JOIN语句
     *
     * jion('表名2', '关系语句')相当于SQL语句中LEFT JOIN 表2 ON 关系SQL语句部分
     *
     * @access public
     *
     * @param string $tableName 数据表名
     * @param string $where join条件。注：不支持数组
     *
     * @return object
     */
    public function join($tableName, $where) {

        //参数分析
        if (!$tableName || !$where) {
            return false;
        }

        if (is_array($tableName)) {
            $tableString = '';
            foreach ($tableName as $key=>$value) {
                $tableString = is_int($key) ? $this->_parseTableName($value) : $this->_parseTableName($value) . ' AS ' . $key;
                //数据处理，只处理一个数组元素
                break;
            }
            $tableName = $tableString;
        } else {
            $tableName = $this->_parseTableName($tableName);
        }

        $this->_parts['join'] = "LEFT JOIN {$tableString} ON {$where}";

        return $this;
    }

    /**
     * 组装SQL语句LIMIT语句
     *
     * limit(10,20)用于处理LIMIT 10, 20之类的SQL语句部分
     *
     * @access public
     *
     * @param integer $startId 启始id
     * @param integer $listNum 显示的行数
     *
     * @return object
     */
    public function limit($startId, $listNum) {

        //参数分析
        $startId     = (int)$startId;
        $listNum     = (int)$listNum;

        $limitString = ($listNum) ? "{$startId}, {$listNum}" : $startId;

        $this->_parts['limit'] = ' LIMIT ' . $limitString;

        return $this;
    }

    /**
     * 组装SQL语句的LIMIT语句
     *
     * 注:本方法与$this-&gt;limit()功能相类，区别在于:本方法便于分页,参数不同
     *
     * @access public
     *
     * @param integer $page 当前页数
     * @param integer $listNume 显示的行数
     *
     * @return object
     */
    public function pageLimit($page, $listNume) {

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
     * 执行SQL语句
     *
     * 注：用于执行查询性的SQL语句（需要数据返回的情况）。
     *
     * @access public
     * @return boolean
     */
    public function query() {

        //获取完整的SQL查询语句。
        $sql = $this->getSql();

        return $this->_dbLink->query($sql);
    }

    /**
     * 获取完整的SQL查询语句
     *
     * @access public
     *
     * @return string
     */
    public function getSql() {

        //分析查询数据表的语句
        if (!$this->_parts['from']) {
            return false;
        }

        //组装完整的SQL查询语句
        $partsNameArray = array('from', 'join', 'where', 'orwhere', 'group', 'having', 'orhaving', 'order', 'limit');

        $sql = '';
        foreach ($partsNameArray as $partsName) {
            if (isset($this->_parts[$partsName]) && $this->_parts[$partsName]) {
                $sql .= ' ' . $this->_parts[$partsName];
            }
        }

        return trim($sql);
    }

    /**
     * 获取查询信息中的一行数据
     *
     * 注：本函数(类方法)需与query()组合使用。
     *
     * @access public
     *
     * @param string $model 返回数据的索引类型：字段型/数据型 等。默认：字段型
     *
     * @return array
     */
    public function fetchRow($model = 'PDO::FETCH_ASSOC') {

        return $this->_dbLink->fetchRow($model);
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

        return $this->_dbLink->fetchAll($model);
    }

    /**
     * 重值系统变量
     *
     * @access protected
     * @return boolean
     */
    protected function _reset() {

        $this->_parts = array();

        return true;
    }

    /**
     * 分析所要查询的数据表名称
     *
     * @access protected
     *
     * @param mixed $tableName 所要查询的数据表名
     *
     * @return string
     */
    protected function _parseTableName($tableName) {

        return (!$this->_prefix) ? $tableName : $this->_prefix . trim($tableName);
    }

    /**
     * 分析所要查询的字段信息
     *
     * @access protected
     *
     * @param mixed $fieldsName 所要查询的数据表字段信息
     *
     * @return string
     */
    protected function _parseFields($fieldsName = null) {

        //参数分析
        if (!$fieldsName) {
            return '*';
        }

        if (!is_array($fieldsName)) {
            return trim($fieldsName);
        }

        $fieldsArray = array();
        foreach ($fieldsName as $key=>$value) {
            $fieldsArray[] = is_int($key) ? $value : $value . ' AS ' . $key;
        }

        return implode(',', $fieldsArray);
    }

    /**
     * 组装SQL语句中的FROM语句(查询函数:MAX、MIN、SUM、AVG、COUNT)
     *
     * 用于处理 SELECT fieldsName FROM tableName之类的SQL语句部分
     *
     * @access protected
     *
     * @param string $methodName 调用的查询函数名
     * @param mixed $tableName 所要查询的数据表名。注：本参数支持数组
     * @param mixed $fields 所要查询的数据表字段。默认数据表全部字段
     *
     * @return object
     */
    protected function _selectByFunction($methodName, $tableName, $fields = null) {

        //参数判断
        if (!$methodName || !$tableName) {
            return false;
        }
        $methodName  = strtoupper($methodName);

        //分析字段信息
        $fields = $this->_parseFields($fields);
        $pos = strpos($fields, ',');
        if ($pos !== false) {
            $fields = trim(substr($fields, 0, $pos));
        }

        $fieldsValue = "{$methodName}({$fields})";

        return $this->from($tableName, array('resultNum'=>$fieldsValue));
    }

    /**
     * 组装SQL语句中的FROM语句(查询函数:DISTINCT)
     *
     * 用于处理 SELECT fieldsName FROM tableName之类的SQL语句部分
     *
     * @access public
     *
     * @param mixed $tableName 所要查询的数据表名。注：本参数支持数组
     * @param mixed $fields 所要查询的数据表字段。默认数据表全部字段
     *
     * @return object
     */
    public function distinct($tableName, $fields) {

        //参数判断
        if (!$tableName || !$fields) {
            return false;
        }

        //分析所要查询的数据表字段名
        $fields = $this->_parseFields($fields);
        $pos    = strpos($fields, ',');
        if ($pos !== false) {
            $fields = trim(substr($fields, 0, $pos));
        }

        return $this->from($tableName, "DISTINCT {$fields}");
    }

    /**
     * 析构方法
     *
     * 当本类程序运行结束后，用于&quot;打扫战场&quot;，如：清空无效的内存占用等
     *
     * @access public
     * @return boolean
     */
    public function __destruct() {

        $this->_dbLink = null;
        $this->_reset();

        return true;
    }

    /**
     * 单例模式实例化当前模型类
     *
     * @access public
     *
     * @param object $modelObject 模型层例化对象名称
     *
     * @return object
     */
    public static function getInstance($modelObject) {

        if (self::$_instance === null) {
            self::$_instance = new self($modelObject);
        }

        return self::$_instance;
    }
}