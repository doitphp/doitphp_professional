<?php
/**
 * 购物车管理操作
 *
 * 适用于网购系统购物车的数据管理
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Cart.php 2.0 2012-12-23 00:20:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Cart {

    /**
     * 购物车名称
     *
     * @var string
     */
    protected $_cartName = 'myShoppingCart';

    /**
     * 扩展类cookie实例化对象
     *
     * @var object
     */
    protected $_cookieObj = null;

    /**
     * 购物车cookie存放路径
     *
     * @var string
     */
    protected $_cookiePath = '/';

    /**
     * 购物车cookie生存周期(单位:秒)
     *
     * 默认30天
     * @var integer
     */
    protected $_lifeTime = 2592000;

    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {
        $this->_cookieObj = Doit::singleton('Cookie');
        return true;
    }

    /**
     * 显示购物车内容
     *
     * @access public
     * @return array
     *
     * @example
     * 返回数据类型为:array(array(商品ID, 商品名称, 商品数量, 商品单价, array(其实信息)), array(...));
     */
    public function readCart() {

        //从购物车cookie中读取数据
        $data = $this->_cookieObj->get($this->_cartName);
        if (!$data) {
            return array();
        }

        return $data;
    }

    /**
     * 添加商品
     *
     * @access public
     *
     * @param integer $id 商品ID(唯一)
     * @param string $productName 商品名称
     * @param integer $num 商品数量
     * @param float $price 商品价格(单价)
     * @param array $options 商品其它属性
     *
     * @return boolean
     */
    public function add($id, $productName = null, $num = 1, $price = null, $options = array()) {

        //参数分析
        if (!$id) {
            return false;
        }
        $num = (int)$num;

        $data = $this->readCart();

        //当购物车中没有商品记录时
        if (!$data) {
            $data = array($id=>array($id, $productName, $num, $price, $options));
        } else {
            //当购物车中已存在所要添加的商品时,只进行库存更改操作
            if (isset($data[$id])) {
                $data[$id][2] += $num;
            } else {
                $data[$id] = array($id, $productName, $num, $price, $options);
            }
        }

        $this->_cookieObj->set($this->_cartName, $data, $this->_lifeTime, $this->_cookiePath);

        return true;
    }

    /**
     * 将N多商品进行批处理添加到购物车
     *
     * @access public
     *
     * @param array $data    所要添加的购物车内容
     *
     * @return boolean
     *
     * @example
     *
     * $data = array(
     *     array(101, 'ak47', 25, 10000, array('maker'=>'china')),
     *     array(103, 'mp5', 10, 2300, array('from'=>'usa')),
     *     array(200, 'F-22', 5, 20000000, array('from'=>'usa')),
     * );
     *
     * $cart = new Cart(); 或 $cart = $this->instance('Cart');
     *
     * $cart->insert($data);
     */
    public function insert($data) {

        //参数分析
        if (!$data || !is_array($data)) {
            return false;
        }

        $cartData = $this->readCart();

        foreach ($data as $lines) {
            if (!is_array($lines) || empty($lines[0])) {
                continue;
            }
            if (isset($cartData[$lines[0]])) {
                $cartData[$lines[0]][2] += $lines[2];
            } else {
                $cartData[$lines[0]] = array($lines[0], $lines[1], $lines[2], $lines[3], $lines[4]);
            }
        }

        $this->_cookieObj->set($this->_cartName, $cartData, $this->_lifeTime, $this->_cookiePath);

        return true;
    }

    /**
     * 修改购物车内容
     *
     * 当购物车中已有要更改信息的商品时,将原有的商品信息替换掉,当购物车中没有该商品时将不更改购物车信息,而是直接返回false
     *
     * @access public
     *
     * @param array  $data 将要修改后的内容,注:本参数为一维数组
     *
     * @return boolean
     *
     * @example
     *
     * $data = array(101, 'ak47', 3, 1200, array('maker'=>'usa'));
     *
     * $cart = new Cart();
     * $cart->update($data);
     *
     * 注:参数$data也可以用于 $cart->add($data),区别在于
     * 用于add()时,如果购物车中已有ID为101的商品时,将库存自动增加3
     * 用于update()时,如果购物车中已有ID为101的商品时,将信息更改为当前$data的数据(更改的不只是库存)
     */
    public function update($data) {

        //参数分析
        if(!is_array($data) || empty($data[0])) {
            return false;
        }

        $cartData = $this->readCart();

        //判断将要更改的商品数据是否在购物车中存在
        if (!isset($cartData[$data[0]])) {
            return  false;
        }

        $cartData[$data[0]] = array($data[0], $data[1], $data[2], $data[3], $data[4]);

        $this->_cookieObj->set($this->_cartName, $cartData, $this->_lifeTime, $this->_cookiePath);

        return true;
    }

    /**
     * 删除购物车中的某商品
     *
     * 注:当购物车中没有商品ID为$key的商品时,同样返回true
     *
     * @access public
     *
     * @param integer $key 商品id(唯一标识)
     *
     * @return boolean
     */
    public function delete($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        $cartData = $this->readCart();

        if(!$cartData) {
            return  true;
        }

        if (isset($cartData[$key])) {
            unset($cartData[$key]);
            $this->_cookieObj->set($this->_cartName, $cartData, $this->_lifeTime, $this->_cookiePath);
        }

        return true;
    }

    /**
     * 清空购物车的内容
     *
     * @access public
     * @return boolean
     */
    public function clear() {

        return $this->_cookieObj->delete($this->_cartName);
    }

    /**
     * 获取购物车内的总商种数(商品种类)
     *
     * @access public
     * @return integer
     */
    public function getTotalItems() {

        $cartData = $this->readCart();

        if(!$cartData) {
            $items = 0;
        } else {
            $items = sizeof($cartData);
        }

        return $items;
    }

    /**
     * 获取购物车商品总数
     *
     * @access public
     * @return integer
     */
    public function getTotalNum() {

        $cartData = $this->readCart();

        $totalNum = 0;

        if($cartData) {
            foreach ($cartData as $lines) {
                $totalNum += $cartData[$lines[0]][2];
            }
        }

        return $totalNum;
    }

    /**
     * 获取购物车总金额
     *
     * @access public
     * @return integer
     */
    public function getTotalPrice() {

        $cartData     = $this->readCart();

        $totalPrice   = 0;
        //当购物车中有商品记录时
        if($cartData) {
            foreach ($cartData as $lines) {
                $totalPrice += $cartData[$lines[0]][3];
            }
        }

        return $totalPrice;
    }

    /**
     * 购物车中是存在$key的商品
     *
     * @access public
     *
     * @param mixted $key 商品id
     *
     * @return boolean
     */
    public function issetInCart($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        $cartData = $this->readCart();

        if($cartData && isset($cartData[$key])) {
            return true;
        }

        return false;
    }

    /**
     * 设置购物车名
     *
     * @access public
     *
     * @param sring $cartName    购物车名
     *
     * @return $this
     */
    public function setName($cartName) {

        //参数分析
        if(!$cartName) {
            return false;
        }

        $this->_cartName = trim($cartName);

        return $this;
    }

    /**
     * 设置购物车cookie存放路径
     *
     * @access public
     *
     * @param string $path cookie路径
     *
     * @return $this
     */
    public function setCookiePath($path) {

        //参数分析
        if (!$path) {
            return false;
        }

        $this->_cookiePath = trim($path);

        return $this;
    }

    /**
     * 设置cookie有效周期
     *
     * @access public
     *
     * @param integer $expire Cookie的生存周期
     *
     * @return $this
     */
    public function setCookieExpire($expire) {

        //参数分析
        if (!$expire) {
            return false;
        }

        $this->_lifeTime = (int)$expire;

        return $this;
    }
}