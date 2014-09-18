<?php
/**
 * 无限分类
 *
 * @author DaBing<InitPHP>, tommy
 * @copyright  CopyRight DoitPHP team, initphp team
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Tree.php 2.0 2012-12-29 19:01:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Tree {

    /**
     * 分类的父ID的键名(key)
     *
     * @var integer
     */
    private $_parentId = 'pid';

    /**
     * 分类的ID(key)
     *
     * @var integer
     */
    private $_id = 'id';

    /**
     * 分类名
     *
     * @var string
     */
    private $_name = 'name';

    /**
     * 子分类名
     *
     * @var string
     */
    private $_child = 'child';

    /**
     * 无限级分类树-初始化配置
     *
     * @access public
     *
     * @param  array $config 配置分类的键
     *
     * @return $this
     *
     * @example
     *
     * 法一：
     * $params = array('parentId'=>'pid', 'id' => 'cat_id', 'name' =>'cat_name');
     * $this->config($params );
     *
     * 法二：
     * $params = array('parentId'=>'pid', 'id' => 'cat_id', 'name' =>'cat_name', 'child'=>'node');
     * $this->config($params );
     */
    public function config($params) {

        //parse params
        if (!$params || !is_array($params)) {
            return false;
        }

        $this->_parentId = (isset($params['parentId'])) ? $params['parentId'] : $this->_parentId;
        $this->_id       = (isset($params['id'])) ? $params['id'] : $this->_id;
        $this->_name     = (isset($params['name'])) ? $params['name'] : $this->_name;
        $this->_child    = (isset($params['child'])) ? $params['child'] : $this->_child;

        return $this;
    }

    /**
     * 无限级分类树-获取树
     *
     * 用于下拉框select标签的option内容
     *
     * @access public
     *
     * @param  array $data 树的数组
     * @param  int $parentId 初始化树时候，代表ID下的所有子集
     * @param  int $selectId 选中的ID值
     * @param  string $prefix 前缀
     *
     * @return mixed
     */
    public function getHtmlOption($data, $parentId = 0, $selectId = null, $preFix = '|-') {

        //parse params
        if (!$data || !is_array($data)) {
            return '';
        }

        $string = '';
        foreach ($data as $key => $value) {
            if (isset($value[$this->_parentId]) && $value[$this->_parentId] == $parentId) {
                $string .= '<option value=\'' . $value[$this->_id] . '\'';
                if (!is_null($selectId)) {
                    $string .= ($value[$this->_id] == $selectId) ? ' selected="selected"' : '';
                }
                $string .= '>' . $preFix . $value[$this->_name] . '</option>';
                $string .= $this->getHtmlOption($data, $value[$this->_id], $selectId, '&nbsp;&nbsp;' . $preFix);
            }
        }

        return $string ;
    }

    /**
     * 获取无限分类树
     *
     * @access public
     *
     * @param array $data 数组
     * @param integer $parentId 父ID
     *
     * @return array
     */
    public function getTree($data, $parentId = 0) {

        //parse params
        if (!$data || !is_array($data)) {
            return '';
        }

        //get child tree array
        $childArray = $this->getChild($data, $parentId);
        //当子分类无元素时,结果递归
        if(!sizeof($childArray)) {
            return '';
        }

        $treeArray = array();
        foreach ($childArray as $lines) {
            $treeArray[] = array(
            $this->_id    => $lines[$this->_id],
            $this->_name  => $lines[$this->_name],
            $this->_child => $this->getTree($data, $lines[$this->_id]),
            );
        }

        return $treeArray;
    }

    /**
     * 无限级分类树-获取子类
     *
     * @access public
     *
     * @param array $data 树的数组
     * @param integer $id 父类ID
     *
     * @return mixed
     */
    public function getChild($data, $id) {

        //parse params
        if (!$data || !is_array($data)) {
            return array();
        }

        $tempArray = array();
        foreach ($data as $value) {
            if ($value[$this->_parentId] == $id) {
                $tempArray[] = $value;
            }
        }

        return $tempArray;
    }

    /**
     * 无限级分类树-获取父类
     *
     * @access public
     *
     * @param array $data 树的数组
     * @param int $id 子类ID
     *
     * @return mixed
     */
    public function getParent($data, $id) {

        //parse params
        if (!$data || !is_array($data)) {
            return array();
        }

        $temp = array();
        foreach ($data as $vaule) {
            $temp[$vaule[$this->_id]] = $vaule;
        }

        $parentId = $temp[$id][$this->_parentId];

        return $temp[$parentId];
    }

}