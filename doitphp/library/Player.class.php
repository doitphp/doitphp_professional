<?php
/**
 * 常用网页播放器
 *
 * Mp3、flv播放器及mediaPlayer，还有幻灯片效果
 *
 * @author tommy <streen003@gmail.com>
 * @copyright  Copyright (c) 2010 Tommy Software Studio
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Player.class.php 2.0 2012-12-23 09:52:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Player {

    /**
     * MP3 PLAYER JS脚本函数(flash 的MP3播放器)
     *
     * @access public
     *
     * @param string $fileUrl 音频文件访问网址
     * @param integer $width 播放器的宽度
     * @param integer $height 播放器的高度
     * @param boolean $autoPlay 是否自动播放 （true:是/false:不是）
     *
     * @return string
     */
    public static function mp3Player($fileUrl, $width = null, $height = null, $autoPlay=false) {

        //参数分析
        $width        = (!$width) ? 290 : $width;
        $height       = (!$height) ? 24 : $height;
        $autoPlay     = ($autoPlay==true) ? 'true' : 'false';

        $baseDirUrl = Controller::getAssetUrl('doit/images');

        return <<<EOT
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="{$width}" height="{$height}">
<param name="movie" value="{$baseDirUrl}/mp3.swf?soundFile={$fileUrl}&autostart={$autoPlay}&loop=yes" />
<param name="quality" value="high" />
<param value="transparent" name="wmode" />
<embed src="{$baseDirUrl}/mp3player.swf?soundFile={$fileUrl}&autostart={$autoPlay}&loop=yes" width="{$width}" height="{$height}" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash">
</embed>
</object>
EOT;
    }

    /**
     * FLV PLAYER JS脚本(视频播放器)
     *
     * @access public
     *
     * @param string $fileUrl Flv文件的访问网址
     * @param integer $width 播放器的宽度
     * @param integer $height 播放器的高度
     *
     * @return string
     */
    public static function flvPlayer($fileUrl, $width = null, $height = null) {

        //参数分析
        $width        = (!$width) ? 400 : $width;
        $height       = (!$height) ? 300 : $height;

        $baseDirUrl = Controller::getAssetUrl('doit/images');

        return <<<EOT
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{$width}" height="{$height}">
<param name="movie" value="{$baseDirUrl}/vcastr3.swf?vcastr_file={$fileUrl}&IsContinue=0&BarColor=0x000000&BarTransparent=50&GlowColor=0xffffff&IconColor=0xffffff"><param name="quality" value="high"><param name="allowFullScreen" value="true" /><param   name="wmode"   value="opaque" /><embed src="{$baseDirUrl}/vcastr3.swf?vcastr_file={$fileUrl}&IsContinue=0&BarColor=0x000000&BarTransparent=50&GlowColor=0xffffff&IconColor=0xffffff" allowFullScreen="true" quality="high" wmode="opaque"  pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="{$width}" height="{$height}"></embed></object>
EOT;
    }

    /**
     * 幻灯片效果 JS脚本.
     *
     * @access public
     *
     * @param array $fileUrl 所要播放的幻灯片的图片信息（注：本参数为二维数组）
     * @param integer $width 幻灯片播放器的宽度
     * @param integer $height 幻灯片播放器的高度
     *
     * @return string
     *
     * 参数说明:$fileUrl:
     *    array(
     *    array('link'=>'http://www.doitphp.com', 'pic'=>'/logo.jpg', 'text'=>'tommy framework'),
     *    array('link'=>'http://www.doitphp.com', 'pic'=>'/logo1.jpg', 'text'=>'bese php framework'),
     *    array('link'=>'http://www.doitphp.com', 'pic'=>'/logo3.jpg', 'text'=>'very easy framwork')
     *  );
     */

    public static function flashSlide($fileUrl, $width = null, $height = null) {

        //参数判断.
        if (!$fileUrl || !is_array($fileUrl) || !is_array($fileUrl[0])) {
            return false;
        }

        $baseDirUrl = Controller::getAssetUrl('doit/images');

        $contentStr="";
        foreach($fileUrl as $keys=>$lines) {
            $key = $keys+1;
            $contentStr.="linkarr[{$key}]=\"{$lines['link']}\";picarr[{$key}]= \"{$lines['pic']}\";textarr[{$key}]=\"{$lines['text']}\";";
        }
        //幻灯片的宽度和高度.
        $width  = (!$width) ? 280 : $width;
        $height = (!$height) ? 192 : $height;
        return <<<EOT
<script type='text/javascript'>linkarr = new Array();picarr = new Array();textarr = new Array();var swf_width={$width};var swf_height={$height};var files = "";var links = "";var texts = "";{$contentStr}for(i=1;i<picarr.length;i++){if(files=="") files = picarr[i];else files += "|"+picarr[i];}for(i=1;i<linkarr.length;i++){if(links=="")links = linkarr[i];else links += "|"+linkarr[i];}for(i=1;i<textarr.length;i++){if(texts=="")texts = textarr[i];else texts += "|"+textarr[i];}document.write('<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,24,0" width="'+ swf_width +'" height="'+ swf_height +'">');document.write('<param name="movie" value="{$baseDirUrl}/focus.swf"><param name="quality" value="high">');document.write('<param name="menu" value="false"><param name=wmode value="transparent">');document.write('<param name="FlashVars" value="bcastr_file='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'">');document.write('<embed src="{$baseDirUrl}/focus.swf" wmode="transparent" FlashVars="bcastr_file='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'& menu="false" quality="high" width="'+ swf_width +'" height="'+ swf_height +'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />');document.write('</object>');</script>
EOT;
    }

    /**
     * Mediaplayer在线音频播放器
     *
     * @access public
     *
     * @param string    $audioUrl    音频网址    (本参数支持微软的wma格式的音频文件)
     * @param integer    $width    播放器的宽度.默认为300px
     * @param integer    $height    播放器的高度.默认为64px
     * @param boolean    $autoPlay    是否自动播放.默认为自动播放
     *
     * @return string
     */
    public static function mediaPlayer($audioUrl, $width = null, $height = null, $autoPlay = true) {

        //parse params
        if (!$audioUrl) {
            return false;
        }
        //分析播放器的大小(宽度和高度)
        $width          = (int)$width;
        $height         = (int)$height;
        $width          = (!$width) ? 300 : $width;
        $height         = (!$height) ? 64 : $height;

        $autoPlay_state = (!$autoPlay) ? 0 : 1;

        return '<object classid="clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6" type="application/x-ms-wmp" width="' . $width . '" height="' . $height . '" border="0"><param name="url" value="' . $audioUrl . '"><param name="rate" value="1"><param name="balance" value="0"><param name="currentPosition" value="0"><param name="playCount" value="100"><param name="autoStart" value="' . $autoPlay_state . '"><param name="defaultFrame" VALUE=""><param name="volume" value="100"><param name="currentMarker" value="0"><param name="invokeURLs" value="-1"><param name="stretchToFit" value="-1"><param name="windowlessVideo" value="-1"><param name="enabled" value="-1"><param name="uiMode" value="Full"><param name="enableContextMenu" value="-1"><param name="fullScreen" value="0"><param name="SAMIStyle" value><param name="SAMILang" value><param name="SAMIFilename" value><param name="captioningID" value><param name="enableErrorDialogs" value="0"><embed id="Mediaplayer" src="' . $audioUrl . '" name="MediaPlayer" type="video/x-ms-wmv" width="' . $width . '" height="' . $height . '" autostart="' . $autoPlay_state . '" showcontrols="1" allowscan="1" playcount="1" enablecontextmenu="0"></embed></object>';
    }
}