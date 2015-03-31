<?php
/**
 * 验证码图片生成类
 *
 * 生成、显示、验证码
 *
 * @author tommy <tommy@doitphp.com>
 * @copyright Copyright (c) 2010 Tommycode Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Captcha.php 2.0 2015-4-1 1:36:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Captcha {

    /**
     * 验证码图片的背景图片.
     *
     * @var string
     */
    protected $_imageUrl;

    /**
     * 字体名称
     *
     * @var sting
     */
    protected $_fontName;

    /**
     * 字体大小
     *
     * @var integer
     */
    protected $_fontSize;

    /**
     * 图片实例化名称
     *
     * @var object
     */
    protected $_image;

    /**
     * 图象宽度
     *
     * @var integer
     */
    protected $_width;

    /**
     * 图象高度
     *
     * @var integer
     */
    protected $_height;

    /**
     * 图片格式, 如:jpeg, gif, png
     *
     * @var string
     */
    protected $_type;

    /**
     * 字体颜色
     *
     * @var string
     */
    protected $_fontColor;

    /**
     * 背景的颜色
     *
     * @var string
     */
    protected $_bgColor;

    /**
     * 验证码内容
     *
     * @var string
     */
    protected $_textContent;

    /**
     * 生成验证码SESSION的名称,用于类外数据验证
     *
     * @var string
     */
    protected $_sessionName;


    /**
     * 构造方法
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->_fontSize     = 14;
        $this->_fontName     = DOIT_ROOT . '/views/source/aispec.ttf';
        $this->_sessionName  = 'doitphp_captcha_session_id';
        $this->_width        = 90;
        $this->_height       = 30;

        return true;
    }

    /**
     * 设置字体名称.
     *
     * @access public
     *
     * @param sting $name    字体名称(字体的路径)
     * @param integer $size  字体大小
     *
     * @return object
     */
    public function setFontName($name, $size = null) {

        if ($name) {
            $this->_fontName = $name;
        }
        if ($size) {
            $this->_fontSize = intval($size);
        }

        return $this;
    }

    /**
     * 设置字体大小.
     *
     * @access public
     *
     * @param integer $size 字体大小
     *
     * @return object
     */
    public function setFontSize($size) {

        if ($size) {
            $this->_fontSize = (int)$size;
        }

        return $this;
    }

    /**
     * 设置背景图片或水印图片的URL.
     *
     * @access public
     *
     * @param string $url 图片的路径(图片的实际地址)
     *
     * @return object
     */
    public function setBgImage($url) {

        if ($url) {
            $this->_imageUrl = $url;
        }

        return $this;
    }

    /**
     * 设置生成图片的大小.
     *
     * @access public
     *
     * @param integer $width    图片的宽度
     * @param integer $height   图片的高度
     *
     * @return object
     */
    public function setImageSize($width, $height) {

        if ($width) {
            $this->_width  = (int)$width;
        }
        if ($height) {
            $this->_height = (int)$height;
        }

        return $this;
    }

    /**
     * 设置验证码的sessionName.
     *
     * @access public
     *
     * @param string $name Session名称
     *
     * @return object
     */
    public function setSessionName($name) {

        if ($name) {
            $this->_sessionName = $name;
        }

        return $this;
    }

    /**
     * 设置验证码内容.
     *
     * @access public
     *
     * @param string $content 验证码内容
     *
     * @return object
     */
    public function setTextContent($content) {

        if ($content) {
            $this->_textContent = $content;
        }

        return $this;
    }

    /**
     * 获取颜色参数.
     *
     * @access public
     *
     * @param string $param 颜色参数. 如：#FF0000
     *
     * @return object
     */
    public function setTextColor($param) {

        //参数分析
        if (!$param) {
            return $this;
        }

        //将十六进制颜色值转化为十进制
        $x = hexdec(substr($param, 1, 2));
        $y = hexdec(substr($param, 3, 2));
        $z = hexdec(substr($param, 5, 2));

        $this->_fontColor = array($x, $y, $z);

        return $this;
    }

    /**
     * 获取背景的颜色参数
     *
     * @access public
     *
     * @param string $param    颜色参数. 如：#FF0000
     *
     * @return object
     */
    public function setBgColor($param) {

        //参数分析
        if (!$param) {
            return $this;
        }

        //将十六进制颜色值转化为十进制
        $x = hexdec(substr($param, 1, 2));
        $y = hexdec(substr($param, 3, 2));
        $z = hexdec(substr($param, 5, 2));

        $this->_bgColor = array($x, $y, $z);

        return $this;
    }

    /**
     * 生成验证码内容.
     *
     * @access protected
     * @return stirng
     */
    protected function getCaptchaContent() {

        if (!$this->_textContent) {
            $char = 'BCEFGHJKMPQRTVWXY2346789';
            $num1 = $char[mt_rand(0, 23)];
            $num2 = $char[mt_rand(0, 23)];
            $num3 = $char[mt_rand(0, 23)];
            $num4 = $char[mt_rand(0, 23)];
            $this->_textContent = $num1 . $num2 . $num3 . $num4;
        }

        return $this->_textContent;
    }

    /**
     * 验证码的判断
     *
     * @access public
     *
     * @param string $code 待验证的验证码内容
     *
     * @return boolean
     */
    public function check($code) {

        if (!$code) {
            return false;
        }
        $code = strtolower($code);

        //start session
        Session::start();

        return (isset($_SESSION[$this->_sessionName]) && (strtolower($_SESSION[$this->_sessionName]) == $code)) ? true : false;
    }

    /**
     * 显示验证码.
     *
     * @access public
     *
     * @param string $imageUrl 验证码的背影图片路径
     *
     * @return void
     */
    public function show($imageUrl = null) {

        //当前面没有session_start()调用时.
        Session::start();

        if (!is_null($imageUrl)) {
            $this->_imageUrl = trim($imageUrl);
        }

        $this->_image = (!function_exists('imagecreatetruecolor')) ? imagecreate($this->_width, $this->_height) : imagecreatetruecolor($this->_width, $this->_height);

        //当有背景图片存在时
        if ($this->_imageUrl) {

            //初始化图片信息.
            list($imageWidth, $imageHeight, $type) = getimagesize($this->_imageUrl);

            //分析图片的格式
            switch ($type) {
                case 1:
                    $image          = imagecreatefromgif ($this->_imageUrl);
                    $this->_type    = 'gif';
                    break;

                case 2:
                    $image          = imagecreatefromjpeg($this->_imageUrl);
                    $this->_type    = 'jpg';
                    break;

                case 3:
                    $image          = imagecreatefrompng($this->_imageUrl);
                    $this->_type    = 'png';
                    break;

                case 4:
                    $image          = imagecreatefromwbmp($this->_imageUrl);
                    $this->_type    = 'bmp';
                    break;
            }

            //背景
            $srcX = ($imageWidth > $this->_width) ? mt_rand(0, $imageWidth - $this->_width) : 0;
            $srcY = ($imageHeight > $this->_height) ? mt_rand(0, $imageHeight - $this->_height) : 0;
            imagecopymerge($this->_image, $image, 0, 0, $srcX, $srcY, $this->_width, $this->_height, 100);
            imagedestroy($image);

            //边框
            $borderColor   = imagecolorallocate($this->_image, 255, 255, 255);
            imagerectangle($this->_image, 1, 1, $this->_width - 2, $this->_height - 2, $borderColor);

        } else {

            //定义图片类型
            $this->_type     = 'png';

            //背景
            $bgColorArray   = (!$this->_bgColor) ? array(255, 255, 255) : $this->_bgColor;
            $back_color     = imagecolorallocate($this->_image, $bgColorArray[0], $bgColorArray[1], $bgColorArray[2]);
            imagefilledrectangle($this->_image, 0, 0, $this->_width -1, $this->_height - 1, $back_color);

            //边框
            $borderColor    = imagecolorallocate($this->_image, 238, 238, 238);
            imagerectangle($this->_image, 0, 0, $this->_width - 1, $this->_height - 1, $borderColor);
        }

        //获取验证码内容.
        $this->getCaptchaContent();

        //验证码中含有汉字
        if (!preg_match('~[\x{4e00}-\x{9fa5}]+~u', $this->_textContent)) {
            //计算验证码的位数
            $codeStrlen    = strlen($this->_textContent);
            //每位验证码所占用的图片宽度
            $perWidth      = ceil(($this->_width - 10)/ $codeStrlen);

            for($i = 0; $i < $codeStrlen; $i ++) {

                //获取单个字符
                $textContent = $this->_textContent[$i];

                $bbox        = imagettfbbox($this->_fontSize, 0, $this->_fontName, $textContent);
                $fontW       = $bbox[2]-$bbox[0];
                $fontH       = abs($bbox[7]-$bbox[1]);

                $fontX       = ceil(($perWidth - $fontW) / 2) + $perWidth * $i + 5;
                $min_y       = $fontH + 5;
                $max_y       = $this->_height -5;
                $fontY       = rand($min_y, $max_y);

                $fontColor   = (!$this->_fontColor) ? imagecolorallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->_image, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
                imagettftext($this->_image, $this->_fontSize, 0, $fontX, $fontY, $fontColor, $this->_fontName, $textContent);
            }
        } else {
            //分析验证码的位置
            $bbox            = imagettfbbox($this->_fontSize, 0, $this->_fontName, $this->_textContent);
            $fontW           = $bbox[2]-$bbox[0];
            $fontH           = abs($bbox[7]-$bbox[1]);
            $fontX           = ceil(($this->_width - $fontW) / 2) + 5;
            $min_y           = $fontH + 5;
            $max_y           = $this->_height -5;
            $fontY           = rand($min_y, $max_y);

            $fontColor       = (!$this->_fontColor) ? imagecolorallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($this->_image, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
            imagettftext($this->_image, $this->_fontSize, 0, $fontX, $fontY, $fontColor, $this->_fontName, $this->_textContent);
        }

        //干扰线
        for ($i = 0; $i < 5; $i ++) {
            $line_color = imagecolorallocate($this->_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imageline($this->_image, mt_rand(2, $this->_width - 3), mt_rand(2, $this->_height  - 3), mt_rand(2, $this->_width  - 3), mt_rand(2, $this->_height  - 3), $line_color);
        }

        //将显示的验证码赋值给session.
        Session::set($this->_sessionName, $this->_textContent);

        //当有headers内容输出时.
        if (headers_sent()) {
            Controller::halt('headers already sent');
        }

        //显示图片,根据背景图片的格式显示相应格式的图片.
        switch ($this->_type) {

            case 'gif':
                header('Content-type:image/gif');
                imagegif ($this->_image);
                break;

            case 'jpg':
                header('Content-type:image/jpeg');
                imagejpeg($this->_image);
                break;

            case 'png':
                header('Content-type:image/png');
                imagepng($this->_image);
                break;

            case 'bmp':
                header('Content-type:image/wbmp');
                imagewbmp($this->_image);
                break;
        }

        imagedestroy($this->_image);
    }
}